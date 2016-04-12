<?php
namespace SPHERE\System\Extension\Repository\Sorter;

use SPHERE\System\Database\Fitting\Element;

/**
 * Class StringNaturalOrderSorter
 *
 * @package SPHERE\System\Extension\Repository\Sorter
 */
class StringNaturalOrderSorter extends AbstractSorter
{

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    public function sortAsc($Property, Element $First, Element $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {
            return strnatcmp($this->getValue($Property, $First), $this->getValue($Property, $Second));
        }
        return 0;
    }

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    public function sortDesc($Property, Element $First, Element $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {
            return strnatcmp($this->getValue($Property, $Second), $this->getValue($Property, $First));
        }
        return 0;
    }

}
