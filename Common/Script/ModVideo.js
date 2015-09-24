(function($)
{
    'use strict';
    $.fn.ModVideo = function(options)
    {
        // This is the easiest way to have default options.
        var settings = $.extend({
            'bgcolor': '#000000',
            'max-width': '100%',
            'adaptiveRatio': true
            // These are the defaults.
        }, options);

        return this;
    };
}(jQuery));
