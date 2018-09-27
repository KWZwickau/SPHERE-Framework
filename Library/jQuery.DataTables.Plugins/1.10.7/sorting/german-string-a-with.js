/**
 * Sorting in Javascript for German Characters. This plug-in will replace the special
 * german letters (non english characters) and replace in English.
 *
 *
 *  @name German
 *  @summary Sort German characters
 *
 *  @example
 *    $('#example').dataTable({
 *       'aoColumns' : [
 *                       {'sType' : 'german-string'}
 *       ]
 *   });
 *
 *  array(
 *      'columnDefs' => array(
 *          array('type' => 'german-string', 'targets' => 0),
 *      )
 *  )
 */
jQuery.extend(jQuery.fn.dataTableExt.oSort, {

    'german-string-a-with-pre': function(str)
    {
        //exclude html tags
//         str = str.replace(//g, '');
        str = str.toLowerCase();
        //replace german umlauts
        str = str.replace(/ä/g, "a");
        str = str.replace(/ö/g, "o");
        str = str.replace(/ü/g, "u");
        str = str.replace(/ß/g, "ss");

        return str;
    },
    'german-string-a-with-asc': function(a, b)
    {
        a = prepareForSorting(a);
        b = prepareForSorting(b);

        return (a == b) ? 0 : (a > b) ? 1 : -1;
    },
    'german-string-a-with-desc': function(a, b)
    {

        a = prepareForSorting(a);
        b = prepareForSorting(b);
        //reverse sorting
        return (a == b) ? 0 : (a > b) ? -1 : 1;
    }
});
