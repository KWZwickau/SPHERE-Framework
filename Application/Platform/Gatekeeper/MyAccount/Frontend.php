<?php
namespace SPHERE\Application\Platform\Gatekeeper\MyAccount;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

class Frontend implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendMyAccount()
    {

        $Stage = new Stage( 'Profil', 'Mein Benutzerkonto' );

        $tblAccount = Account::useService()->getAccountBySession();
        $Stage->setMessage( '['.$tblAccount->getServiceTblConsumer()->getAcronym().'] '.$tblAccount->getServiceTblConsumer()->getName() );

        $Stage->setContent(
            $tblAccount->getUsername()
        );

        return $Stage;
    }
}
