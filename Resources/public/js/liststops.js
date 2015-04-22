/**
 * Created by clesauln on 20/04/2015.
 */
require(['jquery'],(function($){

    $(document).ready(function(){

        $("#saveList").click(function(){
            listRows = table.rows().data();
            idRoute = $("#routeId").val();

            cpt=0;

            var listStops = {stops:[]};
            for(i=0;i<=listRows.length;i++){
                cpt++;
                if(typeof listRows[cpt] !== 'undefined') {
                    listStops.stops.push(listRows[cpt]);

                }
                console.log(listStops);
            }
            //$.post(Routing.generate('tisseo_boa_route_edit',{id:idRoute}));

            $.post('../datatable/save',{"id":idRoute,"list":listStops});

        });

    });

}));