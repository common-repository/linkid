jQuery(document).ready(function ($) {
    var requiredInput = $(":input[required]");

    function validateElement(element) {
        if ($.trim(element.val()) == "") {
            if (!element.hasClass("redborder")) {
                element.addClass("redborder");
                element.after('  <span style="color:red;">' + Link_LinkID_Settings.requiredText + '</span>');
            }
        } else {
            element.removeClass("redborder");
            element.next('span').remove();
        }
    }

    requiredInput.each(function () {
        validateElement($(this));
    });
    requiredInput.blur(function () {
        validateElement($(this));
    });
});

