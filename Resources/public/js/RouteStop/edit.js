define(['jquery', 'jquery_ui_droppable', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    init_autocomplete = function() {
        $('#route-stops-list #stop-search').autocomplete({
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
                            $('#route-stops-list #boa_route_stop_waypoint').val('');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#route-stops-list #boa_route_stop_waypoint').val(ui.item.id);
            },
            minLength: 3,
            delay: 300
        });
    };

    $(document).ready(function() {
        init_autocomplete();
    });


    $(document).on('click', '#route-stops-list #apply-route-stop-form', function(event) {
        event.preventDefault();
        if (!$('#route-stops-list #boa_route_stop_waypoint').val())
        {
            $('#route-stops-list #stop-search').parent().addClass('has-error');
            return false;
        }
        else {
            $('#route-stops-list #stop-search').parent().removeClass('has-error');
        }
        var routeId = $('#route-stops-list #route-id').val();
        var $inputs = $('#route-stops-list .new-route-stop :input');
        var data = {};
        $inputs.each(function() {
            if(!$(this).is(':checkbox') || $(this).is(':checked')) {
                data[this.name] = $(this).val();
            }
        });
        var getForm = false;
        var rank = null;
        $.ajax({
            url : Routing.generate('tisseo_boa_route_stop_create', {'routeId': routeId}),
            type: 'POST',
            data : data,
            success: function(data) {
                if (data.content)
                {
                    if ($(data.content).is('form'))
                        $('#route-stops-list .new-route-stop').html(data.content);
                    else if ($(data.content).is('tr'))
                    {
                        getForm = true;
                        rank = parseInt($(data.content).find('td:first').html()) + 1;
                        $('#route-stops-list #table-route-stops tbody:first').append(data.content);
                    }
                }
            }
        }).promise().then(function() {
            if (getForm === true)
            {
                $.ajax({
                    url : Routing.generate('tisseo_boa_route_stop_form', {'routeId': routeId, 'rank': rank}),
                    type: 'GET',
                    success: function(data) {
                        $('#route-stops-list .new-route-stop').html(data);
                        init_autocomplete();
                    }
                });
            }
        });
    });

    function validateForm(routeStops)
    {
        $('#route-stops').parent().find('div.alert').remove();
        var check = true;
        
        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
 
        if ($(routeStops[0]).find('input.dropOff').is(':checked')) {
            error += Translator.trans('route_stop.error.first_stop_dropoff', {}, 'messages')+"<br>";
            check = false;
        }
        if (!($(routeStops[0]).find('input.scheduledStop').is(':checked'))) {
            error += Translator.trans('route_stop.error.first_stop_regulated', {}, 'messages')+"<br>";
            check = false;
        }
        if ($(routeStops[routeStops.length - 1]).find('input.pickup').is(':checked')) {
            error += Translator.trans('route_stop.error.last_stop_pickup', {}, 'messages')+"<br>";
            check = false;
        }
        if (!($(routeStops[routeStops.length - 1]).find('input.scheduledStop').is(':checked'))) {
            error += Translator.trans('route_stop.error.last_stop_regulated', {}, 'messages')+"<br>";
            check = false;
        }

        error += "</div>";

        if (!check) {
            $('#route-stops').after(error);
        }

        return check;
    }

    $(document).on('click', '#route-stops-list #submit-route-stops', function() {
        var routeId = $('#route-stops-list #route-id').val();
        var routeStops = $('#route-stops-list tr.route-stop');

        if (!validateForm(routeStops))
            return false;

        var data = [];
        routeStops.each(function() {
            var routeStop = {};
            var $inputs = $(this).find('input');
            $inputs.each(function() {
                if ($(this).is(':checkbox'))
                    routeStop[this.name] = $(this).is(':checked');
                else if (this.name === 'waypoint')
                    routeStop.waypoint = {'id': $(this).val()};
                else
                    routeStop[this.name] = $(this).val();
            });
            data.push(routeStop);
        });
        $.ajax({
            url : Routing.generate('tisseo_boa_route_stop_edit', {'routeId': routeId}),
            type: 'POST',
            data : JSON.stringify(data),
            success: function(data) {
                if (data.status === true && data.location) {
                    window.location.replace(data.location);
                }
            }
        });
    });

    $(document).on('click', '#route-stops-list .delete-route-stop', function() {
        var routeStop = $(this).closest('tr');
        var routeStopId = routeStop.find('.route-stop-id').val();
        var routeStopRank = routeStop.find('input.route-stop-rank').val();
        $(document).find('#route-stops-list tr.route-stop').each(function() {
            currentRank = $(this).find('input.route-stop-rank').val();
            if (currentRank > routeStopRank) {
                $(this).find('td.route-stop-rank').html(currentRank-1);
                $(this).find('input.route-stop-rank').val(currentRank-1);
            }
        });
        formRank = $(document).find('#route-stops-list tr.new-route-stop #boa_route_stop_rank');
        formRank.val(formRank.val()-1);
        
        routeStop.fadeOut(300, function() {
            $(this).remove();
        });
    });
});
