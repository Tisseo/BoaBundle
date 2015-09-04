define(['jquery'], function($) {
    $(document).ready(function() {
        // adapting lineVersion field display/value looking at selected calendarType
        $('.calendar-form .calendar-type').on('change', function() {
            if ($.inArray($(this).val(), $(this).data('types')) >= 0) {
                $('.calendar-form .line-version').removeClass('hidden');
            } else {
                $('.calendar-form .line-version').addClass('hidden');
                $('#boa_calendar_lineVersion').prop('selectedIndex',0);
            }
        });
    });
});
