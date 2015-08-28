define(['jquery', 'jquery_ui_sortable', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    var init_autocomplete = function() {
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

        var fixHelperModified = function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function(index) {
                $(this).width($originals.eq(index).width());
            });

            return $helper;
        };

        var updateIndex = function(e, ui) {
            $('td.route-stop-rank', ui.item.parent()).each(function (i) {
                $(this).html(i + 1);
            });
        };

        $("#route-stops-list table.sort tbody:first").sortable({
            helper: fixHelperModified,
            stop: updateIndex
        }).disableSelection();

        $('#route-stops-list #stop-search').focus();
    });

    $(document).on('keypress', '#route-stops-list #stop-search', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $('#route-stops-list #apply-route-stop-form').trigger('click');
        }
    });

    $(document).on('click', '#route-stops-list #apply-route-stop-form', function(event) {
        event.preventDefault();
        $(this).attr('disabled', 'disabled');
        if (!$('#route-stops-list #boa_route_stop_waypoint').val())
        {
            $('#route-stops-list #stop-search').parent().addClass('has-error');
            $(this).removeAttr('disabled');
            $('#route-stops-list #stop-search').focus();
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
                    {
                        $('#route-stops-list .new-route-stop').html(data.content);
                        $(this).removeAttr('disabled');
                        $('#route-stops-list #stop-search').focus();
                    }
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
                        $('#route-stops-list #stop-search').focus();
                    }
                });
                $(this).removeAttr('disabled');
            }
        });
    });

    function validateForm(routeStops)
    {
        $('#route-stops').parent().find('div.alert').remove();
        var check = true;

        if (routeStops.length === 0)
            return check;

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";

        if ($(routeStops[0]).find('input.dropOff').is(':checked')) {
            error += Translator.trans('tisseo.boa.route_stop.validation.first_stop_dropoff')+"<br>";
            check = false;
        }
        if (!($(routeStops[0]).find('input.scheduledStop').is(':checked'))) {
            error += Translator.trans('tisseo.boa.route_stop.validation.first_stop_regulated')+"<br>";
            check = false;
        }
        if ($(routeStops[routeStops.length - 1]).find('input.pickup').is(':checked')) {
            error += Translator.trans('tisseo.boa.route_stop.validation.last_stop_pickup')+"<br>";
            check = false;
        }
        if (!($(routeStops[routeStops.length - 1]).find('input.scheduledStop').is(':checked'))) {
            error += Translator.trans('tisseo.boa.route_stop.validation.last_stop_regulated')+"<br>";
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
            routeStop.rank = $(this).find('td.route-stop-rank').html();
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
        $(this).attr('disabled', 'disabled');
        var row = $(this).parent().parent();

        $(row).nextAll().find('td.route-stop-rank').each(function() {
            $(this).html($(this).html() - 1);
        });

        formRank = $(document).find('#route-stops-list tr.new-route-stop #boa_route_stop_rank');
        formRank.val(formRank.val()-1);

        $(row).fadeOut(300, function() {
            $(this).remove();
        });
    });
});
