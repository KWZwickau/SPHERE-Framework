(function($)
{
    'use strict';
    $.fn.SDDDocument = function(options)
    {
        var _this = this;
        console.log('SDDDocument', _this);

        var RegisterContainer = [];

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
        }, options);

        _this.find('.SDD-Panel').each(function(Event, Container)
        {
            $(Container).SDDPanel();
        });

        _this.find('.SDD-Page').each(function(Event, Container)
        {
            var SDDContainer = $(Container).SDDPage();
            RegisterContainer[RegisterContainer.length] = SDDContainer;

            SDDContainer.on('click', function(Event)
            {
                Event.stopPropagation();
//                console.log(SDDContainer.getTargetPosition(Event));
//                console.log(SDDContainer.getTargetSize(Event));
                console.log(_this.getSerialize());
            });

        });

        _this.on('resize', function(Event)
        {
            Event.stopPropagation();
            var Size = _this.getSize();
            settings.width = Size.width;
            settings.height = Size.height;

        });

        _this.getTargetPosition = function(Event)
        {
            var Target = $(Event.target);
            return {
                'top': Target.offset().top - Target.parent().offset().top,
                'left': Target.offset().left - Target.parent().offset().left
            }
        };

        _this.getTargetSize = function(Event)
        {

            var Target = $(Event.target);
            return {
                'width': Target.outerWidth(),
                'height': Target.outerHeight()
            }
        };

        _this.getPosition = function()
        {

            return {
                'top': _this.offset().top - _this.parent().offset().top,
                'left': _this.offset().left - _this.parent().offset().left
            }
        };

        _this.getSize = function()
        {

            return {
                'width': _this.outerWidth(),
                'height': _this.outerHeight()
            }
        };

        _this.getContainerList = function()
        {
            return RegisterContainer;
        };

        _this.getSerialize = function()
        {
            var List = _this.getContainerList();
            for (var Container in List) {
                if (List.hasOwnProperty(Container)) {
                    console.log(JSON.stringify(List[Container].getSettings()));
                }
            }
        };

        _this.getSettings = function()
        {
            return settings;
        };

        return _this;
    };

}(jQuery));
