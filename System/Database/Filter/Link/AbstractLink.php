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

    protected static $Cache = false;
    /** @var array $PathList */
    private $PathList = array();
    /** @var Probe[] $ProbeList */
    private $ProbeList = array();

    /**
     *
     * @param AbstractService $Service
     * @param Element         $Entity
     *
     * @return $this
     */
    public function addProbe(AbstractService $Service, Element $Entity)
    {

        array_push($this->ProbeList, new Probe($Service, $Entity));
        return $this;
    }

    /**
     * @param int $Index
     *
     * @return Probe
     */
    public function getProbe($Index)
    {

        return $this->ProbeList[$Index];
    }

    /**
     * @return Probe[]
     */
    public function getProbeList()
    {

        return $this->ProbeList;
    }

    /**
     * @param int $Index
     *
     * @return array
     */
    public function getPath($Index)
    {

        return $this->PathList[$Index];
    }

    /**
     * @param null|string $ParentProperty
     * @param null|string $ChildProperty
     *
     * @return $this
     */
    public function addPath($ParentProperty = null, $ChildProperty = null)
    {

        array_push($this->PathList, array($ParentProperty, $ChildProperty));
        return $this;
    }

    /**
     * @return array
     */
    public function getPathList()
    {

        return $this->PathList;
    }
}
