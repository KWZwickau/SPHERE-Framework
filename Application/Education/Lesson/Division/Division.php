<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Division
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Division implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Klassen')
//                , new Link\Icon(new Check()))
            ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, __CLASS__.'::frontendDashboard'
//        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendCreateLevelDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Division', __NAMESPACE__.'\Frontend::frontendChangeDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Division', __NAMESPACE__.'\Frontend::frontendDestroyDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Add', __NAMESPACE__.'\Frontend::frontendAddSubjectGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Change', __NAMESPACE__.'\Frontend::frontendChangeSubjectGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Remove', __NAMESPACE__.'\Frontend::frontendRemoveSubjectGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Add', __NAMESPACE__.'\Frontend::frontendAddStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Add', __NAMESPACE__.'\Frontend::frontendAddTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Subject/Add', __NAMESPACE__.'\Frontend::frontendAddSubject'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Add', __NAMESPACE__.'\Frontend::frontendAddSubjectStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Show', __NAMESPACE__.'\Frontend::frontendShowSubjectTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Add', __NAMESPACE__.'\Frontend::frontendAddSubjectTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Show', __NAMESPACE__.'\Frontend::frontendDivisionShow'
        ));
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

//    /**
//     * @return Stage
//     */
//    public function frontendDashboard()
//    {
//
//        $Stage = new Stage('Dashboard', 'Klassen');
//
//        $Stage->addButton(new Standard('Schulklasse', __NAMESPACE__.'\Create\LevelDivision', null, null, 'erstellen / bearbeiten'));
//
//        $tblDivisionList = $this->useService()->getDivisionAll();
//        if ($tblDivisionList) {
//            foreach ($tblDivisionList as $tblDivision) {
//                $tblDivision->Year = $tblDivision->getServiceTblYear()->getName();
//                $tblPeriodAll = $tblDivision->getServiceTblYear()->getTblPeriodAll();
//                $Period = array();
//                if ($tblPeriodAll) {
//                    foreach ($tblPeriodAll as $tblPeriod) {
//                        $Period[] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
//                    }
//                    $tblDivision->Period = implode('<br/>', $Period);
//                } else {
//                    $tblDivision->Period = 'fehlt';
//                }
//                if ($tblDivision->getTblLevel()) {
//                    $tblDivision->Group = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
//                    $tblDivision->LevelType = $tblDivision->getTblLevel()->getServiceTblType()->getName();
//                } else {
//                    $tblDivision->Group = $tblDivision->getName();
//                    $tblDivision->LevelType = '';
//                }
//
//
//                $StudentList = Division::useService()->getStudentAllByDivision($tblDivision);
//                $TeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
//                $SubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
//                $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
//                $SubjectUsedCount = 0;
//                if (!$StudentList) {
//                    $StudentList = null;
//                }
//                if (!$TeacherList) {
//                    $TeacherList = null;
//                }
//                if (!$SubjectList) {
//                    $SubjectList = null;
//                }
//                if (!$DivisionSubjectList) {
//                } else {
//                    foreach ($DivisionSubjectList as $DivisionSubject) {
//
//                        if (!$DivisionSubject->getTblSubjectGroup()) {
//                            $tblDivisionSubjectActiveList = Division::useService()->getDivisionSubjectBySubjectAndDivision($DivisionSubject->getServiceTblSubject(), $tblDivision);
//                            $TeacherGroup = array();
//                            if ($tblDivisionSubjectActiveList) {
//                                /**@var TblDivisionSubject $tblDivisionSubjectActive */
//                                foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
//                                    $TempList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
//                                    if ($TempList) {
//                                        foreach ($TempList as $Temp)
//                                            array_push($TeacherGroup, $Temp->getServiceTblPerson()->getFullName());
//                                    }
//                                }
//                                if (empty( $TeacherGroup )) {
//                                    $SubjectUsedCount = $SubjectUsedCount + 1;
//                                }
//                            }
//                        }
//
//
////                        $TeacherListUsed = Division::useService()->getSubjectTeacherByDivisionSubject($DivisionSubject);
////                        if (!$DivisionSubject->getTblSubjectGroup()) {
////                            if (!$TeacherListUsed) {
////                                $SubjectUsedCount = $SubjectUsedCount + 1;
////                            }
////                        }
//                    }
//                }
//                $tblDivision->StudentList = count($StudentList);
//                $tblDivision->TeacherList = count($TeacherList);
//                if ($SubjectUsedCount !== 0) {
//                    $tblDivision->SubjectList = count($SubjectList).new Danger(' ('.$SubjectUsedCount.')');
//                } else {
//                    $tblDivision->SubjectList = count($SubjectList);
//                }
//                $tblDivision->Button = new Standard('&nbsp;Klassenansicht', '/Education/Lesson/Division/Show',
//                    new EyeOpen(), array('Id' => $tblDivision->getId()), 'Klassenansicht');
//
//            }
//        }
//
//        $Stage->setContent(
//            new Layout(
//                new LayoutGroup(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new TableData($tblDivisionList, null, array('Year'        => 'Schuljahr',
//                                                                        'Period'      => 'Zeitraum',
//                                                                        'LevelType'   => 'Schultyp',
//                                                                        'Group'       => 'Schulklasse',
//                                                                        'StudentList' => 'Schüler',
//                                                                        'TeacherList' => 'Klassenlehrer',
//                                                                        'SubjectList' => 'Fächer',
//                                                                        'Button'      => 'Option',
//                            ))
//                        )
//                    )
//                )
//            )
//        );
//        return $Stage;
//    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Division', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
