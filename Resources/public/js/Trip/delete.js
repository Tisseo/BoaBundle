define(['jquery', 'bootbox', 'fosjsrouting', 'translations/messages'], function($, bootbox) {

    var composant = {};
    var options = {};


    composant.init = function(opts) {
       options = opts;
    };

    function countSelectedTrip() {
        return $("input.ckTrip:checked").length;
    }

    function activateButton() {
        var delSelectButton = $('#delete-select');
        if (countSelectedTrip() > 0) {
            delSelectButton.removeClass('disabled');
        } else {
            delSelectButton.addClass('disabled');
        }
    }

    $('.ckTripAll').on('click', function(event) {
        $('input.ckTrip').not(this).prop('checked', this.checked);
        activateButton();
    });

    $('#delete-select').on('click', function (event) {
        event.preventDefault();
        var self = this;
        bootbox.confirm(Translator.trans('tisseo.boa.trip.confirm.delete_select'), function (result) {
            if (result) {
                var trips = ($('input.ckTrip:checked'));
                var idTrips = [];
                $.each(trips, function (index, trip) {
                    idTrips.push(trip.value);
                });

                $.ajax({
                    'type': 'POST',
                    'url': Routing.generate('tisseo_boa_trip_delete_all', {'routeId': options.routeId}),
                    'data': JSON.stringify(idTrips),
                    'ProcessData': false,
                    'contentType': false,
                    'success': function (data) {
                        if (data.status === true) {
                            window.location = self.href;
                        }
                    },
                    'error': function (xhr) {
                        console.log(xhr.responseText);
                    }
                });
                return true;
            }
        });
    });

    $('.ckTrip').on('click', function (event) {
        activateButton();
    });


    activateButton();

    return (composant);

});
