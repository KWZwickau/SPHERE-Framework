<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
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
        )->setParameterDefault('Level', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Level', __NAMESPACE__.'\Frontend::frontendDestroyLevel'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Division', __NAMESPACE__.'\Frontend::frontendCreateDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Division', __NAMESPACE__.'\Frontend::frontendChangeDivision'
        )->setParameterDefault('Division', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Division', __NAMESPACE__.'\Frontend::frontendDestroyDivision'
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

        $Stage->addButton(new Standard('Klassenstufe bearbeiten', __NAMESPACE__.'\Create\Level'));
        $Stage->addButton(new Standard('Klassengruppe bearbeiten', __NAMESPACE__.'\Create\Division'));

        $tblLevelAll = $this->useService()->getLevelAll();
        $Content = array();

        if ($tblLevelAll) {
            foreach ((array)$tblLevelAll as $key => $row) {
                $klass[$key] = strtoupper($row->getName());
                $second[$key] = strtoupper($row->getServiceTblType()->getName());
                $id[$key] = $row->getId();
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
//                $Height = floor(( ( count($tblDivisionList) + 2 ) / 3 ) + 1);
                if ($tblDivisionList) {
                    foreach ($tblDivisionList as $key => $row) {
                        $DivisionName[$key] = strtoupper($row->getName());
                    }
                    array_multisort($DivisionName, SORT_ASC, $tblDivisionList);

                    foreach ($tblDivisionList as $tblDivision) {
                        $StudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                        $TeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                        if (!$StudentList) {
                            $StudentList = null;
                        }
                        if (!$TeacherList) {
                            $TeacherList = null;
                        }


                        Main::getDispatcher()->registerWidget($tblLevel->getName(),
                            new Panel(new Standard('', '/Education/Lesson/Division/Show', new EyeOpen(),
                                    array('Id' => $tblDivision->getId()), 'Klassenansicht').'Gruppe: '.$tblDivision->getName()
                                , array(
                                    'Anzahl Schüler: '.count($StudentList)
                                    .new PullRight(new Standard('', '/Education/Lesson/Division/Student/Add', new Pencil(), array('Id' => $tblDivision->getId()), 'Schüler hinzufügen')),
                                    'Anzahl Lehrer: '.count($TeacherList)
                                    .new PullRight(new Standard('', '/Education/Lesson/Division/Teacher/Add', new Pencil(), array('Id' => $tblDivision->getId()), 'Lehrer hinzufügen')),
                                    'Fächer: 0'
                                    .new PullRight(new Standard('', '', new Pencil(), null, 'Fächer hinzufügen')),)
                                , Panel::PANEL_TYPE_DEFAULT
                            )
                        );
                    }
                    array_push($Content, new LayoutRow(array(
                        new LayoutColumn(Main::getDispatcher()->fetchDashboard($tblLevel->getName()))
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
