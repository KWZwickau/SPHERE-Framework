<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

class Node5 extends AbstractNode
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

        array_walk($List[4], function (Element $Node4) use (&$Result, $List) {

            array_walk($List[3], function (Element $Node3) use (&$Result, $List, $Node4) {

                array_walk($List[2], function (Element $Node2) use (&$Result, $List, $Node3, $Node4) {

                    array_walk($List[1], function (Element $Node1) use (&$Result, $List, $Node2, $Node3, $Node4) {

                        array_walk($List[0], function (Element $Node0) use (&$Result, $Node1, $Node2, $Node3, $Node4) {

                            $Data0 = $Node0->__toArray();
                            $Data1 = $Node1->__toArray();
                            $Data2 = $Node2->__toArray();
                            $Data3 = $Node3->__toArray();
                            $Data4 = $Node4->__toArray();

                            if (
                                ( $Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]] )
                                && ( $Data1[$this->getPath(1)[1]] == $Data2[$this->getPath(2)[0]] )
                                && ( $Data2[$this->getPath(2)[1]] == $Data3[$this->getPath(3)[0]] )
                                && ( $Data3[$this->getPath(3)[1]] == $Data4[$this->getPath(4)[0]] )
                            ) {
                                $Result[] = array(
                                    $Node0,
                                    $Node1,
                                    $Node2,
                                    $Node3,
                                    $Node4,
                                );
                            }

                        });
                    });
                });
            });
        });
        return $Result;
    }
}
