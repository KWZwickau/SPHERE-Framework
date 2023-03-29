<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineAbsence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineAbsence;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendOnlineAbsence(): Stage
    {
        $stage = new Stage('Fehlzeiten', 'Übersicht');

        $layoutGroupList = array();

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
        ) {
            // Schüler-Zugang
            $tblPersonList = OnlineAbsence::useService()->getPersonListFromStudentLogin();
            $source = TblAbsence::VALUE_SOURCE_ONLINE_STUDENT;
        } else {
            // Mitarbeiter oder Eltern-Zugang
            $tblPersonList = OnlineAbsence::useService()->getPersonListFromCustodyLogin();
            $source = TblAbsence::VALUE_SOURCE_ONLINE_CUSTODY;
        }

        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $layoutGroupList[] = $this->getPersonOnlineAbsenceLayoutGroup($tblPerson, $source);
            }
        }

        $stage->setContent(ApiOnlineAbsence::receiverModal() . new Layout($layoutGroupList));

        return $stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param int $Source
     *
     * @return LayoutGroup|null
     */
    private function getPersonOnlineAbsenceLayoutGroup(TblPerson $tblPerson, int $Source): ?LayoutGroup
    {
        return new LayoutGroup(array(
            new LayoutRow(new LayoutColumn(
                new Title(
                    $tblPerson->getLastFirstName() . ' ' .
                    new Small(new Muted(DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson)))
                )
            )),
            new LayoutRow(new LayoutColumn(
                (new PrimaryLink(
                    new Plus() . ' Fehlzeit hinzufügen',
                    ApiOnlineAbsence::getEndpoint()
                ))->ajaxPipelineOnClick(ApiOnlineAbsence::pipelineOpenCreateOnlineAbsenceModal($tblPerson->getId(), $Source))
            )),
            new LayoutRow(new LayoutColumn(
                ApiOnlineAbsence::receiverBlock($this->loadOnlineAbsenceTable($tblPerson), 'OnlineAbsenceContent_' . $tblPerson->getId())
            ))
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TableData
     */
    public function loadOnlineAbsenceTable(TblPerson $tblPerson): TableData
    {
        $hasAbsenceTypeOptions = false;
        $tableData = array();
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
        ) {
            $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
            $tblCompany = $tblStudentEducation->getServiceTblCompany();
            $hasAbsenceTypeOptions = $tblSchoolType && $tblSchoolType->isTechnical();

            list($startDateAbsence, $endDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateAbsence && $endDateAbsence
                && ($tblAbsenceList = Absence::useService()->getAbsenceAllBetweenByPerson($tblPerson, $startDateAbsence, $endDateAbsence))
            ) {
                foreach ($tblAbsenceList as $tblAbsence) {
                    $status = '';
                    if ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                        $status = new Success('entschuldigt');
                    } elseif ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                        $status = new Danger('unentschuldigt');
                    }

                    $item = array(
                        'FromDate' => $tblAbsence->getFromDate(),
                        'ToDate' => $tblAbsence->getToDate(),
                        'Days' => ($days = $tblAbsence->getDays($tblYear, null, $tblCompany ?: null, $tblSchoolType ?: null)) == 1
                            ? $days . ' ' . new Small(new Muted($tblAbsence->getWeekDay()))
                            : $days,
                        'Lessons' => $tblAbsence->getLessonStringByAbsence(),
                        'Status' => $status,
                        'PersonCreator' => $tblAbsence->getDisplayPersonCreator(),
                        'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein'
                    );

                    if ($hasAbsenceTypeOptions) {
                        $item['Type'] = $tblAbsence->getTypeDisplayName();
                    }

                    $tableData[] = $item;
                }
            }
        }

        if ($hasAbsenceTypeOptions) {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Type' => 'Typ',
                'PersonCreator' => 'Ersteller',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
            );
        } else {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'PersonCreator' => 'Ersteller',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
            );
        }

        return (new TableData(
            $tableData,
            null,
            $columns,
            array(
                'order' => array(
                    array(0, 'desc'),
                    array(1, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 1),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'searching' => false,
                'responsive' => false
            )
        ))->setHash('OnlineAbsence-' . $tblPerson->getId());
    }

    /**
     * @param null $Data
     * @param null $PersonId
     * @param null $Source
     * @param IMessageInterface|null $messageLesson
     *
     * @return Form
     */
    public function formOnlineAbsence(
        $Data = null,
        $PersonId = null,
        $Source = null,
        IMessageInterface $messageLesson = null
    ): Form {
        if ($Data === null) {
            $isFullDay = true;

            $global = $this->getGlobal();
            $global->POST['Data']['IsFullDay'] = $isFullDay;
            $global->POST['Data']['FromDate'] = (new DateTime('now'))->format('d.m.Y');

            $global->savePost();
        } else {
            $isFullDay = $Data['IsFullDay'] ?? false;
        }


        $formRows[] = new FormRow(array(
            new FormColumn(
                new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 6
            ),
            new FormColumn(
                new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 6
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(array(
                (new CheckBox('Data[IsFullDay]', 'ganztägig', 1))->ajaxPipelineOnClick(ApiAbsence::pipelineLoadLesson()),
                ApiAbsence::receiverBlock(Absence::useFrontend()->loadLesson($isFullDay, $messageLesson), 'loadLesson')
            ))
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                ApiAbsence::receiverBlock(Absence::useFrontend()->loadType($PersonId), 'loadType')
            )
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Remark]', '', 'Bemerkung'), 12
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                (new PrimaryLink('Speichern', ApiOnlineAbsence::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiOnlineAbsence::pipelineCreateOnlineAbsenceSave($PersonId, $Source))
            )
        ));

        return (new Form(new FormGroup(
            $formRows
        )))->disableSubmitAction();
    }
}