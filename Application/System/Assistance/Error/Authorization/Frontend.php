<?php
namespace SPHERE\Application\System\Assistance\Error\Authorization;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Assistance\Error\Authorization
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendRoute()
    {

        $Stage = new Stage( 'Berechtigung', 'Prüfung der Anfrage' );

        $Stage->setMessage( '<strong>Problem:</strong> Die Anwendung darf die Anfrage nicht verarbeiten' );

        $Stage->setContent(
            ( $this->getRequest()->getPathInfo() != '/System/Assistance/Error/Authorization'
                ? new Danger( new \SPHERE\Common\Frontend\Icon\Repository\Warning().'<samp>'.$this->getRequest()->getPathInfo().'</samp>' )
                : ''
            )
            .'<h2><small>Mögliche Ursachen</small></h2>'
            .new Danger( 'Sie haben nicht die benötigte Berechtigung um diese Adresse aufzurufen' )
            .new Warning( 'Die eingegebene Adresse steht im Program nicht zur Verfügung' )
            .'<h2><small>Mögliche Lösungen</small></h2>'
            .new Warning( 'Bitte ändern Sie keine Daten in der Adressleiste des Browsers und verwenden Sie nur die vom System erzeugten Anfragen' )
            .new Success( 'Die Anfrage und alle Parameter wurden aus Sicherheitsgründen verworfen' )
        );

        return $Stage;
    }
}
