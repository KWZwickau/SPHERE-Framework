(function( $ )
{
    'use strict';
    $.fn.ModPicker = function( options )
    {

        // This is the easiest way to have default options.
        var settings = $.extend( {
            // These are the defaults.
        }, options );

        this.datetimepicker( settings );
        return this;

    };

}( jQuery ));
