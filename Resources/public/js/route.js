/**
 * Created by clesauln on 13/04/2015.
 */
$(".editRoute").click(function(){
    var idRoute = $(this).parent().parent().find("#idRoute").html();
    $.post(Routing.generate('tisseo_boa_route_edit',{id:idRoute}));
});
