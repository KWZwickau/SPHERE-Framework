(function( $ )
{
    'use strict';
    $.fn.ModCompleter = function( options )
    {
        // This is the easiest way to have default options.
        var settings = $.extend( {
            // These are the defaults.
            data: [],
            displayKey: 'value'
        }, options );

        // constructs the suggestion engine
        var searchEngine = new Bloodhound( {
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace( 'value' ),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            // `states` is an array of state names defined in "The Basics"
            local: $.map( settings.data, function( data )
            {
                return {value: data};
            } )
        } );

        // kicks off the loading/processing of `local` and `prefetch`
        searchEngine.initialize();
        settings.source = searchEngine.ttAdapter();

        this.typeahead( {
            hint: true,
            highlight: true,
            minLength: 1
        }, settings );
        return this;

    };

}( jQuery ));
