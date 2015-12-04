define(['jquery', 'fosjsrouting', 'translations/messages'], function($) {

    function isNormalInteger(number)
    {
        return ($.isNumeric(number) && parseInt(number) >= 0);
    }

    function validateForm(data)
    {
        $('div.stop-area').parent().find('div.alert').remove();

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";

        var hasError = false;
        var stopAreaTransferDurationError = false;
        var transferDurationError = false;
        var transferDistanceError = false;
        var transferDurationNotFilledError = false;

        var stopAreaTransferDuration = data.stopAreaTransferDuration;
        if (!isNormalInteger(stopAreaTransferDuration) || parseInt(stopAreaTransferDuration)>60){
            stopAreaTransferDurationError = true;
        }

        $.each(data.transfers, function(key, transfer) {
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

        if (stopAreaTransferDurationError){
            error += Translator.trans('tisseo.boa.transfer.error.stop_area_transfer_duration_invalid')+"<br>";
            hasError = true;
        }
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

    $(document).on('click', '.submit-internal-transfers', function() {
        var stopAreaId = $('.stop-area #stop-area-id').val();
        var transfers = [];
        var data = {};
        $('#internal-transfers-list tr.transfer').each(function() {
            var transfer = {};
            var $inputs = $(this).find('input');
            $inputs.each(function() {
                transfer[this.name] = $(this).val();
            });
            if (transfer.duration || (!transfer.id && (transfer.distance || transfer.longName))){
                transfers.push(transfer);
            }
        });
        data.transfers = transfers;
        data.stopAreaTransferDuration = $('#stop-area-transfer-duration').val();

        if (!validateForm(data)){
           return false;
        }

        $.ajax({
            url : Routing.generate('tisseo_boa_internal_transfer_edit', {'stopAreaId': stopAreaId}),
            type: 'POST',
            data : JSON.stringify(data),
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
});
