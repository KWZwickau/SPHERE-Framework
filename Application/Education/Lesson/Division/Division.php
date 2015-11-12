<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;
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
            __NAMESPACE__.'/Create/Division', __NAMESPACE__.'\Frontend::frontendCreateDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Division', __NAMESPACE__.'\Frontend::frontendChangeDivision'
        )->setParameterDefault('Division', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Division', __NAMESPACE__.'\Frontend::frontendDestroyDivision'
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

        //usort( $tblLevelAll,  );

        if ($tblLevelAll) {
            array_push($Content, new LayoutRow(array(
                new LayoutColumn(array(
                    new Title(new Italic(new Bold('Unzugeordnet'))),
                ))
            )));

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
                        new Title('Klassenstufe: '.new Bold($tblLevel->getName()), $tblLevel->getDescription()),
                        new Standard('Zuweisen von Kategorien', __NAMESPACE__.'\Link\Category', new Transfer(),
                            array('Id' => $tblLevel->getId())
                        )
                    ))
                )));
            });
////            $tblCategoryAll = $this->useService()->getCategoryAllByLevel($tblLevel);
////            array_walk($tblCategoryAll, function (TblCategory $tblCategory) use (&$Content, $tblLevel) {
////
////                $tblSubjectAll = $this->useService()->getSubjectAllByCategory($tblCategory);
////                array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {
////
////                    $tblSubject = new Bold($tblSubject->getAcronym()).' - '
////                        .$tblSubject->getName().' '
////                        .new Small(new Muted($tblSubject->getDescription()));
////                });
////
////                $Height = floor(( ( count($tblSubjectAll) + 2 ) / 3 ) + 1);
//                Main::getDispatcher()->registerWidget($tblLevel->getName(),
//                    new Panel(
////                        $tblCategory->getName().' '.$tblCategory->getDescription(),
////                        $tblSubjectAll,
////                        ( $tblCategory->getIsLocked() ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT ),
//                        '',
//                        new Standard('Zuweisen von Klassen-Gruppen', __NAMESPACE__.'\Link\Subject', new Transfer()
//                        //array('Id' => $tblCategory->getId()
//                        ))
//                );
//                //, 2, ( $Height ? $Height : $Height + 2 ));
////            });
//                array_push($Content, new LayoutRow(array(
//                    new LayoutColumn(Main::getDispatcher()->fetchDashboard($tblLevel->getName()))
//                )));
//            });
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
