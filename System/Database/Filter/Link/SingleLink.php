<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class SingleLink
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class SingleLink extends AbstractLink
{
    /**
     * @param array $SearchLeft
     * @param array $SearchRight
     *
     * @return bool|Element[]
     */
    public function searchData($SearchLeft = array(), $SearchRight = array())
    {
        $Result = array();

        $LeftLogic = (new AndLogic($this->getProbeLeft()->useBuilder()))
            ->addLogic(
                (new AndLogic($this->getProbeLeft()->useBuilder()))->addCriteriaList(
                    $SearchLeft, OrLogic::COMPARISON_LIKE
                )
            )->addLogic(
                (new AndLogic($this->getProbeLeft()->useBuilder()))->addCriteria(
                    'EntityRemove', null
                )
            );
        $EntityListLeft = $this->getProbeLeft()->findLogic($LeftLogic);

        if ($EntityListLeft) {

            $SearchConnect = $this->getLinkPathCritria($this->getLinkPath(0), $this->getLinkPath(1), $EntityListLeft);
            $RightLogic = (new AndLogic($this->getProbeLeft()->useBuilder()))
                ->addLogic(
                    (new OrLogic($this->getProbeLeft()->useBuilder()))->addCriteriaList(
                        $SearchConnect, OrLogic::COMPARISON_EXACT
                    )
                )->addLogic(
                    (new AndLogic($this->getProbeLeft()->useBuilder()))->addCriteriaList(
                        $SearchRight, OrLogic::COMPARISON_LIKE
                    )
                )->addLogic(
                    (new AndLogic($this->getProbeLeft()->useBuilder()))->addCriteria(
                        'EntityRemove', null
                    )
                );

            $EntityListRight = $this->getProbeRight()->findLogic($RightLogic);
            Debugger::screenDump($EntityListRight);
            if ($EntityListRight) {
                array_walk($EntityListRight,
                    function (Element $Right) use (&$Result, $EntityListLeft) {

                        array_walk($EntityListLeft,
                            function (Element $Left) use (&$Result, $Right) {

                                $LeftData = $Left->__toArray();
                                $RightData = $Right->__toArray();

                                if (
                                ($LeftData[$this->getLinkPath(0)] == $RightData[$this->getLinkPath(1)])
                                ) {
                                    $Result[] = array(
                                        $Left->getEntityFullName() => $Left,
                                        $Right->getEntityFullName() => $Right,
                                    );
                                }

                            });
                    });
            }
        }

        return $Result;
    }
}
