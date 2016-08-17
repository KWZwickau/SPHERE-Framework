(function($)
{
    'use strict';
    /**
     * @param options
     * @returns {$.fn.ModTable}
     * @constructor
     */
    $.fn.ModTable = function(options)
    {

        var Table;

        // This is the easiest way to have default options.
        var settings = $.extend(true, {
            // These are the defaults.
            "language": {
                "sEmptyTable": "Keine Daten in der Tabelle vorhanden",
                "sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
                "sInfoEmpty": "0 bis 0 von 0 Einträgen",
                "sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ Einträge anzeigen",
                "sLoadingRecords": "Wird geladen...",
                "sProcessing": "Bitte warten...",
                "sSearch": "Suchen",
                "sZeroRecords": "Keine Einträge vorhanden.",
                "oPaginate": {
                    "sFirst": "Erste",
                    "sPrevious": "Zurück",
                    "sNext": "Nächste",
                    "sLast": "Letzte"
                },
                "oAria": {
                    "sSortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
                    "sSortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
                }
            },
            "lengthChange": true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Alle']],
            "pageLength": 10,
            "dom": "<'row'<'col-sm-5 hidden-xs'li><'col-sm-7 hidden-xs'fp>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            stateSave: true,
            responsive: true,
            autoWidth: false,
            // Setup RowReorder Extension
            ExtensionRowReorder: {
                Enabled: false,
                Url: '/Api/',
                Event: {
                    Success: function()
                    {
                        Table.processing(false);
                    },
                    Error: function()
                    {
                        Table.processing(false);
                    },
                },
                Data: {
                    // User-Data (additional)
                }
            }
        }, options);

        // Rewrite Custom-Settings to Api-Settings
        if (settings.ExtensionRowReorder.Enabled) {
            settings.processing = true;

            if (settings.responsive) {
                settings.rowReorder = {
                    selector: 'td:nth-child(2)',
                    snapX: 0
                }
            } else {
                settings.rowReorder = {
                    snapX: 0
                };
            }

            if (settings.columnDefs) {
                settings.columnDefs = settings.columnDefs.concat([
//                     {orderable: false, targets: '_all'},
//                     {orderable: true, targets: 0},
//                     {className: 'reorder', targets: settings.responsive ? 1 : 0},
                ]);
            } else {
                settings.columnDefs = [
                    {orderable: true, targets: 0},
                    {orderable: false, targets: '_all'},
                    {className: 'reorder', targets: settings.responsive ? 1 : 0},
                ];
            }

            if (options.ExtensionRowReorder.Event.Success) {
                settings.ExtensionRowReorder.Event.Success = new Function(
                    options.ExtensionRowReorder.Event.Success
                )
            }
            if (options.ExtensionRowReorder.Event.Error) {
                settings.ExtensionRowReorder.Event.Error = new Function(
                    options.ExtensionRowReorder.Event.Error
                )
            }
        }

        /**
         * Register: Processing Api
         *
         * Table.processing(true) - Show processing message
         * Table.processing(false) - Hide processing message
         */
        jQuery.fn.dataTable.Api.register('processing()', function(show)
        {
            return this.iterator('table', function(ctx)
            {
                ctx.oApi._fnProcessingDisplay(ctx, show);
            });
        });

        /**
         * Activate: DataTable
         */
        Table = this.DataTable(settings);

        /**
         * Register: RowReorder-Extension
         */
        Table.on('row-reorder', function(Event, Diff)
        {
            Table.processing(true);
            var postData = {};
            for (var i = 0, ien = Diff.length; i < ien; i++) {
                var rowData = Table.row(Diff[i].node).data();
                postData[i] = {
                    pre: Diff[i].oldData,
                    post: Diff[i].newData
                }
            }
            if (settings.ExtensionRowReorder.Url) {
                $.post(settings.ExtensionRowReorder.Url,
                    {'Reorder': postData, 'Additional': settings.ExtensionRowReorder.Data}, "json")
                    .fail(settings.ExtensionRowReorder.Event.Error)
                    .fail(function()
                    {
                        Table.processing(false);
                    })
                    .success(settings.ExtensionRowReorder.Event.Success)
                    .done(function()
                    {
                        Table.processing(false);
                    })
            }
        });

        return this;
    };

}(jQuery));
