define([
    'jquery',
    'jquery_ui_autocomplete',
    'bootstrap/datepicker',
    'bootstrap/datepicker/' + requirejs.s.contexts._.config.config.i18n.locale
], function($) {
    init_autocomplete = function(input, target, button) {
        $(document).ready(function() {
            $(input).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: $(this.element).data('url'),
                        dataType: 'json',
                        data : { term: request.term },
                        type: 'POST',
                        success: function(data) {
                            if (data.length > 0) {
                                response($.map(data, function(item) {
                                    return {
                                        label: item.name,
                                        value: item.name,
                                        id: item.id
                                    };
                                }));
                            }
                        }
                    });
                },
                select: function(event, ui) {
                    $(target).val(ui.item.id);
                    $(button).attr('disabled','disabled');
                },
                minLength: 3,
                delay: 300
            });

            $(button).click(function() {
                return $(this).attr("href", $(this).attr("href") + "/" + $(this).attr('data-stop'));
            });
        });
    };

    initCalendar = function(calendarId, startDate, endDate) {
        $.ajax({
            url : $('#calendar-view').data('url'),
            data: {
                'calendarId': calendarId,
                'startDate': startDate,
                'endDate': endDate
            },
            type: 'POST',
            success: function(data) {
                $('#calendar-view').datepicker({
                    language: requirejs.s.contexts._.config.config.i18n.locale,
                    beforeShowDay: function (date) {
                        // bootstrap fr bug (offset day of the week)
                        var fixDate = date;
                        fixDate.setDate(fixDate.getDate()+1);
                        strDate = fixDate.toISOString().slice(0,10).replace(/-/g,"");

                        if (data[strDate] == "1") {
                            return "red";
                        } else {
                            return "green";
                        }
                    }
                });
                //$('#calendar-view').datepicker('setDate', startDate);
                $('#calendar-view').datepicker('setDate', new Date());
                $('#calendar-view').datepicker('update');
            }
        });
    };
});
