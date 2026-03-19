<?php
namespace SPHERE\Application\Transfer\Indiware\ErrorLog;


use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Indiware\ErrorLog
 */
class Service
{

    /**
     * @param IFormInterface $Form
     * @param null|array     $Setting
     *
     * @return IFormInterface|Redirect|string
     */
    public function createCode(IFormInterface $Form, $Setting)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Setting) {
            return $Form;
        }

        if($Setting['Code'] && Account::useService()->getSettingByUniqueValue($Setting['Code'])){
            return new Danger('Code '.$Setting['Code'].' bereits in Verwendung!'
                .new Container('Versuche es nochmal, der Code wurde neu erzeugt.')).$Form;
        }

        $consumerAcronym = Account::useService()->getMandantAcronym();
        if(!($tblAccount = Account::useService()->getAccountByUsername($consumerAcronym.'-Indiware'))){
            $tblAccount = Account::useService()->getAccountBySession();
            $tblConsumer = $tblAccount->getServiceTblConsumer();
            $tblAccount = Account::useService()->createServiceAccount($consumerAcronym.'-Indiware', $tblConsumer);
        }
        Account::useService()->setSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE, $Setting['Code']);
        return new Success('Der Code wurde gespeichert').new Redirect('/Transfer/Indiware/ErrorLog/EditCode', Redirect::TIMEOUT_SUCCESS);
    }
}