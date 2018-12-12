<?php

namespace SPHERE\Application\Api\Billing\Accounting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
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
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDebtorSelection
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiDebtorSelection extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Panel content
        $Dispatcher->registerMethod('getDebtorSelectionContent');
        // DebtorSelection
        $Dispatcher->registerMethod('showAddDebtorSelection');
        $Dispatcher->registerMethod('saveAddDebtorSelection');
        $Dispatcher->registerMethod('showEditDebtorSelection');
        $Dispatcher->registerMethod('saveEditDebtorSelection');
        $Dispatcher->registerMethod('showDeleteDebtorSelection');
        $Dispatcher->registerMethod('deleteDebtorSelection');

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

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal'.$Identifier);
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
     * @param string $ItemId
     * @param array  $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelection = array()
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'      => $Identifier,
            'PersonId'        => $PersonId,
            'ItemId'          => $ItemId,
            'DebtorSelection' => $DebtorSelection
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
    public static function pipelineSaveAddDebtorSelection($Identifier = '', $PersonId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddDebtorSelection'
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
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'DebtorSelectionId' => $DebtorSelectionId,
            'DebtorSelection'   => $DebtorSelection
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelectionId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'DebtorSelectionId' => $DebtorSelectionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $DebtorSelectionId = ''
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'DebtorSelectionId' => $DebtorSelectionId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelectionId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'DebtorSelectionId' => $DebtorSelectionId,
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
            self::API_TARGET => 'getDebtorSelectionContent'
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
    public function getDebtorSelectionContent($PersonId)
    {

        return Debtor::useFrontend()->getDebtorSelectionContent($PersonId);
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return IFormInterface $Form
     */
    public function formDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelectionId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if ('' !== $DebtorSelectionId) {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditDebtorSelection($Identifier, $PersonId,
                $DebtorSelectionId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddDebtorSelection($Identifier, $PersonId));
        }

        $List = array();
        $tblPaymentTypeAll = Balance::useService()->getPaymentTypeAll();
        $PostPaymentTypeId = false;
        foreach ($tblPaymentTypeAll as $tblPaymentType) {
            $List[$tblPaymentType->getId()] = $tblPaymentType->getName();
            if ($tblPaymentType->getName() == 'SEPA-Lastschrift') {
                $PostPaymentTypeId = $tblPaymentType->getId();
            }
        }

        if (!isset($_POST['DebtorSelection']['PaymentType'])) {
            $_POST['DebtorSelection']['PaymentType'] = $PostPaymentTypeId;
        }
        if (!isset($_POST['DebtorSelection']['Vairant'])) {
            $_POST['DebtorSelection']['Vairant'] = 1;
        }


        $RadioBoxListVariant = array();
        if (($tblItem = Item::useService()->getItemById($ItemId))) {
            if (($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
                foreach ($tblItemVariantList as $tblItemVariant) {
                    $PriceString = new DangerText('Nicht verfügbar');
                    if (($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))) {
                        $PriceString = $tblItemCalculation->getPriceString();
                    }

                    $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Vairant]',
                        $tblItemVariant->getName().' - '.$PriceString, $tblItemVariant->getId());
                }
                $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Vairant]',
                   'Individuelle Preiseingabe'.new TextField('DebtorSelection[Price]', '', ''), -1);
            }
        }

        $PersonDebtorList = array();

        $SelectBoxDebtorList = array();

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt')) {
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
            // is Causer Person in Group "Bezahler"
            if (Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)) {
                $SelectBoxDebtorList[$tblPerson->getId()] = $tblPerson->getLastFirstName();
                $PersonDebtorList[] = $tblPerson;
            }

            if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType))) {
                foreach ($tblRelationshipList as $tblRelationship) {
                    if (($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPerson->getId()) {
                        // is Person in Group "Bezahler"
                        if (Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)) {
                            $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName();
                            $PersonDebtorList[] = $tblPersonRel;
                        }
                    } elseif (($tblPersonRel = $tblRelationship->getServiceTblPersonTo()) && $tblPersonRel->getId() !== $tblPerson->getId()) {
                        // is Person in Group "Bezahler"
                        if (Group::useService()->getMemberByPersonAndGroup($tblPersonRel, $tblGroup)) {
                            $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName();
                            $PersonDebtorList[] = $tblPersonRel;
                        }
                    }
                }
            }
        }

        $PostBankAccountId = false;
        $RadioBoxListBankAccount = array();
        if(!empty($PersonDebtorList)){
            /** @var TblPerson $PersonDebtor */
            foreach($PersonDebtorList as $PersonDebtor){
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($PersonDebtor))){
                    foreach($tblBankAccountList as $tblBankAccount){
                        if(!$PostBankAccountId){
                            $PostBankAccountId = $tblBankAccount->getId();
                            if (!isset($_POST['DebtorSelection']['BankAccount'])) {
                                $_POST['DebtorSelection']['BankAccount'] = $PostBankAccountId;
                            }
                        }
                        $RadioBoxListBankAccount[$tblBankAccount->getId()] = new RadioBox('DebtorSelection[BankAccount]'
                            , $tblBankAccount->getOwner().'<br/>'.$tblBankAccount->getBankName().'<br/>'
                            .$tblBankAccount->getIBANFrontend()
                            , $tblBankAccount->getId());
                    }
                }
            }
        }
        if(empty($RadioBoxListBankAccount)){
            $RadioBoxListBankAccount = new Warning('Keine Kontodaten hinterlegt');
        }

