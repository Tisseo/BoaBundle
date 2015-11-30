define(['jquery_ui_autocomplete'], function($) {
    init_autocomplete = function(input, target) {
        $(document).ready(function() {
            $(input).autocomplete({
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
                                $(target).attr('disabled', 'disabled').attr('data-stop', '');
                            }
                        }
                    });
                },
                select: function(event, ui) {
                    $(target).removeAttr('disabled').attr('data-stop', ui.item.id).focus()  ;
                },
                minLength: 3,
                delay: 300
            });

            $(target).click(function() {
                return $(this).attr("href", $(this).attr("href") + "/" + $(this).attr('data-stop'));
            });
        });
    };
});
