(function($)
{
    'use strict';
    $.fn.ModSelect = function(options)
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            mobile: true,
            width: '100%'
            // These are the defaults.
        }, options);

        this.selectpicker(settings);
        return this;

    };

}(jQuery));
