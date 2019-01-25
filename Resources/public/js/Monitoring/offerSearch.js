define(
    ['jquery', 'core/moment', 'boa/monitoring/offer/graph', 'fosjsrouting', 'translations/messages'],
    function($, moment, graph) {
    "use strict";

    var search = {};
    var results = {};
    var strNoResult = '';
    var defaultColors = [];
    var globalCurrentDate = '';
    //$(document).find('.color-picker').colorpicker();
    $(document).find('[data-toggle="tooltip"]').tooltip();

    // Initialize results
    search.init = function(res) {
      results = res.results;
      search.dateformat = 'YYYY-MM-DD';
      strNoResult = res.strNoResult;
      defaultColors = JSON.parse(res.defaultColors);
      // Initialisation de la page, récupère la 1ère date trouvée dans les résultats
      if(results.length > 1) {
        globalCurrentDate = moment(results[0].traffic_date);
        update_gui(globalCurrentDate);
      }
    }

    /**
     * Update GUI (result, current date / next / previous etc..)
     * @param current_date
     */
    function update_gui(current_date) {

      // Update nav button
      var navDate = {
        previousMonth: moment(current_date).subtract(1, 'months').format(search.dateformat),
        nextMonth: moment(current_date).add(1, 'months').format(search.dateformat),
        previousDay: moment(current_date).subtract(1, 'days').format(search.dateformat),
        nextDay: moment(current_date).add(1, 'days').format(search.dateformat),
      };

      $('.month_year div').html(current_date.format('MM/YYYY'));
      $('.month_year .next').attr('data-value', navDate.nextMonth);
      $('.month_year .previous').attr('data-value',  navDate.previousMonth);
      $('.day div').html(current_date.format('DD'));
      $('.day .next').attr('data-value',  navDate.nextDay);
      $('.day .previous').attr('data-value', navDate.previousDay);


      // Extract all routes (create routes list reference)
      var list = [];
      results.filter(function(el) {
        var found = list.find(function(listEl){
          if (list.length === 0) {
            return false;
          }
          return listEl.route_id === el.route_id;
        });
        if (!found) {
          list.push(el);
        }
      });

      // Compute service and trips by month for each route
      var month = [];
      var tripsOnMonth = []
      results.forEach(function(el){
        if(current_date.format('MM-YYYY') === moment(el.traffic_date).format('MM-YYYY')) {
          month[el.route_id] = (month[el.route_id] || 0) + el.number;
          var trips = el.trips.substr(1, el.trips.length - 2);
          if (el.route_id  in tripsOnMonth) {
            tripsOnMonth[el.route_id] = tripsOnMonth[el.route_id] +','+ trips;
          } else {
            tripsOnMonth[el.route_id] = trips;
          }
        }
      });

      // filtering result array on the current date
      var routes = results.filter(function(el) {
        return el.traffic_date === current_date.format(search.dateformat);
      });

      // For each actual routes (current_date), update data and update routes list reference
      routes.forEach(function(el, index) {
        el.number_month = month[el.route_id];
        el.trips_month = tripsOnMonth[el.route_id];
        el.checked = ($('.ckb-route-'+index).prop('checked') === true);
        list.forEach(function(route){
          if (el.route_id === route.route_id) {
            route.number_month = el.number_month;
            route.trips_month = el.trips_month;
            route.checked = el.checked;
          }
        });
      });


      // Check all routes are here
      list.forEach(function(el){
        var found  = routes.find(function(route){
          return route.route_id === el.route_id;
        });

        if (undefined === found) {
          // Route not found for this day, create route
          routes.push({
            route_id: el.route_id,
            number: 0,
            end: el.end,
            start: el.start,
            name: el.name,
            traffic_date: current_date.format(search.dateformat),
            number_month: (undefined !== month[el.route_id])? month[el.route_id] : 0,
            trips_month: tripsOnMonth[el.route_id],
            checked: el.checked,
            trips: null
          });
        }
      });


      if (display_route(routes, getData())) {
        graph.generate(results);
      } else {
        graph.removeChart();
      }
    }

    /**
     * Display routes
     *
     * @param array routes
     * @param json data
     *
     * @retun bool return true if there is results else return false
     */
    var display_route = function(routes, data) {
        // Reset table
        $('tbody.routes').html(null);

        // Load result
        if(routes.length > 0) {
            data = JSON.parse(data);
            routes.forEach(function (route, index) {

                // get the template
                var tpl = $('.boa-js-template tr').html();

                // Get previous data for this route if exists
                var route_data = data.filter(function(el) {
                    return el.route_id === route.route_id;
                });
                route.color_value= (route_data.length > 0) ? route_data[0].color_value :  (defaultColors[index]) || '#0fdd61';

                // is the checkbox checked ?
                if ($('#ckb-route-all').prop('checked') === true) {
                  tpl = tpl.replace(/:checked:/gi, 'checked');
                } else {
                  if (route.checked) {
                    tpl = tpl.replace(/:checked:/gi, 'checked');
                  } else {
                    tpl = tpl.replace(/:checked:/gi, '');
                  }
                }

                // Replace values into template
                tpl = tpl.replace(/:color_value:/gi, route.color_value);
                tpl = tpl.replace(/:index:/gi, index);
                tpl = tpl.replace(/:route_name:/gi, route.name);
                tpl = tpl.replace(/:route_id:/gi, route.route_id);
                tpl = tpl.replace(/:route_start:/gi, route.start);
                tpl = tpl.replace(/:route_end:/gi, route.end);
                tpl = tpl.replace(/:route_number:/gi, route.number);
                tpl = tpl.replace(/:route_number_month:/gi, route.number_month);
                tpl = tpl.replace(/:trips:/gi, route.trips);
                tpl = tpl.replace(/:trips_month:/gi, route.trips_month);

                $('tbody.routes').append('<tr>'+tpl+'</tr>');

                $(document).find('.ckb-route-'+index).val(JSON.stringify(route));


            });
            $(document).find('.routes .color-picker').colorpicker();
            return true;
        } else {
            $('tbody.routes').append('<tr><td>'+strNoResult+'</td></tr>');
            return false;
        }
    }


    /**
     * Get data for graph
     */
    function getData() {
        var data = [];
        $('.routes .color-picker input').each(function () {
            data.push({
              'route_id': $(this).data('route'),
              'color_value': $(this).val(),
              'trips' : $(this).data('trips')
            });
        });
        return JSON.stringify(data);
    }

    /**
     * Replace options of a select element with data
     * @param element
     * @param data
     */
    function replaceOptions(element, data) {
        if (data.length > 0) {
            var currentSelection = parseInt($(element).val());
            $(element).html('');
            $(data).each(function (idx, el) {
                var startDate = moment(el.startDate);
                $(element).append(
                    $('<option>', {
                        value: el.id,
                        text: el.line.number + ' - ' + el.version + ' - ' + startDate.format('DD/MM/YYYY')
                    })
                );
                if (el.id === currentSelection) {
                    $(element).prop('selectedIndex', idx);
                }
            });
        } else {
            $(element).html('');
        }
    }

    // Form submit
    $(document).on('submit', '[name="boa_offer_by_line_type"]', function() {
        $('#loading-indicator').show();
    });

    // When user change the line, we load the associated lines versions
    $(document).on('change','#boa_offer_by_line_type_line', function(ev) {
        var target = $('select[name="boa_offer_by_line_type[offer]"]');
        //var selectElement = $('select[name="boa_offer_by_line_type_line"]')
        var lineId = $(this).val();
        var depElements = $('.ajax_dep_element');
        $('#loading-indicator').show();
        $(depElements).prop('disabled', true);
        $.ajax({
            url: Routing.generate('tisseo_boa_monitoring_offer_by_line_json', { lineId: lineId }),
            async: true,
            type: 'GET',
            success: function(data) {
              try {
                data = JSON.parse(data)
                replaceOptions(target, data);
              } catch(e) {
                console.error(e.name + ' : ' + e.message);
              }
            },
            complete: function(data) {
              if (JSON.parse(data.responseText).length > 0) {
                $(depElements).prop('disabled', false);
              }
              $(this).prop('disabled', false);
              $('#loading-indicator').hide();
            }
        });
    });

    // Change month / day
    $(document).on('click', '.label-control a', function(e) {
      e.preventDefault(e);
      globalCurrentDate = moment(e.currentTarget.dataset.value);
      update_gui(globalCurrentDate);
      return false;
    });

    $(document).on('click', '.ckb-route', function() {
      graph.ckbRouteState();
      update_gui(globalCurrentDate);
    });

    // Select all / unselect all routes
    $(document).on('change', '#ckb-route-all', function() {
      if ($(this).prop('checked') === true) {
        $(document).find('input.ckb-route').each(function() {
          $(this).prop('checked', true);
        });
      } else {
        $(document).find('input.ckb-route').each(function() {
          $(this).prop('checked', false);
        });
      }
      update_gui(globalCurrentDate);
    });

    $(document).on('hidePicker', function(ev) {
        var current_date = moment(globalCurrentDate);
        update_gui(current_date);
    })

    return(search);
});
