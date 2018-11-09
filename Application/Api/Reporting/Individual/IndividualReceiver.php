<?php
namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;

/**
 * Class IndividualReceiver
 * @package SPHERE\Application\Api\Reporting\Individual
 */
abstract class IndividualReceiver extends Extension
{
    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverNavigation($Content = '')
    {
        if( empty($Content) ) {
            $Content =
                new ProgressBar( 0,100, 0, 10 )
                .new Muted( 'Verfügbare Informationen werden geladen...' );
        }
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverNavigation');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {
        if( empty($Content) ) {
            $Content =
                new ProgressBar( 0,100, 0, 10 )
                .new Muted( 'Filteroptionen werden geladen...' );
        }
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverFilter');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverService'.$Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverResult($Content = '')
    {
        if( empty($Content) ) {
            $Content = new Muted( 'Es wurde bisher keine Suche durchgeführt' );
        }
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverResult');
    }

    /**
     * @param string $Header
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '')
    {
        return (new ModalReceiver($Header, new Close()))
            ->setIdentifier('ModalReceiver');
    }
}
