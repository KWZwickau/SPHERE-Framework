<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
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
use SPHERE\Common\Frontend\Icon\Repository\Minus;
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
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
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

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as &$tblDivision) {
                $tblDivision->Year = $tblDivision->getServiceTblYear()->getName();
                if ($tblDivision->getTblLevel()) {
                    $tblDivision->Level = $tblDivision->getTblLevel()->getName();
                    $tblDivision->SchoolType = $tblDivision->getTblLevel()->getServiceTblType()->getName();
                } else {
                    $tblDivision->Level = '';
                    $tblDivision->SchoolType = '';
                }

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
                                    ->appendFormButton(new Primary('Schulklasse hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Level, $Division
                            )
                        )
                    ), new Title('Schulklasse hinzufügen')
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
                                    ->appendFormButton(new Primary('Unterrichtsgruppe hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $SubjectGroup
                            )
                        )
                    ), new Title('Unterrichtsgruppe hinzufügen')
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


    public function frontendChangeSubjectGroup($Id, $SubjectGroup = null, $DivisionId, $DivisionSubjectId)
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
        $Stage->setContent(Division::useService()->changeSubjectGroup($this->formSubjectGroup($tblSubjectGroup)
            ->appendFormButton(new Primary('Änderung speichern'))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
            , $SubjectGroup, $Id, $DivisionId, $DivisionSubjectId));

        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $StudentId
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendStudentAdd($Id, $StudentId = null, $Remove = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {

            if ($tblDivision->getTblLevel()) {
                $Title = 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName());
            } else {
                $Title = 'der Klasse '.new Bold($tblDivision->getName());
            }

            $Stage = new Stage('Schüler', $Title);
            $Stage->setMessage('Liste aller Schüler die im Schuljahr '.$tblDivision->getServiceTblYear()->getName()
                .' noch keiner Klasse zugeordnet sind.');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));

            if ($tblDivision && null !== $StudentId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($StudentId) )) {
                if ($Remove) {
                    Division::useService()->removeStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Student/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Student/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }
            $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $tblDivisionStudentAll = false;
            if ($tblGroup) {

                $tblStudentList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Schüler

                if ($tblDivision->getTblLevel()) {
                    $tblDivisionList = Division::useService()->getDivisionByYear($tblDivision->getServiceTblYear());
                    if ($tblStudentList) {
                        foreach ($tblDivisionList as $Index => $tblDivision) {
                            if ($tblDivision->getTblLevel()) {
                            } else {
                                $tblDivisionList[$Index] = false;
                            }
                        }
                        $tblDivisionList = array_filter($tblDivisionList);

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
                            if (is_array($tblStudentList)) {
                                $tblDivisionStudentAll = $tblStudentList;
                            }
                        }
                    }
                } else {
                    $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                    if ($tblDivisionStudentList) {
                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                            function (TblPerson $invoiceA, TblPerson $invoiceB) {

                                return $invoiceA->getId() - $invoiceB->getId();
                            });
                    }
                    if (is_array($tblStudentList)) {
                        $tblDivisionStudentAll = $tblStudentList;
                    }
                }

            }
            $tblDivisionStudentActive = Division::useService()->getStudentAllByDivision($tblDivision);

            if (is_array($tblDivisionStudentActive) && is_array($tblDivisionStudentAll)) {
                $tblStudentAvailable = array_udiff($tblDivisionStudentAll, $tblDivisionStudentActive,
                    function (TblPerson $ObjectA, TblPerson $ObjectB) {

                        return $ObjectA->getId() - $ObjectB->getId();
                    }
                );
            } else {
                $tblStudentAvailable = $tblDivisionStudentAll;
            }


            /** @noinspection PhpUnusedParameterInspection */
            if (is_array($tblDivisionStudentActive)) {
                array_walk($tblDivisionStudentActive, function (TblPerson &$Entity) use (&$Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Danger('Entfernen', '/Education/Lesson/Division/Student/Add', new Minus(),
                            array(
                                'Id'        => $Id,
                                'StudentId' => $Entity->getId(),
                                'Remove'    => true
                            ))
                    );
                });
            }


            /** @noinspection PhpUnusedParameterInspection */
            if (isset( $tblDivisionStudentAll ) && !empty( $tblDivisionStudentAll )) {
                array_walk($tblDivisionStudentAll, function (TblPerson &$Entity) use ($Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new Success('Hinzufügen', '/Education/Lesson/Division/Student/Add', new Plus(),
                            array(
                                'Id'        => $Id,
                                'StudentId' => $Entity->getId()
                            ))
                    );
                });
            }


            $Stage->setContent(
//                new Info($tblPrivilege->getName())
//                .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('Schüler', 'Zugewiesen'),
                                ( empty( $tblDivisionStudentActive )
                                    ? new Warning('Keine Schüler zugewiesen')
                                    : new TableData($tblDivisionStudentActive, null,
                                        array('FirstName' => 'Vorname',
                                              'LastName'  => 'Nachname',
//                                              'Description' => 'Beschreibung',
                                              'Option'    => 'Optionen'))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Schüler', 'Verfügbar'),
                                ( empty( $tblStudentAvailable )
                                    ? new \SPHERE\Common\Frontend\Message\Repository\Info('Keine weiteren Schüler verfügbar')
                                    : new TableData($tblStudentAvailable, null,
                                        array('FirstName' => 'Vorname',
                                              'LastName'  => 'Nachname',
//                                              'Description' => 'Beschreibung',
                                              'Option'    => 'Optionen'))
                                )
                            ), 6)
                        ))
                    )
                )
            );

        } else {
            $Stage = new Stage('Schüler', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $TeacherId
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendTeacherAdd($Id, $TeacherId = null, $Remove = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {

            if ($tblDivision->getTblLevel()) {
                $Title = 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName());
            } else {
                $Title = 'der Klasse '.new Bold($tblDivision->getName());
            }

            $Stage = new Stage('Klassenlehrer', $Title);
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));

            if ($tblDivision && null !== $TeacherId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($TeacherId) )) {
                if ($Remove) {
                    Division::useService()->removeTeacherToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Teacher/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addTeacherToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Teacher/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }
            $tblGroup = Group::useService()->getGroupByMetaTable('TEACHER');
            if ($tblGroup) {
                $tblDivisionTeacherAll = Group::useService()->getPersonAllByGroup($tblGroup);
            } else {
                $tblDivisionTeacherAll = false;
            }
            $tblDivisionTeacherActive = Division::useService()->getTeacherAllByDivision($tblDivision);

            if (is_array($tblDivisionTeacherActive) && is_array($tblDivisionTeacherAll)) {
                $tblTeacherAvailable = array_udiff($tblDivisionTeacherAll, $tblDivisionTeacherActive,
                    function (TblPerson $ObjectA, TblPerson $ObjectB) {

                        return $ObjectA->getId() - $ObjectB->getId();
                    }
                );
            } else {
                $tblTeacherAvailable = $tblDivisionTeacherAll;
            }

            /** @noinspection PhpUnusedParameterInspection */
            if (is_array($tblDivisionTeacherActive)) {
                array_walk($tblDivisionTeacherActive, function (TblPerson &$Entity) use ($Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Danger('Entfernen', '/Education/Lesson/Division/Teacher/Add', new Minus(),
                            array(
                                'Id'        => $Id,
                                'TeacherId' => $Entity->getId(),
                                'Remove'    => true
                            ))
                    );
                });
            }


            /** @noinspection PhpUnusedParameterInspection */
            if (isset( $tblDivisionTeacherAll ) && !empty( $tblDivisionTeacherAll )) {
                array_walk($tblDivisionTeacherAll, function (TblPerson &$Entity) use ($Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new Success('Hinzufügen', '/Education/Lesson/Division/Teacher/Add', new Plus(),
                            array(
                                'Id'        => $Id,
                                'TeacherId' => $Entity->getId()
                            ))
                    );
                });
            }


            $Stage->setContent(
//                new Info($tblPrivilege->getName())
//                .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('Lehrer', 'Zugewiesen'),
                                ( empty( $tblDivisionTeacherActive )
                                    ? new Warning('Keine Lehrer zugewiesen')
                                    : new TableData($tblDivisionTeacherActive, null,
                                        array('FirstName' => 'Vorname',
                                              'LastName'  => 'Nachname',
//                                              'Description' => 'Beschreibung',
                                              'Option'    => 'Optionen'))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Lehrer', 'Verfügbar'),
                                ( empty( $tblTeacherAvailable )
                                    ? new \SPHERE\Common\Frontend\Message\Repository\Info('Keine weiteren Lehrer verfügbar')
                                    : new TableData($tblTeacherAvailable, null,
                                        array('FirstName' => 'Vorname',
                                              'LastName'  => 'Nachname',
//                                              'Description' => 'Beschreibung',
                                              'Option'    => 'Optionen'))
                                )
                            ), 6)
                        ))
                    )
                )
            );

        } else {
            $Stage = new Stage('Klassenlehrer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;


    }

    /**
     * @param      $Id
     * @param null $Subject
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendSubjectAdd($Id, $Subject = null, $Remove = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            if ($tblDivision->getTblLevel()) {
                $Title = 'der Klasse '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName());
            } else {
                $Title = 'der Klasse '.new Bold($tblDivision->getName());
            }
            $Stage = new Stage('Fächer', $Title);
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));

            if ($tblDivision && null !== $Subject && ( $Subject = Subject::useService()->getSubjectById($Subject) )) {
                if ($Remove) {
                    Division::useService()->removeSubjectToDivision($tblDivision, $Subject);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Subject/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addSubjectToDivision($tblDivision, $Subject);
                    $Stage->setContent(
                        new Redirect('/Education/Lesson/Division/Subject/Add', 0,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }

            $tblSubjectUsedList = Division::useService()->getSubjectAllByDivision($tblDivision);

            if ($tblSubjectUsedList) {
                foreach ($tblSubjectUsedList as $Index => $tblSubjectUsed) {

                }
            }


            $tblSubjectAll = Subject::useService()->getSubjectAll();

            if (is_array($tblSubjectUsedList)) {
                $tblSubjectAvailable = array_udiff($tblSubjectAll, $tblSubjectUsedList,
                    function (TblSubject $ObjectA, TblSubject $ObjectB) {

                        return $ObjectA->getId() - $ObjectB->getId();
                    }
                );
            } else {
                $tblSubjectAvailable = $tblSubjectAll;
            }


            /** @noinspection PhpUnusedParameterInspection */
            if (is_array($tblSubjectUsedList)) {
                array_walk($tblSubjectUsedList, function (TblSubject &$Entity) use ($Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Danger('Entfernen', '/Education/Lesson/Division/Subject/Add', new Minus(),
                            array(
                                'Id'      => $Id,
                                'Subject' => $Entity->getId(),
                                'Remove'  => true
                            ))
                    );
                }, $Id);
            }

            /** @noinspection PhpUnusedParameterInspection */
            if (isset( $tblSubjectAvailable )) {
                array_walk($tblSubjectAvailable, function (TblSubject &$Entity) use ($Id) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new Success('Hinzufügen', '/Education/Lesson/Division/Subject/Add', new Plus(),
                            array(
                                'Id'      => $Id,
                                'Subject' => $Entity->getId()
                            ))
                    );
                });
            }


            $Stage->setContent(
//                new Info($tblPrivilege->getName())
//                .
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('Fächer', 'Zugewiesen'),
                                ( empty( $tblSubjectUsedList )
                                    ? new Warning('Keine Fächer zugewiesen')
                                    : new TableData($tblSubjectUsedList, null,
                                        array('Name'        => 'Fach',
                                              'Description' => 'Beschreibung',
                                              'Option'      => 'Optionen'))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Fächer', 'Verfügbar'),
                                ( empty( $tblSubjectAvailable )
                                    ? new \SPHERE\Common\Frontend\Message\Repository\Info('Keine weiteren Fächer verfügbar')
                                    : new TableData($tblSubjectAvailable, null,
                                        array('Name'        => 'Fach',
                                              'Description' => 'Beschreibung',
                                              'Option'      => 'Optionen'))
                                )
                            ), 6)
                        ))
                    )
                )
            );

        } else {
            $Stage = new Stage('Fächer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param int  $Id
     * @param int  $DivisionSubjectId
     * @param null $Student
     *
     * @return Stage
     */
    public function frontendSubjectStudentAdd($Id, $DivisionSubjectId, $Student = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
            if ($tblDivisionSubject) {
                if ($tblDivision->getTblLevel()) {
                    $Titel = new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName());
                } else {
                    $Titel = new Bold($tblDivision->getName());
                }
                $Stage = new Stage('Schüler', 'der Klasse '.$Titel.' auswählen');
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                    array('Id' => $Id)));


                $Stage->setContent(new Layout(array(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    Division::useService()->addSubjectStudent(
                                        $this->formSubjectStudentAdd($tblDivision)
                                            ->appendFormButton(new Primary('Schühler auswählen'))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $DivisionSubjectId, $Student, $Id
                                    )
                                )
                            ), new Title('Schüler für das Fach '.new Bold($tblDivisionSubject->getServiceTblSubject()->getName())
                                .' und die Gruppe '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName()).' auswählen')
                        )
                    )
                ));
            } else {
                $Stage = new Stage('Schüler', 'auswählen');
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
                $Stage->setContent(new Warning('Fach nicht gefunden'));
            }


        } else {
            $Stage = new Stage('Schüler', 'auswählen');
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
                        $tblPerson->getLastName().', '.$tblPerson->getFirstName().' '.$tblPerson->getSecondName(),
                        $tblPerson->getId()
                    );
                }
            }
        } else {
            $tblStudentList = new Warning('Es sind noch keine Schüler für die Klasse hinterlegt');
        }
