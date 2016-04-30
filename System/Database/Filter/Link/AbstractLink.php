<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractLink
 *
 * @package SPHERE\System\Database\Filter\Link
 */
abstract class AbstractLink
{

    /** @var array $LinkPath */
    private $LinkPath = array();
    /** @var null|Probe $ProbeLeft */
    private $ProbeLeft = null;
    /** @var null|Probe $ProbeRight */
    private $ProbeRight = null;

    /**
     * @param AbstractService $Service
     * @param Element $Entity
     *
     * @return $this
     */
    public function setupProbeLeft(AbstractService $Service, Element $Entity)
    {

        $this->ProbeLeft = new Probe($Service, $Entity);
        return $this;
    }

    /**
     * @param AbstractService $Service
     * @param Element $Entity
     *
     * @return $this
     */
    public function setupProbeRight(AbstractService $Service, Element $Entity)
    {

        $this->ProbeRight = new Probe($Service, $Entity);
        return $this;
    }

    /**
     * @return null|Probe
     */
    public function getProbeLeft()
    {

        return $this->ProbeLeft;
    }

    /**
     * @return null|Probe
     */
    public function getProbeRight()
    {

        return $this->ProbeRight;
    }

    /**
     * @param int $Index
     * @return string
     */
    public function getLinkPath($Index)
    {

        return $this->LinkPath[$Index];
    }

    /**
     * @param string $Property Property
     * @return $this
     */
    public function addLinkPath($Property)
    {

        array_push($this->LinkPath, $Property);
        return $this;
    }

    /**
     * @param int $IndexFrom
     * @param int $IndexTo
     * @param Element[] $EntityList
     * @return array
     */
    protected function getLinkPathCritria($IndexFrom, $IndexTo, $EntityList)
    {
        return array(
            $IndexTo => array_map(function (Element $Entity) use ($IndexFrom) {
                // TODO: Performance, avoid __toArray
                $Entity = $Entity->__toArray();
                return $Entity[$IndexFrom];
            }, $EntityList)
        );
    }


}
