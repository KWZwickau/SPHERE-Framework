<?php
namespace SPHERE\System\Extension\Repository;

/**
 * Class Sorter
 *
 * @package SPHERE\System\Extension\Repository
 */
class Sorter
{

    const ORDER_ASC = 1;
    const ORDER_DESC = -1;

    /** @var array $List */
    private $List = array();

    /**
     * @param array $List
     *
     * @throws \Exception
     */
    public function __construct($List)
    {

        $this->List = $List;
    }

    /**
     * @param string|array $PropertyList
     * @param int $Order
     *
     * @return array
     */
    public function sortObjectList($PropertyList, $Order = Sorter::ORDER_ASC)
    {

        usort($this->List, function ($A, $B) use ($PropertyList, $Order) {

            if (method_exists($A, 'get' . $PropertyList) && method_exists($B, 'get' . $PropertyList)) {
                switch ($Order) {
                    case self::ORDER_ASC:
                        return strnatcmp($A->{'get' . $PropertyList}(), $B->{'get' . $PropertyList}());
                    case self::ORDER_DESC:
                        return strnatcmp($B->{'get' . $PropertyList}(), $A->{'get' . $PropertyList}());
                }
            }
            return 0;
        });

        return $this->List;
    }
}
