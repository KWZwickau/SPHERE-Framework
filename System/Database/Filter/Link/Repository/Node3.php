<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node3
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node3 extends AbstractNode
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
            foreach ($List[2] as $Node2) {

                foreach ($List[1] as $Node1) {

                    foreach ($List[0] as $Node0) {

                        $Data0 = $this->fetchArray($Node0);
                        $Data1 = $this->fetchArray($Node1);
                        $Data2 = $this->fetchArray($Node2);

                        if (
                            ($Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]])
                            && ($Data1[$this->getPath(1)[1]] == $Data2[$this->getPath(2)[0]])
                        ) {
                            $Result[] = array(
                                $Node0,
                                $Node1,
                                $Node2,
                            );
                        }
                        if ($this->checkTimeout()) {
                            throw new NodeException();
                        }
                    };
                };
            };
        } catch (NodeException $E) {
            return $Result;
        }
        return $Result;
    }
}
