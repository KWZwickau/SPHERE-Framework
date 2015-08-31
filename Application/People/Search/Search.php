<?php
namespace SPHERE\Application\People\Search;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Search
 *
 * @package SPHERE\Application\People\Search
 */
class Search implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Personensuche'),
                new Link\Icon(new Info())
            )
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'SPHERE\Application\People\People::frontendDashboard'
        ));
    }

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Group'), new Link\Name('Nach Personengruppe'),
                new Link\Icon(new Info())
            )
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Group', __CLASS__.'::frontendGroup'
        ));
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Attribute'), new Link\Name('Nach Eigenschaften'),
                new Link\Icon(new Info())
            )
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Attribute', __CLASS__.'::frontendAttribute'
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

    public function frontendGroup($Id = false)
    {

        $Stage = new Stage('Personensuche', 'nach Personengruppe');

        $tblGroup = Group::useService()->getGroupById($Id);

        if ($tblGroup) {
            $Stage->setMessage(
                new PullClear(new Bold($tblGroup->getName()).' '.new Small($tblGroup->getDescription())).
                new PullClear(new Danger(new Italic(nl2br($tblGroup->getRemark()))))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Personengruppe');
        }

        $tblGroupAll = Group::useService()->getGroupAll();

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblGroupAll, function (TblGroup &$tblGroup, $Index, Stage $Stage) {

            $Stage->addButton(
                new Standard(
                    $tblGroup->getName(),
                    new Link\Route(__NAMESPACE__.'/Group'), null,
                    array(
                        'Id' => $tblGroup->getId()
                    ), $tblGroup->getDescription())
            );
        }, $Stage);

        if ($tblGroup) {

            // TODO: Person-List

            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

            array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                $tblPerson->Option = new Standard('', '/People/Person', new Pencil(),
                    array('Id' => $tblPerson->getId()), 'Bearbeiten');
            });

//            Debugger::screenDump( $tblPersonAll );

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($tblPersonAll, null,
                                    array(
                                        'Id'           => '#',
                                        'Salutation'   => 'Anrede',
                                        'Title'        => 'Titel',
                                        'FirstName'    => 'Vorname',
                                        'SecondName'   => 'Zweitname',
                                        'LastName'     => 'Nachname',
                                        'EntityCreate' => 'Eingabedatum',
                                        'EntityUpdate' => 'Letzte Änderung',
                                        'Option'       => 'Optionen',
                                    ))
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }


}
