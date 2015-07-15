(function( $ )
{
    'use strict';
    $.fn.ModSelecter = function( options )
    {

        // This is the easiest way to have default options.
        var settings = $.extend( {
            // These are the defaults.
        }, options );

        this.selecter( settings );
        return this;

    };

}( jQuery ));
