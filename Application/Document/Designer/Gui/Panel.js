(function($)
{
    'use strict';
    $.fn.SDDPanel = function(options)
    {
        var _this = this;
        console.log('SDDPanel', _this);

        var RegisterContainer = [];

        // This is the easiest way to have default options.
        var settings = $.extend({
            height: '400',
            width: '200'
            // These are the defaults.
        }, options);

        _this.css({
            width: settings.width,
            height: settings.height,
            zIndex: 999
        });

        _this.draggable({containment: "parent", stack: ".SDD-Document"});
        _this.resizable({containment: "parent"});

        _this.find('.SDD-Element').each(function(Event, Container)
        {
            var SDDContainer = $(Container);
            SDDContainer.draggable({
                revert: 'invalid',
                helper: 'clone',
                start: function()
                {
                    $(this).hide();
                },
                stop: function()
                {
                    $(this).show();
                }
            });
        });

        _this.on('resize', function(Event)
        {
            Event.stopPropagation();
            var Size = _this.getSize();
            settings.width = Size.width;
            settings.height = Size.height;

        });

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

        return this;
    };

}(jQuery));
