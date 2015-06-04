define(['jquery', 'jquery_ui_autocomplete'  , 'fosjsrouting'], function($) {
    function FormatTime(t) {
        var h = Math.floor(t/3600);
        var m = (t - h*3600)/60;
        if( h < 10 ) h = "0"+h;
        if( m < 10 ) m = "0"+m;
        return h+":"+m;
    }

    function TimeToInt(t) {
        if( !t ) t = '00:00';
        var tmp = t.split(':');
        return res = tmp[0]*3600 + tmp[1]*60;
    }

    function serviceHeaderHTML(service_index, trip_id, trip_name, trip_is_instantiated) {
        var name_pattern = "services[" + service_index + "]";
        var service_header = "<th>";
        service_header += "<input type='hidden' name='" + name_pattern + "[id]' value=" + trip_id + ">";
        service_header += "<div><div style='display: table-cell;vertical-align: middle;'>";
        if( trip_is_instantiated ) 
            service_header += "<input type='hidden' name='" + name_pattern + "[name]' value='" + trip_name + "'>" + trip_name;
        else
            service_header += "<input type='text' name='" + name_pattern + "[name]' class='form-control' style='float:left' required value='" + trip_name + "'>";

        service_header += "</div><div style='position:relative;display: table-cell'>";
        service_header += "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' style='float:right'>";
        service_header += "<span class='caret'></span></button>";
        service_header += "<ul class='dropdown-menu  dropdown-menu-right'>";
        service_header += "<li><a class='btn duplicate-trip' role='button' href=''>Dupliquer</a></li>";
        if( !trip_is_instantiated ) 
            service_header += "<li><a class='btn delete-trip' role='button' href=''>Supprimer</a></li>";
        service_header += "</ul></div></div></th>";
        return service_header;
    }

    function timeCellHTML(service_index, line_index, value, formatted_date, readonly, trip_is_instantiated) {
        var name_pattern = "services[" + service_index + "][" + line_index + "]";
        var time_cell = "<td><div><div style='display: table-cell'>";
        time_cell += "<input type='hidden' name='" + name_pattern + "[route_stop_id]' value=" + value['route_stop_id'] + ">";
        time_cell += "<input type='hidden' name='" + name_pattern + "[stop_time_id]' value=" + value['stop_time_id'] + ">";
        if( trip_is_instantiated )
            time_cell += "<input type='hidden' name='" + name_pattern + "[time]' class='time' value='" + formatted_date + "'>" + formatted_date;
        else
            time_cell += "<input type='time' name='" + name_pattern + "[time]' class='form-control time' required value='" + formatted_date + "' " + readonly + " >";
        time_cell += "</div><div class='summary'></div></div></td>";
        return time_cell;
    }

    function updateSummaries(column_index, tripTableId, routeStopTableId) {
        var current_duration = 0;
        var current_scheduled_duration = 0;
        var $line_index = 1;

        $(tripTableId + " tbody tr").each(function () {
            var time_cell = $(this).find('td:eq(' + column_index + ') input.time').val();
            var time = TimeToInt(time_cell);
            var scheduled = $(routeStopTableId + " tr").eq($line_index).find('input.scheduled').attr("checked");
            if( scheduled ) {
                current_scheduled_duration += time;
                current_duration = current_scheduled_duration;
            } else {
                current_duration += time;
            }
            $(this).find('td:eq(' + column_index + ') div.summary').html(FormatTime(current_duration));

            $line_index += 1;
        });
    }

    function circulationDayIsSet(tr) {
        var isSet = false;
        $(tr).find("input.circulation-day").each(function(e) {
            if($(this).is(':checked')) isSet = true;
        });
        return isSet;
    };

    function displayAlert(errorMessage) {
        var div_error = "<div class='alert alert-danger alert-dismissable danger'>";
        div_error += "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" ;
        div_error += errorMessage;
        div_error += "</div>";

        $("#error_alert").html(div_error);
    };

    return {
        displayAlert: function(errorMessage) {
            var div_error = "<div class='alert alert-danger alert-dismissable danger'>";
            div_error += "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" ;
            div_error += errorMessage;
            div_error += "</div>";

            $("#error_alert").html(div_error);
        },
        loadStoptimes: function(stopTimes, tripTableId, routeStopTableId, instanciatedTripPatterns) {
            var service_index = 0;
            $.each(stopTimes, function( trip_id, $datas ) {
                var $line_index = 0;
                var trip_is_instantiated = ($.inArray(trip_id, instanciatedTripPatterns) > -1);

                $.each($datas, function( $key, $value ) {
                    var $FormattedDate = FormatTime($value['arrivalTime']);
                    if( $line_index == 0 ) {
                        var $service_header = serviceHeaderHTML(service_index, trip_id, $value['name'], trip_is_instantiated);
                        $("#trip-table thead th:last").before($service_header);
                    }
                    var scheduled = $(routeStopTableId + " tr").eq($line_index+1).find('input.scheduled').attr("checked");
                    var readonly = scheduled ? "": "readonly";
                    var $time_cell = timeCellHTML(service_index, $line_index, $value, $FormattedDate, readonly, trip_is_instantiated);
                    $(tripTableId + " tbody tr#" + $value['route_stop_id'] + " td:last").before($time_cell);
                    $line_index += 1;
                });

                updateSummaries(service_index, tripTableId, routeStopTableId);
                service_index += 1;
            });
        },
        updateColumnDuration: function(column_index, tripTableId, routeStopTableId) {
            updateSummaries(column_index, tripTableId, routeStopTableId);
        },
        addService: function(tripTableId, routeStopTableId) {
            var col_index = $(tripTableId + " thead th:last").index();
            var $service_name_input = "<div><div style='display: table-cell'>";
            $service_name_input += "<input type='text' name='services[" + col_index + "][name]' required class='form-control' maxlength='20'></div>";
            $service_name_input += "<div style='position:relative;display: table-cell'>";
            $service_name_input += "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' style='float:right'>";
            $service_name_input += "<span class='caret'></span></button>";
            $service_name_input += "<ul class='dropdown-menu  dropdown-menu-right'>";
            $service_name_input += "<li><a class='btn duplicate-trip' role='button' href=''>Dupliquer</a></li>";
            $service_name_input += "<li><a class='btn delete-trip' role='button' href=''>Supprimer</a></li>";
            $service_name_input += "</ul></div></div>";

            $(tripTableId + " thead th:last").before('<th>' + $service_name_input + '</th>');
            $(tripTableId + ' tbody').find('tr').each(function(i, el) {
                var row_index = $(el).index();
                var scheduled = $(routeStopTableId + " tr").eq(row_index+1).find('input.scheduled').attr("checked");
                var addedAttributes = "";
                if(!scheduled) {
                    addedAttributes = "readonly value='00:00'";
                }
                var stop_time_input = "<div><div style='display: table-cell'><input type='time' name='services[" + col_index + "][" + row_index + 
                    "][time]' required class='form-control time' " + addedAttributes +  " ></div>";
                stop_time_input += "<div class='summary'  style='color: #000088;font-weight: bold;padding-left: 5px;display: table-cell'></div></div>";
                $(el).find("td:last").before('<td>' + stop_time_input + '</td>');
            });
        },
        deleteService: function(item, tripTableId) {
            var $column_index = item.parents('th').index();
            $(tripTableId + ' tr').find('th:eq(' + $column_index + '),td:eq(' + $column_index + ')').remove();
        },
        addRouteStop: function (routeStopTableId, TripTableId, zonal) {
            var $index = $(routeStopTableId + ' tbody tr').length;
            var $line = "<tr>";
            $line += "<td>" + ($index+1) + "</td>";
           
            $line += "<td colspan='3'><input type='hidden' name='route_stops[" + $index + "][waypoint_id]'>";
            $line += "<input type='text' id='route_stops[" + $index + "][waypoint_id]' class='form-control stop-search'></td>";
            $line += "<td><input type='checkbox' name='route_stops[" + $index + "][dropOff]' checked></td>";
            $line += "<td><input type='checkbox' name='route_stops[" + $index + "][pickUp]' checked></td>";
            $line += "<td><input type='checkbox' class='scheduled' name='route_stops[" + $index + "][scheduled]' checked></td>";
            if( zonal ) {
                $line += "<td><input type='checkbox' name='route_stops[" + $index + "][internal]'></td>";
            }
            $line += "</tr>";
            $(routeStopTableId + " tbody").append($line);

            $(".stop-search").autocomplete({
                source: function (request, response) {
                    var objData = { term: request.term };
                    var url = Routing.generate('tisseo_boa_json_stop');
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
                    var $id = $(this).attr('id');
                    var $hidden_input = $("input[name='" + $id + "']");
                    $hidden_input.val(ui.item.id);
                },                  
                minLength: 3,
                delay: 300
            });

            var $service_count = $("#trip-table thead").find("th").length;
            $line = "<tr>";
            for (i = 0; i < $service_count-1; i++) { 
                $line += "<td><input type='time' name='services[" + i + "][" + $index + "][time]' required class='form-control'></td>";
            }
            $line += "<td><a style='color: red; float:right' class='btn btn-default remove-route-stop'>";
            $line += "<span class='glyphicon glyphicon-remove'></span></a></td>";
            $line += "</tr>";
            $(TripTableId + ' tbody').append($line);
        },
        deleteRouteStop: function(item, TripTableId, routeStopTableId) {
            var $current_row_index = $(item).closest("tr").index();
            $(TripTableId + " tbody tr").eq($current_row_index).remove();
            $(routeStopTableId + " tbody tr").eq($current_row_index).remove();
            $(routeStopTableId + " tbody").find('tr').each(function(i){
                $(this).find('td:first').text( i+1 );
            });
        },
        scheduledRouteStopChange: function(item, TripTableId) {
            var index = $(item).closest('tr').index();
            var scheduled = item.checked;
            $(TripTableId + ' tr').eq(index+1).find('input[type=time]').each(function() {
                if( scheduled ) {
                    $(this).prop('readonly', false);
                    $(this).val('');

                } else {
                    $(this).prop('readonly', true);
                    $(this).val('00:00');
                }
            });
        },
        calFHTripButtonClic: function(item, detailsLabel, hideLabel) {
            var tr_name = $(item).closest('tr').attr('name');
            var tr_trips_name = 'trips_' + tr_name.split('_')[1] + '_' + tr_name.split('_')[2];
            var tr_trips = $('tr[name=' + tr_trips_name + ']');
            
            if( $(item).html() == detailsLabel ) {
                tr_trips.removeClass( "hide" );
                $(item).html(hideLabel);
            } else {
                tr_trips.addClass( "hide" );
                $(item).html(detailsLabel);
            }
        },
        calFHFormSubmit: function(errorMessage) {
            var formIsValid = true;
            $('table.grid-calendar').each(function(e) {
                $(this).find('tbody').children('tr.grid-item').each(function(e) {

                    var calendar_type = $(this).find("select.grid_calendar_type");
                    var calendar_type_valid = calendar_type.length == 0 ? true: $(calendar_type).val();

                    var calendar_period = $(this).find("select.grid_calendar_period");
                    var calendar_period_valid = calendar_period.length == 0 ? true: $(calendar_period).val();

                    var circulationIsSet = circulationDayIsSet(this);
                    if( !((calendar_type_valid && calendar_period_valid && circulationIsSet) || 
                            (!calendar_type_valid && !calendar_period_valid && !circulationIsSet)) ) {
                        displayAlert(errorMessage);
                        formIsValid = false;
                    }
                });
            });
            return formIsValid;
        }
    }
});
