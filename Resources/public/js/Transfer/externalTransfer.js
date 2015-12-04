define(['jquery', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {
    var initExternalTransfers = function(){
        init_autocomplete();
    };

    function init_autocomplete() {
        $('#external-transfers-list #end-stop-search').autocomplete({
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
                            $('#external-transfers-list #end-stop-type').val('');
                            $('#external-transfers-list #end-stop-id').val('');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#external-transfers-list #end-stop-type').val(ui.item.type);
                $('#external-transfers-list #end-stop-id').val(ui.item.id);
            },
            minLength: 3,
            delay: 300
        });
    }

    function isNormalInteger(number)
    {
        return ($.isNumeric(number) && parseInt(number) >= 0);
    }

    function validateExternalTransfers(transfers)
    {
        $('div.stop-area').parent().find('div.alert').remove();

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";

        var hasError = false;
        var transferDurationError = false;
        var transferDistanceError = false;
        var transferDurationNotFilledError = false;
        $.each(transfers, function(key, transfer) {
            if (transfer.duration) {
                if (!isNormalInteger(transfer.duration) || (parseInt(transfer.duration) > 60 && parseInt(transfer.duration) != 99)){
                    transferDurationError = true;
                }
                if (transfer.distance && !isNormalInteger(transfer.distance)){
                    transferDistanceError = true;
                }
            }
            else if (!transfer.id && (transfer.distance || transfer.longName)){
                transferDurationNotFilledError = true;
            }
        });

        if (transferDurationError){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_duration_invalid')+"<br>";
            hasError = true;
        }
        if (transferDistanceError){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_distance_invalid')+"<br>";
            hasError = true;
        }
        if (transferDurationNotFilledError){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_duration_not_filled')+"<br>";
            hasError = true;
        }
        error += "</div>";

        if (hasError) {
            $('div.stop-area').before(error);
            $('#base-modal').scrollTop(0);
        }

        return !hasError;
    }

    function validateNewTransferForm(transfer){
        $('div.stop-area').parent().find('div.alert').remove();

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        var hasError = false;

        if (!transfer.startStopId){
            error += Translator.trans('tisseo.boa.transfer.error.external_transfer_start_stop_not_filled')+"<br>";
            hasError = true;
            $('tr.new-transfer #start-stop-select').parent().addClass('has-error');
        }
        else {
            $('tr.new-transfer #start-stop-select').parent().removeClass('has-error');
        }

        if (!transfer.endStopId){
            error += Translator.trans('tisseo.boa.transfer.error.external_transfer_end_stop_not_filled')+"<br>";
            hasError = true;
            $('tr.new-transfer #end-stop-search').parent().addClass('has-error');
        }
        else {
            $('tr.new-transfer #end-stop-search').parent().removeClass('has-error');
        }

        if (!transfer.duration){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_duration_not_filled')+"<br>";
            hasError = true;
            $('tr.new-transfer .transfer-duration').parent().addClass('has-error');
        }
        else {
            $('tr.new-transfer .transfer-duration').parent().removeClass('has-error');
        }

        if (transfer.duration && (!isNormalInteger(transfer.duration) || (parseInt(transfer.duration) > 60 && parseInt(transfer.duration) != 99))){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_duration_invalid')+"<br>";
            hasError = true;
            $('tr.new-transfer .transfer-duration').parent().addClass('has-error');
        }

        if (transfer.distance && !isNormalInteger(transfer.distance)){
            error += Translator.trans('tisseo.boa.transfer.error.transfer_distance_invalid')+"<br>";
            hasError = true;
            $('tr.new-transfer .transfer-distance').parent().addClass('has-error');
        }
        else {
            $('tr.new-transfer .transfer-distance').parent().removeClass('has-error');
        }

        error += "</div>";

        if (hasError) {
            $('div.stop-area').before(error);
            $('#base-modal').scrollTop(0);
        }

        return !hasError;
    }

    $(document).on('click', '.submit-external-transfers', function() {
        var stopAreaId = $('.stop-area #stop-area-id').val();
        var transfers = [];
        $('#external-transfers-list tr.transfer').each(function() {
            var transfer = {};
            var $inputs = $(this).find('input');
            $inputs.each(function() {
                transfer[this.name] = $(this).val();
            });
            if (transfer.duration || (!transfer.id && (transfer.distance || transfer.longName))){
                transfers.push(transfer);
            }
        });

        if (!validateExternalTransfers(transfers)){
           return false;
        }

        $.ajax({
            url : Routing.generate('tisseo_boa_external_transfer_edit', {'stopAreaId': stopAreaId}),
            type: 'POST',
            data : JSON.stringify(transfers),
            success: function(data) {
                if (data.status === true && data.location) {
                    window.location.replace(data.location);
                }
            },
            error: function (data) {
                if (data.getResponseHeader('Location')) {
                    window.location.replace(data.getResponseHeader('Location'));
                }
            }
        });
    });

    $(document).on('click', 'tr.transfer .delete-transfer', function(event) {
        $(this).attr('disabled', 'disabled');
        var row = $(this).parent().parent();

        $(row).fadeOut(300, function() {
            $(this).remove();
        });
    });

    $(document).on('click', 'tr.new-transfer #apply-transfer-form', function(event) {
        event.preventDefault();
        $(this).attr('disabled', 'disabled');

        var stopAreaId = $('.stop-area #stop-area-id').val();
        var $inputs = $('#external-transfers-list .new-transfer :input');
        var data = {};
        $inputs.each(function() {
            if (this.name)
                data[this.name] = $(this).val();
        });
        data['startStopId'] = $('.new-transfer #start-stop-select :selected').val();
        data['startStopType'] = $('.new-transfer #start-stop-select :selected').attr('type');

        if (!validateNewTransferForm(data)){
            $(this).removeAttr('disabled');
            return false;
        }

        data = JSON.stringify(data);

        $.ajax({
            url : Routing.generate('tisseo_boa_external_transfer_create', {'stopAreaId': stopAreaId}),
            type: 'POST',
            data : data,
            success: function(data) {
                if (data.content)
                {
                    if ($(data.content).is('tr'))
                    {
                        $('#external-transfers-list #table-transfers tbody:first').append(data.content);
                    }
                }
            }
        }).promise().then(function() {
            var $inputs = $('#external-transfers-list .new-transfer :input');
            $inputs.each(function() {
                $(this).val("");
            });
            $('.new-transfer #apply-transfer-form').removeAttr('disabled');
        });
    });



    return {
        initExternalTransfers:initExternalTransfers
    };

});
