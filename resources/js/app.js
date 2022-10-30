import './bootstrap';

$(".link-account").click(function() {
    createLinkToken();
});

function createLinkToken() {
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        url: "/createLinkToken",
        type: "GET",
        dataType: "json",
        success: function (response) {
            const data = JSON.parse(response.data);
            console.log('Link Token: ' + data.link_token);
            linkPlaidAccount(data.link_token);
        },
        error: function (err) {
            console.log('Error creating link token.');
            const errMsg = JSON.parse(err);
            alert(err.error_message);
            console.error("Error creating link token: ", err);
        }
    });
}

function linkPlaidAccount(linkToken) {
    var linkHandler = Plaid.create({
        token: linkToken,
        onSuccess: function (public_token, metadata) {
            var body = {
                public_token: public_token,
                accounts: metadata.accounts,
                institution: metadata.institution,
                link_session_id: metadata.link_session_id,
                link_token: linkToken
            };
            $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                url: "/storePlaidAccount",
                type: "POST",
                data: body,
                dataType: "json",
                success: function (data) {
                    getInvestmentHoldings(data.item_id);
                },
                error: function (err) {
                    console.log('Error linking Plaid account.');
                    const errMsg = JSON.parse(err);
                    console.error("Error linking Plaid account: ", err);
                }
            });
        },
        onExit: function (err, metadata) {
            console.log("linkBankAccount error=", err, metadata);
            const errMsg = JSON.parse(err);
                    console.error("Error linking Plaid account: ", err);

            linkHandler.destroy();
            if (metadata.link_session_id == null && metadata.status == "requires_credentials") {
                createLinkToken();
            }
        }
    });
    linkHandler.open();
}

function getInvestmentHoldings(itemId) {
    var body = {
        itemId: itemId,
    };
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        url: "/getInvestmentHoldings",
        type: "POST",
        data: body,
        dataType: "json",
        success: function (data) {
            console.log("Plaid holdings successfully imported.");
        },
        error: function (err) {
            const errMsg = JSON.parse(err);
            alert(err.error_message);
            console.error("Error importing holdings from Plaid: ", err);
        }
    });
}