define(['jquery', 'bootstrap/datepicker', 'bootstrap/datepicker/'+global.locale, 'jquery_ui_sortable', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    var init_autocomplete = function() {
        $('#calendar-elements-list #calendar-search').autocomplete({
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
                        } else {
                            $('#calendar-elements-list #boa_calendar_element_includedCalendar').val('');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#calendar-elements-list #boa_calendar_element_includedCalendar').val(ui.item.id);
            },
            minLength: 1,
            delay: 300
        });
    };

    var init_bootstrap_datepicker = function() {
        $('.input-date').datepicker({
            language: global.locale,
            startView: 1,
            autoclose: true
        });
    };

    var init_calendar = function(startDate, endDate) {
        var calendarId = $('#calendar-elements-list').data('calendar-id');
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
                    language: global.locale,
                    beforeShowDay: function (date) {
                        // bootstrap fr bug (offset day of the week)
                        var fixDate = date;
                        fixDate.setDate(fixDate.getDate()+1);
                        strDate = fixDate.toISOString().slice(0,10).replace(/-/g,"");

                        if (data[strDate] == "1")
                            return "green";
                    }
                });
                $('#calendar-view').datepicker('setDate', startDate);
                $('#calendar-view').datepicker('update');
            }
        });
    };

    $(document).ready(function() {
        // autocomplete field on inlcuded_calendar text input
        init_autocomplete();
        init_bootstrap_datepicker();

        var startDate = new Date($('#boa_calendar_computedStartDate').val().split('/').reverse().join('-'));
        var endDate = new Date($('#boa_calendar_computedEndDate').val().split('/').reverse().join('-'));

        init_calendar(startDate, endDate);

        // adapting lineVersion field display/value looking at selected calendarType
        $('.calendar-form .calendar-type').on('change', function() {
            if ($.inArray($(this).val(), $(this).data('types')) >= 0) {
                $('.calendar-form .line-version').removeClass('hidden');
            } else {
                $('.calendar-form .line-version').addClass('hidden');
                $('#boa_calendar_lineVersion').prop('selectedIndex',0);
            }
        });

        // validating calendar-element form
        $(document).on('click', '#calendar-elements-list #apply-calendar-element-form', function(event) {
            event.preventDefault();
            $(this).attr('disabled', 'disabled');
            var calendarId = $('#calendar-elements-list').data('calendar-id');
            var $inputs = $('#calendar-elements-list .new-calendar-element :input');
            var data = {};
            $inputs.each(function() {
                data[this.name] = $(this).val();
            });
            var getForm = false;
            var rank = null;
            $.ajax({
                url : Routing.generate('tisseo_boa_calendar_element_create', {'calendarId': calendarId}),
                type: 'POST',
                data : data,
                success: function(data) {
                    if (data.content)
                    {
                        if ($(data.content).is('form'))
                        {
                            $('#calendar-elements-list .new-calendar-element').html(data.content);
                            init_autocomplete();
                            init_bootstrap_datepicker();
                            $(this).removeAttr('disabled');
                        }
                        else if ($(data.content).is('tr'))
                        {
                            getForm = true;
                            rank = parseInt($(data.content).find('td:first').html()) + 1;
                            if (rank === 2) {
                                $('#calendar-elements-list #delete-calendar-elements').removeClass('hidden');

                            }
                            $('#calendar-elements-list #table-calendar-elements tbody:first').append(data.content);
                        }
                    }
                }
            }).promise().then(function() {
                if (getForm === true)
                {
                    $.ajax({
                        url : Routing.generate('tisseo_boa_calendar_element_form', {'calendarId': calendarId, 'rank': rank}),
                        type: 'GET',
                        success: function(data) {
                            $('#calendar-elements-list .new-calendar-element').html(data);
                            init_autocomplete();
                            init_bootstrap_datepicker();
                        }
                    });
                    $(this).removeAttr('disabled');
                }
            });
        });

        // deleting a specific calendar-element
        $(document).on('click', '#calendar-elements-list .delete-calendar-element', function() {
            $(this).attr('disabled', 'disabled');
            var row = $(this).parent().parent();

            $(row).nextAll().find('td.calendar-element-rank').each(function() {
                $(this).html($(this).html() - 1);
            });

            formRank = $(document).find('#calendar-elements-list tr.new-calendar-element #boa_calendar_element_rank');
            formRank.val(formRank.val()-1);

            $(row).fadeOut(300, function() {
                $(this).remove();
                if ($('#calendar-elements-list tr.calendar-element').length === 0) {
                    $('#calendar-elements-list #delete-calendar-elements').addClass('hidden');
                }
            });
        });

        // submitting whole calendar-elements form
        $(document).on('click', '#calendar-elements-list #submit-calendar-elements', function() {
            var calendarId = $('#calendar-elements-list').data('calendar-id');
            var calendarElements = $('#calendar-elements-list tr.calendar-element');

            var data = [];
            calendarElements.each(function() {
                var calendarElement = {};
                var $inputs = $(this).find('input');
                $inputs.each(function() {
                    if ($.inArray(this.name, ['calendar', 'includedCalendar']) >= 0)
                        calendarElement[this.name] = {'id': $(this).val()};
                    else 
                        calendarElement[this.name] = $(this).val();
                });
                calendarElement.rank = $(this).find('td.calendar-element-rank').html();
                data.push(calendarElement);
            });
            $.ajax({
                url : Routing.generate('tisseo_boa_calendar_element_edit', {'calendarId': calendarId}),
                type: 'POST',
                data : JSON.stringify(data),
                success: function(data) {
                    if (data.status === true && data.location) {
                        window.location.replace(data.location);
                    } else if (data.error) {
                        $('#error-container').find('span').remove();
                        $('#error-container').prepend('<span>'+data.error+'</span>').removeClass('hidden');
                    }
                }
            });
        });

        // deleting all calendar elements
        $(document).on('click', '#calendar-elements-list #delete-calendar-elements', function() {
            var calendarId = $('#calendar-elements-list').data('calendar-id');
            var data = [];
            $.ajax({
                url : Routing.generate('tisseo_boa_calendar_element_edit', {'calendarId': calendarId}),
                type: 'POST',
                data : JSON.stringify(data),
                success: function(data) {
                    if (data.status === true && data.location) {
                        window.location.replace(data.location);
                    } else if (data.error) {
                        $('#error-container').find('span').remove();
                        $('#error-container').prepend('<span>'+data.error+'</span>').removeClass('hidden');
                    }
                }
            });
        });

    });
});
