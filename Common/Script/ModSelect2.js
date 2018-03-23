(function($)
{
    'use strict';
    $.fn.ModSelect2 = function(options)
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            theme: "bootstrap",
            containerCssClass: ':all:',
            selectOnBlur: true,
            selectOnClose: true
        }, options);

        this.select2(settings);
        return this;

    };

}(jQuery));
