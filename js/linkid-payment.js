jQuery(document).ready(function ($) {

    var unloading = false;
    var fetchedLoadingImg = false;
    var hasRetryButton = false;

    window.onbeforeunload = function () {
        unloading = true;
    };

    $(window).unload(function () {
        unloading = true;
    });

    function displayErrorMessage(errorMessage, canRetry) {
        //Set delay on error message to give unloading detector time to check
        setTimeout(function () {
            if (unloading) return;
            var html = '<p class="linkid-action-error-message">' + errorMessage + '</p>';
            $('#linkid-action').html(html);
            if (canRetry) {
                if (!hasRetryButton) {
                    hasRetryButton = true;
                    var retryHtml =
                        '<button class="linkid-action-button linkid-action-button-small" type="button" id="retryPaymentLink">' +
                        Link_LinkID_Payment.retryPaymentButtonText +
                        '</button>';
                    $(retryHtml).click(
                        function () {
                            if (hasRetryButton) {
                                $('#retryPaymentLink').remove();
                                location.reload();
                            }
                        }
                    ).appendTo('#linkid-action');
                }
            }
        }, 250);
    }

    function displaySuccessMessage(successMessage) {
        //Set delay on error message to give unloading detector time to check
        setTimeout(function () {
            if (unloading) return;
            var html = '<p class="message success">' + successMessage + '</p>';
            $('#linkid-action').html(html);
        }, 250);
    }

    function poll() {

        $.ajax({
            dataType: "json",
            url: Link_LinkID_Payment.ajaxUrl,
            data: {
                action: 'link_linkid_payment_poll'
            },
            success: function (data) {
                if (data.linkIDState === "AUTH_STATE_RETRIEVED" && !fetchedLoadingImg) {
                    $("#linkid-action").html('<img style="width:100%" src="' + Link_LinkID_Payment.loadingImgUrl + '" />');
                    fetchedLoadingImg = true;
                }
                if (data.linkIDState === "ERROR") {
                    if (data.shouldRefresh) {
                        displayErrorMessage(data.errorMessage);
                        location.reload();
                    } else if (data.canRetry) {
                        displayErrorMessage(data.errorMessage, true);
                    }
                }
                else if (data.linkIDState === "AUTH_STATE_AUTHENTICATED") {
                    if (data.redirectUrl) {
                        displaySuccessMessage(Link_LinkID_Payment.paymentSuccessRedirectMessage);
                        setTimeout(function () {
                            window.location.href = data.redirectUrl;
                        }, 250);
                    } else {
                        displayErrorMessage(data.responseMessage, false)
                    }
                }
                else if (data.linkIDState === "AUTH_STATE_PAYMENT_ADD") {
                    displaySuccessMessage(Link_LinkID_Payment.paymentAddRedirectMessage);
                    setTimeout(function () {
                        window.location.href = data.redirectUrl;
                    }, 250);
                }
                else {
                    setTimeout(poll, 1000);
                }

            },
            error: function () {
                displayErrorMessage(Link_LinkID_Payment.somethingWentWrongMessage, true)
            }
        });
    }

    poll();
});