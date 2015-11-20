<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Layout\Repository\Badge;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
            __NAMESPACE__.'/Create/Level', __NAMESPACE__.'\Frontend::frontendCreateLevel'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Level', __NAMESPACE__.'\Frontend::frontendChangeLevel'
        )
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Level', __NAMESPACE__.'\Frontend::frontendDestroyLevel'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Division', __NAMESPACE__.'\Frontend::frontendCreateDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Division', __NAMESPACE__.'\Frontend::frontendChangeDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Division', __NAMESPACE__.'\Frontend::frontendDestroyDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/SubjectGroup', __NAMESPACE__.'\Frontend::frontendCreateSubjectGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/SubjectGroup', __NAMESPACE__.'\Frontend::frontendChangeSubjectGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Add', __NAMESPACE__.'\Frontend::frontendStudentAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Remove', __NAMESPACE__.'\Frontend::frontendStudentRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Add', __NAMESPACE__.'\Frontend::frontendTeacherAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Remove', __NAMESPACE__.'\Frontend::frontendTeacherRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Subject/Add', __NAMESPACE__.'\Frontend::frontendSubjectAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Subject/Remove', __NAMESPACE__.'\Frontend::frontendSubjectRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Show', __NAMESPACE__.'\Frontend::frontendSubjectStudentShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Add', __NAMESPACE__.'\Frontend::frontendSubjectStudentAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Remove', __NAMESPACE__.'\Frontend::frontendSubjectStudentRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Show', __NAMESPACE__.'\Frontend::frontendSubjectTeacherShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Add', __NAMESPACE__.'\Frontend::frontendSubjectTeacherAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Remove', __NAMESPACE__.'\Frontend::frontendSubjectTeacherRemove'
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

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Klassen');

        $Stage->addButton(new Standard('Klassenstufe', __NAMESPACE__.'\Create\Level', null, null, 'erstellen / bearbeiten'));
        $Stage->addButton(new Standard('Klassengruppe', __NAMESPACE__.'\Create\Division', null, null, 'erstellen / bearbeiten'));
        $Stage->addButton(new Standard('Gruppen', __NAMESPACE__.'\Create\SubjectGroup', null, null, 'erstellen / bearbeiten'));

        $tblLevelAll = $this->useService()->getLevelAll();
        $Content = array();

        if ($tblLevelAll) {
            /** @var TblLevel $row */
            foreach ((array)$tblLevelAll as $key => $row) {
                $klass[$key] = strtoupper($row->getName());
                $second[$key] = strtoupper($row->getServiceTblType()->getName());
            }
            array_multisort($second, SORT_ASC, $klass, SORT_ASC, $tblLevelAll);

//        sort($tblLevelAll);
        }


        if ($tblLevelAll) {
//            array_push($Content, new LayoutRow(array(
//                new LayoutColumn(array(
//                    new Title(new Italic(new Bold('Unzugeordnet'))),      //ToDO Unzugeordnet verwenden?
//                ))
//            )));

//        $tblUnusedSubjectAll = $this->useService()->getSubjectAllHavingNoCategory();
//        if ($tblUnusedSubjectAll) {
//            array_walk($tblUnusedSubjectAll, function (TblSubject &$tblSubject) {
//
//                $tblSubject = new Bold($tblSubject->getAcronym()).' - '
//                    .$tblSubject->getName().' '
//                    .new Small(new Muted($tblSubject->getDescription()));
//            });
//        } else {
//            $tblUnusedSubjectAll = new Success('Keine unzugeordneten Klassen');
//        }
//        $tblUnusedCategoryAll = $this->useService()->getCategoryAllHavingNoGroup();
//        if ($tblUnusedCategoryAll) {
//            array_walk($tblUnusedCategoryAll, function (TblCategory &$tblCategory) {
//
//                $tblCategory = new Bold($tblCategory->getName()).' - '
//                    .new Small(new Muted($tblCategory->getDescription()));
//            });
//        } else {
//            $tblUnusedCategoryAll = new Success('Keine unzugeordneten Kategorien');
//        }

//        array_push($Content, new LayoutRow(array(
//            new LayoutColumn(new Panel('Kategorien', $tblUnusedCategoryAll), 6),
//            new LayoutColumn(new Panel('Klassen', $tblUnusedSubjectAll), 6),
//        )));

            // Payload
            array_walk($tblLevelAll, function (TblLevel $tblLevel) use (&$Content) {

                array_push($Content, new LayoutRow(array(
                    new LayoutColumn(array(
                        new Title('Klassenstufe: '.new Bold($tblLevel->getName()).' '.$tblLevel->getServiceTblType()->getName())
                    ))
                )));
                $tblDivisionList = $this->useService()->getDivisionByLevel($tblLevel);
//                Debugger::screenDump($tblDivisionList);
//                $Height = floor(( ( count($tblDivisionList) + 2 ) / 3 ) + 1);
                if ($tblDivisionList) {
                    foreach ($tblDivisionList as $key => $row) {
                        $DivisionName[$key] = strtoupper($row->getName());
                    }
                    array_multisort($DivisionName, SORT_ASC, $tblDivisionList);

                    foreach ($tblDivisionList as $tblDivision) {
                        $StudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                        $TeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                        $SubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                        if (!$StudentList) {
                            $StudentList = null;
                        }
                        if (!$TeacherList) {
                            $TeacherList = null;
                        }
                        if (!$SubjectList) {
                            $SubjectList = null;
                        }

                        Main::getDispatcher()->registerWidget($tblLevel->getId(),
                            new Panel('Klassengruppe: '.$tblDivision->getName()
                                , array(
                                    new Standard('Klassenansicht', '/Education/Lesson/Division/Show', new EyeOpen(),
                                        array('Id' => $tblDivision->getId()), 'Klassenansicht')
//                                    'Anzahl Schüler: '.count($StudentList)
//                                    .new PullRight(new Standard('', '/Education/Lesson/Division/Student/Add',
//                                        new Group(), array('Id' => $tblDivision->getId()), 'Schüler hinzufügen')),
//                                    'Anzahl Klassenlehrer: '.count($TeacherList)
//                                    .new PullRight(new Standard('', '/Education/Lesson/Division/Teacher/Add',
//                                        new Group(), array('Id' => $tblDivision->getId()), 'Klassenlehrer hinzufügen')),
//                                    'Anzahl Fächer: '.count($SubjectList)
//                                    .new PullRight(new Standard('', '/Education/Lesson/Division/Subject/Add',
//                                        new Book(), array('Id' => $tblDivision->getId()), 'Fächer hinzufügen')),
//                                    'Zuordnung Gruppen'
//                                    .new PullRight(new Standard('', '/Education/Lesson/Division/SubjectStudent/Show',
//                                        new EyeOpen(), array('Id' => $tblDivision->getId()), 'Übersicht Gruppen')),
//                                    'Zuordnung Fachlehrer'
//                                    .new PullRight(new Standard('', '/Education/Lesson/Division/SubjectTeacher/Show',
//                                        new EyeOpen(), array('Id' => $tblDivision->getId()), 'Übersicht Fachlehrer'))
                                ,)
                                , Panel::PANEL_TYPE_DEFAULT
                                , new Small(new Small('Schüler: '.new Badge(count($StudentList))))
                                .new Pullright(new Small(new Small('Klassenlehrer: '.new Badge(count($TeacherList)))))
                            )
                        );
                    }
                    array_push($Content, new LayoutRow(array(
                        new LayoutColumn(Main::getDispatcher()->fetchDashboard($tblLevel->getId()))
                    )));
//                    , 2, ( $Height ? $Height : $Height + 2 ));
//            });
                } else {
                    array_push($Content, new LayoutRow(array(
                        new LayoutColumn(new Warning('Keine Klassengruppe angelegt'), 6)
                    )));
                }
            });
        }
        $Stage->setContent(
            new Layout(new LayoutGroup($Content, new Title('Klassen', 'Zusammensetzung')))
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
