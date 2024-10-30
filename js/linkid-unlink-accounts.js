jQuery(document).ready(function ($) {

    var unloading = false;
    var canUnlink = true;

    window.onbeforeunload = function () {
        unloading = true;
    };

    $(window).unload(function () {
        unloading = true;
    });

    function displayErrorMessage(errorMessage) {
        //Set delay on error message to give unloading detector time to check
        setTimeout(function () {
            if (unloading) return;
            var html = '<p class="message error-message">' + errorMessage + '</p>';
            $('#linkid-unlink-action').html(html);
        }, 250);
    }

    function displaySuccessMessage(successMessage) {
        //Set delay on error message to give unloading detector time to check
        setTimeout(function () {
            if (unloading) return;
            var html = '<p class="message success">' + successMessage + '</p>';
            $('#linkid-unlink-action').html(html);
        }, 250);
    }

    $("#unlinkLink").click(function () {

        if (!canUnlink)
            return;

        canUnlink = false;


        $("#linkid-unlink-action").html('<img style="width:60px;" src="' + Link_LinkID_Login.loadingImgUrl + '" />');

        $.ajax({
            dataType: "json",
            url: Link_LinkID_Login.ajaxUrl,
            data: {
                action: 'link_linkid_unlink'
            },
            success: function (data) {

                console.log(data);

                if (data.status === 'ERROR') {

                    if (data.message) {
                        displayErrorMessage(data.message);
                    } else {
                        displayErrorMessage(Link_LinkID_Unlink.defaultErrorMessage);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else if (data.status === 'SUCCESS') {

                    console.log("state changed to success. Should reload");

                    if (data.message) {
                        displaySuccessMessage(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 250);

                }
            },
            error: function () {
                displayErrorMessage(Link_LinkID_Unlink.defaultErrorMessage);
            }
        });


    });
});