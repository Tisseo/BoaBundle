define(['jquery', 'jquery_ui_sortable', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    var init_autocomplete = function() {
        $('#odt-stops-list #stop-search').autocomplete({
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
                                    id: item.id,
                                    type: item.type
                                };
                            }));
                        } else {
                            $('#odt-stops-list #boa_odt_stop_stop').val('');
                            $('#odt-stops-list #new-odt-stop-stop-type').val('');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#odt-stops-list #boa_odt_stop_stop').val(ui.item.id);
                $('#odt-stops-list #new-odt-stop-stop-type').val(ui.item.type);
            },
            minLength: 3,
            delay: 300
        });
    };

    $(document).ready(function() {
        init_autocomplete();
        $('#odt-stops-list #stop-search').focus();
    });

    $('#show-closed-odt-stops').change(function() {
        if ($(this).is(":checked"))
            $(".closed-odt-stop").fadeIn();
        else
            $(".closed-odt-stop").fadeOut();
    });

    $(document).on('keypress', '#odt-stops-list #stop-search, #odt-stops-list .input-date input', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $('#odt-stops-list #apply-odt-stop-form').trigger('click');
        }
    });

    $(document).on('click', '#odt-stops-list #apply-odt-stop-form', function(event) {
        event.preventDefault();
        $(this).attr('disabled', 'disabled');
        if (!$('#odt-stops-list #boa_odt_stop_stop').val())
        {
            $('#odt-stops-list #stop-search').parent().addClass('has-error');
            $(this).removeAttr('disabled');
            $('#odt-stops-list #stop-search').focus();
            return false;
        }
        else {
            $('#odt-stops-list #boa_odt_stop_search').parent().removeClass('has-error');
        }
        if (!$('#odt-stops-list #boa_odt_stop_startDate').val())
        {
            $('#odt-stops-list #boa_odt_stop_startDate').parent().addClass('has-error');
            $(this).removeAttr('disabled');
            $('#odt-stops-list #boa_odt_stop_startDate').focus();
            return false;
        }
        else {
            $('#odt-stops-list #boa_odt_stop_startDate').parent().removeClass('has-error');
        }
        var odtAreaId = $('#odt-stops-list #odt-area-id').val();
        var $inputs = $('#odt-stops-list .new-odt-stop :input');
        var data = {};
        $inputs.each(function() {
            if((!$(this).is(':checkbox') || $(this).is(':checked')) && !($(this).attr('id') == 'new-odt-stop-stop-type')) {
                data[this.name] = $(this).val();
            }
        });
        var getForm = false;
        var routingName;
        if ($('#new-odt-stop-stop-type').val() == 'sa') {
            routingName = 'tisseo_boa_odt_stop_create_group';
            data = JSON.stringify(data);
        }
        else {
            routingName = 'tisseo_boa_odt_stop_create';
        }
        $.ajax({
            url : Routing.generate(routingName, {'odtAreaId': odtAreaId}),
            type: 'POST',
            data : data,
            success: function(data) {
                if (data.content)
                {
                    if ($(data.content).is('form'))
                    {
                        $('#odt-stops-list .new-odt-stop').html(data.content);
                        $(this).removeAttr('disabled');
                        $('#odt-stops-list #stop-search').focus();
                    }
                    else if ($(data.content).is('tr'))
                    {
                        getForm = true;
                        $('#odt-stops-list #table-odt-stops tbody:first').append(data.content);
                    }
                }
                else
                {
                    getForm = true;
                }
            },
            error: function (data) {
                window.location.replace(data.getResponseHeader('Location'));
            }
        }).promise().then(function() {
            if (getForm === true)
            {
                $.ajax({
                    url : Routing.generate('tisseo_boa_odt_stop_form', {'odtAreaId': odtAreaId}),
                    type: 'GET',
                    success: function(data) {
                        $('#odt-stops-list .new-odt-stop').html(data);
                        $('.input-date input').datepicker({
                            language: 'fr',
                            todayHighlight: true,
                            autoclose: true
                        });
                        init_autocomplete();
                        $('#odt-stops-list #stop-search').focus();
                    }
                });
                $(this).removeAttr('disabled');
            }
        });
    });

    function validateForm(odtStops)
    {
        $('#odt-stops').parent().find('div.alert').remove();
        var check = true;

        if (odtStops.length === 0)
            return check;

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        error += "</div>";

        if (!check) {
            $('#route-stops').after(error);
        }

        return check;
    }

    $(document).on('click', '#odt-stops-list #submit-odt-stops', function() {
        var odtAreaId = $('#odt-stops-list #odt-area-id').val();
        var odtStops = $('#odt-stops-list tr.odt-stop');

        if (!validateForm(odtStops))
            return false;

        var data = [];
        odtStops.each(function() {
            var odtStop = {};
            var $inputs = $(this).find('input');
            $inputs.each(function() {
                if ($(this).is(':checkbox'))
                    odtStop[this.name] = $(this).is(':checked');
                else if (this.name === '')
                    odtStop.stop = {'id': $(this).val()};
                else
                    odtStop[this.name] = $(this).val();
            });
            data.push(odtStop);
        });
        $.ajax({
            url : Routing.generate('tisseo_boa_odt_stop_edit', {'odtAreaId': odtAreaId}),
            type: 'POST',
            data : JSON.stringify(data),
            success: function(data) {
                if (data.status === true && data.location) {
                    window.location.replace(data.location);
                }
            },
            error: function (data) {
               window.location.replace(data.getResponseHeader('Location'));
            }

        });
    });

    $(document).on('click', '#odt-stops-list .delete-odt-stop', function() {
        $(this).attr('disabled', 'disabled');
        var row = $(this).parent().parent();

        $(row).fadeOut(300, function() {
            $(this).remove();
        });
    });
});
