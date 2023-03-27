<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendClassRegister extends Extension implements IFrontendInterface
{
    /**
     * @param null $DivisionId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return string
     */
    public function frontendAbsenceStudent($DivisionId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = '',
        $GroupId = null, $DivisionSubjectId = null) : string
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Fehlzeiten Übersicht des Schülers');
        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                    array(
                        'DivisionSubjectId' => $DivisionSubjectId,
                        'DivisionId' => $GroupId ? null : $DivisionId,
                        'GroupId'    => $GroupId,
                        'BasicRoute' => $BasicRoute,
                    ))
            );
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
        ) {
            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Kurs',
                                        $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6)
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverModal()
                                    . (new PrimaryLink(
                                        new Plus() . ' Fehlzeit hinzufügen',
                                        ApiAbsence::getEndpoint()
                                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($PersonId, $DivisionId)),
                                    new Container('&nbsp;')
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverBlock(
                                        $this->loadAbsenceTable($tblPerson, $tblDivisionCourse),
                                        'AbsenceContent'
                                    )
                                ))
                            ))
                        )) //, new Title(new ListingTable() . ' Übersicht')),
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function loadAbsenceTable(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse): string
    {
        $hasAbsenceTypeOptions = false;
        $tableData = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear))
            && $startDate
            && $endDate
            && ($tblAbsenceList = Absence::useService()->getAbsenceAllBetweenByPerson($tblPerson, $startDate, $endDate))
        ) {
            $tblCompany = false;
            $tblSchoolType = false;
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                $hasAbsenceTypeOptions = $tblSchoolType && $tblSchoolType->isTechnical();
            }

            foreach ($tblAbsenceList as $tblAbsence) {
                $status = '';
                if ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                    $status = new Success('entschuldigt');
                } elseif ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                    $status = new \SPHERE\Common\Frontend\Text\Repository\Danger('unentschuldigt');
                }

                $isOnlineAbsence = $tblAbsence->getIsOnlineAbsence();

                $item = array(
                    'FromDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getFromDate() . '</span>' : $tblAbsence->getFromDate(),
                    'ToDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getToDate() . '</span>' : $tblAbsence->getToDate(),
                    'Days' => ($days = $tblAbsence->getDays($tblYear, null, $tblCompany ?: null, $tblSchoolType ?: null)) == 1
                        ? $days . ' ' . new Small(new Muted($tblAbsence->getWeekDay()))
                        : $days,
                    'Lessons' => $tblAbsence->getLessonStringByAbsence(),
                    'Remark' => $tblAbsence->getRemark(),
                    'Status' => $status,
                    'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein',
                    'PersonCreator' => $tblAbsence->getDisplayPersonCreator(false),
                    'PersonStaff' => $tblAbsence->getDisplayStaff(),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $tblDivisionCourse->getId()))
                        . (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenDeleteAbsenceModal($tblAbsence->getId(), $tblDivisionCourse->getId()))
                );

                if ($hasAbsenceTypeOptions) {
                    $item['Type'] = $tblAbsence->getTypeDisplayName();
                }

                $tableData[] = $item;
            }
        }

        if ($hasAbsenceTypeOptions) {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Type' => 'Typ',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        } else {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        }
        // name Downloadfile
        $FileName = 'Fehlzeiten '.$tblPerson->getLastName().' '.$tblPerson->getFirstName().' '.(new DateTime())->format('d-m-Y');

        return new TableData(
            $tableData,
            null,
            $columns,
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 1),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1)
                ),
                'responsive' => false,
//                'ExtensionColVisibility' => array('Enabled' => true),
                'ExtensionDownloadExcel' => array(
                    'Enabled' => true,
                    'FileName' => $FileName,
                    'Columns' => '0,1,2,3,4,5,6,7,8',
                )
            )
        );
    }
}