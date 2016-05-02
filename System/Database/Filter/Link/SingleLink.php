<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Database\Filter\Logic\AndLogic;
use SPHERE\System\Database\Filter\Logic\OrLogic;
use SPHERE\System\Database\Fitting\Element;

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

        $Key = array(
            $this->getProbe(0)->getEntity()->getEntityFullName(),
            $this->getProbe(1)->getEntity()->getEntityFullName(),
            $SearchLeft,
            $SearchRight
        );
        $Cache = new DataCacheHandler(json_encode($Key), array(
            $this->getProbe(0)->getEntity(),
            $this->getProbe(1)->getEntity()
        ));

        if (!self::$Cache || null === ( $Result = $Cache->getData() )) {
            $Result = array();

            $LeftLogic = (new AndLogic($this->getProbe(0)->useBuilder()))
                ->addLogic(
                    (new AndLogic($this->getProbe(0)->useBuilder()))->addCriteriaList(
                        $SearchLeft, OrLogic::COMPARISON_LIKE
                    )
                )->addLogic(
                    (new AndLogic($this->getProbe(0)->useBuilder()))->addCriteria(
                        'EntityRemove', null, AndLogic::COMPARISON_EXACT
                    )
                );
            $EntityListLeft = $this->getProbe(0)->findLogic($LeftLogic);

            if ($EntityListLeft) {

                $RestrictionRight = array(
                    $this->getPath(1) => $this->getProbe(0)->findLogicColumn($LeftLogic, $this->getPath(0))
                );

                $RightLogic = (new AndLogic($this->getProbe(1)->useBuilder()))
                    ->addLogic(
                        (new OrLogic($this->getProbe(1)->useBuilder()))->addCriteriaList(
                            $RestrictionRight, OrLogic::COMPARISON_EXACT
                        )
                    )->addLogic(
                        (new AndLogic($this->getProbe(1)->useBuilder()))->addCriteriaList(
                            $SearchRight, OrLogic::COMPARISON_LIKE
                        )
                    )->addLogic(
                        (new AndLogic($this->getProbe(1)->useBuilder()))->addCriteria(
                            'EntityRemove', null, AndLogic::COMPARISON_EXACT
                        )
                    );

                $EntityListRight = $this->getProbe(1)->findLogic($RightLogic);
                if ($EntityListRight) {
                    array_walk($EntityListRight,
                        function (Element $Right) use (&$Result, $EntityListLeft) {

                            array_walk($EntityListLeft,
                                function (Element $Left) use (&$Result, $Right) {

                                    $LeftData = $Left->__toArray();
                                    $RightData = $Right->__toArray();

                                    if (
                                    ( $LeftData[$this->getPath(0)] == $RightData[$this->getPath(1)] )
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
            $Cache->setData($Result);
        }
        return $Result;
    }
}