//        Debugger::screenDump($Global->POST);

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('DebtorSelection[PaymentType]', 'Zahlungsart',
                            $List /*array('{{ Name }}' => $tblPaymentTypeList)*/))
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                    new FormColumn(
                        array(
                            new Bold('Varianten: '),
                            new Listing($RadioBoxListVariant)
                        )
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('DebtorSelection[Debtor]', 'Bezahler',
                            $SelectBoxDebtorList /*array('{{ Name }}' => $tblPaymentTypeList)*/))
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                    new FormColumn(
                        array(
                            new Bold('Konten: '),
                            new Listing($RadioBoxListBankAccount)
                        )
                        , 6),
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
     * @param string $DebtorSelectionId
     * @param array  $DebtorSelection
     *
     * @return false|string|Form
     */
    private function checkInputDebtorSelection(
        $Identifier = '',
        $PersonId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ) {

        $Error = false;
        $form = $this->formDebtorSelection($Identifier, $PersonId, $DebtorSelectionId);
        if (isset($DebtorSelection['Number']) && empty($DebtorSelection['Number'])) {
            $form->setError('DebtorSelection[Number]', 'Bitte geben Sie eine Debitor-Nummer an');
            $Error = true;
        }

        if ($Error) {
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     *
     * @return string
     */
    public function showAddDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '')
    {

        return new Well($this->formDebtorSelection($Identifier,
            $PersonId, $ItemId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param array  $DebtorSelection
     *
     * @return string
     */
    public function saveAddDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelection = array())
    {

        // Handle error's
        if ($form = $this->checkInputDebtorSelection($Identifier, $PersonId, '', $DebtorSelection)) {

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['Number'] = $DebtorSelection['Number'];
            $Global->savePost();
            return Debtor::useFrontend()->getPersonPanel($PersonId).$form;
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // ToDO delete existing DebtorSelection
            /////////

            $tblDebtorSelection = Debtor::useService()->createDebtorSelection($tblPerson, $DebtorSelection['Number']);
            if ($tblDebtorSelection) {
                return new Success('Debitor-Nummer erfolgreich angelegt').self::pipelineCloseModal($Identifier,
                        $PersonId);
            } else {
                return new Danger('Debitor-Nummer konnte nicht gengelegt werden');
            }
        } else {
            return new Danger('Debitor-Nummer konnte nicht gengelegt werden(Person nicht vorhanden)');
        }
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $DebtorSelectionId
     * @param array      $DebtorSelection
     *
     * @return string
     */
    public function saveEditDebtorSelection(
        $Identifier = '',
        $PersonId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ) {

        // Handle error's
        if ($form = $this->checkInputDebtorSelection($Identifier, $PersonId, $DebtorSelectionId, $DebtorSelection)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['Number'] = $DebtorSelection['Number'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if (($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))) {
            $IsChange = Debtor::useService()->changeDebtorSelection($tblDebtorSelection, $DebtorSelection['Number']);
        }

        return ($IsChange
            ? new Success('Debitor-Nummer erfolgreich geändert').self::pipelineCloseModal($Identifier, $PersonId)
            : new Danger('Debitor-Nummer konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $DebtorSelectionId
     *
     * @return string
     */
    public function showEditDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelectionId = '')
    {

        if ('' !== $DebtorSelectionId && ($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))) {
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['Number'] = $tblDebtorSelection->getValue();
            $Global->savePost();
        }

        return Debtor::useFrontend()->getPersonPanel($PersonId)
            .new Well(self::formDebtorSelection($Identifier, $PersonId, $DebtorSelectionId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $DebtorSelectionId
     *
     * @return string
     */
    public function showDeleteDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelectionId = '')
    {

        $tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId);


        if ($tblDebtorSelection) {
            $PersonString = 'Person nicht gefunden!';
            if (($tblPerson = $tblDebtorSelection->getServiceTblPerson())) {
                $PersonString = $tblPerson->getFullName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person: ', 2),
                new LayoutColumn(new Bold($PersonString), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Debitor-Nummer: ', 2),
                new LayoutColumn(new Bold($tblDebtorSelection->getValue()), 10),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Debitor-Nummer wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDebtorSelection($Identifier, $PersonId,
                                    $DebtorSelectionId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Debitor-Nummer wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $DebtorSelectionId
     *
     * @return string
     */
    public function deleteDebtorSelection($Identifier = '', $PersonId = '', $DebtorSelectionId = '')
    {

        if (($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))) {
            Debtor::useService()->removeDebtorSelection($tblDebtorSelection);

            return new Success('Zahlungszuweisung wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier,
                    $PersonId);
        }
        return new Danger('Zahlungszuweisung konnte nicht entfernt werden');
    }

}