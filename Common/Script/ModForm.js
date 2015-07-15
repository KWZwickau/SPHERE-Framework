(function( $ )
{
    'use strict';
    $.fn.ModForm = function( options )
    {
        var thisForm = this;
        // This is the easiest way to have default options.
        var settings = $.extend( {
            notifyChangedField: true,
            notifyChangedMessage: false
        }, options );

        var notifyFieldName;
        var notifyFieldList = this.find( ':input:not(:button)' );

        // script goes here

        /**
         * Page-Leave Confirmation
         * @file ModAlways.js, ModForm.js
         */
        $( window ).on( 'browser:page:unload', function( Event )
        {
            if (thisForm.find( ':input[data-form-leave-prevention="1"]' ).length) {
                Event.preventDefault();
                if (settings.notifyChangedMessage) {
                    Event.message = settings.notifyChangedMessage;
                }
            }
        } );
        this.notifyChangedField = function( FieldName )
        {
            this.find( '[name="' + FieldName + '"]' )
                .attr( 'data-form-leave-prevention', 0 )
                .on( 'propertychange change click keyup input paste', function( Event )
                {
                    $( Event.target ).attr( 'data-form-leave-prevention', 1 );
                } );
        };
        /**
         * Page-Leave Confirmation: All
         */
        if (true === settings.notifyChangedField) {
            for (notifyFieldName in notifyFieldList) {
                if (notifyFieldList.hasOwnProperty( notifyFieldName )) {
                    this.notifyChangedField( $( notifyFieldList[notifyFieldName] ).attr( 'name' ) )
                }
            }
        }
        /**
         * Page-Leave Confirmation: Submit
         */
        this.on( 'submit', function()
        {
            notifyFieldList.attr( 'data-form-leave-prevention', 0 );
        } );

        return this;
    };

}( jQuery ));
