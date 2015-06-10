define(['jquery', 'jquery_ui_autocomplete', 'translations/messages'], function($) {
    function inArray(array, key, value) {
        var item = $.grep(array, function(item) {
            return item[key] == value;
        });

        if (item.length) {
            return true;
        }
        return false;
    };


    //external transfers table tr
    function buildExternalTransferLine(transfer, index)
    {
        var  line = "<tr><td>";
        line += "<input type='hidden' id='transfer_" + index  + "_id' name='transfer[" + index  + "][id]'  value='" +  transfer["id"]  + "'>";
        line += "<input type='hidden' id='transfer_" + index  + "_startStopId' name='transfer[" + index  + "][startStopId]' class='form-control' value='" +  transfer["startStopId"]  + "'>";
        line += transfer["startStopLabel"];             
        line += "</td>";
        

        line += "<td>";             
        line += "<input type='hidden' id='transfer_" + index  + "_endStopType' name='transfer[" + index  + "][endStopType]' class='form-control' value='stop'>";                
        line += "<input type='hidden' id='transfer_" + index  + "_endStopId' name='transfer[" + index  + "][endStopId]' class='form-control' value='" +  transfer["endStopId"]  + "'>";             
        line += transfer["endStopLabel"];               
        line += "</td>";

        $.each(["duration", "distance", "longName"] , function(i, field) { 
            line += "<td><input type='text' id='transfer_" + index  + "_" + field + "' name='transfer[" + index  + "][" + field + "]' class='form-control input-value'";
            if (transfer[field]) line += " value='" +  transfer[field]  + "'";
            line += "></td>";
        });
        line += "</tr>";
        
        return line;
    };

    return {
        init_autocomplete: function(fieldId, hiddenFieldId) {
            $(fieldId).autocomplete({
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
                    $(hiddenFieldId).val(ui.item.id);
                },                  
                minLength: 3,
                delay: 300
            });            
        },
        add_alias: function(tableId) {
            var $index = $( tableId + ' >tbody >tr').length + 1;
            var $prototype = $(tableId).attr('data-prototype');
            var $newLine = $prototype.replace(/__name__/g, $index);
            $( tableId + ' >tbody').append("<tr><td>" + 
                $newLine + 
                "</td><td><a class=\"btn btn-default\" href=\"#\"><span class=\"glyphicon glyphicon-remove\"></span>" + 
                Translator.trans('global.delete', {}, 'messages') + 
                "</a></td></tr>");
        },
        buildExternalTransferTable: function(transfers, transferTableId, startStopId, endStopId, startStops, endStops) {
            var htmlBody = "";
            var index = 0;
            startStops.splice(0, startStops.length);
            endStops.splice(0, endStops.length);
            $.each(transfers, function(key, transfer) {
                if( !inArray(startStops, "id", transfer["startStopId"]) ) {
                    startStops.push({ "id":  transfer["startStopId"], "label": transfer["startStopLabel"] });
                }
                if( !inArray(endStops, "id", transfer["endStopId"]) ) {
                    endStops.push({ "id":  transfer["endStopId"], "label": transfer["endStopLabel"] });
                }
                
                if( !startStopId || startStopId == transfer["startStopId"] ) {
                    if( !endStopId || endStopId == transfer["endStopId"] ) {
                        htmlBody += buildExternalTransferLine(transfer, index);
                        index +=1;
                    }
                }
            });
            
            $(transferTableId + " >tbody").html(htmlBody);
        },
        externalTransferTableValidation: function(tableId) {
            var table_is_valid = true;
            $(tableId + " tbody tr").each(function (i, row) {
                var startStopId = $( "#transfer_" + i  + "_startStopId option:selected" ).val();
                var endStopId = $("#transfer_" + i  + "_endStopId").val();
                var duration = $("#transfer_" + i  + "_duration").val();
                var distance = $("#transfer_" + i  + "_distance").val();
                var longName = $("#transfer_" + i  + "_longName").val();
                var theGeom = $("#transfer_" + i  + "_theGeom").val();

                if( !endStopId ) {
                        displayAlert(Translator.trans('stop_area.transfer.error.end_stop', {}, 'messages').replace("%i%", (i+1)));
                        $(tableId + ' tbody').find("tr:eq("+i+")").addClass("invalid");
                        table_is_valid = false;
                        return false;
                }
                
                if( distance ) {
                    if ( !isInt(distance) ) {
                        displayAlert(Translator.trans('stop_area.transfer.error.distance', {}, 'messages').replace("%d%", distance).replace("%i%", (i+1)));
                        $(tableId + ' tbody').find("tr:eq("+i+")").addClass("invalid");
                        table_is_valid = false;
                        return false;
                    }
                }
                
                if ( !isInt(duration) ) {
                    displayAlert(Translator.trans('stop_area.transfer.error.duration', {}, 'messages').replace("%d%", duration).replace("%i%", (i+1)));
                        $(tableId + ' tbody').find("tr:eq("+i+")").addClass("invalid");
                    table_is_valid = false;
                    return false;
                }
                if ( !(duration == 99 || ( duration >=0 && duration <= 60)) ) {
                    displayAlert(Translator.trans('stop_area.transfer.error.duration_value', {}, 'messages').replace("%i%", (i+1)));
                        $(tableId + ' tbody').find("tr:eq("+i+")").addClass("invalid");
                    table_is_valid = false;
                    return false;
                }
            });
            
            return table_is_valid;
        }  
    }
});