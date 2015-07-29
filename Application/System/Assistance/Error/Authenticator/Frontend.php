<?php
namespace SPHERE\Application\System\Assistance\Error\Authenticator;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Assistance\Error\Authenticator
 */
class Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAuthenticator()
    {

        $Stage = new Stage( 'Authentifikator', 'Prüfung der Anfrage' );

        $Stage->setMessage( '<strong>Problem:</strong> Die Anwendung darf die Anfrage nicht verarbeiten' );

        $Stage->setContent(
            '<h2><small>Mögliche Ursachen</small></h2>'
            .new Danger( 'Das System hat fehlerhafte oder mutwillig veränderte Eingabedaten erkannt' )
            .'<h2><small>Mögliche Lösungen</small></h2>'
            .new Warning( 'Bitte ändern Sie keine Daten in der Adressleiste des Browsers und verwenden Sie nur die vom System erzeugten Anfragen' )
            .new Info( 'Bitte führen Sie Anfragen an das System nicht über Tagesgrenzen hinweg aus' )
            .new Success( 'Die Anfrage und alle Parameter wurden aus Sicherheitsgründen verworfen' )
        );

        return $Stage;
    }
}
