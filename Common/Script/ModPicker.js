(function($)
{
    'use strict';
    $.fn.ModPicker = function(options)
    {
        moment.locale(window.navigator.userLanguage || window.navigator.language);

        // This is the easiest way to have default options.
        var settings = $.extend({
            locale: moment.locale()
            // These are the defaults.
        }, options), _self = this;

        this.datetimepicker(settings).on('dp.change',function(){
            _self.trigger('change')
        });
        return this;

    };

}(jQuery));
