<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
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
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Klassen'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/LevelDivision', __NAMESPACE__.'\Frontend::frontendCreateLevelDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Division', __NAMESPACE__.'\Frontend::frontendChangeDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Division', __NAMESPACE__.'\Frontend::frontendDestroyDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Add', __NAMESPACE__.'\Frontend::frontendSubjectGroupAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Change', __NAMESPACE__.'\Frontend::frontendSubjectGroupChange'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Remove', __NAMESPACE__.'\Frontend::frontendSubjectGroupRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Add', __NAMESPACE__.'\Frontend::frontendStudentAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Add', __NAMESPACE__.'\Frontend::frontendTeacherAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Subject/Add', __NAMESPACE__.'\Frontend::frontendSubjectAdd'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/SubjectStudent/Show', __NAMESPACE__.'\Frontend::frontendSubjectStudentShow'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/SubjectStudent/Add', __NAMESPACE__.'\Frontend::frontendSubjectStudentAdd'
//        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Remove', __NAMESPACE__.'\Frontend::frontendSubjectStudentRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Show', __NAMESPACE__.'\Frontend::frontendSubjectTeacherShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Add', __NAMESPACE__.'\Frontend::frontendSubjectTeacherAdd'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/SubjectTeacher/Remove', __NAMESPACE__.'\Frontend::frontendSubjectTeacherRemove'
//        ));
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

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Klassen');

        $Stage->addButton(new Standard('Schulklasse', __NAMESPACE__.'\Create\LevelDivision', null, null, 'erstellen / bearbeiten'));
        $Stage->addButton(new Standard('Unterrichtsgruppen', __NAMESPACE__.'\Create\SubjectGroup', null, null, 'erstellen / bearbeiten'));

        $tblDivisionList = $this->useService()->getDivisionAll();
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                $tblDivision->Year = $tblDivision->getServiceTblYear()->getName();
                $tblPeriodAll = $tblDivision->getServiceTblYear()->getTblPeriodAll();
                $Period = array();
                if ($tblPeriodAll) {
                    foreach ($tblPeriodAll as $tblPeriod) {
                        $Period[] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    }
                    $tblDivision->Period = implode('<br/>', $Period);
                } else {
                    $tblDivision->Period = 'fehlt';
                }
                if ($tblDivision->getTblLevel()) {
                    $tblDivision->Group = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
                    $tblDivision->LevelType = $tblDivision->getTblLevel()->getServiceTblType()->getName();
                } else {
                    $tblDivision->Group = '';
                    $tblDivision->LevelType = '';
                }


                $StudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                $TeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                $SubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                $SubjectUsedCount = 0;
                if (!$StudentList) {
                    $StudentList = null;
                }
                if (!$TeacherList) {
                    $TeacherList = null;
                }
                if (!$SubjectList) {
                    $SubjectList = null;
                }
                if (!$DivisionSubjectList) {
                } else {
                    foreach ($DivisionSubjectList as $DivisionSubject) {
                        $TeacherListUsed = Division::useService()->getSubjectTeacherByDivisionSubject($DivisionSubject);
                        if (!$TeacherListUsed) {
                            $SubjectUsedCount = $SubjectUsedCount + 1;
                        }
                    }
                }
                $tblDivision->StudentList = count($StudentList);
                $tblDivision->TeacherList = count($TeacherList);
                if ($SubjectUsedCount !== 0) {
                    $tblDivision->SubjectList = count($SubjectList).new Danger(' ('.$SubjectUsedCount.')');
                } else {
                    $tblDivision->SubjectList = count($SubjectList);
                }
                $tblDivision->Button = new Standard('&nbsp;Klassenansicht', '/Education/Lesson/Division/Show',
                    new EyeOpen(), array('Id' => $tblDivision->getId()), 'Klassenansicht');

            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblDivisionList, null, array('Year'        => 'Schuljahr',
                                                                        'Period'      => 'Zeitraum',
                                                                        'LevelType'   => 'Schultyp',
                                                                        'Group'       => 'Schulklasse',
                                                                        'StudentList' => 'Schüler',
                                                                        'TeacherList' => 'Klassenlehrer',
                                                                        'SubjectList' => 'Fächer',
                                                                        'Button'      => 'Option',
                            ))
                        )
                    )
                )
            )
        );
        return $Stage;
    }

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
