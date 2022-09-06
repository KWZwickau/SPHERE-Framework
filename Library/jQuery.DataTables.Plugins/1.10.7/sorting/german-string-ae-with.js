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

    'german-string-ae-with-pre': function(str)
    {
        //exclude html tags
//         str = str.replace(//g, '');
        str = str.toLowerCase();
        //replace german umlauts
        str = str.replace(/ä/g, "ae");
        str = str.replace(/ö/g, "oe");
        str = str.replace(/ü/g, "ue");
        str = str.replace(/ß/g, "ss");
        // extended replacement
        str = str.replace(/à/g, "a");
        str = str.replace(/á/g, "a");
        str = str.replace(/â/g, "a");
        str = str.replace(/ã/g, "a");
        str = str.replace(/å/g, "a");
        str = str.replace(/å/g, "a");
        str = str.replace(/æ/g, "ae");
        str = str.replace(/ç/g, "c");
        str = str.replace(/č/g, "c");
        str = str.replace(/ð/g, "d");
        str = str.replace(/è/g, "e");
        str = str.replace(/é/g, "e");
        str = str.replace(/ê/g, "e");
        str = str.replace(/ë/g, "e");
        str = str.replace(/ĕ/g, "e");
        str = str.replace(/ě/g, "e");
        str = str.replace(/ğ/g, "g");
        str = str.replace(/ģ/g, "g");
        str = str.replace(/ì/g, "i");
        str = str.replace(/í/g, "i");
        str = str.replace(/î/g, "i");
        str = str.replace(/ï/g, "i");
        str = str.replace(/ñ/g, "n");
        str = str.replace(/ō/g, "o");
        str = str.replace(/ò/g, "o");
        str = str.replace(/ó/g, "o");
        str = str.replace(/ô/g, "o");
        str = str.replace(/ø/g, "o");
        str = str.replace(/Þ/g, "p");
        str = str.replace(/þ/g, "p");
        str = str.replace(/ŝ/g, "s");
        str = str.replace(/ş/g, "s");
        str = str.replace(/ù/g, "u");
        str = str.replace(/ú/g, "u");
        str = str.replace(/û/g, "u");
        str = str.replace(/ū/g, "u");
        str = str.replace(/×/g, "x");
        str = str.replace(/ý/g, "y");
        str = str.replace(/ÿ/g, "y");

        return str;
    },
    'german-string-ae-with-asc': function(a, b)
    {
        a = prepareForSorting(a);
        b = prepareForSorting(b);

        return (a == b) ? 0 : (a > b) ? 1 : -1;
    },
    'german-string-ae-with-desc': function(a, b)
    {

        a = prepareForSorting(a);
        b = prepareForSorting(b);
        //reverse sorting
        return (a == b) ? 0 : (a > b) ? -1 : 1;
    }
});
