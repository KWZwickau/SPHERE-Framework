<?php
namespace SPHERE\Application\Billing\Accounting\Debtor;

use SPHERE\Application\Api\Billing\Accounting\ApiDebtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as StandardForm;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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

        $Stage = new Stage('Beitragszahler', '');

        $Content = array();

        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))) {
            $Content[] = new Center('Auswahl für ' . $tblGroup->getName()
                . new Container(new Standard('', __NAMESPACE__ . '/View', new ListingIcon(),
                    array('GroupId' => $tblGroup->getId()))));
        }
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY))) {
            $Content[] = new Center('Auswahl für ' . $tblGroup->getName()
                . new Container(new Standard('', __NAMESPACE__ . '/View', new ListingIcon(),
                    array('GroupId' => $tblGroup->getId()))));
        }

        if(($tblGroupList = Group::useService()->getGroupAll())) {
            foreach ($tblGroupList as &$tblGroup) {
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_CUSTODY
                    || $tblGroup->getMetaTable() === TblGroup::META_TABLE_DEBTOR
                ) {
                    $tblGroup = false;
                }
            }
            $tblGroupList = array_filter($tblGroupList);
        }
        if(false === $tblGroupList
            || empty($tblGroupList)) {
            $tblGroupList = array();
        }

        $Content[] = new Center('Auswahl für Personen'
            . new Container(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(Debtor::useService()->directRoute(
                        new Form(new FormGroup(new FormRow(array(
                            new FormColumn(new SelectBox('GroupId', '', array('{{ Name }}' => $tblGroupList)), 11)
                        ,
                            new FormColumn(new StandardForm('', new ListingIcon()), 1)
                        )))), $GroupId)
                        , 6)
                ))))
            ));

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 3),
                    new LayoutColumn(
                        new Panel('Kategorien:', new Listing($Content))
                        , 6)
                ))
            )
        ));


        return $Stage;
    }

    public function frontendDebtorView($GroupId = null)
    {

        $GroupName = '';
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $GroupName = $tblGroup->getName();
        }
        $Stage = new Stage('Beitragszahler ', 'Gruppe: ' . $GroupName);
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
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $i = 0;
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblGroup, &$i) {
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    $Item['DebtorNumber'] = '';
                    $Item['Address'] = '';
                    $Item['Option'] = new Standard('', __NAMESPACE__ . '/Edit', new Edit(), array(
                        'GroupId'  => $tblGroup->getId(),
                        'PersonId' => $tblPerson->getId(),
                    ));
                    // get Debtor edit / delete options
                    if($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson)) {
                        $NumberList = array();
                        foreach ($tblDebtorNumberList as $tblDebtorNumber) {
                            $NumberList[] = $tblDebtorNumber->getDebtorNumber();
                        }
                        $Item['DebtorNumber'] = implode('<br/>', $NumberList);
                    }
                    // fill Address if exist
                    $tblAddress = false;
                    $tblType = Address::useService()->getTypeByName(TblType::META_INVOICE_ADDRESS);
                    if($tblType) {
                        $tblAddressList = Address::useService()->getAddressAllByPersonAndType($tblPerson, $tblType);
                        if($tblAddressList) {
                            $tblAddress = current($tblAddressList);
                        }
                    }
                    if(!$tblAddress) {
                        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    }
                    if($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiLayout();
                    }


                    array_push($TableContent, $Item);
                });
            }
        }

        return new TableData($TableContent, null, array(
            'Name'         => 'Person',
            'DebtorNumber' => 'Debitor Nr.',
            'Address'      => 'Adresse',
            'Option'       => '',
        ));
    }

    public function getPersonPanel($PersonId)
    {
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson) {
            return new Panel($tblPerson->getFullName(), '', Panel::PANEL_TYPE_INFO);
        } else {
            return new Panel('Person nicht gefunden', '', Panel::PANEL_TYPE_INFO);
        }
    }

    public function frontendDebtorEdit($GroupId, $PersonId)
    {

        $Stage = new Stage('Beitragszahler', 'Informationen');

        $Stage->addButton(new Standard('Zurück', __NAMESPACE__ . '/View', new ChevronLeft(),
            array('GroupId' => $GroupId)));
        $DebtorNumber = '';
        $tableContent = array();
        $PanelBankAccountList = array();

        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $DebtorNumber = ApiDebtor::receiverPanelContent($this->getDebtorNumberContent($tblPerson->getId()));
            // BankAccount
            if(($tblBankAccountList = Debtor::useService()->getBankAccountByPerson($tblPerson))) {
                array_walk($tblBankAccountList,
                    function (TblBankAccount $tblBankAccount) use (&$tableContent, &$PanelBankAccountList) {

                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Name der Bank:', 4),
                            new LayoutColumn($tblBankAccount->getBankName(), 8),
                        ))));
                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Name der Bank:', 4),
                            new LayoutColumn($tblBankAccount->getIBANFrontend(), 8),
                        ))));
                        $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Name der Bank:', 4),
                            new LayoutColumn($tblBankAccount->getBICFrontend(), 8),
                        ))));

                        $PanelBankAccountList[] = new Panel(new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('Besitzer:', 4),
                            new LayoutColumn($tblBankAccount->getOwner(), 8),
                        )))),
                            $ContentArray
                        );
                    });
            }

        }

        $PanelDebtorNumber = new Panel('Debitoren Nummer', $DebtorNumber);


        $Stage->setContent(ApiDebtor::receiverModal('Hinzufügen einer Debitor-Nummer', 'addDebtorNumber')
            . ApiDebtor::receiverModal('Bearbeiten einer Debitor-Nummer', 'editDebtorNumber')
            . ApiDebtor::receiverModal('Entfernen einer Debitor-Nummer', 'deleteDebtorNumber')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn($PanelDebtorNumber, 3),
                        new LayoutColumn($PanelBankAccountList, 9),
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

        if(($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // new DebtorNumber
            $DebtorNumber = (new Link('Debitor-Nummer hinzufügen', ApiDebtor::getEndpoint(), new Plus()))
                ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenAddDebtorNumberModal('addDebtorNumber',
                    $tblPerson->getId()));
            // DebtorNumber
            if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson))) {
                $DebtorNumber = array();
                foreach ($tblDebtorNumberList as $tblDebtorNumber) {
                    $DebtorNumber[] = $tblDebtorNumber->getDebtorNumber() . ' '
                        . (new Link('', ApiDebtor::getEndpoint(), new Pencil(), array(), 'Debitor-Nummer bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenEditDebtorNumberModal('editDebtorNumber'
                                , $tblPerson->getId(), $tblDebtorNumber->getId()))
                        . ' | '
                        . (new Link(new DangerText(new Remove()), ApiDebtor::getEndpoint(), null, array(),
                            'Debitor-Nummer entfernen'))
                            ->ajaxPipelineOnClick(ApiDebtor::pipelineOpenDeleteDebtorNumberModal('deleteDebtorNumber'
                                , $tblPerson->getId(), $tblDebtorNumber->getId()));
                }
                if(!empty($DebtorNumber)) {
                    $DebtorNumber = implode(', ', $DebtorNumber);
                }
            }
            return $DebtorNumber;
        } else {
            return new Warning('Person nicht gefunden');
        }


    }

    public function getBankAccountTable(TblPerson $tblPerson = null)
    {

        $PanelBankAccountList = array();
        if($tblPerson && ($tblBankAccountList = Debtor::useService()->getBankAccountByPerson($tblPerson))) {
            array_walk($tblBankAccountList,
                function (TblBankAccount $tblBankAccount) use (&$tableContent, &$PanelBankAccountList) {

                    $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn('Name der Bank:', 4),
                        new LayoutColumn($tblBankAccount->getBankName(), 8),
                    ))));
                    $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn('Name der Bank:', 4),
                        new LayoutColumn($tblBankAccount->getIBANFrontend(), 8),
                    ))));
                    $ContentArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn('Name der Bank:', 4),
                        new LayoutColumn($tblBankAccount->getBICFrontend(), 8),
                    ))));

                    $PanelBankAccountList[] = new Panel(new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn('Besitzer:', 4),
                        new LayoutColumn($tblBankAccount->getOwner(), 8),
                    )))),
                        $ContentArray
                    );
                });
        }

        return $PanelBankAccountList;
    }
}