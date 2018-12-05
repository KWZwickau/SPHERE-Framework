<?php
namespace SPHERE\Application\Api\Billing\Accounting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
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
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
 * Class ApiBankAccount
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiBankAccount extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Table
        $Dispatcher->registerMethod('getBankAccountTable');
        // BankAccount
        $Dispatcher->registerMethod('showAddBankAccount');
        $Dispatcher->registerMethod('saveAddBankAccount');
        $Dispatcher->registerMethod('showEditBankAccount');
        $Dispatcher->registerMethod('saveEditBankAccount');
        $Dispatcher->registerMethod('showDeleteBankAccount');
        $Dispatcher->registerMethod('deleteBankAccount');

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
    public static function receiverDebtorTable($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockTableContent');
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param array  $BankAccount
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddBankAccountModal($Identifier = '', $PersonId = '', $BankAccount = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'  => $Identifier,
            'PersonId'    => $PersonId,
            'BankAccount' => $BankAccount
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
    public static function pipelineSaveAddBankAccount($Identifier = '', $PersonId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddBankAccount'
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
     * @param int|string $BankAccountId
     * @param array      $BankAccount
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditBankAccountModal($Identifier = '', $PersonId = '', $BankAccountId = '',
        $BankAccount = array()
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'PersonId'      => $PersonId,
            'BankAccountId' => $BankAccountId,
            'BankAccount'   => $BankAccount
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $BankAccountId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditBankAccount($Identifier = '', $PersonId = '',
        $BankAccountId = ''
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'PersonId'      => $PersonId,
            'BankAccountId' => $BankAccountId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $BankAccountId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteBankAccountModal($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'PersonId'      => $PersonId,
            'BankAccountId' => $BankAccountId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $PersonId
     * @param int|string $BankAccountId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'PersonId'      => $PersonId,
            'BankAccountId' => $BankAccountId,
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
        $Emitter = new ServerEmitter(self::receiverDebtorTable(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getBankAccountTable'
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
     * @return array|Danger
     */
    public function getBankAccountTable($PersonId)
    {

        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return Debtor::useFrontend()->getBankAccountTable($tblPerson);
        }
        return new Danger('Person nicht mehr gefunden');
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $BankAccountId
     *
     * @return IFormInterface $Form
     */
    public function formBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if('' !== $BankAccountId) {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditBankAccount($Identifier, $PersonId,
                $BankAccountId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddBankAccount($Identifier, $PersonId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        (new TextField('BankAccount[Number]', 'IBAN', 'IBAN'))->setRequired()
                        , 6)
                ),
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
     * @param string $BankAccountId
     * @param array  $BankAccount
     *
     * @return false|string|Form
     */
    private function checkInputBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '', $BankAccount = array())
    {

        $Error = false;
        $form = $this->formBankAccount($Identifier, $PersonId, $BankAccountId);
        if(isset($BankAccount['IBAN']) && empty($BankAccount['IBAN'])) {
            $form->setError('BankAccount[IBAN]', 'Bitte geben Sie die IBAN an');
            $Error = true;
        }

        if($Error) {
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return $form;
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     *
     * @return string
     */
    public function showAddBankAccount($Identifier = '', $PersonId = '')
    {

        return Debtor::useFrontend()->getPersonPanel($PersonId) . $this->formBankAccount($Identifier, $PersonId);
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param array  $BankAccount
     *
     * @return string
     */
    public function saveAddBankAccount($Identifier = '', $PersonId = '', $BankAccount = array())
    {

        // Handle error's
        if($form = $this->checkInputBankAccount($Identifier, $PersonId, '', $BankAccount)) {

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['BankAccount']['IBAN'] = $BankAccount['IBAN'];
            $Global->savePost();
            return Debtor::useFrontend()->getPersonPanel($PersonId) . $form;
        }

        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblBankAccount = Debtor::useService()->createBankAccount($tblPerson, $BankAccount['Name'],
                $BankAccount['IBAN'], $BankAccount['BIC'], $BankAccount['Owner']);
            if($tblBankAccount) {
                return new Success('Konto erfolgreich angelegt') . self::pipelineCloseModal($Identifier,
                        $PersonId);
            } else {
                return new Danger('Konto konnte nicht gengelegt werden');
            }
        } else {
            return new Danger('Konto konnte nicht gengelegt werden (Person nicht vorhanden)');
        }
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $BankAccountId
     * @param array      $BankAccount
     *
     * @return string
     */
    public function saveEditBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '', $BankAccount = array())
    {

        // Handle error's
        if($form = $this->checkInputBankAccount($Identifier, $PersonId, $BankAccountId, $BankAccount)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['BankAccount']['Number'] = $BankAccount['Number'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if(($tblBankAccount = Debtor::useService()->getBankAccountById($BankAccountId))) {
            $IsChange = Debtor::useService()->changeBankAccount($tblBankAccount, $BankAccount['Number']);
        }

        return ($IsChange
            ? new Success('Konto erfolgreich geändert') . self::pipelineCloseModal($Identifier, $PersonId)
            : new Danger('Konto konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param int|string $BankAccountId
     *
     * @return string
     */
    public function showEditBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        if('' !== $BankAccountId && ($tblBankAccount = Debtor::useService()->getBankAccountById($BankAccountId))) {
            $Global = $this->getGlobal();
            $Global->POST['BankAccount']['IBAN'] = $tblBankAccount->getIBAN();
            $Global->savePost();
        }

        return self::formBankAccount($Identifier, $PersonId, $BankAccountId);
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $BankAccountId
     *
     * @return string
     */
    public function showDeleteBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        $tblBankAccount = Debtor::useService()->getBankAccountById($BankAccountId);


        if($tblBankAccount) {
            $PersonString = 'Person nicht gefunden!';
            if(($tblPerson = $tblBankAccount->getServiceTblPerson())) {
                $PersonString = $tblPerson->getFullName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person: ', 2),
                new LayoutColumn(new Bold($PersonString), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('IBAN: ', 2),
                new LayoutColumn(new Bold($tblBankAccount->getIBANFrontend()), 10),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll das Konto wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteBankAccount($Identifier, $PersonId,
                                    $BankAccountId))
                            . new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Konto wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $BankAccountId
     *
     * @return string
     */
    public function deleteBankAccount($Identifier = '', $PersonId = '', $BankAccountId = '')
    {

        if(($tblBankAccount = Debtor::useService()->getBankAccountById($BankAccountId))) {
            Debtor::useService()->removeBankAccount($tblBankAccount);

            return new Success('Konto wurde erfolgreich entfernt') . self::pipelineCloseModal($Identifier,
                    $PersonId);
        }
        return new Danger('Konto konnte nicht entfernt werden');
    }

}