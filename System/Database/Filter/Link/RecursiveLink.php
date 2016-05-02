<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;

class RecursiveLink extends AbstractLink
{

    /**
     * @param array $Search array( ProbeIndex => array( 'Column' => 'Value', ... ), ... )
     *
     * @return bool|Element[]
     */
    public function searchData($Search)
    {

        $ProbeList = $this->getProbeList();

        $CacheKey = array();
        $CacheDependency = array();
        foreach ($ProbeList as $Probe) {
            array_push($CacheKey, $Probe->getEntity()->getEntityFullName());
            array_push($CacheDependency, $Probe->getEntity());
        }
        array_push($CacheKey, $Search);
        $Cache = new DataCacheHandler(json_encode($CacheKey), $CacheDependency);

        if (!self::$Cache || null === ( $Result = $Cache->getData() )) {
            $Result = array();
            $ResultCache = array();

            $Restriction = array();
            foreach ($ProbeList as $Index => $Probe) {
                if (isset( $Search[$Index] )) {
                    $Filter = $Search[$Index];
                } else {
                    $Filter = array();
                }

                $Logic = $this->createLogic($Filter, $Restriction, $Index);

                $EntityList = $Probe->findLogic($Logic);
                // Exit if Path is Empty = NO Result
                if (empty( $EntityList )) {
                    return array();
                }
                $ResultCache[$Index] = $EntityList;

                $PathCurrent = $this->getPath($Index);
                if (isset( $ProbeList[$Index + 1] )) {
                    $PathNext = $this->getPath($Index + 1);

                    $Restriction = array(
                        $PathNext[0] => $Probe->findLogicColumn($Logic, $PathCurrent[1])
                    );
                }

            }
//            $Cache->setData($Result);
        }
        return $ResultCache;
    }

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

    public function parseResult($List, $Level, $Entities)
    {

        foreach ($List as $Entity) {

        }

        if ($Level == 0) {
            $Return = array();
            /** @var Element $Entity */
            foreach ($Entities as $Index => $Entity) {

                $PathCurrent = $this->getPath($Index);
                $EntityCurrent = $Entity->__toArray();
                $PathParent = $this->getPath($Index - 1);
                $EntityParent = $Entity->__toArray();

                if (
                    $EntityParent[$PathParent[1]] == $EntityCurrent[$PathCurrent[0]]
                ) {
                    $Return[$Entity->getEntityFullName()] = $Entity;
                }
            }
            if (count($Entities) == count($Return)) {
                return $Return;
            } else {
                return false;
            }
        } else {
            return $this->parseResult();
        }
    }
}
