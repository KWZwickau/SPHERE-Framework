<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node1
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node1 extends AbstractNode
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
            foreach ($List[0] as $Node0) {

                $Result[] = array(
                    $Node0,
                );
                if ($this->checkTimeout()) {
                    throw new NodeException();
                }
            }
        } catch (NodeException $E) {
            return $Result;
        }
        return $Result;
    }
}
