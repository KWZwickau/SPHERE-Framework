<?php
namespace SPHERE\Application\System\Gatekeeper\Authentication\Identification;

use SPHERE\Common\Window\Stage;

/**
 * Class SignOut
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication\Identification
 */
class SignOut
{

    /**
     * @return Stage
     */
    public static function stageSignOut()
    {

        $View = new Stage( 'Abmelden', 'Bitte warten...' );
        $View->setContent( Gatekeeper::serviceAccount()->executeActionSignOut() );
        return $View;
    }
}
