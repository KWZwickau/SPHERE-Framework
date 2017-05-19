(function($)
{
    'use strict';
    $.fn.ModSlider = function(options)
    {
        // This is the easiest way to have default options.
        var settings = $.extend({

        }, options);

        this.bootstrapSlider(settings);
        return this;
    };

}(jQuery));
