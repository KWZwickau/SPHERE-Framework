(function($)
{
    'use strict';
    $.fn.ModGrid = function(options)
    {
        var selfElement = this;
        // Basic jQuery handler to prevent event propagation
        var preventClick = function(e)
        {
            e.stopPropagation();
            e.preventDefault();
        };

        // This is the easiest way to have default options.
        var settings = $.extend({
            widget_selector: 'li.Widget',
            widget_base_dimensions: ['auto', 100],
            widget_margins: [15, 15],
//            autogenerate_stylesheet: false,
//            shift_larger_widgets_down: false,
            min_cols: 2,
            max_cols: 8,
//            max_size_x: 2,
//            max_size_y: 2,
//            helper: 'clone',
            storage: 'Default',

            serialize_params: function(Widget, Grid)
            {
                return {
                    id: $(Widget).attr('id'),
                    col: Grid.col,
                    row: Grid.row,
                    size_x: Grid.size_x,
                    size_y: Grid.size_y
                };
            },
            draggable: {
                start: function(event, ui)
                {
                    // Stop event from propagating down the tree on the capture phase
                    ui.$player[0].addEventListener('click', preventClick, true);
                },
                stop: function(e, ui, $widget)
                {
                    Storage.localStorage.set('ModGrid', selfApi.serialize());

                    // Stop event from propagating down the tree on the capture phase
                    var player = ui.$player;
                    setTimeout(function()
                    {
                        player[0].removeEventListener('click', preventClick, true);
                    });
                }
            },
            resize: {
                enabled: true,
                max_size: [5, 10],
                stop: function(e, ui, $widget)
                {
                    Storage.localStorage.set('ModGrid', selfApi.serialize())
                }
            }
        }, options);

        var Storage = $.initNamespaceStorage(settings.storage);
        if (!Storage.localStorage.isEmpty('ModGrid')) {
            var Position = Storage.localStorage.get('ModGrid');
            $.each(Position, function(Index, Value)
            {
                var Widget;

                Widget = "#";
                Widget = Widget + Value.id;

                $(Widget).attr({
                    "data-col": Value.col,
                    "data-row": Value.row,
                    "data-sizex": Value.size_x,
                    "data-sizey": Value.size_y
                });
            });
        }
        selfElement.show();

        if (!settings.draggable.handle) {
            settings.draggable.handle = 'li.Widget';
        }
        var selfGrid = selfElement.gridster(settings);
        var selfApi = selfGrid.data('gridster');
        this.css({'width': $(window).width()});

        /**
         * Fix Selection of Payload not working
         */
        selfElement.find(".Widget-Payload *").on('mousedown', function()
        {
            selfApi.disable().disable_resize();
        });
        selfElement.find(".Widget-Payload *").on('mouseup', function()
        {
            selfApi.enable().enable_resize();
        });

        return this;
    };

}(jQuery));
