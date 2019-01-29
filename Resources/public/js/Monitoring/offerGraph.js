define(['jquery', 'core/moment', 'chartjs', 'fosjsrouting', 'translations/messages'], function($, moment) {
    "use strict";

    var graph = {};
    var ckbRouteAll = $(document).find('#ckb-route-all');
    var monthChart = {};
    var hourChart = {};
    var filteredResult = [];

    graph.ckbRouteState = function () {
        ckbRouteAll.prop('checked', true);
        $(document).find('.ckb-route').each(function() {
            if ($(this).prop('checked') === false) {
              ckbRouteAll.prop('checked', false);
            }
        });
    }

    // Remove old chart
    graph.removeChart = function() {

      if (monthChart instanceof Chart) {
        monthChart.destroy();
      }
      if (hourChart instanceof Chart) {
        hourChart.destroy();
      }
    }

  /**
   * Collect data and generate graph
   *
   * @param result
   */
  graph.generate = function(result) {
      // Get checked routes
      var routes = $(document).find('tbody.routes input.ckb-route:checked');
      filteredResult = result;

      // Remove old chart
      graph.removeChart();

      if (routes.length > 0) {
        $('#loading-indicator-graph').show();

        var data = {
          routes: [],
        };
        var current_date = null;

        data.month = {
          labels: [],
          datasets: []
        };

        $(routes.each(function (idx, route) {
          route = JSON.parse($(route).val());
          data.routes.push(route);

          data.month.datasets.push(
              {
                label: route.name,
                backgroundColor: route.color_value,
                borderColor: route.color_value,
                borderWidth: 1,
                data: []
              }
          );

          if (idx === 0) {
            // Get current date
            current_date = moment(route.traffic_date);

            // Load labels and init datasets with null value
            var currentDay = moment(current_date).startOf('month');
            var daysInMonth = current_date.daysInMonth();
            for (var i = 1; i <= daysInMonth; i++) {
              data.month.labels.push(currentDay.format('DD/MM/YYYY'));
              data.month.datasets[idx].data[i - 1] = 0;
              currentDay.add(1, 'd');
            }
          }

          // Extract service for the month
          var filtered = result.filter(function (el) {
            return (
                moment(el.traffic_date).format('MM-YYYY') === current_date.format('MM-YYYY') &&
                el.route_id === route.route_id
            );
          });
          filtered.forEach(function (prop) {
            // Update datasets for each day
            var index = parseInt(moment(prop.traffic_date).format('D'));
            data.month.datasets[idx].data[index - 1] = prop.number;
          });
        }));


        // Load month graph
        var monthCtx = document.getElementById("chart_month").getContext('2d');
        monthChart = new Chart(monthCtx, {
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
                  beginAtZero: true
                },
                stacked: true,
              }]
            }
          }
        });

      }
      $('#loading-indicator-graph').hide();
    };

    $(document).on('click','#chart_month', function(ev) {
      var activePoints = monthChart.getElementsAtEvent(ev);
      var firstPoint = activePoints[0];

      if (firstPoint !== undefined) {
        var current_date = monthChart.data.labels[firstPoint._index];
        //var value = monthChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
        var routes = $(document).find('tbody.routes input.ckb-route:checked');


        if (routes.length > 0) {
          $('#loading-indicator-graph').show();
          var data = {
            routes: [],
            current_date: current_date
          };

          $(routes.each(function (idx, route) {
            var objRoute = JSON.parse($(route).val());
            var aDate = current_date.split('/');
            var formatedDate = aDate[2]+'-'+aDate[1]+'-'+aDate[0];
            var routeTrips = filteredResult.filter(function(el){
               el.traffic_date = moment(el.traffic_date).format('YYYY-MM-DD');
               if (el.route_id === objRoute.route_id && el.traffic_date === formatedDate) {
                 el.color_value = objRoute.color_value;
                 return true;
               }
            });

            if (routeTrips.length > 0) {
              data.routes.push(
                  JSON.parse(JSON.stringify(routeTrips[0]))
              );
            }
          }));


          $.ajax({
            url: Routing.generate('tisseo_boa_monitoring_generate_graph'),
            type: "POST",
            data: data,
            dataType: 'html',
            success: function (data, status) {
              try {
                var data = JSON.parse(data);
                if (hourChart instanceof Chart) {
                  hourChart.destroy();
                }
                var hourCtx = document.getElementById("chart_hour").getContext('2d');
                hourChart = new Chart(hourCtx, {
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
                          beginAtZero: true
                        },
                        stacked: true,
                      }]
                    }
                  }
                });
              }
              catch (e) {
                console.error(e.name + ' : ' + e.message);
              }
            },
            complete: function (data) {
              $('#loading-indicator-graph').hide();
            }
          });
        }
      }
    });

    return(graph);
});