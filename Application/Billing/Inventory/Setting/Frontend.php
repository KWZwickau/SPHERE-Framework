<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
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
        $TestValue = '';
        if($TestSetting){
            $TestValue = $TestSetting->getValue();
        }

        $DebtorCountReceiver = ApiSetting::receiverDisplaySetting($DebtorNumberCount.' ', TblSetting::IDENT_DEBTOR_NUMBER_COUNT);
        $PersonGroupAsString = implode(', ',$PersonGroup);
        $TestReceiver = ApiSetting::receiverDisplaySetting($TestValue.' ', 'Test_anderer_Werte');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiSetting::receiverModalSetting()
                            .'Länge der Debitorennummer: '.$DebtorCountReceiver.' '
                            .(new Link('', ApiSetting::getEndpoint(), new Pen()))
                            ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, 'Länge der Debitorennummer'))
                        ),
                        new LayoutColumn(
                            'Gruppen, die zur auswahl stehen: '.$PersonGroupAsString
                        ),
                        new LayoutColumn(
                            'Test: '.$TestReceiver.' '
                            .(new Link('', ApiSetting::getEndpoint(), new Pen()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineOpenSetting('Test_anderer_Werte', 'Text des Imputfeldes'))
                        ),
                    ))
                )
            )
        );

        return $Stage;
    }
}