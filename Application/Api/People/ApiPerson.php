<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person as PersonApp;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
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

        $Dispatcher->registerMethod('pieceFormCreatePerson');
        $Dispatcher->registerMethod('pieceFormValidatePerson');

        return $Dispatcher->callMethod($MethodName);
    }


    public function pieceFormValidatePerson( $CreatePersonReceiver, $Person = null, $Confirm = false )
    {

        // check input like Service
        if( (!isset( $Person['FirstName'] ) || empty( $Person['FirstName'] ) )
            || ( !isset( $Person['LastName'] ) || empty( $Person['LastName'] ) )
        ) {
            // get Form to set ErrorMassages
            $Form = PersonApp::useFrontend()->formPerson();
            if( (!isset( $Person['FirstName'] ) || empty( $Person['FirstName'] ) ) ) {
                $Form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            }
            if( (!isset( $Person['LastName'] ) || empty( $Person['LastName'] ) ) ) {
                $Form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            }
            // get same Ajax Submit Button like before
            $Form->appendFormButton(
                new Primary('Speichern', new Save())
            )->ajaxPipelineOnSubmit($this->pipelineValidatePerson($CreatePersonReceiver));

            return (string)$Form.(new Notify('Person konnte nicht angelegt werden','Bitte überprüfen Sie ihre Eingaben', Notify::TYPE_DANGER, 10000));
        }

        // dynamic search
        $Pile = new Pile();
        $Pile->addPile(PersonApp::useService(), new ViewPerson());
        // find Input fields in ViewPerson
        $Result = $Pile->searchPile( array(
            array(
                ViewPerson::TBL_PERSON_FIRST_NAME => explode( ' ', $Person['FirstName'] ),
                ViewPerson::TBL_PERSON_LAST_NAME => explode( ' ', $Person['LastName'] )
            )
        ));

        if (empty($Result) || $Confirm) { // Create new Person

            $Form = PersonApp::useFrontend()->formPerson();
            $Form->appendFormButton(
                new Primary('Speichern', new Save())
            )->ajaxPipelineOnSubmit($this->pipelineValidatePerson($CreatePersonReceiver));

            return PersonApp::useService()->createPerson($Form, $Person);

        } else { // show existent matched Person

            $TableList = array();
            /** @var ViewPerson[] $ViewPerson */
            foreach( $Result as $Index => $ViewPerson ) {
                $TableList[$Index] = current($ViewPerson)->__toArray();

                $PersonId = $PersonName = '';
                $Address = new Warning('Keine Adresse hinterlegt');
                if (isset($TableList[$Index]['TblPerson_Id'])) {
                    $PersonId = $TableList[$Index]['TblPerson_Id'];
                    $tblPerson = PersonApp::useService()->getPersonById($PersonId);
                    if ($tblPerson) {
                        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                        if ($tblAddress) {
                            $Address = $tblAddress->getGuiString();
                        }
                    }
                }
                $TableList[$Index]['Address'] = $Address;
                $TableList[$Index]['Option'] = new Standard('', '/People/Person', new PersonIcon(), array('Id' => $PersonId), 'Zur Person');
            }

            $ConfirmPersonReceiver = (new BlockReceiver())->setIdentifier( $CreatePersonReceiver );
            $ConfirmPersonPipeline = new Pipeline();
            $ConfirmPersonEmitter = new ServerEmitter( $ConfirmPersonReceiver, ApiPerson::getRoute() );
            $ConfirmPersonEmitter->setGetPayload(array(
                ApiPerson::API_DISPATCHER => 'pieceFormValidatePerson',
                'CreatePersonReceiver' => $CreatePersonReceiver,
                'Confirm' => 1
            ));
            $ConfirmPersonEmitter->setPostPayload(array( 'Person' => $Person));
            $ConfirmPersonPipeline->addEmitter( $ConfirmPersonEmitter );

            $Form = PersonApp::useFrontend()->formPersonDisabled();
//            $Form->appendFormButton(
//                    new Primary('Ändern', new Edit())
//                )// ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
//                ->ajaxPipelineOnSubmit(
//                    $this->pipelineCreatePerson($CreatePersonReceiver, $Person)
//                );
            $EditButton = ( new Standard('Eingaben ändern', '#', new Edit()) )->ajaxPipelineOnClick(
                $this->pipelineCreatePerson($CreatePersonReceiver, $Person));

            return new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn($Form),
                        new LayoutColumn(array($EditButton,
                            ( new PrimaryLink('Neue Person speichern', '#', new Save()) )->ajaxPipelineOnClick($ConfirmPersonPipeline)
                        ))
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
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
                                'Address'                             => 'Adresse',
                                'Option'                              => '',
                            ))
                        )
                    )) //, new Title('Meinten Sie vielleicht eine der folgenden Personen?')
                )
            ));
        }
    }

    public function pieceFormCreatePerson( $CreatePersonReceiver )
    {
        $Form = PersonApp::useFrontend()->formPerson();
        $Form->appendFormButton(
            new Primary('Speichern', new Save())
        )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
            ->ajaxPipelineOnSubmit($this->pipelineValidatePerson($CreatePersonReceiver));

        return (string)$Form;
    }

    private function pipelineCreatePerson($CreatePersonReceiver, $Person = null)
    {
        $CreatePersonEditReceiver = ( new BlockReceiver() )->setIdentifier($CreatePersonReceiver);
        $CreatePersonPipeline = new Pipeline();
        $CreatePersonEmitter = new ServerEmitter($CreatePersonEditReceiver, ApiPerson::getRoute());
        $CreatePersonEmitter->setGetPayload(array(
            ApiPerson::API_DISPATCHER => 'pieceFormCreatePerson',
            'CreatePersonReceiver'    => $CreatePersonReceiver
        ));
        $CreatePersonEmitter->setPostPayload(array('Person' => $Person));
        $CreatePersonEmitter->setLoadingMessage('Bearbeitung wird geladen...');
        $CreatePersonPipeline->addEmitter($CreatePersonEmitter);
        return $CreatePersonPipeline;
    }

    private function pipelineValidatePerson( $CreatePersonReceiver )
    {
        $ValidatePersonReceiver = (new BlockReceiver())->setIdentifier( $CreatePersonReceiver );
        $ValidatePersonPipeline = new Pipeline();
        $ValidatePersonEmitter = new ServerEmitter( $ValidatePersonReceiver, ApiPerson::getRoute() );
        $ValidatePersonEmitter->setGetPayload(array(
            ApiPerson::API_DISPATCHER => 'pieceFormValidatePerson',
            'CreatePersonReceiver' => $CreatePersonReceiver
        ));
        $ValidatePersonEmitter->setLoadingMessage('Daten werden überprüft...');
        $ValidatePersonPipeline->addEmitter( $ValidatePersonEmitter );
        return $ValidatePersonPipeline;
    }
}