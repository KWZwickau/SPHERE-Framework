<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
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
        if($DebtorCountSetting){
            $DebtorNumberCount = $DebtorCountSetting->getValue();
        }
        $PersonGroupStringList = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_PERSON_GROUP_ACTIVE_LIST);
        $PersonGroup = array();
        if($PersonGroupStringList){
            $PersonGroupIdList = explode(';', $PersonGroupStringList->getValue());

            foreach($PersonGroupIdList as $PersonGroupId){
                $tblGroup = Group::useService()->getGroupById($PersonGroupId);
                if($tblGroup){
                    $PersonGroup[] = $tblGroup->getName();
                }
            }
        }

        $TestSetting = Setting::useService()->getSettingByIdentifier('Test_anderer_Werte');
        $TestValue = new WarningText('Keine Einstellung vorhanden');
        if($TestSetting){
            $TestValue = $TestSetting->getValue();
        }

        $DebtorCountReceiver = ApiSetting::receiverDisplaySetting($DebtorNumberCount.' ', TblSetting::IDENT_DEBTOR_NUMBER_COUNT);
        $PersonGroupAsString = ApiSetting::receiverDisplaySetting($this->displayPersonGroupLoad(), 'PersonGroup');
        $TestReceiver = ApiSetting::receiverDisplaySetting($TestValue.' ', 'Test_anderer_Werte');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiSetting::receiverModalSetting()
                            .new Title('Länge der Debitorennummer: '
                                .(new Link('', ApiSetting::getEndpoint(), new Pen()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, 'Länge der Debitorennummer')))


                        ),
                        new LayoutColumn(
                           new Listing(array($DebtorCountReceiver))
                        , 4),
                        new LayoutColumn(
                            new Title('Gruppen, die zur Auswahl stehen: '.(new Link('', ApiSetting::getEndpoint(), new Pen()))
                            ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting('PersonGroup', 'PersonGroup')))
                        ),
                        new LayoutColumn(
                            $PersonGroupAsString
                        , 12),
                        new LayoutColumn(
                            new Title('Mögliche weitere Einstellungen: '
                            .(new Link('', ApiSetting::getEndpoint(), new Pen()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting('Test_anderer_Werte', 'Mögliche weitere Einstellungen')))
                        ),
                        new LayoutColumn(
                            new Listing(array($TestReceiver))
                        , 4),
                    ))
                )
            )
        );

        return $Stage;
    }

    public function displayPersonGroupLoad()
    {
        if(($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())) {
            $tblGroupList = array();
            foreach($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                $tblGroupList[] = $tblSettingGroupPerson->getServiceTblGroupPerson();
            }
            $NameListLeft = array();
            $NameListRight = array();
            /** @var TblGroup $tblGroup */
            $tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name');
            foreach($tblGroupList as $tblGroup){
                if($tblGroup->getMetaTable()){
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