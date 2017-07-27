<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
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
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverService');
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

    public static function pipelineAddField($Field, $View)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addField'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNewNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function getNewNavigation()
    {

        return new Panel('Verfügbar', array(
            new Panel('Auswertung über', array(
                'Schüler'.new PullRight((new Primary('', self::getEndpoint(), new Plus()))->ajaxPipelineOnClick(
                    self::pipelineNavigation()
                )),
//                'Lehrer'.new PullRight(new Primary('', self::getEndpoint(), new Plus())),
            ), Panel::PANEL_TYPE_PRIMARY),
        ));
    }

    public static function pipelineNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function addField($Field, $View)
    {

        $Position = 1;
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                if ($tblWorkSpace->getPosition() >= $Position) {
                    $Position = $tblWorkSpace->getPosition();
                }
            }
            $Position++;
        }

        Individual::useService()->addWorkSpaceField($Field, $View, $Position);
    }

    public function getNavigation($FieldList = array())
    {

//        return new Code(print_r($FieldList, true));
        $FieldList = array();
        $FieldList[ViewStudent::TBL_SALUTATION_SALUTATION] = new PullClear('Anrede'.new PullRight((new Primary('',
                self::getEndpoint(),
                new Plus()))->ajaxPipelineOnClick(self::pipelineAddField(ViewStudent::TBL_SALUTATION_SALUTATION,
                'ViewStudent'))));
        $FieldList[ViewStudent::TBL_PERSON_FIRST_NAME] = new PullClear('Vorname'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $FieldList[ViewStudent::TBL_PERSON_SECOND_NAME] = new PullClear('Zweiter-Vorname'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $FieldList[ViewStudent::TBL_PERSON_LAST_NAME] = new PullClear('Nachname'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $FieldList[ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHDAY] = new PullClear('Geburtstag'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $FieldList[ViewStudent::TBL_COMMON_BIRTHDATES_BIRTHPLACE] = new PullClear('Geburtsort'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $FieldList[ViewStudent::TBL_COMMON_INFORMATION_DENOMINATION] = new PullClear('Konfession'.new PullRight(new Primary('',
                self::getEndpoint(), new Plus())));
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                if (isset($FieldList[$tblWorkSpace->getField()]) && $FieldList[$tblWorkSpace->getField()]) {
                    $FieldList[$tblWorkSpace->getField()] = false;
                }
            }
        }
        $FieldList = array_filter($FieldList);

        return new Panel('Verfügbare Felder', array(
            (new Accordion())->addItem('Schüler Grunddaten',
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        new Listing(
                            $FieldList
//                            array(
//                            new PullClear('Anrede'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Zweiter-Vorname'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Nachname'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Geburtstag'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Geburtsort'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),
//                            new PullClear('Konfession'.new PullRight(new Primary('', self::getEndpoint(), new Plus()))),

//                            new PullClear('Staatsangehörigkeit'.new PullRight(new Primary('', self::getEndpoint(),
//                                    new Plus()))),
//                            new PullClear('Mitarbeitsbereitschaft'.new PullRight(new Primary('', self::getEndpoint(),
//                                    new Plus()))),
//                            new PullClear('Mitarbeitsbereitschaft - Tätigkeit'.new PullRight(new Primary('',
//                                    self::getEndpoint(), new Plus()))),
//                            new PullClear('Sonstige Bemerkung'.new PullRight(new Primary('', self::getEndpoint(),
//                                    new Plus()))),
//                            new PullClear('Anzahl Geschwister'.new PullRight(new Primary('', self::getEndpoint(),
//                                    new Plus())))
                        )
                    )
                )))
                , true),
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