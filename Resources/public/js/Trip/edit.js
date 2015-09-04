define(['jquery', 'bootstrap/datepicker', 'bootstrap/datepicker/'+global.locale], function($) {
    var initCalendar = function() {
        $.ajax({
            url : $('#calendar-view').data('url'),
            data: {
                'dayCalendarId': $('#calendar-view').data('day-calendar-id'),
                'periodCalendarId': $('#calendar-view').data('period-calendar-id'),
                'bitmask': true
            },
            type: 'POST',
            success: function(data) {
                $('#calendar-view').datepicker({
                    language: global.locale,
                    beforeShowDay: function (date) {
                        // bootstrap fr bug (offset day of the week)
                        var fixDate = date;
                        fixDate.setDate(fixDate.getDate()+1);
                        strDate = fixDate.toISOString().slice(0,10).replace(/-/g,"");
                        if (data[strDate] === "1")
                            return "green";
                    }
                });
                $('#calendar-view').datepicker('setDate', new Date($('#calendar-view').data('start-date')));
                $('#calendar-view').datepicker('update');
            }
        });
    };

    $(document).ready(function() {
        initCalendar();
    });
});
