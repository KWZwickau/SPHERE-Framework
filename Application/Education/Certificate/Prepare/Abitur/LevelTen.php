<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 16.03.2018
 * Time: 11:52
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;

/**
 * Class LevelTen
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class LevelTen extends AbstractBlock
                                                              {

    // todo Sind es alle Fächer, Profile?
    /**
     * @var array
     */
    private $subjectList = array(
        'DE' => 'Deutsch',
        'EN' => 'Englisch',
        '2FS' => '2. Fremdsprache',
        'KU' => 'Kunst',
        'MU' => 'Musik',
        'GE' => 'Geschichte',
        'GRW' => 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft',
        'GEO' => 'Geographie',

        'MA' => 'Mathematik',
        'BIO' => 'Biologie',
        'CH' => 'Chemie',
        'PH' => 'Physik',
        'SPO' => 'Sport',
        'RELIGION' => 'Religion',
//        'TC' => 'Technik/Computer',  -	Das Fach Technik Computer wird ab Klasse 7 nicht mehr unterrichtet und somit nicht im Zeugnis angezeigt.
        'INF' => 'Informatik'
    );

    private $gradeTextList = array(
        '1' => 'sehr gut',
        '2' => 'gut',
        '3' => 'befriedigend',
        '4' => 'ausreichend',
        '5' => 'mangelhaft',
        '6' => 'ungenügend',
    );

    /**
     * @var array
     */
    protected $gradeList = array();

    /**
     * @var array|TblSubject[]
     */
    private $availableSubjectList = array();

    public function __construct(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepareCertificate
    ) {
        $this->tblDivision = $tblDivision;
        $this->tblPerson = $tblPerson;
        $this->tblPrepareCertificate = $tblPrepareCertificate;

        $this->tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);

        // todo automatische Ermittelung der abgewählten Pflichtfächer aus Klasse 10
        // todo Sortierung?
        $this->setAvailableSubjets();
        $this->setGradeList();

        // Zensuren der Klasse 10 ermitteln
        $tblPrepareStudentLevelTen = false;
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($this->tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblLevel->getName() == '10')
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'YEAR')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $this->tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $tblPrepareStudentLevelTen = $tblPrepareStudent;
                            break;
                        }
                    }

                    if ($tblPrepareStudentLevelTen) {
                        break;
                    }
                }
            }
        }

        if ($tblPrepareStudentLevelTen
            && $tblPrepareStudentLevelTen->getTblPrepareCertificate()
            && $tblPrepareStudentLevelTen->getServiceTblPerson()
            && $tblPrepareStudentLevelTen->getTblPrepareCertificate()->getServiceTblDivision()
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-10'))
        ) {
            $count = 1;
            foreach ($this->availableSubjectList as $tblSubject) {
                if (($tblPrepareGradeLevelTen = Prepare::useService()->getPrepareGradeBySubject(
                    $tblPrepareStudentLevelTen->getTblPrepareCertificate(),
                    $tblPrepareStudentLevelTen->getServiceTblPerson(),
                    $tblPrepareStudentLevelTen->getTblPrepareCertificate()->getServiceTblDivision(),
                    $tblSubject,
                    $tblTestType))
                ) {
                    if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        // #SSW-132 Es sollen nicht immer alle Fächer ausgewiesen werden.
//                        if ($tblPrepareAdditionalGrade->getGrade() !== $tblPrepareGradeLevelTen->getGrade()) {
//                            Prepare::useService()->updatePrepareAdditionalGrade(
//                                $tblPrepareAdditionalGrade,
//                                $tblPrepareGradeLevelTen->getGrade(),
//                                $tblPrepareAdditionalGrade->isSelected()
//                            );
//                        }
                    } else {
                        Prepare::useService()->createPrepareAdditionalGrade(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            $count++,
                            $tblPrepareGradeLevelTen->getGrade(),
                            false,
                            true
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $Data
     * @param $GroupId
     *
     * @return Layout
     * @throws \Exception
     */
    public function getContent($Data, $GroupId)
    {

        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-10'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
            $this->tblPrepareCertificate,
            $this->tblPerson,
            $tblPrepareAdditionalGradeType
        ))) {
            $global = $this->getGlobal();
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                    $global->POST['Data']['Grades'][$tblSubject->getId()] = $tblPrepareAdditionalGrade->getGrade();
                }
            }
            $global->savePost();
        }
        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
            $this->tblPrepareCertificate,
            $this->tblPerson,
            'LevelTenGradesAreNotShown'
            ))
            && $tblPrepareInformation->getValue()
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['LevelTenGradesAreNotShown'] = 1;
            $global->savePost();
        }


        $dataList = $this->setData();

        $table = new TableData(
            $dataList,
            null,
            array(
                'Subject' => 'Fach',
                'Grade' => 'Note',
                'VerbalGrade' => 'Notenstufe',
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
//                'columnDefs' => array(
//                    array('width' => '5%', 'targets' => 0),
//                    array('width' => '35%', 'targets' => 1),
//                    array('width' => '15%', 'targets' => array(2, 3, 4, 5)),
//                ),
//                'fixedHeader' => false
            )
        );

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Ergebnisse der Pflichtfächer, die in Klassenstufe 10 abgeschlossen wurden',
                            array(
                                new CheckBox('Data[LevelTenGradesAreNotShown]','Die Ausweisung der Noten und Notenstufen wurde vom Schüler abgelehnt 
                                    (§ 65 Absatz 3 der Schulordnung Gymnasien Abiturprüfung).',1),
                                $table
                            ),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->tblPrepareStudent && !$this->tblPrepareStudent->isApproved()) {
            $content = new Well(Prepare::useService()->updateAbiturLevelTenGrades($form->appendFormButton(new Primary('Speichern', new Save())),
                $this->tblPrepareCertificate, $this->tblPerson, $Data, $GroupId));
        } else {
            $content = $form;
        }

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Panel(
                            'Schüler',
                            $this->tblPerson ? $this->tblPerson->getLastFirstName() : '',
                            Panel::PANEL_TYPE_INFO
                        ),
                    ))
                )),
                new LayoutRow(array(
                    new LayoutColumn(array(
                        $content
                    ))
                ))
            ))
        ));
    }

    private function setAvailableSubjets()
    {
        $this->setCourses();
        $list = array();
        foreach ($this->subjectList as $acronym => $name) {
            $tblSubject = Subject::useService()->getSubjectByAcronym($acronym);
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByName($name);
            }

            if ($tblSubject) {
                if (!(isset($this->AdvancedCourses[$tblSubject->getId()])
                    || isset($this->BasicCourses[$tblSubject->getId()]))
                ) {
                    $list[$tblSubject->getAcronym()] = $tblSubject;
                }
            }
        }

        ksort($list);
        $this->availableSubjectList = $list;
    }

    private function setData()
    {
        $dataList = array();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-10'))) {
            foreach ($this->availableSubjectList as $tblSubject) {
                $isLocked = false;
                $gradeText = '';
                $selectBox = new SelectCompleter('Data[Grades][' . $tblSubject->getId() . ']', '', '',
                    $this->gradeList);
                if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                    $this->tblPrepareCertificate,
                    $this->tblPerson,
                    $tblSubject,
                    $tblPrepareAdditionalGradeType
                ))) {
                    $isLocked = $tblPrepareAdditionalGrade->isLocked();
                    if (isset($this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()])) {
                        $gradeText = $this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()];
                    }
                }
                if (($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) || $isLocked) {
                    $selectBox->setDisabled();
                }

                $dataList[] = array(
                    'Subject' => $tblSubject->getName(),
                    'Grade' => $selectBox,
                    'VerbalGrade' => $gradeText,
                );
            }
        }

        return $dataList;
    }

    protected function setGradeList()
    {

        $list[-1] = '';
        for ($i = 1; $i < 7; $i++) {
            $list[$i] = (string)$i;
        }

        $this->gradeList = $list;
    }
}