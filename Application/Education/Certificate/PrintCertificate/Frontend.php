<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:33
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\Api\Education\Certificate\PrintCertificate\ApiPrintCertificate;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\PrintCertificate
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendPrintCertificate(): Stage
    {
        $Stage = new Stage('Zeugnis', 'Übersicht (nicht gedruckte Zeugnisse)');
        $Stage->addButton(new Standard('Historie Personen', '/Education/Certificate/PrintCertificate/History', null, array(),
            'Bereits gedruckte Zeugnisse pro Schüler ansehen und drucken'));
        $Stage->addButton(new Standard('Historie Klassen', '/Education/Certificate/PrintCertificate/History/Division', null, array(),
            'Bereits gedruckte Zeugnisse pro Klasse ansehen und drucken'));

        // freigegebene und nicht gedruckte Zeugnisse
        $tableContent = array();
        $tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllWhere(true, false);
        $prepareList = array();
        if ($tblPrepareStudentList) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if ($tblPrepareStudent->getServiceTblPerson()
                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && $tblPrepareStudent->getServiceTblCertificate()
                ) {
                    if (!isset($prepareList[$tblPrepare->getId()])) {
                        $prepareList[$tblPrepare->getId()] = $tblPrepare;
                    }
                }
            }
        }
        $leaveDivisionList = array();
        if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllBy(true, false))) {
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
                    && !isset($leaveDivisionList[$tblDivisionCourse->getId()])
                ) {
                    if (($tblLeaveInformationCertificateDate = Prepare::useService()->getLeaveInformationBy(
                        $tblLeaveStudent, 'CertificateDate'))
                    ) {
                        $date = $tblLeaveInformationCertificateDate->getValue();
                    } else {
                        $date = '';
                    }

                    $leaveDivisionList[$tblDivisionCourse->getId()] = $date;
                }
            }
        }

        // alle automatisch freigegebenen Zeugnisse
        if ($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll()) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblCertificateType->isAutomaticallyApproved()
                ) {
                    if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                        foreach ($tblPrepareList as $tblPrepareCertificate) {
                            if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepareCertificate))) {
                                foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                    if ($tblPrepareStudent->getServiceTblPerson()
                                        && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                                        && $tblPrepareStudent->getServiceTblCertificate()
                                        && !$tblPrepareStudent->isPrinted()
                                    ) {
                                        if (!isset($prepareList[$tblPrepare->getId()])) {
                                            $prepareList[$tblPrepare->getId()] = $tblPrepare;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
            && $tblCertificateTypeLeave->isAutomaticallyApproved()
        ) {
            if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllBy(false, false))) {
                foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                    if (($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
                        && !isset($leaveDivisionList[$tblDivisionCourse->getId()])
                    ) {
                        if (($tblLeaveInformationCertificateDate = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                            $date = $tblLeaveInformationCertificateDate->getValue();
                        } else {
                            $date = '';
                        }

                        $leaveDivisionList[$tblDivisionCourse->getId()] = $date;
                    }
                }
            }
        }

        /** @var TblPrepareCertificate $tblPrepare */
        foreach ($prepareList as $tblPrepare) {
            if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
                $tableContent[] = array(
                    'Year' => $tblDivisionCourse->getServiceTblYear() ? $tblDivisionCourse->getServiceTblYear()->getDisplayName() : '',
                    'Date' => $tblPrepare->getDate(),
                    'Division' => $tblDivisionCourse->getDisplayName(),
                    'CertificateType' => ($tblCertificateType = $tblPrepare->getCertificateType()) ? $tblCertificateType->getName() : '',
                    'Name' => $tblPrepare->getName(),
                    'PrepareStatus' => $this->checkIsPreparedStatus($tblPrepare),
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'PrepareId' => $tblPrepare->getId(),
                        ))
                );
            }
        }
        foreach ($leaveDivisionList as $divisionCourseId => $date) {
            if (($tblDivisionCourseItem = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                $tableContent[] = array(
                    'Year' => $tblDivisionCourseItem->getServiceTblYear() ? $tblDivisionCourseItem->getServiceTblYear()->getDisplayName() : '',
                    'Date' => $date,
                    'Division' => $tblDivisionCourseItem->getDisplayName(),
                    'CertificateType' => 'Abgangszeugnis',
                    'Name' => '',
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'DivisionId' => $tblDivisionCourseItem->getId(),
                            'IsLeave' => true,
                        ))
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($tableContent)
                                ? new Warning('Keine Zeugnisse zum Druck verfügbar', new Exclamation())
                                : new TableData(
                                $tableContent,
                                null,
                                array(
                                    'Year' => 'Schuljahr',
                                    'Date' => 'Zeugnisdatum',
                                    'Division' => 'Klasse',
                                    'Name' => 'Name',
                                    'CertificateType' => 'Zeugnistyp',
                                    'PrepareStatus' => 'Zeugnis&shy;vorbereitung',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc'),
                                        array(1, 'desc'),
                                        array(2, 'asc'),
                                        array(3, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 2),
                                        array('searchable' => false, 'targets' => -1),
                                    )
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendPrintCertificateDivisionTeacher(): Stage
    {
        $Stage = new Stage('Zeugnis', 'Übersicht (nicht gedruckte Zeugnisse)');

        $tableContent = array();
        $prepareList = array();
        $divisionList = array();
        if (($tblPersonAccount = Account::useService()->getPersonByLogin())
            && ($tblYearList = Term::useService()->getYearByNow())
        ) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonAccount, $tblYear))) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $divisionList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                    }
                }
            }
        }

        if ($divisionList) {
            // freigegebene und nicht gedruckte Zeugnisse
            $tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllWhere(true, false);
            if ($tblPrepareStudentList) {
                foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                    if ($tblPrepareStudent->getServiceTblPerson()
                        && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                        && $tblPrepareStudent->getServiceTblCertificate()
                        && ($tblDivisionCourseItem = $tblPrepare->getServiceTblDivision())
                    ) {
                        if (isset($divisionList[$tblDivisionCourseItem->getId()]) && !isset($prepareList[$tblPrepare->getId()])) {
                            $prepareList[$tblPrepare->getId()] = $tblPrepare;
                        }
                    }
                }
            }

            // alle automatisch freigegebenen Zeugnisse
            if ($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll()) {
                foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                    if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        && $tblCertificateType->isAutomaticallyApproved()
                    ) {
                        if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                            foreach ($tblPrepareList as $tblPrepareCertificate) {
                                if (($tblDivisionCourseItem = $tblPrepareCertificate->getServiceTblDivision())
                                    && isset($divisionList[$tblDivisionCourseItem->getId()])
                                ) {
                                    if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepareCertificate))) {
                                        foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                            if ($tblPrepareStudent->getServiceTblPerson()
                                                && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                                                && $tblPrepareStudent->getServiceTblCertificate()
                                                && !$tblPrepareStudent->isPrinted()
                                            ) {
                                                if (!isset($prepareList[$tblPrepare->getId()])) {
                                                    $prepareList[$tblPrepare->getId()] = $tblPrepare;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /** @var TblPrepareCertificate $tblPrepare */
        foreach ($prepareList as $tblPrepare) {
            if (($tblDivisionCourseTemp = $tblPrepare->getServiceTblDivision())) {
                $tableContent[] = array(
                    'Year' => $tblDivisionCourseTemp->getServiceTblYear() ? $tblDivisionCourseTemp->getServiceTblYear()->getDisplayName() : '',
                    'Date' => $tblPrepare->getDate(),
                    'Division' => $tblDivisionCourseTemp->getDisplayName(),
                    'CertificateType' => ($tblCertificateType = $tblPrepare->getCertificateType()) ? $tblCertificateType->getName() : '',
                    'Name' => $tblPrepare->getName(),
                    'PrepareStatus' => $this->checkIsPreparedStatus($tblPrepare),
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => 'DivisionTeacher'
                        ))
                );
            }
        }

        $leaveDivisionList = array();
        $isLeaveAutoApproved = ($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
            && $tblCertificateTypeLeave->isAutomaticallyApproved();
        if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllBy(null, false))) {
            /** @var TblLeaveStudent $tblLeaveStudent */
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
                    && isset($divisionList[$tblDivisionCourse->getId()])
                    && !isset($leaveDivisionList[$tblDivisionCourse->getId()])
                    && ($tblLeaveStudent->isApproved() || $isLeaveAutoApproved)
                ) {
                    if (($tblLeaveInformationCertificateDate = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                        $date = $tblLeaveInformationCertificateDate->getValue();
                    } else {
                        $date = '';
                    }

                    $leaveDivisionList[$tblDivisionCourse->getId()] = $date;
                }
            }
        }

        foreach ($leaveDivisionList as $divisionCourseId => $date) {
            if (($tblDivisionCourseItem = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                $tableContent[] = array(
                    'Year' => $tblDivisionCourseItem->getServiceTblYear() ? $tblDivisionCourseItem->getServiceTblYear()->getDisplayName() : '',
                    'Date' => $date,
                    'Division' => $tblDivisionCourseItem->getDisplayName(),
                    'CertificateType' => 'Abgangszeugnis',
                    'Name' => '',
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'DivisionId' => $tblDivisionCourseItem->getId(),
                            'Route' => 'DivisionTeacher',
                            'IsLeave' => true,
                        ))
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($tableContent)
                                ? new Warning('Keine Zeugnisse zum Druck verfügbar', new Exclamation())
                                : new TableData(
                                $tableContent,
                                null,
                                array(
                                    'Year' => 'Schuljahr',
                                    'Date' => 'Zeugnisdatum',
                                    'Division' => 'Klasse/Gruppe',
                                    'Name' => 'Name',
                                    'CertificateType' => 'Zeugnistyp',
                                    'PrepareStatus' => 'Zeugnis&shy;vorbereitung',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc'),
                                        array(1, 'desc'),
                                        array(2, 'asc'),
                                        array(3, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 2),
                                        array('searchable' => false, 'targets' => -1),
                                    )
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param bool $IsLeave
     * @param string $Route
     *
     * @return Stage
     */
    public function frontendConfirmPrintCertificate(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = false,
        string $Route = 'All'
    ): Stage {
        $Stage = new Stage('Zeugnis', 'Herunterladen und revisionssicher abspeichern');
        if ($Route == 'All') {
            $backRoute = '/Education/Certificate/PrintCertificate';
        } else {
            $backRoute = '/Education/Certificate/DivisionTeacherPrintCertificate';
        }
        $message = new Warning(
            'Bitte drucken Sie die finalen Zeugnisse nicht direkt aus dem Browser heraus, 
            sondern speichern Sie die PDF-Datei auf Ihrem PC und öffnen diese anschließend im 
            Adobe Acrobat Reader oder einem vergleichbaren PDF-Reader. <br/> Beim Druck der Zeugnisse 
            über den Adobe Acrobat Reader verwenden Sie in den Druckeinstellungen, die Option 
            „Seite anpassen und Optionen“ und den Punkt „Tatsächliche Größe“. Bei anderen PDF-Reader 
            schauen Sie bitte ebenfalls nach einer vergleichbaren Option.',
            new Exclamation()
        );
        $Stage->addButton(new Standard(
            'Zurück', $backRoute, new ChevronLeft()
        ));

        if ($IsLeave) {
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))) {
                if (($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
                    && $tblCertificateTypeLeave->isAutomaticallyApproved()
                ) {
                    $isAutomaticallyApproved = true;
                } else {
                    $isAutomaticallyApproved = false;
                }

                $data = array();
                if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                    && ($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))
                ) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                            && ($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                            && $tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId()
                            && !$tblLeaveStudent->isPrinted()
                            && ($isAutomaticallyApproved || $tblLeaveStudent->isApproved())
                        ) {
                            $data[] = $tblPerson->getLastFirstName();
                        }
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            $tblDivisionCourse->getTypeName(),
                            $tblDivisionCourse->getDisplayName(),
                            Panel::PANEL_TYPE_INFO
                        ),
                        $message,
                        new Panel(
                            new Question() . ' Diese Zeugnisse wirklich drucken und revisionssicher abspeichern?',
                            $data,
                            Panel::PANEL_TYPE_DANGER,
                            (new External(
                                'Ja',
                                '/Api/Education/Certificate/Generator/DownLoadMultiLeavePdf',
                                new Ok(),
                                array(
                                    'DivisionId' => $tblDivisionCourse->getId(),
                                ),
                                'Zeugnisse herunterladen und revisionssicher abspeichern'
                            ))->setRedirect('/Education/Certificate/PrintCertificate', 60)
                            . new Standard(
                                'Nein', '/Education/Certificate/PrintCertificate', new Disable()
                            )
                        ),
                    )))))
                );

            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban() . ' Das Zeugnis konnte nicht gefunden werden'),
                            new Redirect('/Education/Certificate/PrintCertificate', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            }

            return $Stage;

        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            ) {
                if (($tblCertificateType = $tblPrepare->getCertificateType())
                    && $tblCertificateType->isAutomaticallyApproved()
                ) {
                    $isAutomaticallyApproved = true;
                } else {
                    $isAutomaticallyApproved = false;
                }

                $data = array();
                if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                    foreach ($tblPersonList as $tblPerson) {
                        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && $tblPrepareStudent->getServiceTblCertificate()
                            && !$tblPrepareStudent->isPrinted()
                        ) {
                            if ($tblPrepareStudent->isApproved() || $isAutomaticallyApproved) {
                                $data[] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            $tblDivisionCourse->getTypeName(),
                            $tblDivisionCourse->getDisplayName(),
                            Panel::PANEL_TYPE_INFO
                        ),
                        $message,
                        new Panel(
                            new Question() . ' Dieses Zeugnis wirklich drucken und revisionssicher abspeichern?',
                            $data,
                            Panel::PANEL_TYPE_DANGER,
                            (new External(
                                'Ja',
                                '/Api/Education/Certificate/Generator/DownLoadMultiPdf',
                                new Ok(),
                                array('PrepareId'  => $tblPrepare->getId()),
                                'Zeugnisse herunterladen und revisionssicher abspeichern'
                            ))->setRedirect($backRoute, 60)
                            . new Standard(
                                'Nein', $backRoute, new Disable()
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban() . ' Das Zeugnis konnte nicht gefunden werden'),
                            new Redirect($backRoute, Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            }

            return $Stage;
        }
    }

    /**
     * @param null $Search
     *
     * @return Stage
     */
    public function frontendPrintCertificateHistory($Search = null): Stage
    {
        if ($Search) {
            $global = $this->getGlobal();
            $global->POST['Data']['Search'] = $Search;
            $global->savePost();
        }

        $Stage = new Stage('Zeugnis', 'Person auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
        ));

        $panel = new Panel(
            new Search() . ' Personen-Suche',
            (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Search]', '', ''))
                        ->ajaxPipelineOnKeyUp(ApiPrintCertificate::pipelineSearchPerson())
                ),
            )))))->disableSubmitAction(),
            Panel::PANEL_TYPE_INFO
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $panel,
                            ApiPersonSearch::receiverBlock($Search ? $this->loadPersonSearch($Search) : '', 'SearchContent')
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistoryPerson($PersonId = null, $Search = null)
    {
        $Stage = new Stage('Zeugnis', 'Auswahl');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History', new ChevronLeft(), array(
            'Search' => $Search
        )));

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson);
            $dataList = array();
            if ($tblFileList) {
                foreach ($tblFileList as $tblFile) {
                    $name = explode(' - ', $tblFile->getName());

                    if ($tblFile->getEntityUpdate()){
                        $date = $tblFile->getEntityUpdate()->format('d.m.Y');
                        $isChanged = true;
                    } else {
                        $date = $tblFile->getEntityCreate()->format('d.m.Y');
                        $isChanged = false;
                    }

                    $optionRevision = Storage::useService()->getBinaryRevisionListByFile($tblFile)
                        ? new Standard(
                            'Revisionen',
                            '/Education/Certificate/PrintCertificate/History/Person/Revisions',
                            new EyeOpen(),
                            array(
                                'PersonId' => $tblPerson->getId(),
                                'FileId' => $tblFile->getId(),
                                'Search' => $Search
                            ),
                            'Zeugnis herunterladen')
                        : '';

                    if (count($name) >= 3) {
                        $dataList[] = array(
                            'Year' => $name[0],
                            'Date' => $date,
                            'Certificate' => $name[2],
                            'Changed' => $isChanged ? 'geändert': '',
                            'Option' => new External(
                                'Herunterladen',
                                '/Api/Education/Certificate/Generator/Download',
                                new Download(),
                                array(
                                    'FileId' => $tblFile->getId(),
                                ),
                                'Zeugnis herunterladen')
                                . $optionRevision
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Person',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Year' => 'Jahr',
                                    'Date' => 'Gedruckt am',
                                    'Certificate' => 'Zeugnis',
                                    'Changed' => 'geändert',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1)
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
                . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/PrintCertificate/History', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $PersonId
     * @param $FileId
     * @param $Search
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistoryPersonRevisions($PersonId = null, $FileId = null, $Search = null)
    {
        $Stage = new Stage('Zeugnis', 'Revisionen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History/Person', new ChevronLeft(), array(
            'PersonId' => $PersonId,
            'Search' => $Search
        )));

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblFile = Storage::useService()->getFileById($FileId))
            && ($tblBinaryRevisionList = Storage::useService()->getBinaryRevisionListByFile($tblFile))
        ) {
            $dataList = array();
            foreach ($tblBinaryRevisionList as $tblBinaryRevision) {
                if (($tblBinary = $tblBinaryRevision->getTblBinary())) {
                    $dataList[] = array(
                        'Version' => $tblBinaryRevision->getVersion(),
                        'Description' => $tblBinaryRevision->getDescription(),
                        'PersonPrinter' => ($tblPersonPrinter = $tblBinary->getServiceTblPersonPrinter()) ? $tblPersonPrinter->getLastFirstName() : '',
                        'Option' => new External(
                            'Revision herunterladen',
                            '/Api/Education/Certificate/Generator/Download',
                            new Download(),
                            array(
                                'BinaryRevisionId' => $tblBinaryRevision->getId(),
                            ),
                            'Zeugnis-Revision herunterladen')
                    );
                }
            }

            $certificate = '';
            $name = explode(' - ', $tblFile->getName());
            if (count($name) >= 3) {
                $certificate = $name[2];
            }
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Person',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnis',
                                    $certificate,
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Version' => 'Version',
                                    'Description' => 'Beschreibung',
                                    'PersonPrinter' => 'Gedruckt von',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                        ),
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
                . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/PrintCertificate/History', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @return Stage
     */
    public function frontendPrintCertificateHistoryDivision(): Stage
    {
        $Stage = new Stage('Zeugnis', 'Klasse auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
        ));

        $divisionTable = array();
        if (($tblPrepareList = Prepare::useService()->getPrepareAll())) {
            foreach ($tblPrepareList as $tblPrepare) {
                if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                    && !isset($divisionTable[$tblDivisionCourse->getId()])
                ) {
                    $divisionTable[$tblDivisionCourse->getId()] = array(
                        'Year' => $tblDivisionCourse->getServiceTblYear() ? $tblDivisionCourse->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $tblDivisionCourse->getTypeName(),
                        'Division' => $tblDivisionCourse->getDisplayName(),
                        'Option' => new Standard(
                            '', '/Education/Certificate/PrintCertificate/History/Division/Selected', new Select(),
                            array(
                                'DivisionId' => $tblDivisionCourse->getId()
                            ),
                            'Auswählen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistorySelectedDivision($DivisionId = null)
    {
        $Stage = new Stage('Zeugnis', 'Auswahl');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History/Division', new ChevronLeft()));

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))) {
            $dataList = array();
            if ($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse)){
                foreach ($tblPrepareList as $tblPrepare){
                    if (Prepare::useService()->isPreparePrinted($tblPrepare)) {
                        $dataList[] = array(
                            'Year' => $tblDivisionCourse->getServiceTblYear() ? $tblDivisionCourse->getServiceTblYear()->getDisplayName() : '',
                            'Date' => $tblPrepare->getDate(),
                            'Name' => $tblPrepare->getName(),
                            'Option' => new External(
                                'Herunterladen',
                                '/Api/Education/Certificate/Generator/History/DownloadZip',
                                new Download(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                ),
                                'Zeugnis herunterladen'
                            )
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    $tblDivisionCourse->getTypeName(),
                                    $tblDivisionCourse->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Year' => 'Jahr',
                                    'Date' => 'Zeugnisdatum',
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1),
                                            array('type' => 'natural', 'targets' => 1),
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
                . new Danger('Klasse nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/PrintCertificate/History/Division', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return string
     */
    private function checkIsPreparedStatus(TblPrepareCertificate $tblPrepare) : string
    {
        if ($tblPrepare->getIsPrepared()) {
            return new Success('abgeschlossen');
        }

        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    && $tblPrepareStudent->getServiceTblCertificate()
                    && !$tblPrepareStudent->isPrinted()
                    && ($tblCertificateType = $tblPrepareStudent->getServiceTblCertificate()->getTblCertificateType())
                    && ($tblPrepareStudent->isApproved() || $tblCertificateType->isAutomaticallyApproved())
                ) {
                    if (Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepare, $tblPerson)
                        || Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare, $tblPerson)
                    ) {
                        return new \SPHERE\Common\Frontend\Text\Repository\Warning('in Bearbeitung');
                    }
                }
            }
        }

        return new \SPHERE\Common\Frontend\Text\Repository\Danger('offen');
    }

    /**
     * @param $Search
     *
     * @return string
     */
    public function loadPersonSearch($Search): string
    {
        if ($Search != '' && strlen($Search) > 2) {
            $Search = str_replace(',', '', $Search);
            $Search = str_replace('.', '', $Search);
            $resultList = array();
            $result = '';
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblDirectoryList = Storage::useService()->getDirectoryAllByPerson($tblPerson))) {
                        $count = 0;
                        foreach ($tblDirectoryList as $tblDirectory) {
                            if (($tblFileList = Storage::useService()->getFileAllByDirectory($tblDirectory))) {
                                $count += count($tblFileList);
                            }
                        }

                        $resultList[] = array(
                            'FullName' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt'),
                            'Count' => $count,
                            'Option' => new Standard(
                                '',
                                '/Education/Certificate/PrintCertificate/History/Person',
                                new Select(),
                                array(
                                    'PersonId' => $tblPerson->getId(),
                                    'Search' => $Search
                                ),
                                'Person auswählen'
                            )
                        );
                    }
                }

                $columnList = array(
                    'FullName'   => 'Name',
                    'Address'    => 'Adresse',
                    'Count'      => 'Zeugnisse',
                    'Option'     => '',
                );

                // https://datatables.net/manual/tech-notes/3
                // 'destroy' => true

                $result = new TableData(
                    $resultList,
                    null,
                    $columnList,
                    array(
                        'columnDefs' => array(
                            array('type' => \SPHERE\Application\Setting\Consumer\Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '30px', 'targets' => -1),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false,
                        'destroy' => true
                    )
                );
            }

            if (empty($resultList)) {
                $result = new WarningMessage('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $result = new WarningMessage('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return new Title('Verfügbare Personen ' . new Small(new Muted('der Personen-Suche: ')) . new Bold($Search))
            . $result;
    }
}