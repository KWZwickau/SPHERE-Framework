/**
 * Automatically detect German (`dd.mm.yyyy`) date types. Goes with DE
 * date sorting plug-in.
 *
 *  @name Date (`dd.mm.yyyy`)
 *  @summary Detect data which is in the date format `dd.mm.yyyy`
 *  @author Gerd Christian Kunze
 */

jQuery.fn.dataTableExt.aTypes.unshift(
    function(sData)
    {
        if (sData !== null && sData.match(/^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[012])\.(19|20|21)\d\d$/)) {
            return 'date-de';
        }
        return null;
    }
);
