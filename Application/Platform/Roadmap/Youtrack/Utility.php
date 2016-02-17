<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

/**
 * Class Utility
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Utility
{

    /**
     * http://php.net/manual/de/function.usort.php#89977
     *
     * @param Issue[]|Sprint[] $List      the array we want to sort
     * @param string           $Clause    a string specifying how to sort the array similar to SQL ORDER BY clause
     *                                    e.g. 'getPropertyMethod() ASC, getPropertyMethod() DESC'
     * @param bool             $Ascending that default sorts fall back to when no direction is specified
     *
     * @return null
     */
    public static function orderIssuesBy(&$List, $Clause, $Ascending = true)
    {

        $Clause = str_ireplace('order by', '', $Clause);
        $Clause = preg_replace('/\s+/', ' ', $Clause);
        $Keys = explode(',', $Clause);
        $DirectionMap = array('desc' => 1, 'asc' => -1);
        $DirectionMode = $Ascending ? -1 : 1;

        $KeyList = array();
        $DirectionList = array();
        foreach ($Keys as $Key) {
            $Key = explode(' ', trim($Key));
            $KeyList[] = trim($Key[0]);
            if (isset( $Key[1] )) {
                $Direction = strtolower(trim($Key[1]));
                $DirectionList[] = $DirectionMap[$Direction] ? $DirectionMap[$Direction] : $DirectionMode;
            } else {
                $DirectionList[] = $DirectionMode;
            }
        }

        $InternalFunction = '';
        for ($RunKey = count($KeyList) - 1; $RunKey >= 0; $RunKey--) {
            $Key = $KeyList[$RunKey];
            $Direction = $DirectionList[$RunKey];
            $Reverse = -1 * $Direction;
            $PropertyA = '$a[\''.$Key.'\']';
            $PropertyB = '$b[\''.$Key.'\']';
            if (strpos($Key, '(') !== false) {
                $PropertyA = '$a->'.$Key;
                $PropertyB = '$b->'.$Key;
            }

            if ($InternalFunction == '') {
                $InternalFunction .= "if({$PropertyA} == {$PropertyB}) { return 0; }\n";
                $InternalFunction .= "return ({$PropertyA} < {$PropertyB}) ? {$Direction} : {$Reverse};\n";
            } else {
                $InternalFunction = "if({$PropertyA} == {$PropertyB}) {\n".$InternalFunction;
                $InternalFunction .= "}\n";
                $InternalFunction .= "return ({$PropertyA} < {$PropertyB}) ? {$Direction} : {$Reverse};\n";
            }
        }

        if ($InternalFunction) {
            $SortFunction = create_function('$a,$b', $InternalFunction);
            usort($List, $SortFunction);
        }
    }
}
