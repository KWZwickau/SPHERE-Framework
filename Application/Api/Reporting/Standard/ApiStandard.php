<?php

namespace SPHERE\Application\Api\Reporting\Standard;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Custom\Annaberg\Person\Person;
use SPHERE\Application\Reporting\Standard\Person\Frontend;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiStandard
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generate
 */
class ApiStandard extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('reloadAbsenceContent');
        $Dispatcher->registerMethod('loadStudentArchiveContent');

        $Dispatcher->registerMethod('waitContent');
        $Dispatcher->registerMethod('loadContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadAbsenceContent() : Pipeline
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter(self::receiverBlock('', 'AbsenceContent'), self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'reloadAbsenceContent'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Fehlzeiten werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function reloadAbsenceContent($Data = null) : string
    {
        if($Data == null){
            // Laden mit Grunddaten (aktueller Tag ohne Zusätze)
            $Data['Date'] = (new DateTime('now'))->format('d.m.Y');
            $Data['ToDate'] = '';
            $Data['Type'] = null;
            $Data['DivisionName'] = '';
        }

        if ($Data['Date'] == null) {
            $date = (new DateTime('today'))->format('d.m.Y');
        } else {
            $date = $Data['Date'];
        }
        $dateTimeFrom = new DateTime($date);

        if ($Data['ToDate'] && $Data['ToDate'] != '') {
            $dateTimeTo = new DateTime($Data['ToDate']);
        } else {
            $dateTimeTo = null;
        }

        if ($Data['Type'] != null) {
            $tblSchoolType = Type::useService()->getTypeById($Data['Type']);
        } else {
            $tblSchoolType = false;
        }

        if (isset($Data['IsCertificateRelevant'])) {
            switch ($Data['IsCertificateRelevant']) {
                case 1:
                    $isCertificateRelevant = true;
                    break;
                case 2:
                    $isCertificateRelevant = false;
                    break;
                default:
                    $isCertificateRelevant = null;
            }
        } else {
            $isCertificateRelevant = null;
        }

        $isAbsenceOnlineOnly = isset($Data['IsAbsenceOnline']);
        $divisionCourseName = $Data['DivisionName'];
        $hasAbsenceTypeOptions = false;
        if ($divisionCourseName != '') {
            $tblYearList = Term::useService()->getYearAllByDate($dateTimeFrom);
            $divisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($divisionCourseName, $tblYearList ?: null, true);
            if (empty($divisionCourseList)) {
                return new Warning('Klasse/Stammgruppe nicht gefunden', new Exclamation());
            }


            $absenceList = Absence::useService()->getAbsenceAllByDay(
                $dateTimeFrom,
                $dateTimeTo,
                $tblSchoolType ?: null,
                $divisionCourseList,
                $hasAbsenceTypeOptions,
                $isCertificateRelevant,
                $isAbsenceOnlineOnly
            );
        } else {
            $absenceList = Absence::useService()->getAbsenceAllByDay(
                $dateTimeFrom,
                $dateTimeTo,
                $tblSchoolType ?: null,
                array(),
                $hasAbsenceTypeOptions,
                $isCertificateRelevant,
                $isAbsenceOnlineOnly
            );
        }

        $title = new Title(
            'Fehlzeiten für den ' . $dateTimeFrom->format('d.m.Y')
            . ($tblSchoolType ? ', Schulart: ' . $tblSchoolType->getName() : '')
        );

        if (!empty($absenceList)) {
            $columns = array(
                'Type'                  => 'Schulart',
                'Division'              => 'Kurs',
                'Person'                => 'Schüler',
                'DateFrom'              => 'Zeitraum von',
                'DateTo'                => 'Zeitraum bis',
                'PersonCreator'         => 'Ersteller',
                'Lessons'               => 'Unterrichts&shy;einheiten',
                'AbsenceType'           => 'Typ',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status'                => 'Status',
                'Remark'                => 'Bemerkung'
            );

            if (!$hasAbsenceTypeOptions) {
                unset($columns['AbsenceType']);
            }

            return new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Primary(
                            'Herunterladen', '/Api/Reporting/Standard/Person/AbsenceList/Download',
                            new Download(),
                            array(
                                'Date' => $Data['Date'],
                                'DateTo' => $Data['ToDate'],
                                'Type' => $Data['Type'],
                                'DivisionName' => $Data['DivisionName'],
                                'IsCertificateRelevant' => $Data['IsCertificateRelevant'] ?? 0,
                                'IsAbsenceOnlineOnly' => $isAbsenceOnlineOnly
                            )
                        )
                    )
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $title
                        . new TableData(
                            $absenceList,
                            null,
                            $columns,
                            array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                ),
                                'columnDefs' => array(
                                    // Klassen
                                    array('type' => 'natural', 'targets' => 1),
                                    // von & bis
                                    array('type' => 'de_date', 'targets' => 3),
                                    array('type' => 'de_date', 'targets' => 4),
                                    //  geht aktuell nicht zusammen mit order beide Spalten
//                                  array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                ),
                            )
                        )
                    )
                )
            )));
        } else {
            return
                $title
                . new Warning('Für diesen Tag liegen keine Fehlzeiten vor.', new Ban());
        }
    }

    /**
     * @param string|null $YearId
     *
     * @return Pipeline
     */
    public static function pipelineLoadStudentArchiveContent(?string $YearId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentArchiveContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentArchiveContent',
            'YearId' => $YearId,
        ));
        $ModalEmitter->setLoadingMessage('Ehemalige Schüler werden geladen', 'Bitte warten');

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $YearId
     *
     * @return string
     */
    public function loadStudentArchiveContent(string $YearId): string
    {
        if (($tblYear = Term::useService()->getYearById($YearId))) {
            return (new Frontend())->getStudentArchiveContent($tblYear);
        }

        return new Warning('Bitte wählen Sie ein Schuljahr aus');
    }

    /**
     * @param array $parameters
     *
     * @return Pipeline
     */
    public static function pipelineLoad(array $parameters): Pipeline
    {

        $Receiver = self::receiverBlock('', 'Content');
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'waitContent'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadContent'
        ));

        $payLoad = [self::API_TARGET => 'loadContent'];
        foreach ($parameters as $key => $value) {
            $payLoad[$key] = $value;
        }

        $Emitter->setGetPayload($payLoad);

        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function waitContent(): string
    {
        return new Info('Inhalt lädt...' . new ProgressBar(0, 100, 0, 12));
    }

    /**
     * @param null $Content
     * @param null $YearId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadContent($Content = null, $YearId = null): string
    {
        if ($Content == 'loadExportContent') {
            return Person::useFrontend()->loadExportContent($YearId);
        } else {
            return new Danger('Inhalt wurde nicht gefunden.', new Exclamation());
        }
    }
}