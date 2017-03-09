<?php
namespace SPHERE\Application\Document\Standard\StudentCard;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Document\Standard\StudentCard
 */
class StudentCard extends AbstractModule implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schülerkartei'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));
    }

    public static function useService()
    {

    }

    public static function useFrontend()
    {

    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schülerkartei', 'Schüler auswählen');

        $dataList = array();
        if (( $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT') )) {
            if (( $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup) )) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new External(
                            'Herunterladen',
                            'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                            new Download(),
                            array(
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Schülerkartei herunterladen'
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
}