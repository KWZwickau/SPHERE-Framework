<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractNode
 *
 * @package SPHERE\System\Database\Filter\Pile
 */
abstract class AbstractNode
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

    /**
     * @param array $Search
     * @param array $Restriction
     * @param int   $ProbeIndex
     *
     * @return AndLogic
     */
    public function createLogic($Search, $Restriction, $ProbeIndex)
    {

        $Logic = (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()));
        if (!empty( $Restriction )) {
            $Logic->addLogic(
                (new OrLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteriaList(
                    $Restriction, OrLogic::COMPARISON_EXACT
                )
            );
        }
        if (!empty( $Search )) {
            $Logic->addLogic(
                (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteriaList(
                    $Search, AndLogic::COMPARISON_LIKE
                )
            );
        }
        $Logic->addLogic(
            (new AndLogic($this->getProbe($ProbeIndex)->useBuilder()))->addCriteria(
                'EntityRemove', null, AndLogic::COMPARISON_EXACT
            )
        );
        return $Logic;
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
}
