(function($)
{
    'use strict';
    $.fn.ModCompleter = function(options)
    {
        var _self = this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            options: {
                minLength: 1,
            },
            // These are the defaults.
            data: [],
            displayKey: 'value',
            limit: 5
        }, options);

        var setup = $.extend({
            hint: true,
            highlight: true,
        }, settings.options);
        delete settings.options;

        if( setup.minLength > 0 ) {
            // constructs the suggestion engine
            var searchEngine = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                // `states` is an array of state names defined in "The Basics"
                local: $.map(settings.data, function (data) {
                    return {value: data};
                }),
                sorter: function (a, b) {

                    //get input text
                    var InputString = $(_self).val();

                    //move exact matches to top
                    if (InputString == a.value) {
                        return -1;
                    }
                    if (InputString == b.value) {
                        return 1;
                    }

                    //close match without case matching
                    if (InputString.toLowerCase() == a.value.toLowerCase()) {
                        return -1;
                    }
                    if (InputString.toLowerCase() == b.value.toLowerCase()) {
                        return 1;
                    }

                    // everything else
                    if ((InputString != a.value) && (InputString != b.value)) {

                        if (a.value < b.value) {
                            return -1;
                        }
                        else if (a.value > b.value) {
                            return 1;
                        }
                        else return 0;
                    }
                }
            });

            // kicks off the loading/processing of `local` and `prefetch`
            searchEngine.initialize();
            settings.source = searchEngine.ttAdapter();

        } else {
            // constructs the suggestion engine for empty query
            var searchEngine = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                identify: function(obj) { return obj.value; },
                // sufficient: settings.data.length,
                // `states` is an array of state names defined in "The Basics"
                local: $.map(settings.data, function (data) {
                    return {value: data};
                }),
                sorter: function (a, b) {

                    //get input text
                    var InputString = $(_self).val();

                    //move exact matches to top
                    if (InputString == a.value) {
                        return -1;
                    }
                    if (InputString == b.value) {
                        return 1;
                    }

                    //close match without case matching
                    if (InputString.toLowerCase() == a.value.toLowerCase()) {
                        return -1;
                    }
                    if (InputString.toLowerCase() == b.value.toLowerCase()) {
                        return 1;
                    }

                    // everything else
                    if ((InputString != a.value) && (InputString != b.value)) {

                        if (a.value < b.value) {
                            return -1;
                        }
                        else if (a.value > b.value) {
                            return 1;
                        }
                        else return 0;
                    }
                }
            });

            var Source = function SearchWithDefaults(q, sync) {
                if (q === '') {
                    sync(searchEngine.get( settings.data ));
                }

                else {
                    searchEngine.search(q, sync);
                }
            }

            // kicks off the loading/processing of `local` and `prefetch`
            // searchEngine.initialize();
            settings.source = Source;

        }

        this.typeahead(setup, settings);
        return this;

    };

}(jQuery));
