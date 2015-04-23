
function displayAlert(errorMessage) {
	var div_error = "<div class='alert alert-danger alert-dismissable danger'>";
	div_error += "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>" ;
	div_error += errorMessage;
	div_error += "</div>";

	$("#error_alert").html(div_error);
};


//check if value is an integer
function isInt(value) {
	return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value));
};


function buildInternalTransferLine(transfers, index, startStop, endStop)
{
	var htmlTR = "";
	var array_key = startStop["id"] + '.' + endStop["id"];
	var transfer = {};
	if( transfers[array_key] ) transfer = transfers[array_key];
	
	htmlTR += "<tr><td>";
	if (transfer["id"]) htmlTR += "<input type='hidden' id='transfer_" + index  + "_id' name='transfer[" + index  + "][id]'  value='" +  transfer["id"]  + "'>";
	htmlTR += "<input type='hidden' id='transfer_" + index  + "_startStopId' name='transfer[" + index  + "][startStopId]' class='form-control' value='" +  startStop["id"]  + "'>";
	htmlTR += startStop["label"];				
	htmlTR += "</td>";
	
	htmlTR += "<td>";				
	htmlTR += "<input type='hidden' id='transfer_" + index  + "_endStopId' name='transfer[" + index  + "][endStopId]' class='form-control' value='" +  endStop["id"]  + "'>";				
	htmlTR += endStop["label"];				
	htmlTR += "</td>";
	
	$.each(["duration", "distance", "longName"] , function(i, field) { 
		htmlTR += "<td><input type='text' id='transfer_" + index  + "_" + field + "' name='transfer[" + index  + "][" + field + "]' class='form-control input-value'";
		if (transfer[field]) htmlTR += " value='" +  transfer[field]  + "'";
		htmlTR += "></td>";
	});
	htmlTR += "</tr>";
	return htmlTR;
};

function buildInternalTransferTable(transfers) {
	var startStopId = $( "#startStop_select option:selected" ).val();
	if (startStopId) {
		var startStop = { "id" : startStopId, "label" : $( "#startStop_select option:selected" ).text() };
		var startStopLabel = $( "#startStop_select option:selected" ).text();
	}
	
	var endStopId = $("#endStop_select option:selected").val();
	if (endStopId) {
		var endStop = { "id" : endStopId, "label" : $( "#endStop_select option:selected" ).text() };
		var endStopLabel = $("#endStop_select option:selected").text();
	}
	
	var htmlBody = "";
	var index = 0;
	if (startStopId) {
		if (endStopId) {
			htmlBody += buildInternalTransferLine(transfers, index, startStop, endStop);
		} else {
			$( "#endStop_select option" ).each(function( endIndex, endStop ) {
				if ( endIndex > 0 ) {
					var endStop = { "id" : $(endStop).val(), "label" : $(endStop).text() };
					htmlBody += buildInternalTransferLine(transfers, index, startStop, endStop);
					index +=1;
				}
			});					
		}
	} else {
		$( "#startStop_select option" ).each(function( startIndex, startStop ) {
			if ( startIndex > 0 ) {
				var startStop = { "id" : $(startStop).val(), "label" : $(startStop).text() };
				if (endStopId) {
					htmlBody += buildInternalTransferLine(transfers, index, startStop, endStop);
					index +=1;
				} else {
					$( "#endStop_select option" ).each(function( endIndex, endStop ) {
						if ( endIndex > 0 ) {
							var endStop = { "id" : $(endStop).val(), "label" : $(endStop).text() };
							htmlBody += buildInternalTransferLine(transfers, index, startStop, endStop);
							index +=1;
						}
					});
				}
			}
		});
	}

	$("#transfer_table >tbody").html(htmlBody);
};


function postForm( form, post_url, callback ){
	var fields = form.serializeArray()
	var values = {};

	$.each( fields, function(i, field) {
		values[field.name] = field.value;
	});
	
	$.ajax({
		type: form.attr( 'method' ),
		url: post_url,
		data: values,
		success: function(data) {
			callback( data );
		},
		error : function(resultat, statut, erreur){
			displayAlert(erreur);
		}
	});
};
