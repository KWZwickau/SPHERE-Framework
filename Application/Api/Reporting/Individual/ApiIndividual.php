<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiIndividual
 * @package SPHERE\Application\Api\Reporting\Individual
 */
class ApiIndividual extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('getNewNavigation');
        $Dispatcher->registerMethod('getNavigation');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverNavigation($Content = '')
    {
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
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverFilter');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverResult($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverResult');
    }

    public static function pipelineNewNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNewNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function getNewNavigation()
    {
        $FieldList = array(1  => false,
                           2  => false,
                           3  => true,
                           4  => true,
                           5  => true,
                           6  => true,
                           7  => true,
                           8  => true,
                           9  => true,
                           10 => true
        );

        return new Panel('Verfügbar', array(
            new Panel('Auswertung über', array(
                'Schüler'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))->ajaxPipelineOnClick(
                    self::pipelineNavigation($FieldList)
                )),
//                'Lehrer'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }

    public static function pipelineNavigation($FieldList = array())
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Emitter->setPostPayload(array(
            'FieldList' => $FieldList
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function getNavigation($FieldList = array())
    {

//        return new Code(print_r($FieldList, true));

        return new Panel('Verfügbare Felder', array(
            (new Accordion())->addItem('Schüler Grunddaten',
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        new Listing(array(
                            (isset($FieldList[1]) && $FieldList[1] ? new PullClear('Anrede'.new PullRight(new Primary('',
                                    self::getEndpoint(), new Plus()))) : '').
                            (isset($FieldList[2]) && $FieldList[2] ? new PullClear('Vorname'.new PullRight(new Primary('',
                                    self::getEndpoint(), new Plus()))) : '').
                            (isset($FieldList[3]) && $FieldList[3] ? new PullClear('Zweiter-Vorname'.new PullRight(new Primary('',
                                    self::getEndpoint(), new Plus()))) : ''),
                            new PullClear('Nachname'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
                            new PullClear('Geburtstag'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
                            new PullClear('Geburtsort'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
                            new PullClear('Konfession'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
                            new PullClear('Staatsangehörigkeit'.new PullRight(new Primary('', self::getEndpoint(),
                                    new Plus()))),
                            new PullClear('Mitarbeitsbereitschaft'.new PullRight(new Primary('', self::getEndpoint(),
                                    new Plus()))),
                            new PullClear('Mitarbeitsbereitschaft - Tätigkeit'.new PullRight(new Primary('',
                                    self::getEndpoint(), new Plus()))),
                            new PullClear('Sonstige Bemerkung'.new PullRight(new Primary('', self::getEndpoint(),
                                    new Plus()))),
                            new PullClear('Anzahl Geschwister'.new PullRight(new Primary('', self::getEndpoint(),
                                    new Plus())))
                        ))
                    )
                )))
                , false),
            new Panel('Schüler Kontaktdaten', array(
                'Straße'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Straßennr.'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Ort'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Ortsteil'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'PLZ'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Bundesland'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Land'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Telefonnummern'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'E-Mail Adressen'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
            new Panel('Schüler Klasse', array(
                'Stufe'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Gruppe'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Jahr'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Bildungsgang'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Schulart'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
            new Panel('Sorgeberechtigte Grunddaten', array(
                'Anrede'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Titel'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Zweiter-Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Nachname'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
            new Panel('Sorgeberechtigte Kontaktdaten', array(
                'Straße'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Straßennr.'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Ort'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Ortsteil'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'PLZ'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Bundesland'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Land'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'Telefonnummern'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
                'E-Mail Adressen'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }
}