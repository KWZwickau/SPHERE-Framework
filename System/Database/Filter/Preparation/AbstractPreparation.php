<?php
namespace SPHERE\System\Database\Filter\Preparation;

/**
 * Class AbstractPreparation
 *
 * @package SPHERE\System\Database\Filter\Preparation
 */
abstract class AbstractPreparation
{

    /** @var array $PropertyList */
    private $PropertyList = array();

    /**
     * @return array
     */
    public function __toArray()
    {

        return $this->getPropertyList();
    }

    /**
     * @return array
     */
    public function getPropertyList()
    {

        return $this->PropertyList;
    }

    /**
     * @param $Property
     * @param $Value
     */
    public function setPropertyList($Property, $Value)
    {

        $this->PropertyList[$Property] = $Value;
    }
}
