define(['jquery', 'fosjsrouting'], function($) {
    $(document).ready(function() {
        $(document).on('change', '.select-offer', function() {
            $.ajax({
                url : Routing.generate('tisseo_tid_line_version_consult')+"/"+$(this).children(":selected").val(),
                type: 'GET',
                data : { history: true },
                success: function(data, textStatus) {
                    $('.modal-content').html(data);
                }
            });
            $(this).prop('selectedIndex', 0);
        });
    });
});
