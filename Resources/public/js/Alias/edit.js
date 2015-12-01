define(['jquery', 'jquery_ui_sortable', 'jquery_ui_autocomplete', 'fosjsrouting', 'translations/messages'], function($) {

    $(document).on('keypress', '#aliases-list #new-alias-name', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $('#aliases-list #apply-alias-form').trigger('click');
        }
    });

    $(document).on('click', '#aliases-list #apply-alias-form', function(event) {
        event.preventDefault();
        $(this).attr('disabled', 'disabled');
        if (!$('#aliases-list #new-alias-name').val())
        {
            $('#aliases-list #new-alias-name').parent().addClass('has-error');
            $(this).removeAttr('disabled');
            $('#aliases-list #new-alias-name').focus();
            return false;
        }
        else {
            $('#aliases-list #new-alias-name').parent().removeClass('has-error');
        }

        $('#aliases-list .new-alias').before($('#new-alias-container tr.alias').clone());
        $('#aliases-list tr.alias:last').find('td:first').append($('#aliases-list #new-alias-name').val());
        $('#aliases-list tr.alias:last').fadeIn().promise().then(function() {
            $('#aliases-list #new-alias-name').val("").focus();
            $('#aliases-list #apply-alias-form').removeAttr('disabled');
        });
    });

    $(document).on('click', '#submit-aliases', function() {
        var stopAreaId = $('#aliases-list #stop-area-id').val();
        var data = [];
        $('#aliases-list tr.alias').each(function() {
            var alias = {};
            var $inputs = $(this).children('input');
            $inputs.each(function() {
                alias[this.name] = $(this).val();
            });
            alias['name'] = $(this).children('td:first').html();
            data.push(alias);
        });
        $.ajax({
            url : Routing.generate('tisseo_boa_alias_edit', {'stopAreaId': stopAreaId}),
            type: 'POST',
            data : JSON.stringify(data),
            success: function(data) {
                if (data.status === true && data.location) {
                    window.location.replace(data.location);
                }
            }
        });
    });

    $(document).on('click', '#aliases-list .delete-alias', function() {
        $(this).attr('disabled', 'disabled');
        var row = $(this).parent().parent();
        $(row).fadeOut(300, function() {
            $(this).remove();
        });
    });
});
