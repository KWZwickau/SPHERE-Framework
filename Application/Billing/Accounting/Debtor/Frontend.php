<?php

namespace SPHERE\Application\Billing\Accounting\Debtor;

use SPHERE\Application\Api\Billing\Accounting\ApiBankAccount;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as StandardForm;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Debtor
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendDebtor($GroupId = null)
    {

        $Stage = new Stage('Auswahl Gruppe der', 'Beitragszahler');

        $Stage->setContent(
            $this->layoutPersonGroupList($GroupId)
        );


        return $Stage;
    }

    /**
     * @param string $GroupId
     *
     * @return Layout
     */
    public static function layoutPersonGroupList($GroupId = '')
    {

        $tblGroupList = array();

        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
            $tblGroupList[] = $tblGroup;
        }
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY))){
            $tblGroupList[] = $tblGroup;
        }

        $leftBoxList = array();
        $rightBoxList = array();
        if(($boxList = Group::useService()->getGroupAll())){
            foreach($boxList as $tblGroup) {
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_CUSTODY
                    || $tblGroup->getMetaTable() === TblGroup::META_TABLE_DEBTOR
                    || $tblGroup->getMetaTable() === TblGroup::META_TABLE_COMMON
                ){
                    continue;
                }
                if($tblGroup->getMetaTable() !== ''){
                    $leftBoxList[] = $tblGroup;
                } else {
                    $rightBoxList[] = $tblGroup;
                }
            }
            $tblGroupList = array_filter($tblGroupList);
        }

        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if (!empty($tblGroupList)) {
            /** @var TblGroup $tblGroup */
            foreach ($tblGroupList as $Index => $tblGroup) {

                $countContent = new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Mitglieder'));
                $content =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblGroup->getName()
                                . new Muted(new Small('<br/>' . $tblGroup->getDescription()))
                                , 5),
                            new LayoutColumn(
                                $countContent
                                , 6),
                            new LayoutColumn(
                                new PullRight(
                                    new Standard('', __NAMESPACE__.'/View',
                                        new GroupIcon(),
                                        array('GroupId' => $tblGroup->getId()))
                                ), 1)
                        )
                    )));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }
        // Standard Gruppen Auswahl über Selectbox
        $tblGroupLockedList[] = Debtor::useService()->directRoute(
            new Form(new FormGroup(new FormRow(array(
                new FormColumn(new SelectBox('GroupId', '', array('{{ Name }}' => $leftBoxList)), 10)
            ,
                new FormColumn(new PullRight(new StandardForm('', new GroupIcon())), 2)
            )))), $GroupId,'left');
        // Individuelle Gruppen Auswahl über Selectbox
        $tblGroupCustomList[] = Debtor::useService()->directRoute(
            new Form(new FormGroup(new FormRow(array(
                new FormColumn(new SelectBox('GroupId', '', array('{{ Name }}' => $rightBoxList)), 10)
            ,
                new FormColumn(new PullRight(new StandardForm('', new GroupIcon())), 2)
            )))), $GroupId, 'right');

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Personen in festen Gruppen', $tblGroupLockedList)
                // platz, damit die Selectbox nach unten auf geht
                .'<div style="height: 240px"></div>', 6
            ),
            !empty($tblGroupCustomList) ?
                new LayoutColumn(
                    new Panel('Personen in individuellen Gruppen', $tblGroupCustomList)
                    // platz, damit die Selectbox nach unten auf geht
                    .'<div style="height: 240px"></div>', 6) : null
        ))));
    }

    public function frontendDebtorView($GroupId = null)
    {

        $GroupName = '';
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            $GroupName = $tblGroup->getName();
        }
        $Stage = new Stage('Beitragszahler ', 'Gruppe: '.$GroupName);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $this->getDebtorTable($GroupId)
                        )
                    ))
                )
            ));

        return $Stage;
    }

    public function getDebtorTable($GroupId)
    {

        $TableContent = array();
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))){

                $IsDebtorNumberNeed = false;
                if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV)){
                    if($tblSetting->getValue() == 1){
                        $IsDebtorNumberNeed = true;
                    }
                }

                array_walk($tblPersonList,
                    function(TblPerson $tblPerson) use (&$TableContent, $tblGroup, $IsDebtorNumberNeed){
                        $Item['Name'] = $tblPerson->getLastFirstName();
                        // nullen sind für die Sortierung wichtig, (sonnst werden die Warnungen der Debitorennummern inmitten der anderen Werte angezeigt)
                        $Item['DebtorNumber'] = ($IsDebtorNumberNeed
                            ? '<span hidden>0000000000'.$tblPerson->getLastFirstName().'</span>'.new DangerText(new ToolTip(new Info(),
                                'Debitoren-Nr. wird benötigt'))
                            : '');
                        $Item['Address'] = '';
                        $Item['BankAccount'] = '<div class="alert alert-danger" style="margin-bottom: 0; padding: 10px 15px">'
                            .new Disable().' keine Bankverbindung</div>';
                        $Item['Option'] = new Standard('', __NAMESPACE__.'/Edit', new Edit(), array(
                            'GroupId'  => $tblGroup->getId(),
                            'PersonId' => $tblPerson->getId(),
                        ), 'Bearbeiten');
                        // get Debtor edit / delete options
                        if($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson)){
                            $NumberList = array();
                            foreach($tblDebtorNumberList as $tblDebtorNumber) {
                                $NumberList[] = $tblDebtorNumber->getDebtorNumber();
                            }
                            $Item['DebtorNumber'] = implode('<br/>', $NumberList);
                        }
                        // fill Address if exist
                        $tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPerson);
                        if($tblAddress){
                            $Item['Address'] = $tblAddress->getGuiLayout();
                        }

                        if(Debtor::useService()->getBankAccountAllByPerson($tblPerson)){
                            $Item['BankAccount'] = '<div class="alert alert-success" style="margin-bottom: 0; padding: 10px 15px">'
                                .new Check().' Bankverbindung vorhanden</div>';
                        }


                        array_push($TableContent, $Item);
                    });
            }
        }

        return new TableData($TableContent, null, array(
            'Name'         => 'Person',
            'DebtorNumber' => 'Debitoren-Nr.',
            'Address'      => 'Adresse',
            'BankAccount'  => 'Bankdaten',
            'Option'       => '',
        ), array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array("orderable" => false, "targets" => -1),
            ),
        ));
    }

    public function getPersonPanel($PersonId)
    {
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return new Panel(new Bold($tblPerson->getFullName()), '', Panel::PANEL_TYPE_SUCCESS);
        } else {
            return new Panel('Person nicht gefunden', '', Panel::PANEL_TYPE_DANGER);
        }
    }

    public function frontendDebtorEdit($GroupId, $PersonId)
    {

        $Stage = new Stage('Beitragszahler', 'Informationen');

        $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/View', new ChevronLeft(),
            array('GroupId' => $GroupId)));
        $DebtorNumber = ApiDebtor::receiverPanelContent($this->getDebtorNumberContent($PersonId));
        $BankAccount = ApiBankAccount::receiverBankAccountPanel($this->getBankAccountPanel($PersonId));
        $PanelDebtorNumber = new Panel('Debitoren-Nr.', $DebtorNumber);


        $Stage->setContent(ApiDebtor::receiverModal('Hinzufügen einer Debitorennummer', 'addDebtorNumber')
            .ApiDebtor::receiverModal('Bearbeiten einer Debitorennummer', 'editDebtorNumber')
            .ApiDebtor::receiverModal('Entfernen einer Debitorennummer', 'deleteDebtorNumber')
            .ApiBankAccount::receiverModal('Hinzufügen einer Bankverbindung', 'addBankAccount')
            .ApiBankAccount::receiverModal('Bearbeiten einer Bankverbindung', 'editBankAccount')
            .ApiBankAccount::receiverModal('Entfernen einer Bankverbindung', 'deleteBankAccount')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn($this->getPersonPanel($PersonId)),
                        new LayoutColumn($PanelDebtorNumber, 4),
                        new LayoutColumn($BankAccount
                            , 8)
                    ))
                )
            ));

        return $Stage;
    }

    /**
     * @param string $PersonId
     *
     * @return string
     */
    public function getDebtorNumberContent($PersonId = '')
    {

        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $IsDebtorNumberNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV)){
                if($tblSetting->getValue() == 1){
                    $IsDebtorNumberNeed = true;
                }
            }

            // new DebtorNumber
            if($IsDebtorNumberNeed){
                $DebtorNumber = new DangerText(new ToolTip(new WarningIcon(),
                        'Debotornummer muss angegeben werden')).(new Link('Debitorennummer hinzufügen',
                        ApiDebtor::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenAddDebtorNumberModal('addDebtorNumber',
                            $tblPerson->getId()));
            } else {
                $DebtorNumber = (new Link('Debitorennummer hinzufügen', ApiDebtor::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenAddDebtorNumberModal('addDebtorNumber',
                        $tblPerson->getId()));
            }

            // DebtorNumber
            if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson))){
                $DebtorNumber = array();
                foreach($tblDebtorNumberList as $tblDebtorNumber) {
                    $DebtorNumber[] = $tblDebtorNumber->getDebtorNumber().' '
                        .(new Link('', ApiDebtor::getEndpoint(), new Pencil(), array(), 'Debitorennummer bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenEditDebtorNumberModal('editDebtorNumber'
                                , $tblPerson->getId(), $tblDebtorNumber->getId()))
                        .' | '
                        .(new Link(new DangerText(new Remove()), ApiDebtor::getEndpoint(), null, array(),
                            'Debitorennummer entfernen'))
                            ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenDeleteDebtorNumberModal('deleteDebtorNumber'
                                , $tblPerson->getId(), $tblDebtorNumber->getId()));
                }
                if(!empty($DebtorNumber)){
                    $DebtorNumber = implode(', ', $DebtorNumber);
                }
            }
            return $DebtorNumber;
        } else {
            return new Warning('Person nicht gefunden');
        }
    }

    /**
     * @param string $PersonId
     *
     * @return string
     */
    public function getBankAccountPanel($PersonId = '')
    {

        $FirstColumn = 3;
        $SecondColumn = 9;
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $ColumnBankAccountList = '';
            if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($tblPerson))){
                array_walk($tblBankAccountList,
                    function(TblBankAccount $tblBankAccount) use (
                        &$tableContent,
                        &$ColumnBankAccountList,
                        $PersonId,
                        $FirstColumn,
                        $SecondColumn
                    ){

                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Bankname:', $FirstColumn),
                            new LayoutColumn($tblBankAccount->getBankName(), $SecondColumn),
                        ))));
                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('IBAN:', $FirstColumn),
                            new LayoutColumn($tblBankAccount->getIBANFrontend(), $SecondColumn),
                        ))));
                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('BIC:', $FirstColumn),
                            new LayoutColumn($tblBankAccount->getBICFrontend(), $SecondColumn),
                        ))));
                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(
                            new LayoutColumn((new Link('', ApiDebtor::getEndpoint(), new Pencil(), array(),
                                    'Bankverbindung bearbeiten'))
                                    ->ajaxPipelineOnClick(ApiBankAccount::pipelineOpenEditBankAccountModal('editBankAccount'
                                        , $PersonId, $tblBankAccount->getId()))
                                .' | '
                                .(new Link(new DangerText(new Remove()), ApiDebtor::getEndpoint(), null, array(),
                                    'Bankverbindung entfernen'))
                                    ->ajaxPipelineOnClick(ApiBankAccount::pipelineOpenDeleteBankAccountModal('deleteBankAccount'
                                        , $PersonId, $tblBankAccount->getId())))
                        )));

                        $ColumnBankAccountList .= new Panel(new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Inhaber:', $FirstColumn),
                            new LayoutColumn(new Bold($tblBankAccount->getOwner()), $SecondColumn),
                        )))),
                            $ContentArray, Panel::PANEL_TYPE_INFO
                        );
                    });
            }
            $ColumnBankAccountList .= new Panel('Informationen der Bankverbindung',
                (new Link('Bankverbindung hinzufügen', ApiBankAccount::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiBankAccount::pipelineOpenAddBankAccountModal('addBankAccount', $PersonId)),
                Panel::PANEL_TYPE_INFO);
            return $ColumnBankAccountList;
        }
        return new Danger('Person nicht gefunden');
    }
}