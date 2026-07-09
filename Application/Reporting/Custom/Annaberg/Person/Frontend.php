<?php
namespace SPHERE\Application\Reporting\Custom\Annaberg\Person;

use SPHERE\Application\Api\Reporting\Standard\ApiStandard;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Annaberg\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendPrintClassList(?int $DivisionCourseId = null, $All = null)
    {
        $Stage = new Stage('EGE Auswertung', 'Klassenliste zum Ausdrucken');
        $Route = '/Reporting/Custom/Annaberg/Person/PrintClassList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())) {
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createPrintClassList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Annaberg/Common/PrintClassList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number'         => '#',
                            'LastName'       => 'Name',
                            'FirstName'      => 'Vorname',
                            'Address'        => 'Adresse',
                            'Birthday'       => 'Geburtsdatum',
                            'PhoneStudent' => 'Tel. Schüler '.
                                new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                            'PhoneGuardian1' => 'Tel. Sorgeber. 1 '.
                                new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                            'PhoneGuardian2' => 'Tel. Sorgeber. 2 '.
                                new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                        ),
                        array(
                            "pageLength" => -1,
                            "responsive" => false,
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @param null $YearId
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendExport($YearId = null): Stage
    {
        $Stage = new Stage('EGE Auswertung', 'Export für SchulAPP');

        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } else {
            $tblYearList = Term::useService()->getYearByNow();
            $tblYear = current($tblYearList);
        }

        $Stage->setContent(
            ApiStandard::receiverBlock(ApiStandard::pipelineLoad(['Content' => 'loadExportContent', 'YearId' => $tblYear]), 'Content')
        );

        return $Stage;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function loadExportContent($YearId): string
    {
        $yearFilterList = [];
        $buttonList = Digital::useService()->setYearGroupButtonList(
            '/Reporting/Custom/Annaberg/Person/Export', false, $YearId, false, false, $yearFilterList, false, true);

        $content = '';
        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $dataList = Person::useService()->createExportList($tblYear);

            $download = [];
            if (!empty($dataList)) {
                $download[] = new Primary('Herunterladen', '/Api/Reporting/Custom/Annaberg/Common/Export/Download', new Download(), ['YearId' => $tblYear]);
                $download[] = new Danger('Die dauerhafte Speicherung des CSV-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation());
            }

            $content .=
                new Layout(array(
                    new LayoutGroup(new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        empty($download)
                            ? null
                            : new LayoutColumn($download),
                        new LayoutColumn(
                            new TableData($dataList, null,
                                Person::useService()->getExportHeaderList(),
                                array(
                                    "pageLength" => -1,
                                    "responsive" => false,
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 7),
                                        // beides aktiv geht gerade aktuell nicht
                                        // array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(1, 2, 3)),
                                    ),
                                    'order' => array(
                                        array(7, 'asc'),
                                        array(2, 'asc'),
                                    ),
                                )
                            )
                        )
                    ))),
                ));

            return $content;
        }

        return new Danger("Schuljahr nicht gefunden.", new Exclamation());
    }
}