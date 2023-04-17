<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 09:25
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class Frontend extends Extension
{
    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaAbiturPreview($PrepareId = null)
    {
        $stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                'DivisionId' => $tblDivisionCourse->getId(),
                'Route' => 'Diploma'
            )));
        } else {
            return $stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban())
                . new Redirect('/Education/Certificate/Prepare', Redirect::TIMEOUT_ERROR);
        }

        $headerColumns = array();
        $headerColumns[] = new LayoutColumn(
            new Panel(
                $tblDivisionCourse->getDisplayName(),
                $tblDivisionCourse->getDisplayName(),
                Panel::PANEL_TYPE_INFO
            )
            , 3);

        if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
            $tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType();
            $headerColumns[] = new LayoutColumn(
                new Panel(
                    'Zeugnisdatum',
                    $tblGenerateCertificate->getDate(),
                    Panel::PANEL_TYPE_INFO
                )
                , 3);
            $headerColumns[] = new LayoutColumn(
                new Panel(
                    'Typ',
                    $tblCertificateType ? $tblCertificateType->getName() : '',
                    Panel::PANEL_TYPE_INFO
                )
                , 3);
            $headerColumns[] = new LayoutColumn(
                new Panel(
                    'Name',
                    $tblGenerateCertificate->getName(),
                    Panel::PANEL_TYPE_INFO
                )
                , 3);
        }

        $studentTable = array();
        $count = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $tblCertificate = false;
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                }

                list($countCourses, $resultBlockI) = Prepare::useService()->getResultForAbiturBlockI($tblPrepare, $tblPerson);
                if ($countCourses == 40) {
                    $countCourses = new Success(new Check() . ' ' . $countCourses . ' von 40');
                } else {
                    $countCourses = new Warning(new Disable() . ' ' . $countCourses . ' von 40');
                }
                if ($resultBlockI >= 200) {
                    $resultBlockI = new Success(new Check() . ' ' . $resultBlockI . ' von mindestens 200');
                } else {
                    $resultBlockI = new Warning(new Disable() . ' ' . $resultBlockI . ' von mindestens 200');
                }

                $resultBlockII = Prepare::useService()->getResultForAbiturBlockII($tblPrepare, $tblPerson);
                if ($resultBlockII >= 100) {
                    $resultBlockII = new Success(new Check() . ' ' . $resultBlockII . ' von mindestens 100');
                } else {
                    $resultBlockII = new Warning(new Disable() . ' ' . $resultBlockII . ' von mindestens 100');
                }

                $studentTable[] = array(
                    'Number' => $count++,
                    'Name' => $tblPerson->getLastFirstName(),
                    'SelectedCourses' => $countCourses,
                    'ResultBlockI' => $resultBlockI,
                    'ResultBlockII' => $resultBlockII,
                    'Option' => ($tblCertificate
                        ?
                        (new Standard(
                            'Block I', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                            null,
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                            ),
                            'Block I bearbeiten und anzeigen'))
                        . (new Standard(
                            'Block II', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockII',
                            null,
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                            ),
                            'Block II bearbeiten und anzeigen'))
                        . (new Standard(
                            'Klassenstufe 10', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/LevelTen',
                            null,
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                            ),
                            'Klassenstufe 10 bearbeiten und anzeigen'))
                        . (new Standard(
                            'Sonstige Informationen', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/OtherInformation',
                            null,
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                            ),
                            'Sonstige Informationen'))
