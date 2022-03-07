<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {
        // todo
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param Stage $Stage
     * @param $view
     * @param $Route
     */
    public function setHeaderButtonList(Stage $Stage, $view, $Route)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization($Route . '/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization($Route . '/Headmaster');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        $Route . '/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        $Route . '/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Alle Klassenbücher')),
                        $Route . '/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Alle Klassenbücher',
                        $Route . '/Headmaster'));
                }
            }
        }
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $IsGroup
     * @param $YearId
     * @param $HasAllYears
     * @param $HasCurrentYears
     * @param $yearFilterList
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $IsGroup, $YearId, $HasAllYears, $HasCurrentYears,
        &$yearFilterList)
    {
        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && !$IsGroup && $tblYearList && !$HasCurrentYears) {
            $tblYear = end($tblYearList);
        }
        $isCurrentYears = $HasCurrentYears && !$IsAllYears && !$IsGroup && !$YearId;

        $buttonList = array();
        if ($tblYearList) {
            if ($HasCurrentYears) {
                if ($isCurrentYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Aktuelles Schuljahr')),
                        $Route, new Edit()));
                } else {
                    $buttonList[] = (new Standard('Aktuelles Schuljahr', $Route, null));
                }
            }

            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                    $yearFilterList[$tblYearItem->getId()] = $tblYearItem;
                } else {
                    if ($isCurrentYears || $IsGroup) {
                        $yearFilterList [$tblYearItem->getId()] = $tblYearItem;
                    }

                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            if ($HasAllYears) {
                if ($IsAllYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                        $Route, new Edit(), array('IsAllYears' => true)));
                }  else {
                    $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                        array('IsAllYears' => true)));
                }
            }

            if ($IsGroup) {
                $buttonList[] = (new Standard(new Info(new Bold('Gruppen')),
                    $Route, new Edit(), array('IsGroup' => true)));
            }  else {
                $buttonList[] = (new Standard('Gruppen', $Route, null,
                    array('IsGroup' => true)));
            }

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }
}