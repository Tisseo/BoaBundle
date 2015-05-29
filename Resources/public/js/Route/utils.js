define(['jquery', 'jquery_ui_autocomplete'  , 'fosjsrouting'], function($) {
    function FormatTime(t) {
        var h = Math.floor(t/3600);
        var m = (t - h*3600)/60;
        if( h < 10 ) h = "0"+h;
        if( m < 10 ) m = "0"+m;
        return h+":"+m;
    }

    return {
        loadStoptimes: function(stopTimes, tableId, routeStopTableId) {
            var $service_index = 0;
            $.each(stopTimes, function( $trip_id, $datas ) {
                var $line_index = 0;
                $.each($datas, function( $key, $value ) {
                    var $FormattedDate = FormatTime($value['arrivalTime']);
                    if( $line_index == 0 ) {
                        var $service_header = "<th>";
                        $service_header += "<input type='hidden' name='services[" + $service_index + "][id]' value=" + $trip_id + ">";
                        $service_header += "<div><div style='display: table-cell'>";
                        $service_header += "<input type='text' name='services[" + $service_index + "][name]' class='form-control' style='float:left' required value='" + $value['name'] + "'></div>";
                        $service_header += "<div style='position:relative;display: table-cell'>";
                        $service_header += "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' style='float:right'>";
                        $service_header += "<span class='caret'></span></button>";
                        $service_header += "<ul class='dropdown-menu  dropdown-menu-right'>";
                        $service_header += "<li><a class='btn delete-trip' role='button' href=''>Supprimer</a></li>";
                        $service_header += "</ul></div></div>";
                        $service_header += "</th>";
                        $("#trip-table thead th:last").before($service_header);
                    }
                    var scheduled = $(routeStopTableId + " tr").eq($line_index+1).find('input.scheduled').attr("checked");
                    var readonly = scheduled ? "": "readonly";

                    var $time_cell = "<td>";
                    $time_cell += "<input type='hidden' name='services[" + $service_index + "][" + $line_index + "][route_stop_id]' value=" + $value['route_stop_id'] + ">";
                    $time_cell += "<input type='hidden' name='services[" + $service_index + "][" + $line_index + "][stop_time_id]' value=" + $value['stop_time_id'] + ">";
                    $time_cell += "<input type='time' name='services[" + $service_index + "][" + $line_index + "][time]' class='form-control' required value='" + $FormattedDate + "' " + readonly + " >";

                    $time_cell += "</td>";
                    $(tableId + " tbody tr#" + $value['route_stop_id'] + " td:last").before($time_cell);
                    $line_index += 1;
                });
                $service_index += 1;
            });
        },
        addService: function(tableId, routeStopTableId) {
            var col_index = $(tableId + " thead th:last").index();
            var $service_name_input = "<div><div style='display: table-cell'>";
            $service_name_input += "<input type='text' name='services[" + col_index + "][name]' required class='form-control'></div>";
            $service_name_input += "<div style='position:relative;display: table-cell'>";
            $service_name_input += "<button data-toggle='dropdown' class='btn btn-default dropdown-toggle' style='float:right'>";
            $service_name_input += "<span class='caret'></span></button>";
            $service_name_input += "<ul class='dropdown-menu  dropdown-menu-right'>";
            $service_name_input += "<li><a class='btn delete-trip' role='button' href=''>Supprimer</a></li>";
            $service_name_input += "</ul></div></div>";

            $(tableId + " thead th:last").before('<th>' + $service_name_input + '</th>');
            $(tableId + ' tbody').find('tr').each(function(i, el) {
                var row_index = $(el).index();
                var scheduled = $(routeStopTableId + " tr").eq(row_index+1).find('input.scheduled').attr("checked");
                var addedAttributes = "";
                if(!scheduled) {
                    addedAttributes = "readonly value='00:00'";
                }
                var stop_time_input = "<input type='time' name='services[" + col_index + "][" + row_index + 
                    "][time]' required class='form-control ' " + addedAttributes +  " >";
                $(el).find("td:last").before('<td>' + stop_time_input + '</td>');
            });
        },
        deleteService: function(item, tableId) {
            var $column_index = item.parents('th').index();
            $(tableId + ' tr').find('th:eq(' + $column_index + '),td:eq(' + $column_index + ')').remove();
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
        }
    }
});
