<?php
namespace SPHERE\Application\Setting\Device;

use SPHERE\Application\Api\Setting\Device\ApiDevice;
use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Service extends \SPHERE\Application\App\Authentication\Process\Service
{

    /**
     * @param TblAccount $tblAccount
     * @return TblDevice[]|null
     */
    public function getDeviceByAccount(): ?array
    {
        if(($tblAccount = Account::useService()->getAccountBySession())){
            return (new Data($this->getBinding()))->getDeviceAllByAccount($tblAccount);
        }
        return null;
    }

    /**
     * @param TblDevice $tblDevice
     * @return string
     */
    public function getDeviceForm(TblDevice $tblDevice): string
    {

        if(!isset($_POST['Device']['Name'])){
            $_POST['Device']['Name'] = $tblDevice->getDeviceName();
        }
        if(!isset($_POST['Device']['Status'])){
            $isActive = $tblDevice->getIsActive();
            if($isActive === true){
                $_POST['Device']['Status'] = 1;
            } elseif($isActive === false){
                $_POST['Device']['Status'] = 2;
            }
        }

        return new Well(new Form(new FormGroup(array(
            new FormRow(new FormColumn(
                new TextField('Device[Name]', '', 'Name des Gerätes', new PhoneMobil(), null, 42)
            )),
            new FormRow(array(
                new FormColumn(
                    new RadioBox('Device[Status]', 'Aktiv '
                        .(new ToolTip(new Info(), htmlspecialchars('Das Gerät darf sich in die App einloggen'), false))
                            ->enableHtml()
                        , '1', RadioBox::RADIO_BOX_TYPE_SUCCESS)
                , 2),
                new FormColumn(
                    new RadioBox('Device[Status]', 'Blockiert '
                        .(new ToolTip(new Info(), htmlspecialchars('Das Gerät ist für die App gesperrt'), false))
                            ->enableHtml()
                        , '2', RadioBox::RADIO_BOX_TYPE_WARNING)
                , 2),
                new FormColumn(
                    new RadioBox('Device[Status]', 'Löschen '
                        .(new ToolTip(new Info(), htmlspecialchars('Geräteanfrage entfernen.<br/>Kann mit erneutem Login wieder Anfrage stellen'), false))
                            ->enableHtml()
                        , '3', RadioBox::RADIO_BOX_TYPE_DANGER)
                , 2),
            )),
            new FormRow(new FormColumn(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiDevice::pipelineSaveModalDevice($tblDevice->getId()))
            ))
        ))));
    }
}
