<?php
namespace SPHERE\Application\Api\Billing\Accounting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Causer\Causer;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBankReference
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiBankReference extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Panel content
        $Dispatcher->registerMethod('getReferenceContent');
        // Reference
        $Dispatcher->registerMethod('showAddReference');
        $Dispatcher->registerMethod('saveAddReference');
        $Dispatcher->registerMethod('showEditReference');
        $Dispatcher->registerMethod('saveEditReference');
        $Dispatcher->registerMethod('showDeleteReference');
        $Dispatcher->registerMethod('deleteReference');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Identifier = '')
    {

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal' . $Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverPanelContent($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockPanelContent');
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param array  $Reference
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddReferenceModal($Identifier = '', $PersonId = '', $Reference = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'   => $Identifier,
            'PersonId'     => $PersonId,
            'Reference' => $Reference
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddReference($Identifier = '', $PersonId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'PersonId'   => $PersonId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     * @param array      $Reference
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditReferenceModal($Identifier = '', $PersonId = '', $ReferenceId = '',
        $Reference = array()
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'     => $Identifier,
            'PersonId'       => $PersonId,
            'ReferenceId' => $ReferenceId,
            'Reference'   => $Reference
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'     => $Identifier,
            'PersonId'       => $PersonId,
            'ReferenceId' => $ReferenceId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteReferenceModal($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'     => $Identifier,
            'PersonId'       => $PersonId,
            'ReferenceId' => $ReferenceId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteReference'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'     => $Identifier,
            'PersonId'       => $PersonId,
            'ReferenceId' => $ReferenceId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '', $PersonId = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverPanelContent(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getReferenceContent'
        ));
        $Emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function getReferenceContent($PersonId)
    {

        return Causer::useFrontend()->getReferenceContent($PersonId);
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     *
     * @return IFormInterface $Form
     */
    public function formReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if('' !== $ReferenceId) {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditReference($Identifier, $PersonId,
                $ReferenceId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddReference($Identifier, $PersonId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Reference[Number]', 'Referenznummer', 'Referenznummer'))->setRequired()
                        , 6),
                    new FormColumn(
                        (new DatePicker('Reference[Date]', 'Datum', 'Gültig ab'))->setRequired()
                        , 6)
                )),
                new FormRow(
                    new FormColumn(
                        $SaveButton
                    )
                )
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ReferenceId
     * @param array  $Reference
     *
     * @return false|string|Form
     */
    private function checkInputReference($Identifier = '', $PersonId = '', $ReferenceId = '',
        $Reference = array()
    ) {

        $Error = false;
        $form = $this->formReference($Identifier, $PersonId, $ReferenceId);
        if(isset($Reference['Number']) && empty($Reference['Number'])) {
            $form->setError('Reference[Number]', 'Bitte geben Sie eine Referenznummer an');
            $Error = true;
        } else {
            if(($tblReference = Debtor::useService()->getBankReferenceByReference($Reference['Number']))) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
                if($tblPerson && ($tblPersonCompare = $tblReference->getServiceTblPerson())
                    && $tblPerson->getId() !== $tblPersonCompare->getId()) {
                    $form->setError('Reference[Number]',
                        'Bitte geben sie eine noch nicht vergebene Referenznummer an');
                    $Error = true;
                }
            }
        }
        if(isset($Reference['Date']) && empty($Reference['Date'])) {
            $form->setError('Reference[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }

        if($Error) {
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     *
     * @return string
     */
    public function showAddReference($Identifier = '', $PersonId = '')
    {

        return Debtor::useFrontend()->getPersonPanel($PersonId) . new Well($this->formReference($Identifier,
                $PersonId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param array  $Reference
     *
     * @return string
     */
    public function saveAddReference($Identifier = '', $PersonId = '', $Reference = array())
    {

        // Handle error's
        if($form = $this->checkInputReference($Identifier, $PersonId, '', $Reference)) {

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Reference']['Number'] = $Reference['Number'];
            $Global->savePost();
            return Debtor::useFrontend()->getPersonPanel($PersonId) . $form;
        }

        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblReference = Debtor::useService()->createBankReference($tblPerson, $Reference['Number'], $Reference['Date']);
            if($tblReference) {
                return new Success('Referenznummer erfolgreich angelegt') . self::pipelineCloseModal($Identifier,
                        $PersonId);
            } else {
                return new Danger('Referenznummer konnte nicht gengelegt werden');
            }
        } else {
            return new Danger('Referenznummer konnte nicht gengelegt werden(Person nicht vorhanden)');
        }
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     * @param array      $Reference
     *
     * @return string
     */
    public function saveEditReference($Identifier = '', $PersonId = '', $ReferenceId = '', $Reference = array()
    ) {

        // Handle error's
        if($form = $this->checkInputReference($Identifier, $PersonId, $ReferenceId, $Reference)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Reference']['Number'] = $Reference['Number'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if(($tblReference = Debtor::useService()->getBankReferenceById($ReferenceId))) {
            $IsChange = Debtor::useService()->changeBankReference($tblReference, $Reference['Number'], $Reference['Date']);
        }

        return ($IsChange
            ? new Success('Referenznummer erfolgreich geändert') . self::pipelineCloseModal($Identifier, $PersonId)
            : new Danger('Referenznummer konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $ReferenceId
     *
     * @return string
     */
    public function showEditReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        if('' !== $ReferenceId && ($tblReference = Debtor::useService()->getBankReferenceById($ReferenceId))) {
            $Global = $this->getGlobal();
            $Global->POST['Reference']['Number'] = $tblReference->getReferenceNumber();
            $Global->POST['Reference']['Date'] = $tblReference->getReferenceDate();
            $Global->savePost();
        }

        return Debtor::useFrontend()->getPersonPanel($PersonId)
            . new Well(self::formReference($Identifier, $PersonId, $ReferenceId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ReferenceId
     *
     * @return string
     */
    public function showDeleteReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        $tblReference = Debtor::useService()->getBankReferenceById($ReferenceId);


        if($tblReference) {
            $PersonString = 'Person nicht gefunden!';
            if(($tblPerson = $tblReference->getServiceTblPerson())) {
                $PersonString = $tblPerson->getFullName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person: ', 2),
                new LayoutColumn(new Bold($PersonString), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Referenznummer: ', 2),
                new LayoutColumn(new Bold($tblReference->getReferenceNumber()), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Gültig ab: ', 2),
                new LayoutColumn(new Bold($tblReference->getReferenceDate()), 10),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Referenznummer wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteReference($Identifier, $PersonId,
                                    $ReferenceId))
                            . new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Referenznummer wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ReferenceId
     *
     * @return string
     */
    public function deleteReference($Identifier = '', $PersonId = '', $ReferenceId = '')
    {

        if(($tblReference = Debtor::useService()->getBankReferenceById($ReferenceId))) {
            if(($tblDebtorSelection = Debtor::useService()->getDebtorSelectionByBankReference($tblReference))){
                return new Danger('Referenznummer wird benutzt, diese kann nicht entfernt werden!');
            }
            Debtor::useService()->removeBankReference($tblReference);

            return new Success('Referenznummer wurde erfolgreich entfernt') . self::pipelineCloseModal($Identifier,
                    $PersonId);
        }
        return new Danger('Referenznummer konnte nicht entfernt werden');
    }

}