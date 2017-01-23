<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person as PersonApp;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPerson
 *
 * @package SPHERE\Application\Api\People
 */
class ApiPerson extends Extension implements IApiInterface
{

    const API_DISPATCHER = 'MethodName';

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('pieceFormValidatePerson');

        return $Dispatcher->callMethod($MethodName);
    }

    public function pieceFormValidatePerson($Person = null)
    {

        if( (!isset( $Person['FirstName'] ) || empty( $Person['FirstName'] ) )
            || ( !isset( $Person['LastName'] ) || empty( $Person['LastName'] ) )
        ) {
            // nothing for missing information
            return (string)'';
        } else {
            // dynamic search
            $Pile = new Pile();
            $Pile->addPile(PersonApp::useService(), new ViewPerson());
            // find Input fields in ViewPerson
            $Result = $Pile->searchPile(array(
                array(
                    ViewPerson::TBL_PERSON_FIRST_NAME => explode(' ', $Person['FirstName']),
                    ViewPerson::TBL_PERSON_LAST_NAME  => explode(' ', $Person['LastName'])
                )
            ));

            if (!empty($Result)) { // show Person

                $TableList = array();
                /** @var ViewPerson[] $ViewPerson */
                foreach ($Result as $Index => $ViewPerson) {
                    $TableList[$Index] = current($ViewPerson)->__toArray();

                    $PersonId = $PersonName = '';
                    $Address = new Warning('Keine Adresse hinterlegt');
                    $BirthDay = new Warning('Kein Datum hinterlegt');
                    if (isset($TableList[$Index]['TblPerson_Id'])) {
                        $PersonId = $TableList[$Index]['TblPerson_Id'];
                        $tblPerson = PersonApp::useService()->getPersonById($PersonId);
                        if ($tblPerson) {
                            $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                            if ($tblCommon) {
                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                if ($tblCommonBirthDates) {
                                    $BirthDay = $tblCommonBirthDates->getBirthday();
                                }
                            }

                            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                            if ($tblAddress) {
                                $Address = $tblAddress->getGuiString();
                            }
                        }
                    }
                    $TableList[$Index]['BirthDay'] = $BirthDay;
                    $TableList[$Index]['Address'] = $Address;
                    $TableList[$Index]['Option'] = new Standard('', '/People/Person', new PersonIcon(), array('Id' => $PersonId), 'Zur Person');
                }

                return (string)new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Container('&nbsp;').
                                new Info('Personen mit ähnlichem Namen gefunden. Ist diese Person schon angelegt?')
                            ),
                            new LayoutColumn(
                                new TableData($TableList, null, array(
                                    ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                                    ViewPerson::TBL_PERSON_TITLE          => 'Titel',
                                    ViewPerson::TBL_PERSON_FIRST_NAME     => 'Vorname',
                                    ViewPerson::TBL_PERSON_SECOND_NAME    => 'Zweiter Vorname',
                                    ViewPerson::TBL_PERSON_LAST_NAME      => 'Nachname',
                                    ViewPerson::TBL_PERSON_BIRTH_NAME     => 'Geburtsname',
                                    'BirthDay'                            => 'Geburtstag',
                                    'Address'                             => 'Adresse',
                                    'Option'                              => '',
                                ))
                            )
                        ))
                    )
                ));
            }
            return (string)'';
        }
    }
}