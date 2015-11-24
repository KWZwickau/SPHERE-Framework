<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Level
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendCreateLevelDivision($Level = null, $Division = null)
    {

        $Stage = new Stage('Schulklasse', 'erstellen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $Stage->setMessage('Bezeichnet die Gesamtheit der Klassen, die in demselben Lernabschnitt zugehörig sind.');

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as &$tblDivision) {
                $tblDivision->Year = $tblDivision->getServiceTblYear()->getName();
                $tblDivision->Level = $tblDivision->getTblLevel()->getName();
                $tblDivision->SchoolType = $tblDivision->getTblLevel()->getServiceTblType()->getName();
                $tblDivision->Option = new Standard('', '/Education/Lesson/Division/Change/Division', new Pencil(),
                        array('Id' => $tblDivision->getId()), 'Beschreibung bearbeiten')
                    .new Standard('', '/Education/Lesson/Division/Destroy/Division', new Remove(),
                        array('Id' => $tblDivision->getId()), 'Klasse löschen');
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblDivisionAll, null,
                                array('Year'        => 'Jahr',
                                      'Level'       => 'Stufe',
                                      'SchoolType'  => 'Schultyp',
                                      'Name'        => 'Klassenname',
                                      'Description' => 'Beschreibung',
                                      'Option'      => 'Option',
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Division::useService()->createLevel(
                                $this->formLevelDivision()
                                    ->appendFormButton(new Primary('Schulklassen hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Level, $Division
                            )
                        )
                    ), new Title('Klassenstufe hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblLevel|null    $tblLevel
     * @param TblDivision|null $tblDivision
     *
     * @return Form
     */
    public function formLevelDivision(TblLevel $tblLevel = null, TblDivision $tblDivision = null)
    {

        $tblLevelAll = Division::useService()->getLevelAll();
        $acNameAll = array();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel $tblLevel) use (&$acNameAll) {

                if (!in_array($tblLevel->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblLevel->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Level'] ) && $tblLevel) {
            $Global->POST['Level']['Type'] = ( $tblLevel->getServiceTblType() ? $tblLevel->getServiceTblType()->getId() : 0 );
            $Global->POST['Level']['Name'] = $tblLevel->getName();
//            $Global->POST['Level']['Description'] = $tblLevel->getDescription();
            $Global->POST['Division']['Year'] = ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : 0 );
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblYearAll = Term::useService()->getYearAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klassenstufe',
                            array(
                                new SelectBox('Level[Type]', 'Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education()),
                                new AutoCompleter('Level[Name]', 'Klassenstufe (Nummer)', 'z.B: 5', $acNameAll,
                                    new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Klassengruppe',
                            array(
                                new SelectBox('Division[Year]', 'Schuljahr', array(
                                    '{{ Name }} {{ Description }}' => $tblYearAll
                                ), new Education()),
                                new AutoCompleter('Division[Name]', 'Klassengruppe (Name)', 'z.B: Alpha', $acNameAll,
                                    new Pencil()),
                                new TextField('Division[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param null $SubjectGroup
     *
     * @return Stage
     */
    public function frontendCreateSubjectGroup($SubjectGroup = null)
    {

        $Stage = new Stage('Unterrichtsgruppen', 'für Klassen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $Stage->setMessage('Zum erstellen von Klasseninternen Gruppen z.B.: 2 Gruppen für Informatik.');

        $tblSubjectGroupList = Division::useService()->getSubjectGroupAll();
        if ($tblSubjectGroupList) {
            array_walk($tblSubjectGroupList, function (TblSubjectGroup &$tblSubjectGroup) {

                $tblSubjectGroup->Option = new Standard('', '/Education/Lesson/Division/Change/SubjectGroup', new Pencil(),
                    array('Id' => $tblSubjectGroup->getId()));
            });
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblSubjectGroupList, null, array(
                                'Name'        => 'Schulart',
                                'Description' => 'Beschreibung',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Gruppen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Division::useService()->createSubjectGroup(
                                $this->formSubjectGroup()
                                    ->appendFormButton(new Primary('Gruppe hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $SubjectGroup
                            )
                        )
                    ), new Title('Klassenstufe hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return Form
     */
    public function formSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['SubjectGroup'] ) && $tblSubjectGroup) {
            $Global->POST['SubjectGroup']['Name'] = $tblSubjectGroup->getName();
            $Global->POST['SubjectGroup']['Description'] = $tblSubjectGroup->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Gruppe', array(
                                new TextField('SubjectGroup[Name]', 'Info I / Info II', 'Gruppenname',
                                    new Pencil()),
                                new TextField('SubjectGroup[Description]', 'zb: für Gruppe 1 / 2', 'Beschreibung',
                                    new Pencil()))
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param int  $Id
     * @param null $SubjectGroup
     *
     * @return Stage
     */
    public function frontendChangeSubjectGroup($Id, $SubjectGroup = null)
    {

        $Stage = new Stage('Gruppe', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Create/SubjectGroup', new ChevronLeft()));
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($Id);
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Id'] ) && $tblSubjectGroup) {
            $Global->POST['SubjectGroup']['Name'] = $tblSubjectGroup->getName();
            $Global->POST['SubjectGroup']['Description'] = $tblSubjectGroup->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(Division::useService()
            ->changeSubjectGroup($this->formSubjectGroup($tblSubjectGroup)
                ->appendFormButton(new Primary('Änderung speichern'))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                , $SubjectGroup, $Id));

        return $Stage;
    }

    /**
     * @param int  $Id
     * @param null $Student
     *
     * @return Stage
     */
    public function frontendStudentAdd($Id, $Student = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage = new Stage('Schüler', 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()).' hinzufügen');
            $Stage->setMessage('Liste aller Schüler die keiner Klasse zugefügt sind.');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));


            $Stage->setContent(new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Division::useService()->addStudentToDivision(
                                    $this->formStudentAdd($tblDivision)
                                        ->appendFormButton(new Primary('Schüler hinzufügen'))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblDivision, $Student
                                )
                            )
                        )//, new Title('Schüler ohne Klassen')
                    )
                )
            ));

        } else {
            $Stage = new Stage('Schüler', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return Form
     */
    public function formStudentAdd(TblDivision $tblDivision)
    {

        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');

        $tblStudentList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Schüler
        $tblDivisionList = Division::useService()->getDivisionAll();
        if ($tblStudentList) {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblSingleDivision) {
                    $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblSingleDivision);
                    if ($tblSingleDivision && $tblDivisionStudentList) {
                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                            function (TblPerson $invoiceA, TblPerson $invoiceB) {

                                return $invoiceA->getId() - $invoiceB->getId();
                            });
                    }
                }
            }
            if ($tblStudentList) {
                foreach ($tblStudentList as &$tblStudent) {
                    $tblStudent = new CheckBox(
                        'Student['.$tblStudent->getId().']',
                        $tblStudent->getFirstName().' '.$tblStudent->getSecondName().' '.$tblStudent->getLastName(),
                        $tblStudent->getId()
                    );
                }
            } else {
                $tblStudentList = new Warning('Kein Schüler ohne Klasse im System');
            }
        } else {
            $tblStudentList = new Warning('Kein Schüler im System');
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schüler ohne Klasse'.
                            new PullRight(new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()))
                            , $tblStudentList, Panel::PANEL_TYPE_INFO)
                        , 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param int $StudentId
     *
     * @return Stage
     */
    public function frontendStudentRemove($Id, $StudentId)
    {

        $Stage = new Stage('Schüler', 'aus der Klasse entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($StudentId);
        if ($tblPerson) {
            $Stage->setContent(Division::useService()->removeStudentToDivision($tblDivision, $tblPerson));
        } else {
            $Stage->setContent(new Warning('Person zur Klasse nicht gefunden'));
        }


        return $Stage;
    }

    /**
     * @param int  $Id
     * @param null $Teacher
     *
     * @return Stage
     */
    public function frontendTeacherAdd($Id, $Teacher = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage = new Stage('Klassenlehrer', 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()).' hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));


            $Stage->setContent(new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Division::useService()->addTeacherToDivision(
                                    $this->formTeacherAdd($tblDivision)
                                        ->appendFormButton(new Primary('Klassenlehrer hinzufügen'))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblDivision, $Teacher
                                )
                            )
                        ), new Title('Verfügbare Lehrer')
                    )
                )
            ));

        } else {
            $Stage = new Stage('Klassenlehrer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return Form
     */
    public function formTeacherAdd(TblDivision $tblDivision)
    {

        $tblGroup = Group::useService()->getGroupByMetaTable('STAFF');

        $tblTeacherList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Lehrer
        if ($tblTeacherList) {
            $tblDivisionTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
            if ($tblDivision && $tblDivisionTeacherList) {
                $tblTeacherList = array_udiff($tblTeacherList, $tblDivisionTeacherList,
                    function (TblPerson $invoiceA, TblPerson $invoiceB) {

                        return $invoiceA->getId() - $invoiceB->getId();
                    });
            }
            if ($tblTeacherList) {
                foreach ($tblTeacherList as $key => $row) {
                    $last[$key] = strtoupper($row->getLastName());
                    $first[$key] = strtoupper($row->getFirstName());
                    $id[$key] = $row->getId();
                }
                array_multisort($last, SORT_ASC, $first, SORT_ASC, $tblTeacherList);

                foreach ($tblTeacherList as &$tblTeacher) {
                    $tblTeacher = new CheckBox(
                        'Teacher['.$tblTeacher->getId().']',
                        $tblTeacher->getTitle().' '.$tblTeacher->getFirstName().' '.$tblTeacher->getSecondName().' '.$tblTeacher->getLastName(),
                        $tblTeacher->getId()
                    );
                }
            }
        } else {
            $tblTeacherList = new Warning('Es sind noch keine Lehrer im System');
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Lehrer'.
                            new PullRight(new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()))
                            , $tblTeacherList, Panel::PANEL_TYPE_INFO)
                        , 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param int $TeacherId
     *
     * @return Stage
     */
    public function frontendTeacherRemove($Id, $TeacherId)
    {

        $Stage = new Stage('Lehrer', 'aus der Klasse entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($TeacherId);
        if ($tblPerson) {
            $Stage->setContent(Division::useService()->removeTeacherToDivision($tblDivision, $tblPerson));
        } else {
            $Stage->setContent(new Warning('Person zur Klasse nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param int  $Id
     * @param null $Subject
     *
     * @return Stage
     */
    public function frontendSubjectAdd($Id, $Subject = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage = new Stage('Fächer', 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()).' hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));
            $tblSubjectListUsedList = Division::useService()->getSubjectAllByDivision($tblDivision);

            $Global = $this->getGlobal();
            if ($tblSubjectListUsedList) {
                foreach ($tblSubjectListUsedList as $tblSubjectListUsed) {
                    $Global->POST['Subject'][$tblSubjectListUsed->getId()] = true;

                }
                $Global->savePost();
            }

            $Stage->setContent(new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Division::useService()->addSubjectToDivision(
                                    $this->formSubjectAdd($tblDivision)
                                        ->appendFormButton(new Primary('Fächer hinzufügen'))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblDivision, $Subject
                                )
                            )
                        ), new Title('Verfügbare Fächer')
                    )
                )
            ));

        } else {
            $Stage = new Stage('Fächer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return Form
     */
    public function formSubjectAdd(TblDivision $tblDivision)
    {

        $tblSubjectList = Subject::useService()->getSubjectAll();  // Alle Fächer
        $ListLeft = array();
        $ListRight = array();
        if ($tblSubjectList) {
//            $tblSubjectListUsed = Division::useService()->getSubjectAllByDivision($tblDivision);
//            if ($tblDivision && $tblSubjectListUsed) {                            // Ausblenden der vorhandenen Fächer
//                $tblSubjectList = array_udiff($tblSubjectList, $tblSubjectListUsed,
//                    function (TblSubject $invoiceA, TblSubject $invoiceB) {
//
//                        return $invoiceA->getId() - $invoiceB->getId();
//                    });
//            }

            if ($tblSubjectList) {
                foreach ($tblSubjectList as $key => $row) {
                    $name[$key] = strtoupper($row->getName());
                    $acronym[$key] = strtoupper($row->getAcronym());
                }
                array_multisort($acronym, SORT_ASC, $name, SORT_ASC, $tblSubjectList);

                $Counting = count($tblSubjectList);
                $Counting = $Counting / 2;
                $Count = 0;
                foreach ($tblSubjectList as $tblSubject) {
                    if ($Counting > $Count) {
                        $ListLeft[] = $tblSubject;
                    } else {
                        $ListRight[] = $tblSubject;
                    }
                    $Count++;
                }
                /** @var TblSubject $tblSubject */
                foreach ($ListLeft as &$tblSubject) {
                    $tblSubject = new CheckBox(
                        'Subject['.$tblSubject->getId().']',
                        $tblSubject->getAcronym().' - '.$tblSubject->getName(),
                        $tblSubject->getId()
                    );
                }
                foreach ($ListRight as &$tblSubject) {
                    $tblSubject = new CheckBox(
                        'Subject['.$tblSubject->getId().']',
                        $tblSubject->getAcronym().' - '.$tblSubject->getName(),
                        $tblSubject->getId()
                    );
                }

//                foreach ($tblSubjectList as &$tblSubject) {
//                    $tblSubject = new CheckBox(
//                        'Subject['.$tblSubject->getId().']',
//                        $tblSubject->getAcronym().' - '.$tblSubject->getName(),
//                        $tblSubject->getId()
//                    );
//                }
            } else {
                $ListLeft = new Warning('Alle Fächer im System bereits vergeben');
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fächer'.
                            new PullRight(new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()))
                            , $ListLeft, Panel::PANEL_TYPE_INFO)
                        , 6),
                    ( $ListRight ) ?
                        new FormColumn(
                            new Panel('Fächer'.
                                new PullRight(new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()))
                                , $ListRight, Panel::PANEL_TYPE_INFO)
                            , 6) : null,
                )),
            ))
        );
    }

    /**
     * @param int  $Id
     * @param null $DivisionSubject
     * @param null $Student
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendSubjectStudentAdd($Id, $DivisionSubject = null, $Student = null, $Group = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage = new Stage('Zuordung', 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()).' hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectStudent/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));


            $Stage->setContent(new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Division::useService()->addSubjectStudent(
                                    $this->formSubjectStudentAdd($tblDivision)
                                        ->appendFormButton(new Primary('Gruppe hinzufügen'))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $DivisionSubject, $Student, $Id, $Group
                                )
                            )
                        ), new Title('Schüler der Klasse '.$tblDivision->getTblLevel()->getName().$tblDivision->getName())
                    )
                )
            ));

        } else {
            $Stage = new Stage('Zuordung', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return Form
     */
    public function formSubjectStudentAdd(TblDivision $tblDivision)
    {

        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);  // Alle Schüler der Klasse
        if ($tblStudentList) {

            if ($tblStudentList) {
                foreach ($tblStudentList as $key => $row) {
                    $name[$key] = strtoupper($row->getLastName());
                    $firstName[$key] = strtoupper($row->getFirstName());
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $tblStudentList);

                foreach ($tblStudentList as &$tblPerson) {
                    $tblPerson = new CheckBox(
                        'Student['.$tblPerson->getId().']',
                        $tblPerson->getFirstName().' '.$tblPerson->getSecondName().' - '.$tblPerson->getLastName(),
                        $tblPerson->getId()
                    );
                }
            }
        } else {
            $tblStudentList = new Warning('Es sind noch keine Schüler für die Klasse hinterlegt');
        }
        $tblGroupList = Division::useService()->getSubjectGroupAll();
        $tblGroupList[] = new TblSubjectGroup('');

        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
        if ($tblDivisionSubjectList) {
            foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
                $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()->getName().' - '.$tblDivisionSubject->getServiceTblSubject()->getAcronym();
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schüler', $tblStudentList, Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel(' Fächer:'
                            , new SelectBox('DivisionSubject', '', array('Name' => $tblDivisionSubjectList)), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gruppen', new SelectBox('Group', '', array('{{ Name }} {{ Description }}' => $tblGroupList)), Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
            ))
        );
    }

    /**
     * @param int  $Id
     * @param null $DivisionSubject
     * @param null $Teacher
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendSubjectTeacherAdd($Id, $DivisionSubject = null, $Teacher = null, $Group = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage = new Stage('Zuordung', 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()).' hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectTeacher/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));
            $Stage->setMessage('Blaue Fächer sind mindestens einem Lehrer zugeordnet');


            $Stage->setContent(new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Division::useService()->addSubjectTeacher(
                                    $this->formSubjectTeacherAdd($tblDivision)
                                        ->appendFormButton(new Primary('Zuweisung hinzufügen'))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $DivisionSubject, $Teacher, $Id, $Group
                                )
                            )
                        ), new Title('Verfügbare Fächer')
                    )
                )
            ));

        } else {
            $Stage = new Stage('Zuordung', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return Form
     */
    public function formSubjectTeacherAdd(TblDivision $tblDivision)
    {

        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);  // Alle Fächer der Klasse
        if ($tblDivisionSubjectList) {

            foreach ($tblDivisionSubjectList as $key => $row) {
                $name[$key] = strtoupper($row->getServiceTblSubject()->getName());
                $acronym[$key] = strtoupper($row->getServiceTblSubject()->getAcronym());
            }
            array_multisort($name, SORT_ASC, $acronym, SORT_ASC, $tblDivisionSubjectList);

            foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
                if (Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject)) {
                    $tblDivisionSubject = new CheckBox(
                        'DivisionSubject['.$tblDivisionSubject->getId().']',
                        new Info($tblDivisionSubject->getServiceTblSubject()->getName().' - '.$tblDivisionSubject->getServiceTblSubject()->getAcronym()),
                        $tblDivisionSubject->getId()
                    );
                } else {
                    $tblDivisionSubject = new CheckBox(
                        'DivisionSubject['.$tblDivisionSubject->getId().']',
                        $tblDivisionSubject->getServiceTblSubject()->getName().' - '.$tblDivisionSubject->getServiceTblSubject()->getAcronym(),
                        $tblDivisionSubject->getId()
                    );
                }
            }

        } else {
            $tblDivisionSubjectList = new Warning('Es sind noch keine Fächer für die Klasse hinterlegt');
        }
        $tblGroupList = Division::useService()->getSubjectGroupAll();
        $tblGroupList[] = new TblSubjectGroup('');

        $tblTeacherlist = null;
        $tblGroupTeacher = Group::useService()->getGroupByMetaTable('STAFF');
        if ($tblGroupTeacher) {
            $tblTeacherlist = Group::useService()->getPersonAllByGroup($tblGroupTeacher);
            if ($tblTeacherlist) {
                foreach ($tblTeacherlist as &$tblTeacher) {
                    $tblTeacher->Name = $tblTeacher->getFullName();
                }
            }
        }


        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fächer', $tblDivisionSubjectList, Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel(' Lehrer:'
                            , new SelectBox('Teacher', '', array('Name' => $tblTeacherlist)), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gruppen', new SelectBox('Group', '', array('{{ Name }} {{ Description }}' => $tblGroupList)), Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param int $SubjectId
     *
     * @return Stage
     */
    public function frontendSubjectRemove($Id, $SubjectId)
    {

        $Stage = new Stage('Fächer', 'von der Klasse entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        if ($tblSubject) {
            $Stage->setContent(Division::useService()->removeSubjectToDivision($tblDivision, $tblSubject));
        } else {
            $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param int $Id
     * @param int $SubjectStudentId
     *
     * @return Stage
     */
    public function frontendSubjectStudentRemove($Id, $SubjectStudentId)
    {

        $Stage = new Stage('Zuordnung', 'von eines Schülers entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectStudent/Show', new ChevronLeft(), array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblSubjectStudent = Division::useService()->getSubjectStudentById($SubjectStudentId);
        if ($tblSubjectStudent) {
            $Stage->setContent(Division::useService()->removeSubjectStudent($tblSubjectStudent, $tblDivision));
        } else {
            $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param int $Id
     * @param int $DivisionSubjectId
     *
     * @return Stage
     */
    public function frontendSubjectTeacherRemove($Id, $DivisionSubjectId)
    {

        $Stage = new Stage('Zuordnung', 'entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectTeacher/Show', new ChevronLeft(), array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        if ($tblDivision) {
            if ($tblDivisionSubject) {
                $Stage->setContent(Division::useService()->removeSubjectTeacher($tblDivisionSubject, $tblDivision));
            } else {
                $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
            }
        } else {
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }


        return $Stage;
    }

    /**
     * @param int  $Id
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendChangeDivision($Id, $Division = null)
    {

        $Stage = new Stage('Beschreibung', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Create/LevelDivision', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Id'] ) && $tblDivision) {
//            $Global->POST['Division']['Year'] = $tblDivision->getServiceTblYear()->getId();
//            $Global->POST['Division']['Level'] = $tblDivision->getTblLevel()->getId();
//            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }
        if ($tblDivision) {
            $Info = new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel($tblDivision->getServiceTblYear()->getName()
                                .' - '.$tblDivision->getTblLevel()->getName().$tblDivision->getName()
                                , $tblDivision->getTblLevel()->getServiceTblType()->getName()
                                , Panel::PANEL_TYPE_INFO), 3
                        )
                    )
                )
            );
        } else {
            $Info = null;
        }
        $Stage->setContent($Info.
            Division::useService()->changeDivision($this->formDivision()
                ->appendFormButton(new Primary('Änderung speichern'))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                , $Division, $Id));

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formDivision()
    {

        return new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TextField('Division[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                            new Pencil())
                    )
                )
            )
        );
    }

    /**
     * @param int $Id
     *
     * @return Stage
     */
    public function frontendDivisionShow($Id)
    {

        $Stage = new Stage('Klassenübersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->setMessage($tblDivision->getDescription());
            $Stage->addButton(new Standard('Fächer hinzufügen', '/Education/Lesson/Division/Subject/Add',
                new Book(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Klassenlehrer hinzufügen', '/Education/Lesson/Division/Teacher/Add',
                new Person(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Schüler hinzufügen', '/Education/Lesson/Division/Student/Add',
                new \SPHERE\Common\Frontend\Icon\Repository\Group(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Übersicht Fächer/Fachlehrer', '/Education/Lesson/Division/SubjectTeacher/Show',
                new EyeOpen(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Übersicht Zuweisung', '/Education/Lesson/Division/SubjectStudent/Show',
                new EyeOpen(), array('Id' => $tblDivision->getId())));

            $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivisionStudent->FullName = $tblDivisionStudent->getFirstName().' '.
                        $tblDivisionStudent->getSecondName().' '.
                        $tblDivisionStudent->getLastName();
                    $tblDivisionStudent->Option =
                        new Standard('', '/Education/Lesson/Division/Student/Remove', new Remove(),
                            array('Id'        => $tblDivision->getId(),
                                  'StudentId' => $tblDivisionStudent->getId()),
                            'Schüler entfernen');

                    $tblCommon = Common::useService()->getCommonByPerson($tblDivisionStudent);
                    if ($tblCommon) {
                        $tblDivisionStudent->Birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                    } else {
                        $tblDivisionStudent->Birthday = 'nicht eingetragen';
                    }

                    $tblSubjectStudentList = Division::useService()->getSubjectStudentByPerson($tblDivisionStudent);
                    if ($tblSubjectStudentList) {
                        $GroupList = array();
                        /** @var TblSubjectStudent $tblSubjectStudent */
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            $GroupList[] = $tblSubjectStudent->getTblDivisionSubject()->getServiceTblSubject()->getName();
                        }
                        asort($GroupList);
                        $tblDivisionStudent->Group = implode(', ', $GroupList);
                    } else {
                        $tblDivisionStudent->Group = 'keine Zuordnung';
                    }
                }
            }
            $tblDivisionTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
            if ($tblDivisionTeacherList) {
                foreach ($tblDivisionTeacherList as &$tblDivisionTeacher) {
                    $Content = array();
                    $tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblDivisionTeacher);
                    if ($tblPhoneList) {
                        foreach ($tblPhoneList as $tblPhone)
                            if ($tblPhone->getTblType()->getName() === 'Geschäftlich')
                                array_push($Content, $tblPhone->getTblType()->getName().' - '.$tblPhone->getTblPhone()->getNumber());
                    }

                    $tblDivisionTeacher = new LayoutColumn(
                        new Panel($tblDivisionTeacher->getFullName(), $Content, Panel::PANEL_TYPE_INFO,
//                            new Standard('', '', new Person(), null, 'Test')
//                            .new Standard('', '', new Book(), null, 'Test (Fächer)').
                            new Standard('', '/Education/Lesson/Division/Teacher/Remove', new Remove(),
                                array('Id'        => $tblDivision->getId(),
                                      'TeacherId' => $tblDivisionTeacher->getId()),
                                'Lehrer entfernen')), 4
                    );
                }
            } else {
                $tblDivisionTeacherList = new LayoutColumn('');
            }
//            $tblDivisionSubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
//            if ($tblDivisionSubjectList) {
//                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
//                    $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/Subject/Remove', new Remove(),
//                        array('Id'        => $tblDivision->getId(),
//                              'SubjectId' => $tblDivisionSubject->getId()), 'Fach entfernen');
//                }
//            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($tblDivisionStudentList,
                                    new \SPHERE\Common\Frontend\Table\Repository\Title('Schüler in der Klasse '
                                        .$tblDivision->getTblLevel()->getName().$tblDivision->getName()),
                                    array('FirstName' => 'Vorname',
                                          'LastName'  => 'Nachname',
                                          'Group'     => 'Zuweisung(en)',
                                          'Option'    => 'Option')
                                    , true)
                            )
                        ))
                    ),
                    new LayoutGroup(array(
                            new LayoutRow($tblDivisionTeacherList),
                        )
                        , new Title('Klassenlehrer:')
                    )
//                ,
//                    new LayoutGroup(
//                        new LayoutRow(array(
//                            new LayoutColumn(
//                                new TableData($tblDivisionSubjectList,
//                                    new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer für die Klasse '
//                                        .$tblDivision->getTblLevel()->getName().$tblDivision->getName()),
//                                    array('Acronym'     => 'Kürzel',
//                                          'Name'        => 'Name',
//                                          'Description' => 'Beschreibung',
//                                          'Option'      => 'Option')
//                                    , true)
//                            )
//                        ))
//                    )
                ))
            );
        } else {
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage
     */
    public function frontendSubjectStudentShow($Id)
    {

        $Stage = new Stage('Zuweisung', 'Übersicht');

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Hinzufügen', '/Education/Lesson/Division/SubjectStudent/Add', new Plus(), array('Id' => $tblDivision->getId())));
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);

            $Content = array();
            $Count = 0;
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $key => $row) {
                    $Subject[$key] = strtoupper($row->getServiceTblSubject()->getName());
//                    $first[$key] = strtoupper($row->getFirstName());
                    $id[$key] = $row->getId();
                }
                array_multisort($Subject, SORT_ASC, $tblDivisionSubjectList);

                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
                    $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                    if ($tblSubjectStudentList) {
                        /** @var TblSubjectStudent $tblSubjectStudent */
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            $Count++;
                            $tblSubjectStudent->Person = $tblSubjectStudent->getServiceTblPerson()->getFullName();
                            $tblSubjectGroup = $tblSubjectStudent->getTblSubjectGroup();
                            if ($tblSubjectGroup) {
                                $tblSubjectStudent->Group = $tblSubjectGroup->getName().' - '.$tblSubjectGroup->getDescription();

                            } else {
                                $tblSubjectStudent->Group = 'keine Gruppe';
                            }
                            $tblSubjectStudent->Option = new Standard('', '/Education/Lesson/Division/SubjectStudent/Remove', new Remove,
                                array('Id'               => $tblDivision->getId(),
                                      'SubjectStudentId' => $tblSubjectStudent->getId())
                                , 'Zuweisung entfernen');
                        }

                        $Content[] = new LayoutColumn(
                            new TableData($tblSubjectStudentList, new \SPHERE\Common\Frontend\Table\Repository\Title($tblDivisionSubject->getServiceTblSubject()->getName())
                                , array(//'Id'     => 'Indentnumber',
                                    'Person' => 'Name',
                                    'Group'  => 'Gruppe',
                                    'Option' => 'Option'), false)
                            , 12);
                    }
                }
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            ( $Count >= 1 ) ? $Content
                                : new LayoutColumn(new Warning('Keine Zuordnungen vorhanden'))
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Warning('Klasse nicht gefunden')
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage
     */
    public function frontendSubjectTeacherShow($Id)
    {

        $Stage = new Stage('Lehrer / Fächerzuweisung', 'Übersicht');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Hinzufügen', '/Education/Lesson/Division/SubjectTeacher/Add', new Plus(), array('Id' => $tblDivision->getId())));

            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            $Content = array();
            $Count = 0;
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
                    $Count++;
                    $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
                    $teacherString = new \SPHERE\Common\Frontend\Text\Repository\Warning('leer');
                    $teacherArray = array();
                    if ($tblTeacherList) {
                        /** @var TblPerson $Teacher */
                        foreach ($tblTeacherList as $Teacher) {
                            $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
                        }
                        $teacherString = implode(', ', $teacherArray);
                    }
                    $tblDivisionSubject->Teacher = $teacherString;
                    $tblDivisionSubject->Subject = $tblDivisionSubject->getServiceTblSubject()->getName();

                    $tblSubjectGroupList = Division::useService()->getSubjectGroupByDivisionSubject($tblDivisionSubject);

                    $subjectGroupString = new \SPHERE\Common\Frontend\Text\Repository\Warning('keine');
                    $subjectGroupArray = array();
                    if ($tblSubjectGroupList) {
                        /** @var TblSubjectGroup $tblSubjectGroup */
                        foreach ($tblSubjectGroupList as $tblSubjectGroup) {
                            if ($tblSubjectGroup) {
                                $subjectGroupArray[] = $tblSubjectGroup->getName().' '.$tblSubjectGroup->getDescription();
                            }
                        }
                        if (!empty( $subjectGroupArray )) {
                            $subjectGroupString = implode(', ', $subjectGroupArray);
                        }
                    }
                    $tblDivisionSubject->Group = $subjectGroupString;

                    if ($teacherString != new \SPHERE\Common\Frontend\Text\Repository\Warning('leer'))
                        $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/SubjectTeacher/Remove', new Remove(), array('Id'                => $tblDivision->getId(),
                                                                                                                                               'DivisionSubjectId' => $tblDivisionSubject->getId()
                        ), 'Zuweisung entfernen');
                }
                $Content[] = new LayoutColumn(
                    new TableData($tblDivisionSubjectList, new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
                            .$tblDivision->getTblLevel()->getName().$tblDivision->getName())
                        , array('Subject' => 'Name',
                                'Teacher' => 'Name',
                                'Group'   => 'Gruppen',
                                'Option'  => 'Option'), true)
                );
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            ( $Count >= 1 ) ? $Content
                                : new LayoutColumn(new Warning('Keine Zuordnungen vorhanden'))
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Warning('Klasse nicht gefunden')
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyDivision($Id)
    {

        $Stage = new Stage('Klassengruppe', 'entfernen');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->setContent(Division::useService()->destroyDivision($tblDivision));
        } else {
            return $Stage.new Warning('Klassengruppe nicht gefunden!')
            .new Redirect('/Education/Lesson/Division/Create/LevelDivision');
        }
        return $Stage;
    }
}
