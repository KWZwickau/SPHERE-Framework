<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

class Node2 extends AbstractNode
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

            $Result = $this->parseResult($ResultCache);
            $Cache->setData($Result);
        }
        return $Result;
    }

    private function parseResult($List)
    {

        array_walk($List[1], function (Element $Node1) use (&$Result, $List) {

            array_walk($List[0], function (Element $Node0) use (&$Result, $Node1) {

                $Data0 = $Node0->__toArray();
                $Data1 = $Node1->__toArray();

                if (
                ( $Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]] )
                ) {
                    $Result[] = array(
                        $Node0,
                        $Node1,
                    );
                }

            });
        });
        return $Result;
    }
}
