<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TomorrowIdeas\Plaid\Entities\User;
use TomorrowIdeas\Plaid\Plaid;
use Illuminate\Support\Facades\Log;
use App\Models\PlaidAccounts;
use App\Models\Holdings;

class PlaidController extends Controller
{
    public function createLinkToken()
    {
        Log::info('-----------------------------------------');
        Log::info('Creating new link token');
        $user_id = 1;
        $plaidUser = new User($user_id);
        $plaid = new Plaid(env('PLAID_CLIENT_ID'), env('PLAID_SECRET'), env('PLAID_ENV'));
        $response = $plaid->tokens->create('Plaid Test', 'en', ['US'], $plaidUser, ['investments'], env('PLAID_WEBHOOK'));
        Log::info('Plaid link_token - User: ' . $user_id . ', ' . json_encode($response));
        return response()->json([
            'result' => 'success',
            'data' => json_encode($response)
        ], 200);
    }

    public function storePlaidAccount(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'public_token' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'message' => $validator->errors()], 201);
        }

        $user_id = 1;
        Log::info('-----------------------------------------');
        Log::info('Plaid public_token : ' . $request->public_token . ', link_token: ' . $request->link_token);
        $plaid = new Plaid(env('PLAID_CLIENT_ID'), env('PLAID_SECRET'), env('PLAID_ENV'));
        $obj = $plaid->items->exchangeToken($request->public_token);
        Log::info('Plaid exchange token : ' . json_encode($obj));

        try {
            \DB::transaction(function () use($request, $obj, $user_id) {
                foreach($request->accounts as $account) {
                    $query = PlaidAccounts::where('account_id', isset($account['id']) ? $account['id'] : $account['account_id']);
                    if ($query->count() > 0) {
                        Log::info('[Update Plaid Account]: ' . json_encode($account));
                        $new_account = $query->first();
                        $new_account->plaid_item_id = $obj->item_id;
                        $new_account->plaid_access_token = $obj->access_token;
                        $new_account->plaid_public_token = $request->public_token;
                        $new_account->link_session_id = $request->link_session_id;
                        $new_account->link_token = $request->link_token;
                        $new_account->institution_id = $request->institution['institution_id'];
                        $new_account->institution_name = $request->institution['name'];
                        $new_account->account_id = isset($account['id']) ? $account['id'] : $account['account_id'];
                        $new_account->account_name = isset($account['name']) ? $account['name'] : $account['account_name'];
                        $new_account->account_mask = isset($account['account_number']) ? $account['account_number'] : $account['mask'];
                        $new_account->account_mask = null;
                        $new_account->account_type = isset($account['type']) ? $account['type'] : $account['account_type'];
                        $new_account->account_subtype = isset($account['subtype']) ? $account['subtype'] : $account['account_sub_type'];
                        $new_account->user_id = $user_id;
                        $new_account->save();
                    } else {
                        Log::info('[New Plaid Account]: ' . json_encode($account));
                        $new_account = ([
                            'plaid_item_id' => $obj->item_id,
                            'plaid_access_token' => $obj->access_token,
                            'plaid_public_token' => $request->public_token,
                            'link_session_id' => $request->link_session_id,
                            'link_token' => $request->link_token,
                            'institution_id'    => $request->institution['institution_id'],
                            'institution_name' => $request->institution['name'],
                            'account_id' => isset($account['id']) ? $account['id'] : $account['account_id'],
                            'account_name' => isset($account['name']) ? $account['name'] : $account['account_name'],
                            'account_mask' => isset($account['account_number']) ? $account['account_number'] : $account['mask'],
                            'account_mask' => null,
                            'account_type' => isset($account['type']) ? $account['type'] : $account['account_type'],
                            'account_subtype' => isset($account['subtype']) ? $account['subtype'] : $account['account_sub_type'],
                            'user_id' => $user_id
                        ]);
                        PlaidAccounts::create($new_account);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('An error occurred linking a Plaid account: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred attempting to link a Plaid account.'
            ], 200);
        }
        return response()->json([
            'message' => 'Successfully linked plaid account.',
            'item_id' => $obj->item_id
        ], 200);
    }


    public function getInvestmentHoldings(Request $request) {
        if ($request->itemId != NULL) {
            $account = PlaidAccounts::where('plaid_item_id', $request->itemId)->first();
            Log::info('Account pulled: ' . $account);
        }

        if (!isset($plaid)) {
            $plaid = new Plaid(env('PLAID_CLIENT_ID'), env('PLAID_SECRET'), env('PLAID_ENV'));
        }


        try {
            \DB::transaction(function () use ($plaid, $account) {
                try {
                    $results = $plaid->investments->listHoldings($account->plaid_access_token);
                    $account->last_update = new \DateTime();
                    $account->last_status = '';
                    $account->save();
                } catch (\Exception $e) {
                    $response = json_decode(json_encode($e->getResponse(), true), true);
                    $account->last_status = $response['error_code'];
                    $account->save();
                    Log::error('Error pulling holdings from Plaid: '.$response['error_code']);
                    return response()->json(['error' => $e->getMessage()], 404);
                }

                foreach ($results->holdings as $holding) {
                    $user_id = 1;
                    
                    $holdingObj = ([
                        'holding_id' => $holding->security_id,
                        'user_id' => $user_id,
                        'cost_basis' => $holding->cost_basis,
                        'price' => $holding->institution_price
                    ]);
                    Holdings::create($holdingObj);
                }
            });
        } catch (PlaidRequestException $e) {
            Log::error('Adding holdings failed: ' . json_encode($e->getResponse()));
            return [
                'result' => 'error',
                'message' => $e
            ];
        }

        return [
            'result' => 'success',
            'message' => 'Successfully added holdings from Plaid.'
        ];
    }
}