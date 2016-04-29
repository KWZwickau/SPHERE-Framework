<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class MultipleLink
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class ConnectLink extends AbstractLink
{

    /** @var null|Probe $ProbeCenter */
    private $ProbeCenter = null;

    /**
     * @param AbstractService $Service
     * @param Element $Entity
     *
     * @return $this
     */
    public function setupProbeCenter(AbstractService $Service, Element $Entity)
    {

        $this->ProbeCenter = new Probe($Service, $Entity);
        return $this;
    }

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
                    'EntityRemove', null, AndLogic::COMPARISON_EXACT
                )
            );
        $EntityListLeft = $this->getProbeLeft()->findLogic($LeftLogic);

        if ($EntityListLeft) {

            $SearchCenter = $this->getLinkPathCritria($this->getLinkPath(0), $this->getLinkPath(1), $EntityListLeft);
            $CenterLogic = (new AndLogic($this->getProbeLeft()->useBuilder()))
                ->addLogic(
                    (new OrLogic($this->getProbeCenter()->useBuilder()))->addCriteriaList(
                        $SearchCenter, OrLogic::COMPARISON_EXACT
                    )
                )->addLogic(
                    (new AndLogic($this->getProbeLeft()->useBuilder()))->addCriteria(
                        'EntityRemove', null, AndLogic::COMPARISON_EXACT
                    )
                );
            $EntityListCenter = $this->getProbeCenter()->findLogic($CenterLogic);

            if ($EntityListCenter) {
                $SearchConnect = $this->getLinkPathCritria($this->getLinkPath(2), $this->getLinkPath(3),
                    $EntityListCenter);
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
                            'EntityRemove', null, AndLogic::COMPARISON_EXACT
                        )
                    );
                $EntityListRight = $this->getProbeRight()->findLogic($RightLogic);
                if ($EntityListRight) {
                    array_walk($EntityListRight,
                        function (Element $Right) use (&$Result, $EntityListCenter, $EntityListLeft) {

                            array_walk($EntityListCenter,
                                function (Element $Center) use (&$Result, $EntityListLeft, $Right) {

                                    array_walk($EntityListLeft,
                                        function (Element $Left) use (&$Result, $Right, $Center) {

                                            $LeftData = $Left->__toArray();
                                            $CenterData = $Center->__toArray();
                                            $RightData = $Right->__toArray();

                                            if (
                                                ($LeftData[$this->getLinkPath(0)] == $CenterData[$this->getLinkPath(1)])
                                                && ($CenterData[$this->getLinkPath(2)] == $RightData[$this->getLinkPath(3)])
                                            ) {
                                                $Result[] = array(
                                                    $Left->getEntityFullName() => $Left,
                                                    $Center->getEntityFullName() => $Center,
                                                    $Right->getEntityFullName() => $Right,
                                                );
                                            }

                                        });
                                });
                        });
                }
            }
        }

        return $Result;
    }

    /**
     * @return null|Probe
     */
    public function getProbeCenter()
    {

        return $this->ProbeCenter;
    }
}
