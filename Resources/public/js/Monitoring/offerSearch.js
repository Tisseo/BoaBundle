define(['jquery', 'core/moment', 'fosjsrouting', 'translations/messages'], function($, moment) {
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

        // Form submit
        $(document).on('submit', '[name="boa_offer_by_line_type"]', function() {
            $('#loading-indicator').show();
        });

        $(document).on('click', '.btn-submit', function() {
            var form = $('form[name="boa_offer_by_line_type"]');

            if ($(form).find('input[name="boa_offer_by_line_type[reset]"]').prop('checked')) {
                $(form).find('select[name="boa_offer_by_line_type[month][date][day]"]').val(1);
                $(form).find('select[name="boa_offer_by_line_type[month][time][hour]"]').val(8);
            }
            $(form).find('input[name="boa_offer_by_line_type[reset]"]').prop('checked', false);
            $(form).submit();
        });

        // On change date
        $(document).on('change', '[name=boa_offer_by_line_type] .bootstrap-date select', function(ev) {
            var month = $('select[name="boa_offer_by_line_type[month][date][month]"]').val();
            var year = $('select[name="boa_offer_by_line_type[month][date][year]"]').val();
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

        // Change month / day / hour
        $(document).on('click', '.label-control a', function(e) {
            e.preventDefault(e);
            // get form
            var form = $('form[name="boa_offer_by_line_type"]');
            var date = moment($(this).data('value'));
            $(form).find('select[name="boa_offer_by_line_type[month][date][year]"]').val(date.format('YYYY'));
            $(form).find('select[name="boa_offer_by_line_type[month][date][month]"]').val(date.format('M'));
            $(form).find('select[name="boa_offer_by_line_type[month][date][day]"]').val(date.format('D'));
            $(form).find('select[name="boa_offer_by_line_type[month][time][hour]"]').val(date.format('H'));
            $(form).find('input[name="boa_offer_by_line_type[routes]"]').val(function() {
                var data = [];
                $('.color-picker input').each(function() {
                    data.push({
                        'route': $(this).data('route'),
                        'value': $(this).val(),
                        'checked': $(document).find('.ckb-route-' + $(this).data('index')).prop('checked')
                    });
                });
                return JSON.stringify(data);
            });
            $(form).find('input[name="boa_offer_by_line_type[reset]"]').prop('checked', false);
            $(form).submit();
        });

    });
});