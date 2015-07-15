(function( $ )
{
    'use strict';
    $.fn.ModTable = function( options )
    {

        // This is the easiest way to have default options.
        var settings = $.extend( {
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
            "lengthMenu": [[10, 25, 50], [10, 25, 50]],
            "pageLength": 10,
            "dom": "<'row'<'col-sm-5 hidden-xs'li><'col-sm-7 hidden-xs'fp>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            stateSave: true,
            responsive: true,
            autoWidth: false
        }, options );

        this.DataTable( settings );
        return this;

    };

}( jQuery ));
