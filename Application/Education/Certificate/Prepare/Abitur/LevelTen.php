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
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
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
        'TC' => 'Technik/Computer',
        'INF' => 'Informatik'
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
    }

    /**
     * @return Layout
     */
    public function getContent()
    {

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
                            $table,
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->tblPrepareStudent && !$this->tblPrepareStudent->isApproved()) {
            $content = new Well($form->appendFormButton(new Primary('Speichern', new Save())));
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
        // todo automatische Ermittelung der abgewählten Pflichtfächer aus Klasse 10
        // todo Sortierung?
        $this->setAvailableSubjets();
        $this->setGradeList();

        $dataList = array();


        foreach ($this->availableSubjectList as $tblSubject) {
            // todo data, post
            $selectBox = new SelectCompleter('Data[Grade]', '', '', $this->gradeList);
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBox->setDisabled();
            }

            $dataList[] = array(
                'Subject' => $tblSubject->getName(),
                'Grade' => $selectBox,
                // todo
                'VerbalGrade' => '',
            );
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