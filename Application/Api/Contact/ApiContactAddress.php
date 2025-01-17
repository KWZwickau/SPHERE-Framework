<?php

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiContactAddress
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiContactAddress extends Extension implements IApiInterface
{
    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';

    /**
     * @param string $Method
     *
     * @return string
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openModal');
        $Dispatcher->registerMethod('saveModal');
        $Dispatcher->registerMethod('refillColumnReceiver');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param int $PersonId
     *
     * @return BlockReceiver
     */
    public static function receiverColumn($PersonId)
    {

        // include EditButton & AddressString/Warning & ModalReceiver
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblAddress = $tblPerson->fetchMainAddress();
        if ($tblAddress) {
            return (new BlockReceiver($tblAddress->getGuiString()))
                ->setIdentifier('AddressField-'.$PersonId);
        } else {
            return
                (new BlockReceiver(new WarningMessage('Keine Adresse hinterlegt!')))
                    ->setIdentifier('AddressField-'.$PersonId);
        }
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver('Hinzufügen/Bearbeiten der '.new Bold('Hauptadresse'), new Close()))
            ->setIdentifier('Modal-ChangeAddress');
    }

    public static function pipelineOpen($PersonId)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
        ));
        $Emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Emitter->setLoadingMessage('Lädt');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineSave()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveModal'
        ));
        $Emitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineClose($PersonId)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverColumn($PersonId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'refillColumnReceiver'
        ));
        $Emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Form|string
     */
    public function openModal($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return new DangerMessage('Person wurde nicht gefunden!');
        }
        $tblAddress = $tblPerson->fetchMainAddress();

        // always select Main Address
        $Global = $this->getGlobal();
        $tblType = Address::useService()->getTypeByName('Hauptadresse');
        $Global->POST['Type']['Type'] = $tblType->getId();
        if ($tblAddress) {
            $tblCity = $tblAddress->getTblCity();
            $tblState = $tblAddress->getTblState();
            $tblToPerson = Address::useService()->getAddressToPersonByPerson($tblPerson);
            // TblType
            $Global->POST['Type']['Type'] = $tblType->getId();
            //TblAddress
            $Global->POST['Street']['Number'] = $tblAddress->getStreetNumber();
            $Global->POST['Street']['Name'] = $tblAddress->getStreetName();
            $Global->POST['County'] = $tblAddress->getCounty();
            $Global->POST['Nation'] = $tblAddress->getNation();
            if ($tblState) {
                $Global->POST['State'] = $tblState->getId();
            }
            //TblToPerson
            if ($tblToPerson) {
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            }
            //TblCity
            if ($tblCity) {
                $Global->POST['City']['Code'] = $tblCity->getCode();
                $Global->POST['City']['Name'] = $tblCity->getName();
                $Global->POST['City']['District'] = $tblCity->getDistrict();
            }
        }
        $Global->savePost();

        return
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new InfoMessage('Hiermit ändern Sie die Adressdaten der Person direkt in den Stammdaten')
                        ),
                        new LayoutColumn(new Well($this->formAddress($PersonId)))
                    ))
                )
            );
    }

    private function formAddress($PersonId)
    {

        $tblAddress = Address::useService()->getAddressAll();
        $tblCity = Address::useService()->getCityAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));
        $tblType = Address::useService()->getTypeByName('Hauptadresse');

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anschrift', array(
                            (new SelectBox('Type[Type]', 'Typ', array('Name' => array($tblType)),
                                new TileBig()))->setDisabled(),
                            (new AutoCompleter('Street[Name]', 'Straße', 'Straße',
                                array('StreetName' => $tblAddress), new MapMarker()
                            ))->setRequired(),
                            (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()
                            ))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Stadt', array(
                            (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array('Code' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            (new AutoCompleter('City[Name]', 'Ort', 'Ort',
                                array('Name' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil',
                                array('District' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('County', 'Landkreis', 'Landkreis',
                                array('County' => $tblAddress), new Map()
                            ),
                            new SelectBox('State', 'Bundesland',
                                array('Name' => $tblState), new Map()
                            ),
                            new AutoCompleter('Nation', 'Land', 'Land',
                                array('Nation' => $tblAddress), new Map()
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        (new Primary('Speichern', '', new Save(), array('PersonId' => $PersonId)))
                            ->ajaxPipelineOnClick(self::pipelineSave())
                        , 12),
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param int    $PersonId
     * @param array  $Type
     * @param array  $Street
     * @param array  $City
     * @param string $County
     * @param int    $State
     * @param string $Nation
     *
     * @return Layout|String
     */
    public function saveModal(
        $PersonId,
        $Type,
        $Street,
        $City,
        $County,
        $State,
        $Nation
    ) {

        $tblType = Address::useService()->getTypeByName('Hauptadresse');
        if ($tblType) {
            $Type['Type'] = $tblType->getId();
        } else {
            $Type['Type'] = 1;
        }

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if ($form = $this->checkInputAddress($PersonId, $Street, $City)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $tblType = Address::useService()->getTypeByName('Hauptadresse');
            $Global->POST['Type']['Type'] = $tblType->getId();
            $Global->savePost();
            return new Well($form);
        }
        // do service
        if (Address::useService()->createAddressToPersonByApi($tblPerson, $Street, $City, $State, $Type, $County,
            $Nation)
        ) {
            return new SuccessMessage('Adresse wurde erfolgreich gespeichert.').self::pipelineClose($PersonId);
        }

        return new DangerMessage('Service konnte wegen eines Fehlers nicht ausgeführt werden!');
    }

    /**
     * @param int   $PersonId
     * @param array $Street
     * @param array $City
     *
     * @return false|string|Form
     */
    private function checkInputAddress($PersonId, $Street = array(), $City = array())
    {
        $Error = false;
        $form = $this->formAddress($PersonId);
        if (isset($Street['Name']) && empty($Street['Name'])) {
            $form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $Error = true;
        }
        if (isset($Street['Number']) && empty($Street['Number'])) {
            $form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        }

        if (isset($City['Code']) && empty($City['Code'])) {
            $form->setError('City[Code]', 'Bitte geben Sie eine Postleitzahl ein');
            $Error = true;
        }
        if (isset($City['Name']) && empty($City['Name'])) {
            $form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        }

        if ($Error) {
            return $form;
        }

        return $Error;
    }

    /**
     * @param int $PersonId
     *
     * @return string|BlockReceiver
     */
    public function refillColumnReceiver($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return new DangerMessage('Person nicht gefunden!');
        }
        $tblAddress = $tblPerson->fetchMainAddress();
        if ($tblAddress) {
            return $tblAddress->getGuiString();
        } else {
            return new WarningMessage('Keine Adresse hinterlegt!');
        }
    }
}