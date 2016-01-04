(function($)
{
    'use strict';
    $.fn.ModCarousel = function(options)
    {
        var _this = this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            wrap: 'circular',
            ItemCountPerWidth: {
                '300': 2,
                '500': 3,
                '600': 4,
                '700': 5,
                '800': 6,
                '900': 7
            },
            Plugin: {
                Autoscroll: {
                    enabled: true,
                    interval: 3000,
                    target: '+=1',
                    autostart: true
                }
            }
            // These are the defaults.
        }, options);

        _this.on('jcarousel:create jcarousel:reload', function()
        {
            var Element = $(this),
                Width = Element.innerWidth();

            var ScreenCount = Width;

            for (var ScreenSize in settings.ItemCountPerWidth) {
                if (settings.ItemCountPerWidth.hasOwnProperty(ScreenSize)) {
                    if (Width > ScreenSize) {
                        ScreenCount = Width / settings.ItemCountPerWidth[ScreenSize];
                    }
                }
            }

            Element.jCarousel('items').css('width', ScreenCount + 'px');
        }).jCarousel(
            // Your configurations options
            settings
        );

        if (settings.Plugin.Autoscroll.enabled) {
            _this.jcarouselAutoscroll({
                interval: settings.Plugin.Autoscroll.interval,
                target: settings.Plugin.Autoscroll.target,
                autostart: settings.Plugin.Autoscroll.autostart
            })
        }

        return this;
    };
}(jQuery));
