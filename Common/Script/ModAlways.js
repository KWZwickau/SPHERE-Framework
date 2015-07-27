(function( $ )
{
    'use strict';
    $.fn.ModAlways = function()
    {
        $( document ).ready( function()
        {
            /**
             * Autocomplete Attribute OFF
             */
            $( 'form' ).attr( 'autocomplete', 'off' );
            $( 'input[type="password"]' ).attr( 'autocomplete', 'off' );
            $( 'input[type="text"]' ).attr( 'autocomplete', 'off' );
            $( 'input[type="number"]' ).attr( 'autocomplete', 'off' );

            //noinspection FunctionWithInconsistentReturnsJS
            /**
             * Page-Leave Confirmation-Handler
             * @file ModAlways.js, ModForm.js
             */
            $( window ).on( 'beforeunload', function()
            {
                var closingEvent = $.Event( 'browser:page:unload' );
                // let other modules determine whether to prevent closing
                $( window ).trigger( closingEvent );
                if (closingEvent.isDefaultPrevented()) {
                    // closingEvent.message is optional
                    return closingEvent.message || 'Warnung: Die Daten wurden noch nicht gespeichert.';
                }
            } );

            /**
             * Tooltip
             */
            $( '[data-toggle="tooltip"]' ).tooltip( {
                container: 'body',
                placement: 'auto top'
            } )
        } );
        return this;

    };

}( jQuery ));
