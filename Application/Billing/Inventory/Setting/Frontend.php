<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function FrontendSetting()
    {

        $Stage = new Stage('Einstellungen', 'für die Fakturierung');

        $DebtorCountSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DEBTOR_NUMBER_COUNT);
        $DebtorNumberCount = '';
        if($DebtorCountSetting) {
            $DebtorNumberCount = $DebtorCountSetting->getValue();
        }
        $PersonGroupStringList = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_PERSON_GROUP_ACTIVE_LIST);
        $PersonGroup = array();
        if($PersonGroupStringList) {
            $PersonGroupIdList = explode(';', $PersonGroupStringList->getValue());

            foreach ($PersonGroupIdList as $PersonGroupId) {
                $tblGroup = Group::useService()->getGroupById($PersonGroupId);
                if($tblGroup) {
                    $PersonGroup[] = $tblGroup->getName();
                }
            }
        }

        $IsDebtorNumberNeed = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED);
        $DebtorNumberNeed = new DangerText(new Disable());
        if($IsDebtorNumberNeed->getValue() == '1'){
            $DebtorNumberNeed = new SuccessText(new Check());
        }
        $DebtorNumberReceiver = ApiSetting::receiverDisplaySetting($DebtorNumberNeed . ' ', TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED);
        $IsSepaAccountNeed = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED);
        $SepaAccountNeed = new DangerText(new Disable());
        if($IsSepaAccountNeed->getValue() == '1'){
            $SepaAccountNeed = new SuccessText(new Check());
        }
        $SepaAccountReceiver = ApiSetting::receiverDisplaySetting( $SepaAccountNeed. ' ', TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED);

//        $TestSetting = Setting::useService()->getSettingByIdentifier('Test_anderer_Werte');
//        $TestValue = new WarningText('Keine Einstellung vorhanden');
//        if($TestSetting) {
//            $TestValue = $TestSetting->getValue();
//        }

        $DebtorCountReceiver = ApiSetting::receiverDisplaySetting($DebtorNumberCount . ' ',
            TblSetting::IDENT_DEBTOR_NUMBER_COUNT);
        $PersonGroupAsString = ApiSetting::receiverDisplaySetting($this->displayPersonGroupLoad(), 'PersonGroup');
//        $TestReceiver = ApiSetting::receiverDisplaySetting($TestValue . ' ', 'Test_anderer_Werte');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Title('Personengruppen, die für Beitragsarten zur Auswahl stehen: '
                                . (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting('PersonGroup',
                                        'PersonGroup')))
                            .new Well($PersonGroupAsString)
                        ),
                        new LayoutColumn(
                            new Title('Allgemeine Einstellungen:')
                            .new Well(
                                ApiSetting::receiverModalSetting()
                                . new Layout(new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            new Listing(array('Länge der Debit.-Nr.: '.$DebtorCountReceiver.new PullRight(
                                                    (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                                        ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT,
                                                            'Länge der Debit.-Nr.'))
                                                )))
                                            , 6),
                                        )),
//                                    new LayoutRow(array(
//                                        new LayoutColumn(
//                                            new Listing(array('Mögliche weitere Einstellungen: '.$TestReceiver.new PullRight(
//                                                    (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
//                                                        ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting('Test_anderer_Werte',
//                                                            'Mögliche weitere Einstellungen'))
//                                                )))
//                                            , 6),
//                                        )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            new Listing(array('Debit.-Nr. ist eine Pflichtangabe: '.$DebtorNumberReceiver.new PullRight(
                                                    (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                                        ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED,
                                                            'Debit.-Nr. ist eine Pflichtangabe'))
                                                )))
                                            , 6),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            new Listing(array('Konto für SEPA-Lastschrift ist eine Pflichtangabe: '.$SepaAccountReceiver.new PullRight(
                                                    (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                                        ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED,
                                                            'Konto für SEPA-Lastschrift ist eine Pflichtangabe: '))
                                                )))
                                            , 6),
                                    )),
                                )))
                            )
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Layout|string
     */
    public function displayPersonGroupLoad()
    {
        if(($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())) {
            $tblGroupList = array();
            foreach ($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                $tblGroupList[] = $tblSettingGroupPerson->getServiceTblGroupPerson();
            }
            $NameListLeft = array();
            $NameListRight = array();
            /** @var TblGroup $tblGroup */
            $tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name');
            foreach ($tblGroupList as $tblGroup) {
                if($tblGroup->getMetaTable()) {
                    $NameListLeft[] = $tblGroup->getName();
                } else {
                    $NameListRight[] = $tblGroup->getName();
                }

            }
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Title('Standard Personengruppen')
                            , 6),
                        new LayoutColumn(
                            new Title('Individuelle Personengruppen')
                            , 6),
                        new LayoutColumn(
                            new Listing($NameListLeft)
                            , 6),
                        new LayoutColumn(
                            new Listing($NameListRight)
                            , 6),
                    ))
                )
            );
        }
        return 'Personengruppen konnten nicht geladen werden.';
    }
}