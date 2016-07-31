<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Node4
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node4 extends AbstractNode
{
    /**
     * @param $List
     * @param int $Timeout
     * @return array
     */
    protected function parseResult($List, $Timeout = 60)
    {
        $this->setTimeout($Timeout);

        $Result = array();
        try {
            array_walk($List[3], function (Element $Node3) use (&$Result, $List) {

                array_walk($List[2], function (Element $Node2) use (&$Result, $List, $Node3) {

                    array_walk($List[1], function (Element $Node1) use (&$Result, $List, $Node2, $Node3) {

                        array_walk($List[0], function (Element $Node0) use (&$Result, $Node1, $Node2, $Node3) {

                            $Data0 = $Node0->__toArray();
                            $Data1 = $Node1->__toArray();
                            $Data2 = $Node2->__toArray();
                            $Data3 = $Node3->__toArray();

                            if (
                                ($Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]])
                                && ($Data1[$this->getPath(1)[1]] == $Data2[$this->getPath(2)[0]])
                                && ($Data2[$this->getPath(2)[1]] == $Data3[$this->getPath(3)[0]])
                            ) {
                                $Result[] = array(
                                    $Node0,
                                    $Node1,
                                    $Node2,
                                    $Node3,
                                );
                            }
                            if ($this->checkTimeout()) {
                                throw new NodeException();
                            }
                        });
                    });
                });
            });
        } catch (NodeException $E) {
            return $Result;
        }
        return $Result;
    }
}
