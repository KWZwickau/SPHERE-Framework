(function($)
{
    'use strict';
    $.fn.ModProgress = function(options)
    {
        var settings, pWidth;

        settings = $.extend({
            'Total': 0, 'Size': 0,
            'Speed': 0,
            'Time': 0,
            'Class': null,
            'Message': null
        }, options);

        if (null === settings.Message) {
            this.find('.progress-bar').html('');
        } else {
            this.find('.progress-bar').html(settings.Message);
        }

        if (0 >= settings.Total && 0 < settings.Size) {
            this.find('.progress-bar').removeClass('progress-bar-warning');
            this.find('.progress-bar').addClass('progress-bar-info');
            settings.Total = 100;
            settings.Size = 99.9;
        }

        if (1 > settings.Total) {
            settings.Total = 1;
        }

        pWidth = 100 / settings.Total * settings.Size;

        if (100 < pWidth) {
            pWidth = 100;
        }

        if (null !== settings.Class) {
            this.find('.progress-bar').removeClass('progress-bar-info');
            this.find('.progress-bar').addClass(settings.Class);
        }

        this.find('.progress-bar').css({"width": pWidth + '%'});

        if (0 < settings.Total && settings.Total === settings.Size) {
            this.removeClass('active');
            this.removeClass('progress-striped');
            this.find('.progress-bar').removeClass('progress-bar-info');
            this.find('.progress-bar').removeClass('progress-bar-warning');
            this.find('.progress-bar').addClass('progress-bar-success');
        }

        return this;
    };

}(jQuery));
