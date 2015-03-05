define(['jquery'], function($) {
    textfill = function(min, max, container) {
        $(document).ready(function() {
            $(container).textfill({
                minFontPixels: min,
                maxFontPixels: max
            });
        });
    };
});
