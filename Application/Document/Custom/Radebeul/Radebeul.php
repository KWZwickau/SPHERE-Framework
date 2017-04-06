<?php

namespace SPHERE\Application\Document\Custom\Radebeul;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Reporting\AbstractModule;
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
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schülerbogen', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new External(
                            'Herunterladen',
                            'SPHERE\Application\Api\Document\Custom\Radebeul\StudentCard\Create',
                            new Download(),
                            array(
                                'PersonId' => $tblPerson->getId(),
                            ),
                            'Notfallzettel herunterladen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse',
                                    'Option'   => ''
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
        $tblDivisionList = array();

        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionArray = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionArray) {
                    foreach ($tblDivisionArray as $tblDivision) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }
        }

        if (!empty($tblDivisionList)) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                $tblPersonPrepareList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonPrepareList) {
                    foreach ($tblPersonPrepareList as $tblPerson) {
                        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                        if ($tblGroupList) {
                            foreach ($tblGroupList as $tblGroupSingle) {
                                if ($tblGroupSingle->getName() == 'Frühling') {
                                    $StudentList[$tblDivision->getTblLevel()->getName()]['Frühling'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                }
                                if ($tblGroupSingle->getName() == 'Sommer') {
                                    $StudentList[$tblDivision->getTblLevel()->getName()]['Sommer'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                }
                                if ($tblGroupSingle->getName() == 'Herbst') {
                                    $StudentList[$tblDivision->getTblLevel()->getName()]['Herbst'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                }
                                if ($tblGroupSingle->getName() == 'Winter') {
                                    $StudentList[$tblDivision->getTblLevel()->getName()]['Winter'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                }
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
    public function frontendStudentList()
    {

        $Stage = new Stage('SchülerListe');

        $Stage->addbutton(new External('Herunterladen',
            'SPHERE\Application\Api\Document\Custom\Radebeul\StudentList\Create',
            new Download(), array(), 'Schülerliste Herungerladen'));

        $StudentList = $this->getPersonListByRadebeul();
//        Debugger::screenDump($StudentList);
        $columnList = array();
        if ($StudentList) {
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