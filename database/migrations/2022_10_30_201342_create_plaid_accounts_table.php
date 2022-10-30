<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaidAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plaid_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('plaid_item_id');
            $table->string('plaid_access_token');
            $table->string('plaid_public_token');
            $table->string('link_session_id');
            $table->string('link_token');
            $table->string('institution_id');
            $table->string('institution_name');
            $table->string('account_id');
            $table->string('account_name');
            $table->string('account_mask')->nullable();
            $table->string('account_type');
            $table->string('account_subtype');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->datetime('last_update')->nullable();
            $table->string('last_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plaid_accounts');
    }
}