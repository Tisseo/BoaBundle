define(['jquery', 'fosjsrouting', 'translations/messages'], function($) {
    $(document).on('click', '#trip-patterns-list #add-pattern', function() {
        var newRank = parseInt($(this).parent().prev().attr('id')) + 1;
        if (isNaN(newRank))
            newRank = 1;

        $('#trip-patterns-list .add-pattern').fadeOut().promise().then(function() {
            $('#trip-patterns-list .add-pattern.name').before($('#new-pattern-container .new-pattern.name').clone().removeClass('new-pattern').addClass('pattern-column').addClass('new'));
            $('#trip-patterns-list .add-pattern.action').before($('#new-pattern-container .new-pattern.action').clone().removeClass('new-pattern').addClass('new'));
            $('#trip-patterns-list .add-pattern.time').before($('#new-pattern-container .new-pattern.time').clone().removeClass('new-pattern').addClass('new'));
            $('#trip-patterns-list .time.new').first().children().first().attr('readonly', true);
            $('#trip-patterns-list .new').each(function() {
                $(this).attr('id', newRank).addClass(''+newRank).removeClass('new').fadeIn();
            }); 
            $('#trip-patterns-list .add-pattern').fadeIn();
        }); 
    });

    $(document).on('click', '#trip-patterns-list .delete-pattern', function() {
        var deleteRank = $(this).parent().attr('id');
        $('#trip-patterns-list #add-pattern').fadeOut();
        $('#trip-patterns-list .'+deleteRank).fadeOut().promise().then(function() {
            $(this).remove();
            $('#trip-patterns-list #add-pattern').fadeIn();
        });
    });

    function isNormalInteger(number)
    {
        return ($.isNumeric(number) && parseInt(number) >= 0);
    }

    function validateForm(tripPatterns)
    {
        $('#trip-patterns').parent().find('div.alert').remove();

        var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
       
        var patternNameError = false;
        var patternTimeError = false;
        $.each(tripPatterns, function(key, pattern) {
            if (!pattern.name)
                patternNameError = true;
            $.each(pattern.stopTimes, function(key, stopTime) {
                if (!isNormalInteger(stopTime.time))
                    patternTimeError = true;
            });
        }); 

        if (patternNameError)
            error += Translator.trans('trip.error.pattern_name_empty', {}, 'messages')+"<br>";

        if (patternTimeError)
            error += Translator.trans('trip.error.pattern_time_invalid', {}, 'messages')+"<br>";

        error += "</div>";

        if (patternNameError || patternTimeError) {
            $("#trip-patterns").after(error);
        }

        return !(patternNameError || patternTimeError);
    }

    $(document).on('click', '#submit-trip-patterns', function() {
        var routeId = $('#trip-patterns-list #route-id').val();

        var data = [];
        $('#trip-patterns-list .pattern-column').each(function(index) {
            var stopTimes = [];
            var rank = this.id;

            $('#trip-patterns-list td.time.'+rank).each(function(index) {
                var inputs = $(this).children('input');
                stopTimes[index] = {
                    'id': inputs[1].value,
                    'time': inputs[0].value
                };
            });

            var inputs = $(this).children();
            data[index] = {
                'id': inputs[1].value,
                'name': inputs[0].value,
                'stopTimes': stopTimes
            };
        });

        if (!validateForm(data))
            return false;

        $.ajax({
            url : Routing.generate('tisseo_boa_trip_edit_pattern', {'routeId': routeId}),
            type: 'POST',
            data : JSON.stringify(data),
            success: function(data) {
                if (data.status === true && data.location) {
                    window.location.replace(data.location);
                } else {
                    var error = "<div class='alert alert-danger alert-dismissable danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
                    error += data.content;
                    error += "</div>";
                    $("#trip-patterns").after(error);
                }
            }
        });
    });
});
