<?php
namespace SPHERE\Application\Platform\Assistance\Error;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Platform\Assistance\Error
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAuthenticator()
    {

        $Stage = new Stage('Authentifikator', 'Prüfung der Anfrage');

        $Stage->setMessage('<strong>Problem:</strong> Die Anwendung darf die Anfrage nicht verarbeiten');

        $Stage->setContent(
            '<h2><small>Mögliche Ursachen</small></h2>'
            .new Danger('Das System hat fehlerhafte oder mutwillig veränderte Eingabedaten erkannt')
            .'<h2><small>Mögliche Lösungen</small></h2>'
            .new Warning('Bitte ändern Sie keine Daten in der Adressleiste des Browsers und verwenden Sie nur die vom System erzeugten Anfragen')
            .new Info('Bitte führen Sie Anfragen an das System nicht über Tagesgrenzen hinweg aus')
            .new Success('Die Anfrage und alle Parameter wurden aus Sicherheitsgründen verworfen')
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendRoute()
    {

        $Stage = new Stage('Berechtigung', 'Prüfung der Anfrage');

        $Stage->setMessage('<strong>Problem:</strong> Die Anwendung darf die Anfrage nicht verarbeiten');

        $Stage->setContent(
            ( $this->getRequest()->getPathInfo() != '/Platform/Assistance/Error'
                ? new Danger(new \SPHERE\Common\Frontend\Icon\Repository\Warning().'<samp>'.$this->getRequest()->getPathInfo().'</samp>')
                : ''
            )
            .'<h2><small>Mögliche Ursachen</small></h2>'
            .new Danger('Sie haben nicht die benötigte Berechtigung um diese Adresse aufzurufen')
            .new Warning('Sie haben eine Anfrage mit Parametern (z.B. per Lesezeichen) über Tagesgrenzen hinweg an das System gestellt')
            .new Warning('Die eingegebene Adresse steht im Program nicht zur Verfügung')
            .'<h2><small>Mögliche Lösungen</small></h2>'
            .new Warning('Bitte ändern Sie keine Daten in der Adressleiste des Browsers und verwenden Sie nur die vom System erzeugten Anfragen')
            .new Success('Bitte führen Sie Anfragen (mit Parametern) an das System nicht über Tagesgrenzen hinweg aus')
            .new Success('Die Anfrage und alle Parameter wurden aus Sicherheitsgründen verworfen')
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendShutdown()
    {

        $Stage = new Stage('Betriebsstörung', 'Fehler in der Anwendung');

        $Stage->setMessage('<strong>Problem:</strong> Nach Aufruf der Anwendung arbeitet diese nicht wie erwartet');

        $Stage->setContent(
            ( $this->getRequest()->getPathInfo() != '/Platform/Assistance/Error/Shutdown'
                ? new Danger(new \SPHERE\Common\Frontend\Icon\Repository\Warning().'<samp>'.$this->getRequest()->getPathInfo().'</samp>')
                : ''
            )
            .( ( $Error = error_get_last() )
                ? new Warning(new \SPHERE\Common\Frontend\Icon\Repository\Info().'<samp>'.$Error['message'].'<br/>'.$Error['file'].':'.$Error['line'].'</samp>')
                : ''
            )
            .'<h2><small>Mögliche Ursachen</small></h2>'
            .new Info('Dieser Bereich der Anwendung wird eventuell gerade gewartet')
            .new Danger('Die Anwendung hat erkannt, dass das System nicht fehlerfrei arbeiten kann')
            .new Danger('Die interne Kommunikation der Anwendung mit weiteren, notwendigen Resourcen zum Beispiel Programmen kann gestört sein')
            .'<h2><small> Mögliche Lösungen </small></h2> '
            .new Info('Versuchen Sie die Anwendung zu einem späteren Zeitpunkt erneut aufzurufen')
            .new Success('Bitte wenden Sie sich an den Support damit das Problem schnellstmöglich behoben werden kann')
        );

        if ($this->getRequest()->getPathInfo() != '/Platform/Assistance/Error') {
            $Stage->addButton(
                new Primary('Fehlerbericht senden', '/Platform/Assistance/Support/Ticket', null,
                    array(
                        'TicketSubject' => urlencode('Fehler in der Anwendung'),
                        'TicketMessage' => urlencode($this->getRequest()->getPathInfo().': '.$Error['message'].'<br/>'.$Error['file'].':'.$Error['line'])
                    )
                )
            );

        }

        return $Stage;
    }
}
