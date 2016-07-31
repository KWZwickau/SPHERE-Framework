<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node2
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node2 extends AbstractNode
{
    /**
     * @param array $List
     * @param int $Timeout
     * @return array
     */
    protected function parseResult($List, $Timeout = 60)
    {
        $this->setTimeout($Timeout);

        $Result = array();
        try {
            foreach( $List[1] as $Node1 ) {
                foreach ($List[0] as $Node0) {

                    $Data0 = $this->fetchArray($Node0);
                    $Data1 = $this->fetchArray($Node1);

                    if (
                    ($Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]])
                    ) {
                        $Result[] = array(
                            $Node0,
                            $Node1,
                        );
                    }
                    if ($this->checkTimeout()) {
                        throw new NodeException();
                    }
                }
            }
        } catch (NodeException $E) {
            return $Result;
        }
        return $Result;
    }
}
