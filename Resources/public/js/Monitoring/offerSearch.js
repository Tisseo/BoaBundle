define(['jquery', 'fosjsrouting', 'translations/messages'], function($) {
    "use strict";

    $(document).ready(function () {

        $(document).find('.color-picker').colorpicker();
        function replaceOptions(element, data) {
            if (data.length > 0) {
                var currentSelection = parseInt($(element).val());
                $(element).html('');
                $(data).each(function (idx, el) {
                    $(element).append(
                        $('<option>', { value: el.id, text: el.line.number + ' - ' + el.version})
                    );
                    if (el.id === currentSelection) {
                        $(element).prop('selectedIndex', idx);
                    }
                });
            } else {
                $(element).html('');
            }
        }


        // On change date
        $(document).on('change', '[name=boa_offer_by_line_type] .bootstrap-date select', function(ev) {
            var month = $('select[name="boa_offer_by_line_type[month][month]"]').val();
            var year = $('select[name="boa_offer_by_line_type[month][year]"]').val();
            var target = $('#boa_offer_by_line_type_offer');
            var depElements = $('.ajax_dep_element');
            var selectElement = $('#boa_offer_by_line_type_month select');

            $('#loading-indicator').show();

            $(depElements).prop('disabled', true);
            $(selectElement).prop('disabled', true);

            $.ajax({
                url: Routing.generate('tisseo_boa_monitoring_offer_by_date_json', { month: month, year: year }),
                async: true,
                type: 'GET',
                success: function(data) {
                    try {
                        data = JSON.parse(data)
                        replaceOptions(target, data);
                    } catch(e) {
                        console.error(e.name + ' : ' + e.message);
                    }
                },
                complete: function(data) {
                    if (JSON.parse(data.responseText).length > 0) {
                        $(depElements).prop('disabled', false);
                    }
                    $(selectElement).prop('disabled', false);
                    $('#loading-indicator').hide();
                }
            });
        });
    });
});