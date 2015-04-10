/**
 * Created by clesauln on 10/04/2015.
 */
$(document).ready(function(){
   $(document).on("click", "tr", function() {

        var indexo=$(this).index();
        var idLine = $(this).find("td").first().attr("id");

        var data = { lineId: idLine};
       $.post("create",data );
    });
});