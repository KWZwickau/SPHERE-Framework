<?php
namespace SPHERE\System\Extension\Repository\Sorter;

use SPHERE\System\Database\Fitting\Element;

/**
 * Class DateTimeSorter
 *
 * @package SPHERE\System\Extension\Repository\Sorter
 */
class DateTimeSorter extends AbstractSorter
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
            $ValueFirst = $this->getDateTime($Property, $First);
            $ValueSecond = $this->getDateTime($Property, $Second);

            return ( $ValueFirst < $ValueSecond ? -1 : ( $ValueFirst > $ValueSecond ? 1 : 0 ) );
        }
        return 0;
    }

    /**
     * @param string  $Property
     * @param Element $Element
     *
     * @return \DateTime
     */
    private function getDateTime($Property, Element $Element)
    {

        $Value = $this->getValue($Property, $Element);
        if ($Value && !$Value instanceof \DateTime) {
            return new \DateTime('@'.strtotime($Value));
        }
        return $Value;
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
            $ValueFirst = $this->getDateTime($Property, $First);
            $ValueSecond = $this->getDateTime($Property, $Second);

            return ( $ValueFirst > $ValueSecond ? -1 : ( $ValueFirst < $ValueSecond ? 1 : 0 ) );
        }
        return 0;
    }

}
