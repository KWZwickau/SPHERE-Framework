<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 13:47
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
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
 * Class BlockII
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class BlockII extends AbstractBlock
{

    /**
     * BlockII constructor.
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepareCertificate
     */
    public function __construct(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepareCertificate
    ) {
        $this->tblDivision = $tblDivision;
        $this->tblPerson = $tblPerson;
        $this->tblPrepareCertificate = $tblPrepareCertificate;

        $this->tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);

        $this->setCourses();
        $this->setPointList();
    }

    /**
     * @return Layout
     */
    public function getContent()
    {

        $dataList = array();
        for ($i = 1; $i < 6; $i++) {
            $dataList = $this->setRow($dataList, $i);
        }

        $tableExam = new TableData(
            $dataList,
            null,
            array(
                'Number' => '#',
                'Exam' => 'Prüfungsfach',
                'WrittenExam' => 'schriftliche Prüfung',
                'VerbalExam' => 'mündliche Prüfung',
                'ExtraVerbalExam' => 'zusätzliche mündliche Prüfung',
                'Total' => 'Gesamtergebnis in vierfacher Wertung'
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '5%', 'targets' => 0),
                    array('width' => '35%', 'targets' => 1),
                    array('width' => '15%', 'targets' => array(2, 3, 4, 5)),
                ),
                'fixedHeader' => false
            )
        );

        $tableBELL = new TableData(
            array(array(
                'Description' => new TextArea(
                    'Data[Area]',
                    'Thema'
                ),
                'Points' => new TextField(
                    'Data[Points]',
                    'vierfache Wertung'
                )
            )),
            null,
            array(
                'Description' => 'Thema',
                'Points' => 'Punktzahl in vierfacher Wertung'
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '80%', 'targets' => 0),
                    array('width' => '20%', 'targets' => 1),
                ),
                'fixedHeader' => false
            )
        );

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Ergebnisse in der Abiturprüfung',
                            $tableExam,
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Besondere Lernleistung',
                            $tableBELL,
                            Panel::PANEL_TYPE_PRIMARY
                        )
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

    /**
     * @param $dataList
     * @param $i
     * @return array
     */
    private function setRow($dataList, $i)
    {
        $number = $i . '.';
        if ($i == 1) {
            $tblSubject = $this->getFirstAdvancedCourse();
            $number .= ' (LF)';
            $exam = ($tblSubject ? $tblSubject->getName() : '&nbsp;');
        } elseif ($i == 2) {
            $tblSubject = $this->getSecondAdvancedCourse();
            $number .= ' (LF)';
            $exam = ($tblSubject ? $tblSubject->getName() : '&nbsp;');
        } else {
            // todo data
            $exam = new SelectBox('Data', '', array('Name' => $this->BasicCourses));
        }

        if ($i < 4) {
            // todo data, post
            $selectBox = new SelectCompleter('Data[Grade]', '', '', $this->pointsList);
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBox->setDisabled();
            }
            $writtenExam = $selectBox;

            $verbalExam = '&nbsp;';
        } else {
            $writtenExam = '&nbsp;';

            // todo data, post
            $selectBox = new SelectCompleter('Data[Grade]', '', '', $this->pointsList);
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBox->setDisabled();
            }
            $verbalExam = $selectBox;
        }

        // todo data, post
        $selectBox = new SelectCompleter('Data[Grade]', '', '', $this->pointsList);
        if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
            $selectBox->setDisabled();
        }
        $extraVerbalExam = $selectBox;

        // todo total
        $total = '&nbsp;';

        $dataList[] = array(
            'Number' => $number,
            'Exam' => $exam,
            'WrittenExam' => $writtenExam,
            'VerbalExam' => $verbalExam,
            'ExtraVerbalExam' => $extraVerbalExam,
            'Total' => $total
        );

        return $dataList;
    }
}