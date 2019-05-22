<?php
namespace SPHERE\Application\Api\Billing\Accounting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
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
 * Class ApiCreditor
 * @package SPHERE\Application\Api\Billing\Creditor
 */
class ApiCreditor extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Table
        $Dispatcher->registerMethod('getCreditorTable');
        // Creditor / Gläubiger
        $Dispatcher->registerMethod('showAddCreditor');
        $Dispatcher->registerMethod('saveAddCreditor');
        $Dispatcher->registerMethod('showEditCreditor');
        $Dispatcher->registerMethod('saveEditCreditor');
        $Dispatcher->registerMethod('showDeleteCreditor');
        $Dispatcher->registerMethod('deleteCreditor');

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
    public static function receiverCreditorTable($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockTableContent');
    }

    /**
     * @param string $Identifier
     * @param array  $Creditor
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddCreditorModal($Identifier = '', $Creditor = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Creditor'   => $Creditor
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddCreditor($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     * @param array      $Creditor
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditCreditorModal($Identifier = '', $CreditorId = '', $Creditor = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CreditorId' => $CreditorId,
            'Creditor'   => $Creditor
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditCreditor($Identifier = '', $CreditorId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CreditorId' => $CreditorId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteCreditorModal($Identifier = '', $CreditorId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CreditorId' => $CreditorId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteCreditor($Identifier = '', $CreditorId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteCreditor'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CreditorId' => $CreditorId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverCreditorTable(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getCreditorTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    public function getCreditorTable()
    {

        return Creditor::useFrontend()->getCreditorTable();
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     *
     * @return Form
     */
    public function formCreditor($Identifier = '', $CreditorId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if('' !== $CreditorId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditCreditor($Identifier, $CreditorId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddCreditor($Identifier));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        (new TextField('Creditor[Owner]', 'Inhaber der Bankverbindung', 'Inhaber der Bankverbindung'))->setRequired()
                        , 6)
                ),
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Creditor[Street]', 'Straße', 'Straße'))->setRequired()
                        , 10),
                    new FormColumn(
                        (new TextField('Creditor[Number]', 'Hausnummer', 'Hausnummer'))->setRequired()
                        , 2),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Creditor[Code]', 'PLZ', 'PLZ'))->setRequired()
                        , 2),
                    new FormColumn(
                        (new TextField('Creditor[City]', 'Stadt', 'Stadt'))->setRequired()
                        , 5),
                    new FormColumn(
                        new TextField('Creditor[District]', 'Ortsteil', 'Ortsteil')
                        , 5),
                )),
                new FormRow(
                    new FormColumn(
                        new TextField('Creditor[BankName]', 'Bankname', 'Bankname')
                        , 6)
                ),
                new FormRow(
                    new FormColumn(
                        new TextField('Creditor[CreditorId]', 'Gläubiger Id', 'Gläubiger Id')
                        , 6)
                ),
                new FormRow(
                    new FormColumn(
                        (new TextField("Creditor[IBAN]", "DE00 0000 0000 0000 0000 00", "IBAN", null, 'aa99 9999 9999 9999 9999 99'))->setRequired()
                        , 6)
                ),
                new FormRow(
                    new FormColumn(
                        new TextField('Creditor[BIC]', 'BIC', 'BIC')
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
     * @param string $CreditorId
     * @param array  $Creditor
     *
     * @return false|string|Form
     */
    private function checkInputCreditor($Identifier = '', $CreditorId = '', $Creditor = array())
    {
        $Error = false;
        $form = $this->formCreditor($Identifier, $CreditorId);
        if(isset($Creditor['Owner']) && empty($Creditor['Owner'])){
            $form->setError('Creditor[Owner]', 'Bitte geben Sie einen Inhaber der Bankverbindung an');
            $Error = true;
        }
        if(isset($Creditor['Street']) && empty($Creditor['Street'])){
            $form->setError('Creditor[Street]', 'Bitte geben Sie eine Straße an');
            $Error = true;
        }
        if(isset($Creditor['Number']) && empty($Creditor['Number'])){
            $form->setError('Creditor[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        }
        if(isset($Creditor['Code']) && empty($Creditor['Code'])){
            $form->setError('Creditor[Code]', 'Bitte geben Sie eine Postleitzahl an');
            $Error = true;
        }
        if(isset($Creditor['City']) && empty($Creditor['City'])){
            $form->setError('Creditor[City]', 'Bitte geben Sie eine Stadt an');
            $Error = true;
        }
        if(isset($Creditor['IBAN']) && empty($Creditor['IBAN'])){
            $form->setError('Creditor[IBAN]', 'Bitte geben Sie eine IBAN an');
            $Error = true;
        }

        if($Error){
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     *
     * @return string
     */
    public function showAddCreditor($Identifier = '')
    {

        return new Well($this->formCreditor($Identifier));
    }

    /**
     * @param string $Identifier
     * @param array  $Creditor
     *
     * @return string
     */
    public function saveAddCreditor($Identifier = '', $Creditor = array())
    {

        // Handle error's
        if($form = $this->checkInputCreditor($Identifier, '', $Creditor)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Creditor']['Owner'] = $Creditor['Owner'];
            $Global->POST['Creditor']['Street'] = $Creditor['Street'];
            $Global->POST['Creditor']['Number'] = $Creditor['Number'];
            $Global->POST['Creditor']['Code'] = $Creditor['Code'];
            $Global->POST['Creditor']['City'] = $Creditor['City'];
            $Global->POST['Creditor']['District'] = $Creditor['District'];
            $Global->POST['Creditor']['CreditorId'] = $Creditor['CreditorId'];
            $Global->POST['Creditor']['BankName'] = $Creditor['BankName'];
            $Global->POST['Creditor']['IBAN'] = $Creditor['IBAN'];
            $Global->POST['Creditor']['BIC'] = $Creditor['BIC'];
            $Global->savePost();
            return $form;
        }

        $tblCreditor = Creditor::useService()->createCreditor($Creditor['Owner'], $Creditor['Street'],
            $Creditor['Number']
            , $Creditor['Code'], $Creditor['City'], $Creditor['District'], $Creditor['CreditorId'],
            $Creditor['BankName'], $Creditor['IBAN']
            , $Creditor['BIC']);

        return ($tblCreditor
            ? new Success('Gläubiger erfolgreich angelegt').self::pipelineCloseModal($Identifier)
            : new Danger('Gläubiger konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     * @param array      $Creditor
     *
     * @return string
     */
    public function saveEditCreditor($Identifier = '', $CreditorId = '', $Creditor = array())
    {

        // Handle error's
        if($form = $this->checkInputCreditor($Identifier, $CreditorId, $Creditor)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Creditor']['Owner'] = $Creditor['Owner'];
            $Global->POST['Creditor']['Street'] = $Creditor['Street'];
            $Global->POST['Creditor']['Number'] = $Creditor['Number'];
            $Global->POST['Creditor']['Code'] = $Creditor['Code'];
            $Global->POST['Creditor']['City'] = $Creditor['City'];
            $Global->POST['Creditor']['District'] = $Creditor['District'];
            $Global->POST['Creditor']['CreditorId'] = $Creditor['CreditorId'];
            $Global->POST['Creditor']['BankName'] = $Creditor['BankName'];
            $Global->POST['Creditor']['IBAN'] = $Creditor['IBAN'];
            $Global->POST['Creditor']['BIC'] = $Creditor['BIC'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if(($tblCreditor = Creditor::useService()->getCreditorById($CreditorId))){
            $IsChange = Creditor::useService()->changeCreditor($tblCreditor, $Creditor['Owner'], $Creditor['Street']
                , $Creditor['Number'], $Creditor['Code'], $Creditor['City'], $Creditor['District'],
                $Creditor['CreditorId']
                , $Creditor['BankName'], $Creditor['IBAN'], $Creditor['BIC']);
        }

        return ($IsChange
            ? new Success('Gläubiger erfolgreich geändert').self::pipelineCloseModal($Identifier)
            : new Danger('Gläubiger konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $CreditorId
     *
     * @return string
     */
    public function showEditCreditor($Identifier = '', $CreditorId = '')
    {

        if('' !== $CreditorId && ($tblCreditor = Creditor::useService()->getCreditorById($CreditorId))){
            $Global = $this->getGlobal();
            $Global->POST['Creditor']['Owner'] = $tblCreditor->getOwner();
            $Global->POST['Creditor']['Street'] = $tblCreditor->getStreet();
            $Global->POST['Creditor']['Number'] = $tblCreditor->getNumber();
            $Global->POST['Creditor']['Code'] = $tblCreditor->getCode();
            $Global->POST['Creditor']['City'] = $tblCreditor->getCity();
            $Global->POST['Creditor']['District'] = $tblCreditor->getDistrict();
            $Global->POST['Creditor']['CreditorId'] = $tblCreditor->getCreditorId();
            $Global->POST['Creditor']['BankName'] = $tblCreditor->getBankName();
            $Global->POST['Creditor']['IBAN'] = $tblCreditor->getIBAN();
            $Global->POST['Creditor']['BIC'] = $tblCreditor->getBIC();
            $Global->savePost();
        }

        return new Well(self::formCreditor($Identifier, $CreditorId));
    }

    /**
     * @param string $Identifier
     * @param string $CreditorId
     *
     * @return string
     */
    public function showDeleteCreditor($Identifier = '', $CreditorId = '')
    {

        $tblCreditor = Creditor::useService()->getCreditorById($CreditorId);


        if($tblCreditor){
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Inhaber der Bankverbindung: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getOwner()), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Adresse: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getStreet().' '.$tblCreditor->getNumber().', '.$tblCreditor->getCode()
                    .' '.$tblCreditor->getCity().' '.$tblCreditor->getDistrict()), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Gläubiger Id: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getCreditorId()), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Bankname: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getBankName()), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('IBAN: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getIBAN(true)), 10),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('BIC: ', 2),
                new LayoutColumn(new Bold($tblCreditor->getBIC()), 10),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll der Gläubiger wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteCreditor($Identifier, $CreditorId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Gläubiger wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $CreditorId
     *
     * @return string
     */
    public function deleteCreditor($Identifier = '', $CreditorId = '')
    {

        if(($tblCreditor = Creditor::useService()->getCreditorById($CreditorId))){
            Creditor::useService()->removeCreditor($tblCreditor);

            return new Success('Gläubiger wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier);
        }
        return new Danger('Gläubiger konnte nicht entfernt werden');
    }

}