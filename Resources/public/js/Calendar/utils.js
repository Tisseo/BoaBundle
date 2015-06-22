define(['jquery', 'jquery_ui_autocomplete', 'bootstrap/datepicker', 'bootstrap/datepicker/fr', 'translations/messages'], function($) {

        function init_autocomplete(selector){
            $(selector).autocomplete({
                source: function (request, response) {
                    var objData = { term: request.term };
                    var url = $(this.element).attr('data-url');
                    $.ajax({ url: url, dataType: "json", data : objData,  type: 'POST',
                        success: function (data) {
                            response($.map(JSON.parse(data.content), function (item) {
                                return {
                                    label: item.name,
                                    value: item.name,
                                    id: item.id
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    var hidden_id = '#' + $(this).attr('id').replace("calendarName", "includedCalendar");
                    $(hidden_id).val(ui.item.id);
                },
                minLength: 2,
                delay: 300
            });
        };


    function _displayCalendar(calendarDiv, FirstDate, LastDate, calendarPattern){
        var Beginning = new Date(FirstDate);
        Beginning.setHours(0,0,0,0);
        var Ending = new Date(LastDate);
        Ending.setHours(0,0,0,0);

        var table_html = "<table class='calendar_table'>";
        table_html += "<thead><tr><th></th><th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th></tr></thead>";

        //days before month
        if(Beginning.getDay() != 1){
            table_html += "<tr>";
            table_html += "<td>" + Beginning.toDateString().split(' ')[1] + " " + Beginning.toDateString().split(' ')[3] + "</td>";
            for (var diffDays=(Beginning.getDay()+6)%7; diffDays >0; diffDays--) {
                table_html += "<td></td>";
            }
        } else {
            if(Beginning.getDay() == 1){
                table_html += "<tr>";
                table_html += "<td>" + Beginning.toDateString().split(' ')[1] + " " + Beginning.toDateString().split(' ')[3] + "</td>";
            }
        }

        var pattern_index = 1;
        var changeMonth = false;
        for (var currentDate = Beginning; currentDate <= Ending; currentDate.setDate(currentDate.getDate() + 1)) {
            var td_class = "calendar_cell ";
            if(changeMonth) {
                if(currentDate.getDate() > 7) {
                    td_class += "last_week_of_month ";
                } else {
                    if(currentDate.getDate() == 1) td_class += "first_day_of_month ";
                    if(currentDate.getDate() >= 1) td_class += "first_week_of_month ";
                    if(currentDate.getDay() == 0) changeMonth = false;
                }
            }
            if(calendarPattern[pattern_index] ==  1)  {
                td_class += "accessible ";
            } else {
                td_class += "inaccessible ";
            }
            table_html += "<td class='" + td_class + "'>" + currentDate.getDate() + "</td>";

            if(currentDate.getDay() == 0){
                if(+currentDate == +Ending) {
                    table_html += "</tr>";
                } else {
                    table_html += "</tr><tr>";

                    var next_week_month = new Date(currentDate.getTime());
                    next_week_month.setDate(next_week_month.getDate() + 7);
                    if(next_week_month.getMonth() != currentDate.getMonth()) {
                        changeMonth = true;
                        table_html += "<td class='month_cell'>" + next_week_month.toDateString().split(' ')[1] + " " + next_week_month.toDateString().split(' ')[3] + "</td>";
                    } else {
                        table_html += "<td class='month_cell'></td>";
                    }
                }
            }
            pattern_index += 1;
        }
        table_html += "</table>";
        $(calendarDiv).html(table_html);
    };

    return {
        initStandardDateInputs: function(startDatePickerId, endDatePickerId, pickerClass) {
            var today = new Date();
            var firstDate = new Date(today.getFullYear(), today.getMonth(), 1);
            var lastDate = new Date(today.getFullYear(), today.getMonth()+2, 0);
            var month = (firstDate.getMonth()<10 ? "0" + firstDate.getMonth() : firstDate.getMonth());
            var sFirstDate = '01/' + month + '/' + firstDate.getFullYear();

            $(startDatePickerId).val(sFirstDate);
            $(startDatePickerId).datepicker('update');

            month = (lastDate.getMonth()<10 ? "0" + (lastDate.getMonth()+1) : (lastDate.getMonth()+1));
            var sLastDate = lastDate.getDate() + '/' + month + '/' + firstDate.getFullYear();
            $(endDatePickerId).val(sLastDate);
            $(endDatePickerId).datepicker('update');


            var previousDate;
            $(pickerClass).datepicker({
                language: 'fr',
                todayHighlight: true,
                autoclose: true
            })
            // Save date picked
            .on('show', function () {
                previousDate = $(this).val();
            })
            // Replace with previous date if no date is picked or if same date is picked to avoid toggle error
            .on('hide', function () {
                if ($(this).val() === '' || $(this).val() === null) {
                    $(this).val(previousDate).datepicker('update');
                }
            })
            .on('changeDate', function(e) {
                if (!($(this).val() === '' || $(this).val() === null)) {
                    $(e.target.name).datepicker('update');
                }
            });
        },
        initRefreshCalendarClick: function(calendarDivId, buttonId, startDateId, endDateId, Calendar1Id, Calendar2Id) {
            var url = $(buttonId).attr('data-url');
            var start_array = $(startDateId).val().split("/");
            var start_date  =  start_array[2] + '-' + start_array[1] + '-' + start_array[0];
            var end_array = $(endDateId).val().split("/");
            var end_date  =  end_array[2] + '-' + end_array[1] + '-' + end_array[0];

            var objData = {
                id1: $(Calendar1Id).val(),
                id2: $(Calendar2Id).val(),
                startDate: start_date,
                endDate: end_date
            };

            $.ajax({ url: url, data : objData, type: 'POST',
                success : function(data){
                    _displayCalendar(calendarDivId, start_date, end_date, data.content);
                }
            });

        },
        displayCalendar: function(calendarDiv, FirstDate, LastDate, calendarPattern) {
            _displayCalendar(calendarDiv, FirstDate, LastDate, calendarPattern);
        },
        addCalendarElement: function(tableId, selector) {
            var $index = $(tableId + ' >tbody >tr').length + 1;
            var $prototype = $(tableId).attr('data-prototype');
            var $newLine = $prototype.replace(/__name__/g, $index);
            $(tableId + ' >tbody').append(
                "<tr>" + $newLine +
                "<td><a class=\"btn btn-default\" href=\"#\"><span class=\"glyphicon glyphicon-remove\"></span>" +
                Translator.trans('global.delete', {}, 'messages') +
                "</a></td></tr>");

            init_autocomplete(selector);

            $('.element-date').datepicker({
                language: 'fr',
                todayHighlight: true,
                startView: 1,
                autoclose: true
            });
        }
    }
});