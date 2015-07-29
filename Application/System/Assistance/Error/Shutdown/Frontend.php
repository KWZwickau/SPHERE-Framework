<?php
namespace SPHERE\Application\System\Assistance\Error\Shutdown;

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
 * @package SPHERE\Application\System\Assistance\Error\Shutdown
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendShutdown()
    {

        $Stage = new Stage( 'Betriebsstörung', 'Fehler in der Anwendung' );

        $Stage->setMessage( '<strong>Problem:</strong> Nach Aufruf der Anwendung arbeitet diese nicht wie erwartet' );

        $Stage->setContent(
            ( $this->getRequest()->getPathInfo() != '/System/Assistance/Error/Shutdown'
                ? new Danger( new \SPHERE\Common\Frontend\Icon\Repository\Warning().'<samp>'.$this->getRequest()->getPathInfo().'</samp>' )
                : ''
            )
            .( ( $Error = error_get_last() )
                ? new Warning( new \SPHERE\Common\Frontend\Icon\Repository\Info().'<samp>'.$Error['message'].'<br/>'.$Error['file'].':'.$Error['line'].'</samp>' )
                : ''
            )
            .'<h2><small>Mögliche Ursachen</small></h2>'
            .new Info( 'Dieser Bereich der Anwendung wird eventuell gerade gewartet' )
            .new Danger( 'Die Anwendung hat erkannt, dass das System nicht fehlerfrei arbeiten kann' )
            .new Danger( 'Die interne Kommunikation der Anwendung mit weiteren, notwendigen Resourcen zum Beispiel Programmen kann gestört sein' )
            .'<h2><small> Mögliche Lösungen </small></h2> '
            .new Info( 'Versuchen Sie die Anwendung zu einem späteren Zeitpunkt erneut aufzurufen' )
            .new Success( 'Bitte wenden Sie sich an den Support damit das Problem schnellstmöglich behoben werden kann' )
        );

        if ($this->getRequest()->getPathInfo() != '/System/Assistance/Error/Shutdown') {
            $Stage->addButton(
                new Primary( 'Fehlerbericht senden', '/System/Assistance/Support/Ticket', null,
                    array(
                        'TicketSubject' => urlencode( 'Fehler in der Anwendung' ),
                        'TicketMessage' => urlencode( $this->getRequest()->getPathInfo().': '.$Error['message'].'<br/>'.$Error['file'].':'.$Error['line'] )
                    )
                )
            );

        }

        return $Stage;
    }

}
