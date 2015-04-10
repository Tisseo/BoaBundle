define(['jquery', 'jquery_ui_autocomplete', 'bootstrap/datepicker', 'bootstrap/datepicker/{{ app.request.locale }}'], function($) {
		function display_dates_save() {
			if( calendarID != '' )  { 
				sessionStorage.setItem(calendarID, $("#start_date_picker").val() + '##' + $("#end_date_picker").val());
			}
		};

		function display_dates_validation(item) {		
			var startDate=$("#start_date_picker").val().split('/');
			var formattedStart=startDate[2] + startDate[1] + startDate[0];
			var endDate=$("#end_date_picker").val().split('/');
			var formattedEnd=endDate[2] + endDate[1] + endDate[0];
			
			if(formattedStart > formattedEnd) {
				alert("la date de début ne peut être supérieure à la date de fin");
				item.focus();
				return false;
			}	
			
			return true;
		};		
			
		function displayCalendar($FirstDate, $LastDate, $calendarPattern){
			var Beginning = new Date($FirstDate);
			Beginning.setHours(0,0,0,0);
			var Ending = new Date($LastDate);
			Ending.setHours(0,0,0,0);
			
			var table_html = "<table class='calendar_table'>";
			table_html += "<thead><tr><th></th><th>Lu</th><th>Ma</th><th>Me</th><th>Je</th><th>Ve</th><th>Sa</th><th>Di</th></tr></thead>";
			
			//days before month
			if(Beginning.getDay() != 1){
				table_html += "<tr>";
				table_html += "<td>" + Beginning.toDateString().split(' ')[1] + " " + Beginning.toDateString().split(' ')[3] + "</td>";
				for (var diffDays=(Beginning.getDay()+6)%7; diffDays >0; diffDays--) {
					table_html += "<td></td>";
				}
			}
			
			var pattern_index = 1;				
			var changeMonth = false;
			for (var currentDate = Beginning; currentDate <= Ending; currentDate.setDate(currentDate.getDate() + 1)) {
				var td_class = "calendar_cell ";
				if(changeMonth) {
					if(currentDate.getDate() > 7) { 
						td_class += "last_week_of_month ";
					} else {
						if(currentDate.getDate() == 1) td_class += "first_day_of_month ";
						if(currentDate.getDate() >= 1) td_class += "first_week_of_month ";
						if(currentDate.getDay() == 0) changeMonth = false;
					}						
				}
				if($calendarPattern[pattern_index] ==  1)  {
					td_class += "accessible ";
				} else {
					td_class += "inaccessible ";
				}
				table_html += "<td class='" + td_class + "'>" + currentDate.getDate() + "</td>";

				if(currentDate.getDay() == 0){
					if(+currentDate == +Ending) {
						table_html += "</tr>";
					} else {
						table_html += "</tr><tr>";
						
						var next_week_month = new Date(currentDate.getTime());
						next_week_month.setDate(next_week_month.getDate() + 7);
						if(next_week_month.getMonth() != currentDate.getMonth()) {
							changeMonth = true;
							table_html += "<td class='month_cell'>" + next_week_month.toDateString().split(' ')[1] + " " + next_week_month.toDateString().split(' ')[3] + "</td>";
						} else {
							table_html += "<td class='month_cell'></td>";
						}
					}
				}
				pattern_index += 1;
			}
			
			table_html += "</table>";
			
			$('#calendar_view').html(table_html);
		};

		function init_autocomplete(){
			$("input[data-id=calendar]").autocomplete({
				source: function (request, response) {
					var objData = { term: request.term };
					var url = $(this.element).attr('data-url');
					$.ajax({ url: url, dataType: "json", data : objData,  type: 'POST',
						success: function (data) {
							response($.map(JSON.parse(data.content), function (item) {									
								return {				
									label: item.name, 
									value: item.name, 
									id: item.id
								};
							}));
						},
					});
				},
				select: function (event, ui) {
					var hidden_id = '#' + $(this).attr('id').replace("calendarName", "includedCalendar");
					$(hidden_id).val(ui.item.id);
				},					
				minLength: 3,
				delay: 300
			});
		};
					
		function add() {
			var $index = $('#calendar_elements >tbody >tr').length + 1;
			
			var $prototype = $collectionHolder.attr('data-prototype');
			var $newLine = $prototype.replace(/__name__/g, $index);

			$('#calendar_elements >tbody')
				.append("<tr>" + 
							$newLine + 
							"<td><a class=\"btn btn-default\" href=\"#\"><span class=\"glyphicon glyphicon-remove\"></span>{{'global.delete'|trans({}, 'messages')}}</a></td></tr>");
			
			init_autocomplete();

			$('.element-date').datepicker({
				language: '{{ app.request.locale }}',
				todayHighlight: true,
				startView: 1,
				autoclose: true
			});
		};
			
		var previousDate;
		$('.input-date').datepicker({
				language: '{{ app.request.locale }}',
				todayHighlight: true,
				autoclose: true
			})
			// Save date picked
			.on('show', function () {
				previousDate = $(this).val();
			})
			// Replace with previous date if no date is picked or if same date is picked to avoide toggle error
			.on('hide', function () {
				if ($(this).val() === '' || $(this).val() === null) {
					$(this).val(previousDate).datepicker('update');
				}
			})
			.on('changeDate', function(e) {
				if (!($(this).val() === '' || $(this).val() === null)) {
					if(!display_dates_validation($(this))) {
						return false;
					}
					
					display_dates_save();
					$(e.target.name).datepicker('update');					
				}
			});
		
//			var calendarID="{{ calendarId }}";
			
			var data_stored = null;
			
			if( calendarID != '' )  { 
				data_stored = sessionStorage.getItem(calendarID);
				if( data_stored != null )  { 
					var res = data_stored.split("##");
					$('#start_date_picker').val(res[0]);
					$('#start_date_picker').datepicker('update');
					$('#end_date_picker').val(res[1]);
					$('#end_date_picker').datepicker('update');
				}
			}	
			if( data_stored == null )  { 
				var today = new Date();
				var firstDate = new Date(today.getFullYear(), today.getMonth(), 1);
				var lastDate = new Date(today.getFullYear(), today.getMonth()+2, 0);
				
				var month = (firstDate.getMonth()<10 ? "0" + firstDate.getMonth() : firstDate.getMonth());
				var sFirstDate = '01/' + month + '/' + firstDate.getFullYear();
				$('#start_date_picker').val(sFirstDate);	
				$('#start_date_picker').datepicker('update');
				
				month = (lastDate.getMonth()<10 ? "0" + (lastDate.getMonth()+1) : (lastDate.getMonth()+1));
				var sLastDate = lastDate.getDate() + '/' + month + '/' + firstDate.getFullYear();
				$('#end_date_picker').val(sLastDate);
				$('#end_date_picker').datepicker('update');
			}
						
			$('#refresh_button').click(
				function(event) {
					if( calendarID == '' ) {
						alert("Vous devez d'abord enregistrer le calendrier");
						return false;
					}
					var url = $(this).attr('data-url');
					
					var start_array = $('#start_date_picker').val().split("/");
					var start_date  =  start_array[2] + '-' + start_array[1] + '-' + start_array[0];
					
					var end_array = $('#end_date_picker').val().split("/");
					var end_date  =  end_array[2] + '-' + end_array[1] + '-' + end_array[0];
					
					var objData = {
						id: parseInt(calendarID),
						startDate: start_date,
						endDate: end_date
					};

					$.ajax({ url: url, data : objData, type: 'POST',
						success : function(data){
							displayCalendar(start_date, end_date, data.content);
						}
					});
				}
			);
			
			var $collectionHolder = $('#calendar_elements');
			var $removeCollectionHolder = $('#divRemoveElement');
	
			$('#empty_elements').click(function(event) {
				if(!confirm("{{'calendar.empty_elements'|trans({}, 'default')}}") )
					return false;
				
				$('#calendar_elements > tbody  > tr').each(function() {
					var checkbox = $(this).find("input.remove_checkbox[type=checkbox]");
					if( checkbox.length ){
						checkbox.prop('checked', true);
						checkbox.triggerHandler( "click" );
					} else {
						$(this).remove();
					}
					$('#save-form').click();
				});
			});

			$( ".remove_checkbox" ).on( "click", function() {
				var $index = $(this).closest('tr').index() + 1; 
				if(this.checked) {					
					var $IdClass = '#id_' + $index;
					var $IdValue = $($IdClass).val();
					var $prototype = $removeCollectionHolder.attr('data-prototype');
					var $newInput = $prototype.replace(/__name__/g, $index).replace("%id_value%", $IdValue).replace(/%remove_class%/g, "class_remove_" + $index);
					$(this).closest('td').append($newInput);
				} else {
					var $name = "#boa_calendar_element_remove_element_" + $index + "_id";
					$($name).remove();
					$name = "#boa_calendar_element_remove_element_" + $index + "_remove";
					$($name).remove();
				}
			});
			
			$('#add_element').click(function(event) {
				add(); 
			});
			
			$('#calendar_elements').on('click',  "a", function(event) {
				event.preventDefault();
				if ($(this).text() === "{{'global.delete'|trans({}, 'messages')}}") { 
					$(this).closest("tr").remove(); 
				}
			});
			
			$('#calendar_element_form').submit(function() {
				if( calendarID == '' ) {
					alert("Vous devez d'abord enregistrer le calendrier");
					return false;
				}
			});

			if( calendarID != '' )  { $("#refresh_button").click(); }
//		 });
});
	