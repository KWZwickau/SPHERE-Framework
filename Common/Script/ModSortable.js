(function( $ )
{
    'use strict';
    $.fn.ModSortable = function( options )
    {

        // This is the easiest way to have default options.
        var settings = $.extend( {
            // These are the defaults.
        }, options );

        this.sortable( settings ).disableSelection();
        return this;

    };

}( jQuery ));
