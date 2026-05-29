<?php
namespace SPHERE\Application\Setting\Device;

use SPHERE\Application\Api\Setting\Device\ApiDevice;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success as SuccessLink;
use SPHERE\Common\Frontend\Link\Repository\Warning as WarningLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Device
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public static function frontendDevice()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        $Stage = new Stage($tblAccount->getUsername().' - Meine Geräte', 'Übersicht');
        $DeviceReceiver = ApiDevice::receiverDevice(self::getDevicePanelLayout());
        $DeviceModalReceiver = ApiDevice::receiverDeviceModal();
        $ServiceReceiver = ApiDevice::receiverService();
        // ToDO Empfehlung die Geräte zu benennen? -> müsste wahrscheinlich immer angezeigt werden
        $Stage->setContent(
            // ToDO Warnung für alle mit 2 fach Auth. die ein Gerät in der liste haben, das nicht aktiv geschalten ist
            //  if create && no update && isActive == false -> Initial also warnung anzeigen
            $DeviceModalReceiver
            .$DeviceReceiver
            .$ServiceReceiver
        );

        return $Stage;
    }

    /**
     * @return Layout
     */
    public static function getDevicePanelLayout()
    {

        $tblDeviceList = (new Device())::useService()->getDeviceByAccount();
        // ToDO Idee ein Device als Panel abzubilden
        $PanelList = array();
        if($tblDeviceList){
            foreach($tblDeviceList as $tblDevice){
                // Mögliche Felder
//                $tblDevice->getServiceTblAccount()->getUsername();
//                ($tblDevice->getEntityCreate())->format('H:i:s - d.m.Y');
//                $tblDevice->getDeviceName();
//                $tblDevice->getAuthenticationToken();
//                $tblDevice->getAuthenticationTimeout();
//                $tblDevice->getAccessToken();
//                $tblDevice->getAccessTimeout();
//                $tblDevice->getIsActive();
                $Edit = (new Standard('', ApiDevice::getEndpoint(), new Edit(), array(), 'Name bearbeiten'))
                    ->ajaxPipelineOnClick(ApiDevice::pipelineShowModalDevice($tblDevice->getId()));
                $Success = (new SuccessLink('', ApiDevice::getEndpoint(), new Check(), array(), 'Gerät freischalten'))
                    ->ajaxPipelineOnClick(ApiDevice::pipelineChangeDevice($tblDevice->getId(), '1'));
                $Block = (new WarningLink('', ApiDevice::getEndpoint(), new Disable(), array(), 'Gerät blockieren'))
                    ->ajaxPipelineOnClick(ApiDevice::pipelineChangeDevice($tblDevice->getId(), '2'));
                $Remove = (new DangerLink('', ApiDevice::getEndpoint(), new Minus(), array(), 'Gerät entfernen'))
                    ->ajaxPipelineOnClick(ApiDevice::pipelineChangeDevice($tblDevice->getId(), '3'));
                $ButtonListRequest = $Edit.$Success.$Block.$Remove;
                $ButtonListActive = $Edit.$Block.$Remove;
                $ButtonListBlock = $Edit.$Success.$Remove;

                $Date = $tblDevice->getEntityCreate()->format('d.m.Y');
                $Time = $tblDevice->getEntityCreate()->format('H:i:s');
                if($tblDevice->getIsActive() === true){
                    $PanelList[] = new Panel(new PullClear('Gerät: '.new Bold($tblDevice->getDeviceName()). new PullRight($ButtonListActive)
                        .new Container('&nbsp;'))
                        .new Success('Aktiv seit '.($tblDevice->getEntityCreate())->format('d.m.Y'), null, false, 5, 0),
                        '', Panel::PANEL_TYPE_SUCCESS);
                } elseif($tblDevice->getIsActive() === false){
                    $deactivateDate = '';
                    if($tblDevice->getEntityUpdate()){
                        $deactivateDate = $tblDevice->getEntityUpdate()->format('d.m.Y');
                        $deactivateTime = $tblDevice->getEntityUpdate()->format('H:i:s');
                    }
                    $PanelList[] = new Panel(new PullClear('Gerät: '.new Bold($tblDevice->getDeviceName()).new PullRight($ButtonListBlock)
                        .new Container('&nbsp;'))
                        .new Danger(($deactivateDate
                            ? 'Gerät wurde am '.$deactivateDate.' um '.$deactivateTime.' deaktiviert'
                            .new Container('Gerät kann keine Anfragen mehr zum einloggen stellen')
                            : ''), null, false, 5, 0)
                    , '', Panel::PANEL_TYPE_DANGER);
                } else {
                    $PanelList[] = new Panel(new PullClear('Gerät: '.new Bold($tblDevice->getDeviceName()).new PullRight($ButtonListRequest)
                        .new Container('&nbsp;'))
                        .new Warning('Anfrage vom: '.new ToolTip($Date, 'Uhrzeit: '.$Time), null, false, 5, 0),
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn(new Warning(new WarningIcon().' Nicht von Ihnen? Empfehlung Passwort ändern'
                                    , null, false, 5, 0)),
                            ),
                        ))), Panel::PANEL_TYPE_WARNING);
                }
            }
        }

        $LayoutRowCount = 0;
        $LayoutRowList = array();
        if(!empty($PanelList)){
            foreach($PanelList as $Panel){
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn(new LayoutColumn($Panel, 4));
                $LayoutRowCount++;
            }
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

//    /**
//     * @return Stage
//     */
//    public static function frontendDeviceTwo()
//    {
//
//        $Stage = new Stage('Meine Geräte', 'Vertieft');
//        $Stage->addButton(new Standard('Zurück', '/Setting/Device', new ChevronLeft()));
//        return $Stage;
//    }

}
