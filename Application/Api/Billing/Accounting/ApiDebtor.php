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
 * Class ApiDebtor
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiDebtor extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Table
        $Dispatcher->registerMethod('getDebtorTable');
        // DebtorNumber / Debitor Nummer
        $Dispatcher->registerMethod('showAddDebtorNumber');
        $Dispatcher->registerMethod('saveAddDebtorNumber');
        $Dispatcher->registerMethod('showEditDebtorNumber');
        $Dispatcher->registerMethod('saveEditDebtorNumber');
        $Dispatcher->registerMethod('showDeleteDebtorNumber');
        $Dispatcher->registerMethod('deleteDebtorNumber');

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

        return (new ModalReceiver($Header,  new Close()))->setIdentifier('Modal'.$Identifier);
    }

    /**
     * @param string $Content
     * @return BlockReceiver
     */
    public static function receiverDebtorTable($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockTableContent');
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param array $DebtorNumber
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddDebtorNumberModal($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumber = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId'    => $GroupId,
            'PersonId'   => $PersonId,
            'DebtorNumber'   => $DebtorNumber
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId'    => $GroupId,
            'PersonId'   => $PersonId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param int|string $DebtorNumberId
     * @param array $DebtorNumber
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDebtorNumberModal($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '',$DebtorNumber = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId'    => $GroupId,
            'PersonId'       => $PersonId,
            'DebtorNumberId' => $DebtorNumberId,
            'DebtorNumber'   => $DebtorNumber
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param int|string $DebtorNumberId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'     => $Identifier,
            'GroupId'        => $GroupId,
            'PersonId'       => $PersonId,
            'DebtorNumberId' => $DebtorNumberId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param int|string $DebtorNumberId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorNumberModal($Identifier = '', $GroupId = '', $DebtorNumberId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId'    => $GroupId,
            'DebtorNumberId' => $DebtorNumberId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param int|string $DebtorNumberId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorNumber($Identifier = '', $GroupId = '', $DebtorNumberId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorNumber'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'GroupId'    => $GroupId,
            'DebtorNumberId' => $DebtorNumberId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '', $GroupId = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverDebtorTable(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getDebtorTable'
        ));
        $Emitter->setPostPayload(array(
            'GroupId' => $GroupId
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    public function getDebtorTable($GroupId)
    {

        return Debtor::useFrontend()->getDebtorTable($GroupId);
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param int|string $DebtorNumberId
     *
     * @return IFormInterface $Form
     */
    public function formDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if('' !== $DebtorNumberId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditDebtorNumber($Identifier, $GroupId, $PersonId, $DebtorNumberId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddDebtorNumber($Identifier, $GroupId, $PersonId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        (new TextField('DebtorNumber[Number]', 'Debitor-Nummer', 'Debitor-Nummer'))->setRequired()
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
     * @param string $GroupId
     * @param string $PersonId
     * @param string $DebtorNumberId
     * @param array  $DebtorNumber
     *
     * @return false|string|Form
     */
    private function checkInputDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '',$DebtorNumber = array())
    {

        $Error = false;
        $form = $this->formDebtorNumber($Identifier, $GroupId, $PersonId, $DebtorNumberId);
        if (isset($DebtorNumber['Number']) && empty($DebtorNumber['Number'])) {
            $form->setError('DebtorNumber[Number]', 'Bitte geben Sie eine Debitor-Nummer an');
            $Error = true;
        } else {
            if(($tblDebtorNumber = Debtor::useService()->getDebtorNumberByNumber($DebtorNumber['Number']))){
                $tblPerson = Person::useService()->getPersonById($PersonId);
                if($tblPerson && ($tblPersonCompare = $tblDebtorNumber->getServiceTblPerson())
                && $tblPerson->getId() !== $tblPersonCompare->getId()){
                    $form->setError('DebtorNumber[Number]', 'Bitte geben sie eine noch nicht vergebene Debitor-Nummer an');
                    $Error = true;
                }
            }
        }

        if ($Error) {
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return $form;
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     *
     * @return string
     */
    public function showAddDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '')
    {

        return Debtor::useFrontend()->getPersonPanel($PersonId).$this->formDebtorNumber($Identifier, $GroupId, $PersonId);
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param array $DebtorNumber
     *
     * @return string
     */
    public function saveAddDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumber = array())
    {

        // Handle error's
        if ($form = $this->checkInputDebtorNumber($Identifier, $GroupId, $PersonId , '', $DebtorNumber)) {

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorNumber']['Number'] = $DebtorNumber['Number'];
            $Global->savePost();
            return Debtor::useFrontend()->getPersonPanel($PersonId).$form;
        }

        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $tblDebtorNumber = Debtor::useService()->createDebtorNumber($tblPerson, $DebtorNumber['Number']);
            if($tblDebtorNumber){
                return new Success('Debitor-Nummer erfolgreich angelegt'). self::pipelineCloseModal($Identifier, $GroupId);
            } else {
                return new Danger('Debitor-Nummer konnte nicht gengelegt werden');
            }
        } else {
            return new Danger('Debitor-Nummer konnte nicht gengelegt werden(Person nicht vorhanden)');
        }
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param int|string $DebtorNumberId
     * @param array $DebtorNumber
     *
     * @return string
     */
    public function saveEditDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '',$DebtorNumber = array())
    {

        // Handle error's
        if ($form = $this->checkInputDebtorNumber($Identifier, $GroupId, $PersonId, $DebtorNumberId, $DebtorNumber)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorNumber']['Number'] = $DebtorNumber['Number'];
            $Global->savePost();
            return $form;
        }

        $IsChange= false;
        if(($tblDebtorNumber = Debtor::useService()->getDebtorNumberById($DebtorNumberId))){
            $IsChange = Debtor::useService()->changeDebtorNumber($tblDebtorNumber, $DebtorNumber['Number']);
        }

        return ($IsChange
            ? new Success('Debitor-Nummer erfolgreich geändert') . self::pipelineCloseModal($Identifier, $GroupId)
            : new Danger('Debitor-Nummer konnte nicht geändert werden'));
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $PersonId
     * @param int|string $DebtorNumberId
     *
     * @return string
     */
    public function showEditDebtorNumber($Identifier = '', $GroupId = '', $PersonId = '', $DebtorNumberId = '')
    {

        if('' !== $DebtorNumberId && ($tblDebtorNumber = Debtor::useService()->getDebtorNumberById($DebtorNumberId))){
            $Global = $this->getGlobal();
            $Global->POST['DebtorNumber']['Number'] = $tblDebtorNumber->getDebtorNumber();
            $Global->savePost();
        }

        return self::formDebtorNumber($Identifier, $GroupId, $PersonId, $DebtorNumberId);
    }

    /**
     * @param string $Identifier
     * @param string $GroupId
     * @param string $DebtorNumberId
     *
     * @return string
     */
    public function showDeleteDebtorNumber($Identifier = '', $GroupId = '', $DebtorNumberId = '')
    {

        $tblDebtorNumber = Debtor::useService()->getDebtorNumberById($DebtorNumberId);


        if($tblDebtorNumber){
            $PersonString = 'Person nicht gefunden!';
            if(($tblPerson = $tblDebtorNumber->getServiceTblPerson())){
                $PersonString = $tblPerson->getFullName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person: ', 2),
                new LayoutColumn(new Bold($PersonString), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Debitor-Nummer: ', 2),
                new LayoutColumn(new Bold($tblDebtorNumber->getDebtorNumber()), 10),
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
                            ->ajaxPipelineOnClick(self::pipelineDeleteDebtorNumber($Identifier, $GroupId, $DebtorNumberId))
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
     * @param string $GroupId
     * @param string $DebtorNumberId
     * @return string
     */
    public function deleteDebtorNumber($Identifier = '', $GroupId = '', $DebtorNumberId = '')
    {

        if(($tblDebtorNumber = Debtor::useService()->getDebtorNumberById($DebtorNumberId))){
            Debtor::useService()->removeDebtorNumber($tblDebtorNumber);

            return new Success('Debitor-Nummer wurde erfolgreich entfernt'). self::pipelineCloseModal($Identifier, $GroupId);
        }
        return new Danger('Debitor-Nummer konnte nicht entfernt werden');
    }

}