//        $tblGroupList = Division::useService()->getSubjectGroupAll();
//        $tblGroupList[] = new TblSubjectGroup('');
//
//        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
//        if ($tblDivisionSubjectList) {
//            foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
//                $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()->getName().' - '.$tblDivisionSubject->getServiceTblSubject()->getAcronym();
//            }
//        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
//                    new FormColumn(
//                        new Panel(' Fach:'
//                            , new SelectBox('DivisionSubject', '', array('Name' => $tblDivisionSubjectList)), Panel::PANEL_TYPE_INFO)
//                        , 4),
//                )),
//                new FormRow(array(
                    new FormColumn(
                        new Panel('Schüler', $tblStudentList, Panel::PANEL_TYPE_INFO)
                        , 6),

//                    new FormColumn(
//                        new Panel('Gruppen', new SelectBox('Group', '', array('{{ Name }} {{ Description }}' => $tblGroupList)), Panel::PANEL_TYPE_INFO)
//                        , 4)
                )),
            ))
        );
    }

    /**
     * @param      $Id
     * @param      $DivisionSubjectId
     * @param null $SubjectTeacher
     *
     * @return Stage
     */
    public function frontendSubjectTeacherAdd($Id, $DivisionSubjectId, $SubjectTeacher = null)
    {

        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
            if ($tblDivisionSubject) {

                if ($tblDivision->getTblLevel()) {
                    $Title = new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName());
                } else {
                    $Title = new Bold($tblDivision->getName());
                }
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $TableTitle = new Title('Fachlehrer für das Fach '.new Bold($tblDivisionSubject->getServiceTblSubject()->getName())
                        .' und die Gruppe '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName()).' auswählen');
                } else {
                    $TableTitle = new Title('Fachlehrer für das Fach '.new Bold($tblDivisionSubject->getServiceTblSubject()->getName()));
                }

                $Stage = new Stage('Fachlehrer der Klasse ', $Title.' auswählen');
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                    array('Id' => $tblDivision->getId())));
//                $Stage->setMessage('Blaue Fächer sind mindestens einem Lehrer zugeordnet');

                $Stage->setContent(new Layout(array(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    Division::useService()->addSubjectTeacher(
                                        $this->formSubjectTeacherAdd($tblDivisionSubject)
                                            ->appendFormButton(new Primary('Lehrer zuweisen'))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $SubjectTeacher, $Id, $DivisionSubjectId
                                    )
                                )
                            ), $TableTitle
                        )
                    )
                ));
            } else {
                $Stage = new Stage('Fachlehrer', 'auswählen');
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
                $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
            }
        } else {
            $Stage = new Stage('Fachlehrer', 'auswählen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return Form
     */
    public function formSubjectTeacherAdd(TblDivisionSubject $tblDivisionSubject)
    {

        $tblSubjectTeacherAllSelected = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
        if ($tblSubjectTeacherAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblSubjectTeacherAllSelected, function (TblSubjectTeacher &$tblSubjectTeacher) use (&$Global) {

                $Global->POST['SubjectTeacher'][$tblSubjectTeacher->getServiceTblPerson()->getId()] = $tblSubjectTeacher->getServiceTblPerson()->getId();
            });
            $Global->savePost();
        }

        $tblGroup = Group::useService()->getGroupByMetaTable('TEACHER');
        $tblTeacherList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Fächer der Klasse
        if ($tblTeacherList) {

            foreach ($tblTeacherList as $key => $row) {
                $name[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $tblTeacherList);

            foreach ($tblTeacherList as &$tblTeacher) {
                $tblTeacher = new CheckBox(
                    'SubjectTeacher['.$tblTeacher->getId().']',
                    $tblTeacher->getFullName(),
                    $tblTeacher->getId()
                );
            }

        } else {
            $tblTeacherList = new Warning('Es sind keine Lehrer hinterlegt');
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Lehrer', $tblTeacherList, Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
            ))
        );
    }

    /**
     * @param int        $Id
     * @param int        $DivisionSubjectId
     * @param null|array $Group
     *
     * @return Stage
     */
    public function frontendSubjectGroupAdd($Id, $DivisionSubjectId, $Group = null)
    {

        $Stage = new Stage('FachGruppe', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $Id)));
        if (Division::useService()->getDivisionById($Id) && Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getServiceTblSubject()) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            $tblSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getServiceTblSubject();

            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
            if ($tblDivisionSubjectList) {
                /** @var TblDivisionSubject $tblDivisionSubject */
                foreach ($tblDivisionSubjectList as $Index => $tblDivisionSubject) {
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()->getName();
                        $tblDivisionSubject->Description = $tblDivisionSubject->getTblSubjectGroup()->getDescription();
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            $tblDivisionSubject->GroupName = $tblDivisionSubject->getTblSubjectGroup()->getName();
                        } else {
                            $tblDivisionSubject->GroupName = '';
                        }
                        $tblDivisionSubject->Option = new Standard('Bearbeiten', '/Education/Lesson/Division/SubjectGroup/Change', new Pencil(),
                                array('Id'                => $tblDivisionSubject->getTblSubjectGroup()->getId(),
                                      'DivisionId'        => $tblDivision->getId(),
                                      'SubjectId'         => $tblSubject->getId(),
                                      'DivisionSubjectId' => $tblDivisionSubject->getId()))
                            .new Standard('Löschen', '/Education/Lesson/Division/SubjectGroup/Remove', new Remove(),
                                array('Id'                => $tblDivision->getId(),
                                      'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                      'SubjectGroupId'    => $tblDivisionSubject->getTblSubjectGroup()->getId()));

                    } else {
                        $tblDivisionSubjectList[$Index] = false;
                    }
                }
                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);
            }


            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($tblDivisionSubjectList, null,
                                    array('Name'        => 'Fach',
                                          'GroupName'   => 'Gruppe',
                                          'Description' => 'Beschreibung',
                                          'Option'      => 'Optionen',))
                            )
                        )
                    )
                )
                .Division::useService()->addSubjectToDivisionWithGroup(
                    $this->formSubjectGroupAdd($tblSubject)
                        ->appendFormButton(new Primary('Gruppe hinzufügen'))
                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                    , $tblDivision, $tblSubject, $Group, $DivisionSubjectId)
            );
        } else {
            $Stage->setContent(new Warning('Fach nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @param      $Id
     * @param      $SubjectId
     * @param      $DivisionId
     * @param      $DivisionSubjectId
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendSubjectGroupChange($Id, $SubjectId, $DivisionId, $DivisionSubjectId, $Group = null)
    {

        $Stage = new Stage('Gruppe', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectGroup/Add', new ChevronLeft(),
            array('Id'                => $Id,
                  'DivisionSubjectId' => $DivisionSubjectId)));

        $tblSubjectGroup = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup();
        if ($tblSubjectGroup) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Id'] ) && $DivisionSubjectId) {
                $Global->POST['Group']['Name'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getName();
                $Global->POST['Group']['Description'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getDescription();
                $Global->savePost();
            }

            $tblSubject = Subject::useService()->getSubjectById($SubjectId);

            $Stage->setContent(Division::useService()->changeSubjectGroup(
                $this->formSubjectGroupAdd($tblSubject)
                    ->appendFormButton(new Primary('Änderung speichern'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                , $Group, $Id, $DivisionId, $DivisionSubjectId)
            );
        } else {
            $Stage->setContent(new Warning('Keine Gruppe gefunden'));
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param $DivisionSubjectId
     * @param $SubjectGroupId
     *
     * @return Stage
     */
    public function frontendSubjectGroupRemove($Id, $DivisionSubjectId, $SubjectGroupId)
    {

        $Stage = new Stage('Gruppe', 'entfernen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $Id)));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($SubjectGroupId);

        if ($tblDivision) {
            if ($tblDivisionSubject) {
                if ($tblSubjectGroup) {
                    if (Division::useService()->removeSubjectGroup($tblSubjectGroup, $tblDivisionSubject)) {
                        Division::useService()->removeDivisionSubject($tblDivisionSubject);
                        $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Success('Gruppe erfolgreich entfernt')
                            .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $Id)));
                    } else {
                        $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Danger('Gruppe konnte nicht entfernt werden')
                            .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $Id)));
                    }
                } else {
                    $Stage->setContent(new Warning('Gruppe in der Klasse nicht gefunden'));
                }
            } else {
                $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
            }
        } else {
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }


        return $Stage;
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return Form
     */
    public function formSubjectGroupAdd(TblSubject $tblSubject)
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Group[Name]', '', 'Gruppenname')
                        , 6),
                    new FormColumn(
                        new TextField('Group[Description]', '', 'Beschreibung')
                        , 6),
                ))
                , new \SPHERE\Common\Frontend\Form\Repository\Title('Eine Gruppe für das Fach '.$tblSubject->getName().' erstellen'))
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
            $Stage->addButton(new Standard('Fächer', '/Education/Lesson/Division/Subject/Add',
                new Book(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Klassenlehrer', '/Education/Lesson/Division/Teacher/Add',
                new Person(), array('Id' => $tblDivision->getId())));
            $Stage->addButton(new Standard('Schüler', '/Education/Lesson/Division/Student/Add',
                new \SPHERE\Common\Frontend\Icon\Repository\Group(), array('Id' => $tblDivision->getId())));

            $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivisionStudent->FullName = $tblDivisionStudent->getFirstName().' '.
                        $tblDivisionStudent->getSecondName().' '.
                        $tblDivisionStudent->getLastName();

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
            } else {
                $tblDivisionStudentList = array();
            }
            $tblDivisionTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
            if ($tblDivisionTeacherList) {
                foreach ($tblDivisionTeacherList as &$tblDivisionTeacher) {
                    $tblDivisionTeacher = new LayoutColumn(
                        new Panel('Klassenlehrer', $tblDivisionTeacher->getFullName(), Panel::PANEL_TYPE_INFO
                        ), 4
                    );
                }
            } else {
                $tblDivisionTeacherList = new LayoutColumn(new Warning('Kein Klassenlehrer festgelegt'), 6);
            }

            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            $tblDivisionSubjectGroupList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            if ($tblDivisionSubjectGroupList) {
                foreach ($tblDivisionSubjectGroupList as $Index => $tblDivisionSubjectGroup) {
                    if ($tblDivisionSubjectGroup->getTblSubjectGroup()) {
                        if ($tblDivisionSubjectGroup->getTblSubjectGroup()) {
                            $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubjectGroup);
                            $teacherArray = array();
                            if ($tblTeacherList) {
                                /** @var TblPerson $Teacher */
                                foreach ($tblTeacherList as $Teacher) {
                                    $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
                                }
                            }
                            $tblDivisionSubjectGroup->SubjectGroup = new Panel($tblDivisionSubjectGroup->getServiceTblSubject()->getName(),
                                $tblDivisionSubjectGroup->getTblSubjectGroup()->getName(), Panel::PANEL_TYPE_INFO,
                                new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                    array('Id'                => $tblDivision->getId(),
                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId())));

                            $tblDivisionSubjectGroup->TeacherGroup = new Panel('Fachlehrer', $teacherArray, Panel::PANEL_TYPE_INFO,
                                new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                                    array('Id'                => $tblDivision->getId(),
                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId())));

                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectGroup);
                            $StudentGroupCount = 0;
                            if ($tblSubjectStudentList) {
                                foreach ($tblSubjectStudentList as &$tblSubjectStudent) {
                                    $StudentGroupCount = $StudentGroupCount + 1;
                                    $tblSubjectStudent = $tblSubjectStudent->getServiceTblPerson()->getFullName();
                                }
                            } else {
                                $tblSubjectStudentList = '';
                            }


                            $tblDivisionSubjectGroup->StudentGroup = new Panel($StudentGroupCount.' Schüler', $tblSubjectStudentList, Panel::PANEL_TYPE_INFO,
                                new Standard('Schüler', '/Education/Lesson/Division/SubjectStudent/Add', new Pencil(),
                                    array('Id'                => $tblDivision->getId(),
                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId())));
                        }
                    } else {
                        $tblDivisionSubjectGroupList[$Index] = false;
                    }
                }
                $tblDivisionSubjectGroupList = array_filter($tblDivisionSubjectGroupList);
            } else {
                $tblDivisionSubjectGroupList = array();
            }
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $Index => $tblDivisionSubject) {
                    if (!$tblDivisionSubject->getTblSubjectGroup()) {

                        $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
                        $teacherArray = array();
                        if ($tblTeacherList) {
                            /** @var TblPerson $Teacher */
                            foreach ($tblTeacherList as $Teacher) {
                                $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
                            }
                        }

                        $tblDivisionSubjectActiveList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(), $tblDivision);
                        $TeacherGroup = array();
                        if ($tblDivisionSubjectActiveList) {
                            /**@var TblDivisionSubject $tblDivisionSubjectActive */
                            foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
                                if ($tblDivisionSubjectActive->getTblSubjectGroup()) {
                                    $TempList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
                                    if ($TempList) {
                                        foreach ($TempList as $Temp)
                                            array_push($TeacherGroup, $Temp->getServiceTblPerson()->getFullName());
                                    }
                                }
                            }
                            $TeacherGroup = array_unique($TeacherGroup);
                        }
                        $tblDivisionSubject->Subject = new Panel($tblDivisionSubject->getServiceTblSubject()->getName(),
                            new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array('Id'                => $tblDivision->getId(),
                                      'DivisionSubjectId' => $tblDivisionSubject->getId())), Panel::PANEL_TYPE_INFO);

                        $tblDivisionSubject->Teacher = new Panel('Fachlehrer', $teacherArray, Panel::PANEL_TYPE_INFO,
                                new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                                    array('Id'                => $tblDivision->getId(),
                                          'DivisionSubjectId' => $tblDivisionSubject->getId()))).
                            ( ( !empty( $TeacherGroup ) ) ?
                                new Panel('Gruppenlehrer', $TeacherGroup) : null );

                        $tblDivisionSubject->Student = new Panel('Alle Schüler', 'aus der Klasse', Panel::PANEL_TYPE_INFO);
                    } else {
                        $tblDivisionSubjectList[$Index] = false;
                    }
                }

                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);
            } else {
                $tblDivisionSubjectList = array();
            }

            if ($tblDivision->getTblLevel()) {
                $TitleClass = new \SPHERE\Common\Frontend\Table\Repository\Title('Schüler in der Klasse '
                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
                $TitleSubject = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
                $TitleSubjectGroup = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer mit Gruppen der Klasse '
                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
            } else {
                $TitleClass = new \SPHERE\Common\Frontend\Table\Repository\Title('Schüler in der Klasse '
                    .$tblDivision->getName());
                $TitleSubject = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
                    .$tblDivision->getName());
                $TitleSubjectGroup = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer mit Gruppen der Klasse '
                    .$tblDivision->getName());
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                            new LayoutRow($tblDivisionTeacherList),
                        )
                    ),
                ))
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                    new TableData($tblDivisionStudentList, $TitleClass
                                        , array('FirstName' => 'Vorname',
                                                'LastName'  => 'Nachname',
//                                                          'Group'     => 'Zuweisung(en)',
//                                                          'Option'    => 'Option'
                                        ), false),
                                    new TableData($tblDivisionSubjectList, $TitleSubject
                                        , array('Subject' => 'Fach',
                                                'Teacher' => 'Fachlehrer',
                                                'Student' => 'Schüler',
                                        ), false))

                                , 6),
