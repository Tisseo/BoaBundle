define(['jquery', 'chartjs', 'fosjsrouting', 'translations/messages'], function($) {
    "use strict";

    $(document).ready(function () {
        var btn = $(document).find('.generate-graph');
        var ckbRouteAll = $(document).find('#ckb-route-all');

        function ckbRouteState() {
            btn.prop('disabled', true);
            ckbRouteAll.prop('checked', true);
            $(document).find('.ckb-route').each(function() {
                if ($(this).prop('checked')) {
                    btn.prop('disabled', false);
                } else {
                    ckbRouteAll.prop('checked', false);
                }
            });
        }

        ckbRouteState();

        $(document).on('click', '.ckb-route', function() {
            ckbRouteState();
        });

        // Select all / unselect all routes
        $(document).on('change', '#ckb-route-all', function() {
            if ($(this).prop('checked') === true) {
                $(document).find('input.ckb-route').each(function() {
                    $(this).prop('checked', true);
                    btn.prop('disabled', false);
                });
            } else {
                $(document).find('input.ckb-route').each(function() {
                    $(this).prop('checked', false);
                    btn.prop('disabled', true);
                });
            }
        });

        $(document).on('click', '.generate-graph', function(ev) {
            var routes = $(document).find('input.ckb-route:checked');
            if (routes.length > 0) {
                var data = {};
                data.routes = [];
                var dom = $(document).find('.table-stats tbody');
                $(routes.each(function($idx, route) {
                    route = JSON.parse($(route).val());
                    route.color = $(dom).find('input[data-route="'+route.name+'"]').val();
                    data.routes.push(route);
                }));

                $('#loading-indicator-graph').show();
                btn.prop('disabled', true);

                $.ajax({
                    url: Routing.generate('tisseo_boa_monitoring_generate_graph'),
                    type: "POST",
                    data: data,
                    dataType: 'html',
                    success: function(data, status) {
                        try {
                            var data = JSON.parse(data);
                            var monthCtx = document.getElementById("chart_month").getContext('2d');
                            var monthChart = new Chart(monthCtx, {
                                type: 'bar',
                                data: data.month,
                                options: {
                                    reponsive: true,
                                    scales: {
                                        xAxes: [{
                                            stacked: true
                                        }],
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero:true
                                            },
                                            stacked: true,
                                        }]
                                    }
                                }
                            });

                            var hourCtx = document.getElementById("chart_hour").getContext('2d');
                            var hourChart = new Chart(hourCtx, {
                                type: 'bar',
                                data: data.hour,
                                options: {
                                    reponsive: true,
                                    scales: {
                                        xAxes: [{
                                            stacked: true
                                        }],
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero:true
                                            },
                                            stacked: true,
                                        }]
                                    }
                                }
                            });

                            $(document).find('.control-graph').removeClass('hide');

                        } catch(e) {
                            console.error(e.name + ' : ' + e.message);
                        }
                    },
                    complete: function(data) {
                        $('#loading-indicator-graph').hide();
                        btn.prop('disabled', false);
                    }
                });
            }
        });

        $(document).on('click', '.btn-display', function(ev) {
           var elem = $(this).find('input');
           if ($(elem).data('type') === 'grid') {
               $('.chart').addClass('col-md-6').removeClass('col-md-12');
           } else {
               $('.chart').addClass('col-md-12').removeClass('col-md-6');
           }

        });
    });
});