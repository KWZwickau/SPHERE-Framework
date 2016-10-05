<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Filter\Link\Probe;

/**
 * Class Node1
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node1 extends AbstractNode
{

    /**
     * @param AbstractView[][] $List
     * @param int $Timeout
     * @param Probe[] $ProbeList
     * @param array $SearchList
     *
     * @return array
     */
    protected function parseResult($List, $Timeout = 60, $ProbeList = array(), $SearchList = array())
    {

        $this->setTimeout($Timeout);

        $Result = array();
        try {
            /** @var AbstractView $Node0 */
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
