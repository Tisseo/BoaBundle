define(['jquery', 'translations/messages'], function($) {
    $(document).ready(function() {
        $(document).on("click", ".line-version-locked", function(event) {
            console.log(event);
            event.preventDefault();
            console.log(event);

            var response = confirm(Translator.trans('line-version.locked_alert', {}, 'messages'));
            if (response === true)
                window.location = $(this).attr('href');
        });
    });
});
