jQuery(document).ready(function ($) {

    //var loginBlock = $("#linkid-login-block");
    //loginBlock.appendTo("#loginform").css("display", "table");

    var unloading = false;
    var canStartAuthnSession = false;
    var fetchedLoadingImg = false;

    window.onbeforeunload = function () {
        unloading = true;
    };

    $(window).unload(function () {
        unloading = true;
    });

    function init(initialize) {

        canStartAuthnSession = true;
        fetchedLoadingImg = false;

        $("#loginLink").click(function () {

            if (!canStartAuthnSession)
                return;

            $("#loginLink").remove();

            if (initialize) {
                var loginBlock = $("#linkid-login-block");
                loginBlock.css("display", "block");
                loginBlock.css("float", "left");
            }
            $("#linkid-action").html('<img style="width:100%" src="' + Link_LinkID_Login.loadingImgUrl + '" />');

            canStartAuthnSession = false;

            $.ajax({
                dataType: "json",
                url: Link_LinkID_Login.ajaxUrl,
                data: {
                    action: 'link_linkid_login_init',
                    isMerge: Link_LinkID_Login.isMerge
                },
                success: function (data) {
                    $("#loginMessages").remove();
                    if (data.linkIDState === 'ERROR') {

                        displayErrorMessage(data.errorMessage);
                        setTimeout(function () {
                            if (data.shouldRefresh) {
                                location.reload();
                            } else if (data.shouldRedirect) {
                                window.location.href = data.redirectUrl;
                            }
                        }, 500);

                    } else {

                        $("#linkid-action").html(
                            '<div class="qr-wrapper" id="linkIDQRImage" style="width:250px;">' +
                            '   <img class="qr-image" src="data:image/png;base64,' + data.qrCodeImageEncoded + '"/>' +
                            '</div>'
                        );

                        poll();
                    }
                },
                error: function () {
                    displayErrorMessage(Link_LinkID_Login.defaultErrorMessage);
                }
            });
        });
    }

    init(true);

    function displayErrorMessage(errorMessage) {
        //Set delay on error message to give unloading detector time to check
        setTimeout(function () {
            if (unloading) return;
            var html =
                '<p class="linkid-action-error-message">' + errorMessage + '</p>' +
                '<button class="linkid-action-button linkid-action-button-small linkid-action-button-retry" type="button" id="loginLink">' +
                Link_LinkID_Login.tryAgainButton +
                '</button>';

            $('#linkid-action').html(html);

            init();

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
            url: Link_LinkID_Login.ajaxUrl,
            data: {
                action: 'link_linkid_login_poll',
                isMerge: Link_LinkID_Login.isMerge,
                isWC: Link_LinkID_Login.isWC
            },
            success: function (data) {
                if (data.linkIDState === "AUTH_STATE_RETRIEVED" && !fetchedLoadingImg) {
                    $("#linkIDQRImage").html('<img style="width:100%" src="' + Link_LinkID_Login.loadingImgUrl + '" />');
                    fetchedLoadingImg = true;
                }

                if (data.linkIDState === "ERROR") {
                    displayErrorMessage(data.errorMessage);
                    if (data.shouldRefresh) {
                        location.reload();
                    }
                }
                else if (data.linkIDState === "AUTH_STATE_EXPIRED") {
                    displayErrorMessage(Link_LinkID_Login.expiredMessage);
                }
                else if (data.linkIDState === "AUTH_STATE_AUTHENTICATED") {
                    displaySuccessMessage(Link_LinkID_Login.successMessage);
                    setTimeout(function () {
                        if (Link_LinkID_Login.redirectUrl || data.redirectUrl) {
                            window.location.href = Link_LinkID_Login.redirectUrl ? Link_LinkID_Login.redirectUrl : data.redirectUrl;
                        } else if (data.shouldRefresh) {
                            location.reload();
                        }
                    }, 250);
                }
                else {
                    setTimeout(poll, 1000);
                }

            },
            error: function () {
                displayErrorMessage(Link_LinkID_Login.defaultErrorMessage)
            }
        });

    }
});