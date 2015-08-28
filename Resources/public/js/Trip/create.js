define(['jquery', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    var newTripForm = function() {
        var index = $('#stop-times').children().length;
        $('#stop-times').append($('#stop-time-container').children().clone());
        $('#stop-times').find('input#begin').last().attr('name', 'stopTimes['+index+'][begin]');
        $('#stop-times').find('input#frequency').last().attr('name', 'stopTimes['+index+'][frequency]');
        $('#stop-times').find('input#end').last().attr('name', 'stopTimes['+index+'][end]');
    };

    var addCalendarError = function() {
        $('#period-calendar').val('').parent().addClass('has-error');
        $('#day-calendar').parent().addClass('has-error');

        if (!$('.modal-body').find('.errors').length) {
            var error = Translator.trans('tisseo.boa.trip.validation.calendar_intersection');
            errorDiv = "<div class='errors alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>"+error+"</div>";
            $('.modal-body').prepend(errorDiv);
        }
    };

    var removeCalendarError = function() {
        $('#period-calendar').parent().removeClass('has-error');
        $('#day-calendar').parent().removeClass('has-error');
        $('.modal-body').find('.errors').remove();
    };

    var checkCalendarsIntersection = function() {
        var dayCalendarId = $('#boa_trip_create_dayCalendar').val();
        var periodCalendarId = $('#boa_trip_create_periodCalendar').val();

        if (dayCalendarId && periodCalendarId)
        {
            $.ajax({
                url: Routing.generate('tisseo_boa_calendar_intersection'),
                dataType: 'json',
                data : {'dayCalendarId': dayCalendarId, 'periodCalendarId': periodCalendarId},
                type: 'POST',
                success: function (data) {
                    if (!data)
                    {
                        $('#boa_trip_create_periodCalendar').val('');
                        addCalendarError();
                    } else {
                        removeCalendarError();
                    }
                }
            });
        }
    };

    initialize = function() {
        $(document).ready(function () {
            $('#day-calendar').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: $(this.element).data('url'),
                        dataType: 'json',
                        data : { term: request.term },
                        type: 'POST',
                        success: function (data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.name,
                                    id: item.id
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    $('#boa_trip_create_dayCalendar').val(ui.item.id);
                    checkCalendarsIntersection();
                },
                minLength: 1,
                delay: 300
            });

            $('#period-calendar').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: $(this.element).data('url'),
                        dataType: 'json',
                        data : { term: request.term },
                        type: 'POST',
                        success: function (data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.name,
                                    id: item.id
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    $('#boa_trip_create_periodCalendar').val(ui.item.id);
                    checkCalendarsIntersection();
                },
                minLength: 1,
                delay: 300
            });

            $('#add-stop-time-block').click(function(event) {
                event.preventDefault();
                newTripForm();
            });

            newTripForm();
        });
    };
});