//                            new LayoutColumn(
//                                new TableData($tblDivisionSubjectList, new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
//                                        .$tblDivision->getTblLevel()->getName().$tblDivision->getName())
//                                    , array('Subject' => 'Fach',
//                                            'Teacher' => 'Fachlehrer',
//                                            'Student' => 'Schüler',
//                                    ), false)
//                                , 6),
                            new LayoutColumn(
                                new TableData($tblDivisionSubjectGroupList, $TitleSubjectGroup
                                    , array('SubjectGroup' => 'Fach',
                                            'TeacherGroup' => 'Gruppen/Fachlehrer',
                                            'StudentGroup' => 'Schüler',
                                    ), false)
                                , 6),
                        ))
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
    public function frontendSubjectTeacherShow($Id)
    {

        $Stage = new Stage('Lehrer', 'Auswahl');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(), array('Id' => $tblDivision->getId())));
//            $Stage->addButton(new Standard('Hinzufügen', '/Education/Lesson/Division/SubjectTeacher/Add', new Plus(), array('Id' => $tblDivision->getId())));

            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {

                    $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()->getName();
                    $tblDivisionSubject->Acronym = $tblDivisionSubject->getServiceTblSubject()->getAcronym();
                    $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/SubjectTeacher/Add',
                        new Plus(), array('Id'                => $Id,
                                          'DivisionTeacherId' => $tblDivisionSubject->getId()));


                    $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
                    $teacherString = new Danger('leer');
                    $teacherArray = array();
                    if ($tblTeacherList) {
                        /** @var TblPerson $Teacher */
                        foreach ($tblTeacherList as $Teacher) {
                            $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
                        }
                        $teacherString = implode(', ', $teacherArray);
                    }
                    $tblDivisionSubject->Teacher = $teacherString;

                }
            }

