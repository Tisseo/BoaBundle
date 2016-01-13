define(['jquery', 'fosjsrouting', 'translations/messages'], function($) {

    $(document).ready(function() {
        $('#search-form #line-version-select').change(function() {
            $('#stops-list').fadeOut().promise().then(function() {
                $(this).remove();
            });
        });
    });

    function validateForm() {
        $('#search-form').parent().find('div.alert').remove();
        var check = true;

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";

        if ($('#search-form #line-version-select option:selected').val().length < 1) {
            error += Translator.trans('tisseo.boa.monitoring.poi.validation.line_version')+"<br>";
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
        window.location.href = Routing.generate('tisseo_boa_monitoring_poi_search', {'lineVersionId': lineVersionId});
    });
});
