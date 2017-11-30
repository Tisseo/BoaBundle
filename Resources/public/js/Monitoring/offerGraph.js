define(['jquery', 'chartjs', 'fosjsrouting', 'translations/messages'], function($) {
    "use strict";

    $(document).ready(function () {

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
/*
                var ctx = document.getElementById("chart_month").getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ["1/11", "2/11", "3/11", "4/11", "5/11", "6/11", "7/11", "8/11", "9/11", "10/11", "11/11", "12/11",
                            "13/11", "14/11", "15/11", "16/11", "17/11", "18/11", "19/11", "20/11", "21/11", "22/11", "23/11", "24/11",
                            "25/11", "26/11", "27/11", "28/11", "29/11", "30/11"],
                        datasets: [{
                            label: '18/L01',
                            data: [12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 19, 3, 5, 2, 3],
                            backgroundColor: 'rgba(41,37,237,0.8)',
                            borderColor: 'rgba(41,37,237,0.8)',
                            borderWidth: 1
                        }, {
                            label: '14/L01',
                            data: [15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 5, 10, 1, 6, 4, 9],
                            backgroundColor: 'rgba(44,186,109,0.83)',
                            borderColor: 'rgba(44,186,109,0.83)',
                            borderWidth: 1
                        }, {
                            label: '7/L01',
                            data: [15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 5, 10, 1, 6, 4, 9],
                            backgroundColor: 'rgba(44,0,109,0.83)',
                            borderColor: 'rgba(44,0,109,0.83)',
                            borderWidth: 1
                        }]
                    },
                    options: {
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

                var ctx = document.getElementById("chart_day").getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ["05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00",
                            "14:00", "05:00", "05:00", "05:00", "05:00", "05:00", "05:00", "05:00", "05:00",
                            "05:00"],
                        datasets: [{
                            label: '18/L01',
                            data: [12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12],
                            backgroundColor: 'rgba(41,37,237,0.8)',
                            borderColor: 'rgba(41,37,237,0.8)',
                            borderWidth: 1
                        }, {
                            label: '14/L01',
                            data: [15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15],
                            backgroundColor: 'rgba(44,186,109,0.83)',
                            borderColor: 'rgba(44,186,109,0.83)',
                            borderWidth: 1
                        }]
                    },
                    options: {
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
*/
                $.ajax({
                    url: Routing.generate('tisseo_boa_monitoring_generate_graph'),
                    type: "POST",
                    data: data,
                    dataType: 'html',
                    success: function(data, status) {
                        try {
                            var data = JSON.parse(data);
                            console.log(data);
                            var monthCtx = document.getElementById("chart_month").getContext('2d');
                            var monthChart = new Chart(monthCtx, {
                                type: 'bar',
                                data: data.month,
                                options: {
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
                    error: function(data) {
                        try {
                            console.log(data);
                        } catch(e) {
                            console.error(e.name + ' : ' + e.message);
                        }
                    },
                    complete: function(data) {
                        //$('#loading-indicator').hide();
                        console.log('complete');
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