//                                    . (new Standard(
//                                        new EyeOpen(), '/Education/Certificate/Prepare/Certificate/Show',
//                                        null,
//                                        array(
//                                            'PrepareId' => $tblPrepare->getId(),
//                                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
//                                            'PersonId' => $tblPerson->getId(),
//                                            'Route' => 'Diploma'
//                                        )
//                                    ))
                        . (new External(
                            '',
                            '/Api/Education/Certificate/Generator/Preview',
                            new Download(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'Name' => 'Zeugnismuster'
                            ),
                            'Zeugnis als Muster herunterladen'))
                        : '')
                );
            }
        }

        $table = new TableData(
            $studentTable,
            null,
            array(
                'Number' => '#',
                'Name' => 'Name',
                'SelectedCourses' => 'Eingebrachte Kurse',
                'ResultBlockI' => 'Block I Punktsumme',
                'ResultBlockII' => 'Block II Punktsumme',
                'Option' => ' '
            ),
            array(
                'columnDefs' => array(
                    array(
                        "width" => "7px",
                        "targets" => 0
                    ),
//                    array(
//                        "width" => "200px",
//                        "targets" => 1
//                    ),
                ),
                'order' => array(
                    array('0', 'asc'),
                ),
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                    "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            )
        );

        $stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        $headerColumns
                    )
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn($table)
                    )
                ), new Title('Übersicht')),
            ))
        );

        return $stage;
    }


    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param int $View
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturBlockI(
        $PrepareId = null,
        $PersonId = null,
        $View = BlockIView::PREVIEW,
        $Data = null
    ): Stage {
        $stage = new Stage('Abiturzeugnis', 'Block I: Ergebnisse in der Qualifikationsphase');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
        ) {
            $blockI = new BlockI($tblPerson, $tblPrepare, $View);

            $form = $blockI->getForm();

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                $service = Prepare::useService()->updateAbiturPreliminaryGrades(
                    $form,
                    $tblPerson,
                    $tblPrepare,
                    $View,
                    $Data
                );
                if ($View != BlockIView::PREVIEW && !$tblPrepareStudent->isApproved()) {
                    $content = new Well($service);
                } else {
                    $content = $form;
                }
            } else {
                $content = $form;
            }

            if ($View == BlockIView::EDIT_GRADES) {
                $textEditGrades = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Primary('Punkte bearbeiten'));
                $textChooseCourses = 'Kurse einbringen';
            } elseif ($View == BlockIView::CHOOSE_COURSES) {
                $textEditGrades = 'Punkte bearbeiten';
                $textChooseCourses = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Primary('Kurse einbringen'));
            } else {
                $textEditGrades = 'Punkte bearbeiten';
                $textChooseCourses = 'Kurse einbringen';
            }

            list($countCourses, $resultBlockI) = Prepare::useService()->getResultForAbiturBlockI($tblPrepare, $tblPerson);

            if ($countCourses == 40) {
                $countCourses = new \SPHERE\Common\Frontend\Message\Repository\Success(
                    new Check() . ' ' . $countCourses . ' von 40 Kursen eingebracht.');
            } else {
                $countCourses = new \SPHERE\Common\Frontend\Message\Repository\Warning(
                    new Disable() . ' ' . $countCourses . ' von 40 Kursen eingebracht.');
            }

            if ($resultBlockI >= 200) {
                $resultBlockI = new \SPHERE\Common\Frontend\Message\Repository\Success(
                    new Check() . ' ' . $resultBlockI . ' von mindestens 200 Punkten erreicht.');
            } else {
                $resultBlockI = new \SPHERE\Common\Frontend\Message\Repository\Warning(
                    new Disable() . ' ' . $resultBlockI . ' von mindestens 200 Punkten erreicht.');
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Standard($textEditGrades,
                                    '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                                    new Edit(), array(
                                        'PrepareId' => $PrepareId,
                                        'PersonId' => $PersonId,
                                        'View' => BlockIView::EDIT_GRADES
                                    )
                                ),
                                new Standard($textChooseCourses,
                                    '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                                    new Check(), array(
                                        'PrepareId' => $PrepareId,
                                        'PersonId' => $PersonId,
                                        'View' => BlockIView::CHOOSE_COURSES
                                    )
                                ),
                            ))
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(#
                                '<br>',
                                new Warning(
                                    new Bold('Hinweise:')
                                    . new Container('* Die Punkte wurden aus dem entsprechenden Kurshalbjahreszeugnis ermittelt.')
                                    . new Container('** Die Punkte wurden aus dem Stichtagsnotenauftrag in der 12/2 ermittelt.')
                                )
                            ))
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                '<br>',
                                $countCourses,
                                $resultBlockI
                            ))
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                '<br>',
                                $content
                            ))
                        ))
                    ))
                ))
            );
        }

        return $stage;
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturBlockII(
        $PrepareId = null,
        $PersonId = null,
        $Data = null
    ): Stage {
        $stage = new Stage('Abiturzeugnis', 'Block II: Ergebnisse in der Abiturprüfung');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
        ) {
            $blockII = new BlockII($tblPerson, $tblPrepare);

            $stage->setContent($blockII->getContent($Data));
        }

        return $stage;
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturLevelTen(
        $PrepareId = null,
        $PersonId = null,
        $Data = null
    ): Stage {
        $stage = new Stage('Abiturzeugnis', 'Ergebnisse der Pflichtfächer, die in Klassenstufe 10 abgeschlossen wurden');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
        ) {
            $levelTen = new LevelTen($tblPerson, $tblPrepare);

            $stage->setContent($levelTen->getContent($Data));
        }

        return $stage;
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturOtherInformation(
        $PrepareId = null,
        $PersonId = null,
        $Data = null
    ): Stage {
        $stage = new Stage('Abiturzeugnis', 'Sonstige Informationen');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
            && ($tblYear = $tblPrepare->getYear())
        ) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
            $global = $this->getGlobal();
            if (($tblPrepareInformationRemark = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Remark'))) {
                $global->POST['Data']['Remark'] = $tblPrepareInformationRemark->getValue();
            }
            if (($tblPrepareInformationLatinums = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Latinums'))) {
                $global->POST['Data']['Latinums'] = $tblPrepareInformationLatinums->getValue();
            }
            if (($tblPrepareInformationGraecums = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Graecums'))) {
                $global->POST['Data']['Graecums'] = $tblPrepareInformationGraecums->getValue();
            }
            if (($tblPrepareInformationHebraicums = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Hebraicums'))) {
                $global->POST['Data']['Hebraicums'] = $tblPrepareInformationHebraicums->getValue();
            }
            $global->savePost();

            $textArea = new TextArea('Data[Remark]', 'Bemerkungen', 'Bemerkungen');
            $checkBox1 = new CheckBox('Data[Latinums]', 'Nachweis des Latinums', 1);
            $checkBox2 = new CheckBox('Data[Graecums]', 'Nachweis des Graecums', 1);
            $checkBox3 = new CheckBox('Data[Hebraicums]', 'Nachweis des Hebraicums', 1);

            if ($tblPrepareStudent->isApproved()) {
                $textArea->setDisabled();
                $checkBox1->setDisabled();
                $checkBox2->setDisabled();
                $checkBox3->setDisabled();
            }

            $global = $this->getGlobal();
            $contentForeignLanguages = array();
            if (($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
            ) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                        if ($tblCertificate) {
                            if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                $tblPrepare,
                                $tblPerson,
                                'ForeignLanguages' . $tblStudentSubject->getTblStudentSubjectRanking()->getId()
                            ))) {
                                $global->POST['Data']['ForeignLanguages'][$tblStudentSubject->getTblStudentSubjectRanking()->getId()]
                                    = $tblPrepareInformation->getValue();
                            } else {
                                $global->POST['Data']['ForeignLanguages'][$tblStudentSubject->getTblStudentSubjectRanking()->getId()]
                                    = Generator::useService()->getReferenceForLanguageByStudent(
                                    $tblCertificate,
                                    $tblStudentSubject,
                                    $tblPerson,
                                    $tblYear
                                );
                            }

                            $global->savePost();
                        }

                        $contentForeignLanguages[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($tblStudentSubject->getTblStudentSubjectRanking()->getName() . ' FS: ' . $tblSubject->getDisplayName(), 4),
                            new LayoutColumn(
                                'von ' . ($tblStudentSubject->getLevelFrom() ?: '&ndash;')
                                . ' bis ' . ($tblStudentSubject->getLevelTill() ?: '12')
                                , 4),
                            new LayoutColumn(new TextField('Data[ForeignLanguages][' . $tblStudentSubject->getTblStudentSubjectRanking()->getId() . ']'),
                                4),
                        ))));
                    }
                }
            }

            $form = new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new Panel(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn('Fremdsprachen', 4),
                                    new LayoutColumn('Klassen-/Jahrgangsstufe', 4),
                                    new LayoutColumn('Niveau gemäß GER', 4),
                                )))),
                                $contentForeignLanguages,
                                Panel::PANEL_TYPE_PRIMARY
                            ),
                            new Panel(
                                'Sonstige Informationen',
                                array(
                                    $textArea,
                                    $checkBox1,
                                    $checkBox2,
                                    $checkBox3
                                ),
                                Panel::PANEL_TYPE_PRIMARY
                            ),
                        ))
                    ))
                ))
            ));

            if (!$tblPrepareStudent->isApproved()) {
                $content = new Well(Prepare::useService()->updateAbiturPrepareInformation(
                    $form->appendFormButton(new Primary('Speichern', new Save())),
                    $tblPrepare, $tblPerson, $Data
                ));
            } else {
                $content = $form;
            }

            $stage->setContent(new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                $tblPerson->getLastFirstName(),
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
            )));
        }

        return $stage;
    }
}