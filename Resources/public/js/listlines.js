require(['jquery'], function($) {
    function displayError(responseText) {
        document.open();
        document.write(responseText);
        document.close();
    }
    $(document).ready(function() {
        $(document).on("click", "tr", function() {
            var indexo=$(this).index();
            var idLine = $(this).find("td").first().attr("id");
            var data = { lineId: idLine};

            //simulate a form and submit idLine to get routes//
            var url = '../list';
            var form = $('<form action="' + url + '" method="post">' +
            '<input type="text" name="lineId" value="' + idLine + '" />' +
            '</form>');
            $('body').append(form);
            form.submit();
        });
    });
});

