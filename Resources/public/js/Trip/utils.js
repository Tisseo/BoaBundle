define(['jquery', 'jquery_ui_autocomplete'], function($) {
    return {
        displayAlert: function(errorMessage) {
            var div_error = "<div class='alert alert-danger alert-dismissable danger'>";
            div_error += "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" ;
            div_error += errorMessage;
            div_error += "</div>";

            $("#error_alert").html(div_error);
        },
        initAutoComplete: function(fieldId, IdFieldId) {
            $(fieldId).autocomplete({
                source: function (request, response) {
                    var objData = { term: request.term };
                    var url = $(this.element).attr('data-url');
                    $.ajax({ url: url, dataType: "json", data : objData,  type: 'POST',
                        success: function (data) {
                            response($.map(JSON.parse(data.content), function (item) {                                  
                                return {                
                                    label: item.name, 
                                    id: item.id
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    $(IdFieldId).val(ui.item.id);
                },                  
                minLength: 3,
                delay: 300
            });
        },
        initTripPatternAutoComplete: function(fieldId, IdFieldId, routeId) {
            $(fieldId).autocomplete({
                source: function (request, response) {
                    var objData = { term: request.term, 'routeId': routeId };
                    var url = $(this.element).attr('data-url');
                    $.ajax({ url: url, dataType: "json", data : objData,  type: 'POST',
                        success: function (data) {
                            response($.map(JSON.parse(data.content), function (item) {                                  
                                return {                
                                    label: item.name, 
                                    id: item.id
                                };
                            }));
                        },
                    });
                },
                select: function (event, ui) {
                    $(IdFieldId).val(ui.item.id);
                },                  
                minLength: 2,
                delay: 300
            });
        },
        addStopTimeBlock: function (container, startLabel, frequencyLabel, stopLabel) {
            var index = $(container).children().length;
            var html = "<div class='form form-edit'><div class = 'col-md-12'><div class='row stop-time-block'>";
            html += "<div class = 'col-md-4'>";
            html += "<label class='control-label required' >" + startLabel + "</label>";
            html += "<input name='stop_times[" + index + "][start]' type='time' class='form-control' required>";
            html += "</div><div class = 'col-md-4'>";
            html += "<label class='control-label required' >" + frequencyLabel + "</label>";
            html += "<input name='stop_times[" + index + "][frequency]' type='number' class='form-control frequency' min='0'>";
            html += "</div><div class = 'col-md-4'>";
            html += "<label class='control-label required' >" + stopLabel + "</label>";
            html += "<input name='stop_times[" + index + "][stop]' type='time' class='form-control stop'>";
            html += "</div>";
            html += "</div></div></div>";
            $(container).append(html);
        }

    }

});