//            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
//            $Content = array();
//            $Count = 0;
//            if ($tblDivisionSubjectList) {
//                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {
//                    $Count++;
//                    $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
//                    $teacherString = new \SPHERE\Common\Frontend\Text\Repository\Warning('leer');
//                    $teacherArray = array();
//                    if ($tblTeacherList) {
//                        /** @var TblPerson $Teacher */
//                        foreach ($tblTeacherList as $Teacher) {
//                            $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
//                        }
//                        $teacherString = implode(', ', $teacherArray);
//                    }
//                    $tblDivisionSubject->Teacher = $teacherString;
//                    $tblDivisionSubject->Subject = $tblDivisionSubject->getServiceTblSubject()->getName();
//
//                    $tblSubjectGroupList = Division::useService()->getSubjectGroupByDivisionSubject($tblDivisionSubject);
//
//                    $subjectGroupString = new \SPHERE\Common\Frontend\Text\Repository\Warning('keine');
//                    $subjectGroupArray = array();
//                    if ($tblSubjectGroupList) {
//                        /** @var TblSubjectGroup $tblSubjectGroup */
//                        foreach ($tblSubjectGroupList as $tblSubjectGroup) {
//                            if ($tblSubjectGroup) {
//                                $subjectGroupArray[] = $tblSubjectGroup->getName().' '.$tblSubjectGroup->getDescription();
//                            }
//                        }
//                        if (!empty( $subjectGroupArray )) {
//                            $subjectGroupString = implode(', ', $subjectGroupArray);
//                        }
//                    }
//                    $tblDivisionSubject->Group = $subjectGroupString;
//
//                    if ($teacherString != new \SPHERE\Common\Frontend\Text\Repository\Warning('leer'))
//                        $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/SubjectTeacher/Remove', new Remove(), array('Id'                => $tblDivision->getId(),
//                                                                                                                                               'DivisionSubjectId' => $tblDivisionSubject->getId()
//                        ), 'Zuweisung entfernen');
//                }
//                $Content[] = new LayoutColumn(
//                    new TableData($tblDivisionSubjectList, new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
//                            .$tblDivision->getTblLevel()->getName().$tblDivision->getName())
//                        , array('Subject' => 'Name',
//                                'Teacher' => 'Name',
//                                'Group'   => 'Gruppen',
//                                'Option'  => 'Option'), true)
//                );
//            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($tblDivisionSubjectList, null, array('Acronym' => 'Kürzel',
                                                                                   'Name'    => 'Name',
                                                                                   'Teacher' => 'Lehrer',
                                                                                   'Option'  => 'Lehrer Zuweisung'))
                            )
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
