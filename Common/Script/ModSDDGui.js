(function($)
{
    'use strict';
    $.fn.ModSDDGui = function(options)
    {
        var _this = this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
        }, options);

        console.log('ModSDDGui', _this);

        this.find('.SDD-Document').SDDDocument();

        return this;
    };
}(jQuery));
