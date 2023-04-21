<?php

namespace SPHERE\Application\Document\Custom\Radebeul;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Radebeul
 *
 * @package SPHERE\Application\Document\Custom\Radebeul
 */
class Radebeul extends AbstractModule implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/StudentCard'), new Link\Name('Schülerbogen'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentCard', __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/StudentList'), new Link\Name('Schülerliste'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentList', __CLASS__.'::frontendStudentList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson(): Stage
    {
        $Stage = new Stage('Schülerbogen', 'Schüler auswählen');

        $dataList = array();
        $showDivision = false;
        $showCoreGroup = false;
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $displayDivision = '';
                $displayCoreGroup = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if (($tblDivision = $tblStudentEducation->getTblDivision())
                        && ($displayDivision = $tblDivision->getName())
                    ) {
                        $showDivision = true;
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        && ($displayCoreGroup = $tblCoreGroup->getName())
                    ) {
                        $showCoreGroup = true;
                    }
                }
                $tblAddress = $tblPerson->fetchMainAddress();
                $dataList[] = array(
                    'Name'     => $tblPerson->getLastFirstName(),
                    'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                    'Division' => $displayDivision,
                    'CoreGroup' => $displayCoreGroup,
                    'Option'   => new External(
                        'Herunterladen',
                        'SPHERE\Application\Api\Document\Custom\Radebeul\StudentCard\Create',
                        new Download(),
                        array(
                            'PersonId' => $tblPerson->getId(),
                        ),
                        'Schülerbogen herunterladen'
                    )
                );
            }
        }

        $columnList['Name'] = 'Name';
        $columnList['Address'] = 'Adresse';
        if ($showDivision) {
            $columnList['Division'] = 'Klasse';
        }
        if ($showCoreGroup) {
            $columnList['CoreGroup'] = 'Stammgruppe';
        }
        $columnList['Option'] = '';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                $columnList,
                                array(
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                    ),
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @return array|bool
     */
    public static function getPersonListByRadebeul()
    {
        $StudentList = array();
        if (($tblSchoolTypeGs = Type::useService()->getTypeByShortName('GS'))
            && ($tblYearList = Term::useService()->getYearByNow())
        ) {
            foreach ($tblYearList as $tblYear) {
                if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolTypeGs))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (!$tblStudentEducation->isInActive()
                            && ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
                            && ($level = $tblStudentEducation->getLevel())
                        ) {
                            if ($tblCoreGroup->getName() == 'Frühling' || $tblCoreGroup->getName() == 'Fruehling') {
                                $StudentList[$level]['Frühling'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                            if ($tblCoreGroup->getName() == 'Sommer') {
                                $StudentList[$level]['Sommer'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                            if ($tblCoreGroup->getName() == 'Herbst') {
                                $StudentList[$level]['Herbst'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                            if ($tblCoreGroup->getName() == 'Winter') {
                                $StudentList[$level]['Winter'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                }
            }
        }

        if (!empty($StudentList)) {
            ksort($StudentList);
            return $StudentList;
        } else {
            return false;
        }
    }

    /**
     * @return Stage
     */
    public function frontendStudentList(): Stage
    {
        $Stage = new Stage('SchülerListe');

        $StudentList = $this->getPersonListByRadebeul();
        $columnList = array();
        if ($StudentList) {
            $Stage->addbutton(new External('Herunterladen',
                'SPHERE\Application\Api\Document\Custom\Radebeul\StudentList\Create',
                new Download(), array(), 'Schülerliste herunterladen'));

            foreach ($StudentList as $LevelName => $GroupList) {
                $GroupSpring = array();
                $GroupSummer = array();
                $GroupAutumn = array();
                $GroupWinter = array();
                if ($GroupList) {
                    foreach ($GroupList as $Group => $PersonList) {
                        if ($Group == 'Frühling') {
                            $GroupSpring = $PersonList;
                        }
                        if ($Group == 'Sommer') {
                            $GroupSummer = $PersonList;
                        }
                        if ($Group == 'Herbst') {
                            $GroupAutumn = $PersonList;
                        }
                        if ($Group == 'Winter') {
                            $GroupWinter = $PersonList;
                        }
                    }
                }

                if (!empty($GroupSpring)) {
                    $columnList[] = new LayoutColumn(new Panel('Klasse '.$LevelName.' Gruppe: '.'Frühling',
                        $GroupSpring), 3);
                }
                if (!empty($GroupSummer)) {
                    $columnList[] = new LayoutColumn(new Panel('Klasse '.$LevelName.' Gruppe: '.'Sommer', $GroupSummer),
                        3);
                }
                if (!empty($GroupAutumn)) {
                    $columnList[] = new LayoutColumn(new Panel('Klasse '.$LevelName.' Gruppe: '.'Herbst', $GroupAutumn),
                        3);
                }
                if (!empty($GroupWinter)) {
                    $columnList[] = new LayoutColumn(new Panel('Klasse '.$LevelName.' Gruppe: '.'Winter', $GroupWinter),
                        3);
                }
            }
        }

        if (!empty($columnList)) {
            $Layout = $this->getLayoutByColumnList($columnList);
        } else {
            $Layout = new Warning('Keine Schüler in den Gruppen gefunden (Frühling, Sommer, Herbst oder Winter)');
        }

        $Stage->setContent(
            $Layout
        );

        return $Stage;
    }

    /**
     * @param array $columnList
     *
     * @return Layout
     */
    private function getLayoutByColumnList($columnList = array())
    {
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $column
         */
        foreach ($columnList as $column) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($column);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

}