(function($)
{
    'use strict';
    $.fn.ModPicker = function(options)
    {
        moment.locale(window.navigator.userLanguage || window.navigator.language);
        // This is the easiest way to have default options.
        var settings = $.extend({
            locale: moment.locale(),
            useCurrent: false,
            showTodayButton: true,
            calendarWeeks: true,
            focusOnShow: false,
            icons: {
                today: 'glyphicons glyphicons-home'
            },
            keyBinds: {
                up: false,
                down: false,
                left: false,
                right: false,
                delete: false
            },
            useStrict: true,
            format: 'DD.MM.YYYY',
            extraFormats: [
                'DDMMYYYY', 'DDMMYY',
                'DD.MM.YY', 'DD.M.YY', 'D.MM.YY', 'D.M.YY',
                'DD.M.YYYY', 'D.MM.YYYY', 'D.M.YYYY',
                'DD,MM,YY', 'DD,M,YY', 'D,MM,YY', 'D,M,YY',
                'DD,M,YYYY', 'D,MM,YYYY', 'D,M,YYYY', 'DD,MM,YYYY'
            ]
            // These are the defaults.
        }, options), _self = this;

        this.datetimepicker(settings).on('dp.change',function(){
            _self.trigger('change')
        });
        return this;

    };

}(jQuery));
