define(['jquery', 'bootstrap/datepicker', 'bootstrap/datepicker/'+global.locale, 'fosjsrouting', 'translations/messages'], function($) {

    $(document).ready(function() {
        $('#search-form .input-date').datepicker({
            language: global.locale,
            todayHighlight: true,
            autoclose: true
        });
        $('#search-form #line-version-select').change(function() {
            $('#search-form #start-date').val($(this).find('option:selected').attr('date'));
            $('#routes-list').fadeOut().promise().then(function() {
                $(this).remove();
            });
        });
        $('#search-form #start-date').change(function() {
            $('#routes-list').fadeOut().promise().then(function() {
                $(this).remove();
            });
        });
    });

    function validateForm() {
        $('#search-form').parent().find('div.alert').remove();
        var check = true;

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";

        if ($('#search-form #line-version-select option:selected').val().length < 1) {
            error += Translator.trans('tisseo.boa.monitoring.accessibility.validation.line_version')+"<br>";
            check = false;
        }
        if ($('#search-form #start-date').val().length < 1) {
            error += Translator.trans('tisseo.boa.monitoring.accessibility.validation.date_not_filled')+"<br>";
            check = false;
        }

        error += "</div>";

        if (!check) {
            $('#search-form').before(error);
        }

        return check;
    }

    $(document).on('click', '#search-form #consult-button', function() {
        if (!validateForm())
            return false;
        var lineVersionId = $('#search-form #line-version-select option:selected').val();
        var startDate = $('#search-form #start-date').val().replace(/\//g, '-');
        window.location.href = Routing.generate('tisseo_boa_monitoring_accessibility_search', {'lineVersionId': lineVersionId, 'startDate': startDate});
    });
});
