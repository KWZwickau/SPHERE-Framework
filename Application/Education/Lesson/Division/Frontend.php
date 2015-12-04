<?php
namespace SPHERE\Application\Education\Lesson\Division;

use Doctrine\Common\Cache\ArrayCache;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
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
use SPHERE\Common\Frontend\Form\Repository\Field\InputCheckBox;
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
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
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
                    $tblDivision->ClassGroup = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
                    $tblDivision->SchoolType = $tblDivision->getTblLevel()->getServiceTblType()->getName();
                } else {
                    $tblDivision->ClassGroup = $tblDivision->getName();
                    $tblDivision->SchoolType = '';
                }

                $tblPeriodAll = $tblDivision->getServiceTblYear()->getTblPeriodAll();
                $Period = array();
                if ($tblPeriodAll) {
                    foreach ($tblPeriodAll as $tblPeriod) {
                        $Period[] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    }
                    $tblDivision->Period = implode('<br/>', $Period);
                } else {
                    $tblDivision->Period = 'fehlt';
                }


                $tblDivision->Option = new Standard('', '/Education/Lesson/Division/Change/Division', new Pencil(),
                        array('Id' => $tblDivision->getId()))
                    .new Standard('', '/Education/Lesson/Division/Destroy/Division', new Remove(),
                        array('Id' => $tblDivision->getId()));
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblDivisionAll, null,
                                array('Year'        => 'Schuljahr',
                                      'Period'      => 'Zeitraum',
                                      'SchoolType'  => 'Schultyp',
                                      'ClassGroup'  => 'Schulklasse',
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
//        $Global->POST['Level']['Name'] = '';
//        $Global->savePost();

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
                                new InputCheckBox('Level[Check]', 'Stufenübergreifende Klassengruppe anlegen', 1, array('Level[Name]',
                                    'Level[Type]')),
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
     * @param      $Id
     * @param null $StudentId
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendAddStudent($Id, $StudentId = null, $Remove = null)
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

                        if ($tblDivisionList) {
                            foreach ($tblDivisionList as $tblSingleDivision) {
                                $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblSingleDivision);
                                if ($tblSingleDivision->getTblLevel() && $tblDivisionStudentList) {
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
    public function frontendAddTeacher($Id, $TeacherId = null, $Remove = null)
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
                    Division::useService()->addDivisionTeacher($tblDivision, $tblPerson);
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
                                        array('FirstName'   => 'Vorname',
                                              'LastName'    => 'Nachname',
                                              'Description' => 'Beschreibung',
                                              'Option'      => 'Optionen'))
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
    public function frontendAddSubject($Id, $Subject = null, $Remove = null)
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
                                        array('Acronym'     => 'Kürzel',
                                              'Name'        => 'Fach',
                                              'Description' => 'Beschreibung',
                                              'Option'      => 'Optionen'))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Fächer', 'Verfügbar'),
                                ( empty( $tblSubjectAvailable )
                                    ? new \SPHERE\Common\Frontend\Message\Repository\Info('Keine weiteren Fächer verfügbar')
                                    : new TableData($tblSubjectAvailable, null,
                                        array('Acronym'     => 'Kürzel',
                                              'Name'        => 'Fach',
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
    public function frontendAddSubjectStudent($Id, $DivisionSubjectId, $Student = null)
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
                $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning('"Schüler in Gelb"')
                    .' sind bereits in einer anderen Gruppe in diesem Fach angelegt.');


                $Stage->setContent(new Layout(array(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    Division::useService()->addSubjectStudent(
                                        $this->formSubjectStudentAdd($tblDivisionSubject)
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
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return Form
     */
    public function formSubjectStudentAdd(TblDivisionSubject $tblDivisionSubject)
    {

        $tblSubjectStudentAllSelected = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
        if ($tblSubjectStudentAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblSubjectStudentAllSelected, function (TblSubjectStudent &$tblSubjectStudent) use (&$Global) {

                $Global->POST['Student'][$tblSubjectStudent->getServiceTblPerson()->getId()] = $tblSubjectStudent->getServiceTblPerson()->getId();
            });
            $Global->savePost();
        }

        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionSubject->getTblDivision());  // Alle Schüler der Klasse
        if ($tblStudentList) {

            if ($tblStudentList) {
                foreach ($tblStudentList as $key => $row) {
                    $name[$key] = strtoupper($row->getLastName());
                    $firstName[$key] = strtoupper($row->getFirstName());
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $tblStudentList);

                $tblDivisionSubjectControlList = Division::useService()->
                getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(), $tblDivisionSubject->getTblDivision());
                if ($tblDivisionSubjectControlList) {
                    /** @var TblDivisionSubject $tblDivisionSubjectControl */
                    $PersonId = array();
                    foreach ($tblDivisionSubjectControlList as $tblDivisionSubjectControl) {
                        if ($tblDivisionSubjectControl->getId() !== $tblDivisionSubject->getId()) {
                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectControl);
                            if ($tblSubjectStudentList) {
                                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                    $PersonId[] = $tblSubjectStudent->getServiceTblPerson()->getId();
                                }
                            }
                        }
                    }
                }

                foreach ($tblStudentList as &$tblPerson) {
                    $trigger = false;
                    if (isset( $PersonId )) {
                        foreach ($PersonId as $Person) {

                            if ($Person === $tblPerson->getId()) {
                                $trigger = true;
                            }
                        }
                    }
                    $tblPerson = new CheckBox(
                        'Student['.$tblPerson->getId().']',
                        ( ( $trigger ) ? new \SPHERE\Common\Frontend\Text\Repository\Warning($tblPerson->getLastName().', '.$tblPerson->getFirstName().' '.$tblPerson->getSecondName())
                            : $tblPerson->getLastName().', '.$tblPerson->getFirstName().' '.$tblPerson->getSecondName() )
                        ,
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
    public function frontendAddSubjectTeacher($Id, $DivisionSubjectId, $SubjectTeacher = null)
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
    public function frontendAddSubjectGroup($Id, $DivisionSubjectId, $Group = null)
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
                ( ( !empty( $tblDivisionSubjectList ) ) ?
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new TableData($tblDivisionSubjectList, null,
                                        array('Name'        => 'Fach',
                                              'GroupName'   => 'Gruppe',
                                              'Description' => 'Beschreibung',
                                              'Option'      => 'Optionen',), false)
                                )
                            ), new Title('Vorhandene Gruppen')
                        )
                    ) : null )
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
    public function frontendChangeSubjectGroup($Id, $SubjectId, $DivisionId, $DivisionSubjectId, $Group = null)
    {

        $Stage = new Stage('Gruppe', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectGroup/Add', new ChevronLeft(),
            array('Id'                => $DivisionId,
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
    public function frontendRemoveSubjectGroup($Id, $DivisionSubjectId, $SubjectGroupId)
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

        if ($tblDivision->getTblLevel()) {
            $PanelShow = new Panel($tblDivision->getServiceTblYear()->getName()
                .' - '.$tblDivision->getTblLevel()->getName().$tblDivision->getName()
                , $tblDivision->getTblLevel()->getServiceTblType()->getName()
                , Panel::PANEL_TYPE_INFO);
        } else {
            $PanelShow = new Panel($tblDivision->getServiceTblYear()->getName()
                .' - '.$tblDivision->getName()
                , null
                , Panel::PANEL_TYPE_INFO);
        }

        if ($tblDivision) {
            $Info = new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelShow, 4
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
    public function frontendShowDivision($Id)
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
            $StudentTableCount = 0;
            $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivisionStudent->FullName = $tblDivisionStudent->getFirstName().' '.
                        $tblDivisionStudent->getSecondName().' '.
                        $tblDivisionStudent->getLastName();
                    $StudentTableCount = $StudentTableCount + 1;

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
                    $tblDivisionTeacher = new Panel('Klassenlehrer', $tblDivisionTeacher->getFullName(), Panel::PANEL_TYPE_INFO);
                }
            } else {

                $tblDivisionTeacherList = new Warning('Kein Klassenlehrer festgelegt');
            }

//            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
//            $tblDivisionSubjectGroupList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
//            if ($tblDivisionSubjectGroupList) {
//                foreach ($tblDivisionSubjectGroupList as $Index => $tblDivisionSubjectGroup) {
//                    if ($tblDivisionSubjectGroup->getTblSubjectGroup()) {
//                        if ($tblDivisionSubjectGroup->getTblSubjectGroup()) {
//                            $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubjectGroup);
//                            $teacherArray = array();
//                            if ($tblTeacherList) {
//                                /** @var TblPerson $Teacher */
//                                foreach ($tblTeacherList as $Teacher) {
//                                    $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
//                                }
//                            }
//                            $tblDivisionSubjectGroup->SubjectGroup = new Panel($tblDivisionSubjectGroup->getServiceTblSubject()->getName(),
//                                $tblDivisionSubjectGroup->getTblSubjectGroup()->getName(), Panel::PANEL_TYPE_INFO,
//                                new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
//                                    array('Id'                => $tblDivision->getId(),
//                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId())));
//
//                            $tblDivisionSubjectGroup->TeacherGroup = new Panel('Fachlehrer', $teacherArray, Panel::PANEL_TYPE_INFO,
//                                new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
//                                    array('Id'                => $tblDivision->getId(),
//                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId())));
//
//                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectGroup);
//                            $StudentGroupCount = 0;
//                            if ($tblSubjectStudentList) {
//                                foreach ($tblSubjectStudentList as &$tblSubjectStudent) {
//                                    $StudentGroupCount = $StudentGroupCount + 1;
//                                    $tblSubjectStudent = $tblSubjectStudent->getServiceTblPerson()->getFullName();
//                                }
//                            } else {
//                                $tblSubjectStudentList = '';
//                            }
//
//
//                            $tblDivisionSubjectGroup->StudentGroup = (new Accordion())
//                            ->addItem('Enthaltene Schüler',new Panel($StudentGroupCount.' Schüler', $tblSubjectStudentList, Panel::PANEL_TYPE_INFO,
//                                new Standard('Schüler', '/Education/Lesson/Division/SubjectStudent/Add', new Pencil(),
//                                    array('Id'                => $tblDivision->getId(),
//                                          'DivisionSubjectId' => $tblDivisionSubjectGroup->getId()))), false);
//                        }
//                    } else {
//                        $tblDivisionSubjectGroupList[$Index] = false;
//                    }
//                }
//                $tblDivisionSubjectGroupList = array_filter($tblDivisionSubjectGroupList);
//            } else {
//                $tblDivisionSubjectGroupList = array();
//            }
//            if ($tblDivisionSubjectList) {
//                foreach ($tblDivisionSubjectList as $Index => $tblDivisionSubject) {
//                    if (!$tblDivisionSubject->getTblSubjectGroup()) {
//
//                        $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
//                        $teacherArray = array();
//                        if ($tblTeacherList) {
//                            /** @var TblPerson $Teacher */
//                            foreach ($tblTeacherList as $Teacher) {
//                                $teacherArray[] = $Teacher->getFirstName().' '.$Teacher->getLastName();
//                            }
//                        }
//
//                        $tblDivisionSubjectActiveList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(), $tblDivision);
//                        $TeacherGroup = array();
//                        if ($tblDivisionSubjectActiveList) {
//                            /**@var TblDivisionSubject $tblDivisionSubjectActive */
//                            foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
//                                if ($tblDivisionSubjectActive->getTblSubjectGroup()) {
//                                    $TempList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
//                                    if ($TempList) {
//                                        foreach ($TempList as $Temp)
//                                            array_push($TeacherGroup, $Temp->getServiceTblPerson()->getFullName());
//                                    }
//                                }
//                            }
//                            $TeacherGroup = array_unique($TeacherGroup);
//                        }
//                        $tblDivisionSubject->Subject = new Panel($tblDivisionSubject->getServiceTblSubject()->getName(),
//                            new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
//                                array('Id'                => $tblDivision->getId(),
//                                      'DivisionSubjectId' => $tblDivisionSubject->getId())), Panel::PANEL_TYPE_INFO);
//
//                        $tblDivisionSubject->Teacher = new Panel('Fachlehrer', $teacherArray, Panel::PANEL_TYPE_INFO,
//                                new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
//                                    array('Id'                => $tblDivision->getId(),
//                                          'DivisionSubjectId' => $tblDivisionSubject->getId()))).
//                            ( ( !empty( $TeacherGroup ) ) ?
//                                new Panel('Gruppenlehrer', $TeacherGroup) : null );
//
//                        $StudentCount = 0;
//                        $tblDivisionSubjectStudentList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(), $tblDivision);
//                        if (count($tblDivisionSubjectStudentList) >= 2) {
//                            foreach ($tblDivisionSubjectStudentList as $tblDivisionSubjectStudent) {
//                                $tblSubjectStudentCountList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectStudent);
//                                if (is_array($tblSubjectStudentCountList)) {
//                                    $StudentCount = $StudentCount + count($tblSubjectStudentCountList);
//                                }
//                            }
//                        }
//
//                        if ($StudentCount >= 1 && $StudentCount < $StudentTableCount) {
//                            $tblDivisionSubject->Student = new Panel($StudentCount.' Schüler', 'aus der Klasse', Panel::PANEL_TYPE_INFO);
//                        } else {
//                            $tblDivisionSubject->Student = new Panel('Alle Schüler', 'aus der Klasse', Panel::PANEL_TYPE_INFO);
//                        }
//                    } else {
//                        $tblDivisionSubjectList[$Index] = false;
//                    }
//                }
//
//                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);
//            } else {
//                $tblDivisionSubjectList = array();
//            }
//
//            if ($tblDivision->getTblLevel()) {
//                $TitleClass = new \SPHERE\Common\Frontend\Table\Repository\Title('Schüler in der Klasse '
//                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
//                $TitleSubject = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
//                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
//                $TitleSubjectGroup = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer mit Gruppen der Klasse '
//                    .$tblDivision->getTblLevel()->getName().$tblDivision->getName());
//            } else {
//                $TitleClass = new \SPHERE\Common\Frontend\Table\Repository\Title('Schüler in der Klasse '
//                    .$tblDivision->getName());
//                $TitleSubject = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer der Klasse '
//                    .$tblDivision->getName());
//                $TitleSubjectGroup = new \SPHERE\Common\Frontend\Table\Repository\Title('Fächer mit Gruppen der Klasse '
//                    .$tblDivision->getName());
//            }

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $tblTestDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);

            if ($tblTestDivisionSubjectList) {
                foreach ($tblTestDivisionSubjectList as $Index => $tblTestDivisionSubject) {
                    if ($tblTestDivisionSubject->getTblSubjectGroup()) {
                        $tblTestDivisionSubjectList[$Index] = false;
                    }
                }
                $tblTestDivisionSubjectList = array_filter($tblTestDivisionSubjectList);

                /** @var TblDivisionSubject $row */
                foreach ($tblTestDivisionSubjectList as $key => $row) {
                    $name[$key] = strtoupper($row->getServiceTblSubject()->getName());
                    $Acronym[$key] = strtoupper($row->getServiceTblSubject()->getAcronym());
                }
                array_multisort($name, SORT_ASC, $Acronym, SORT_ASC, $tblTestDivisionSubjectList);


                /** @var TblDivisionSubject $tblTestDivisionSubject */
                foreach ($tblTestDivisionSubjectList as &$tblTestDivisionSubject) {

                    $tblTestDivisionSubject->GroupTeacher = '';
//                    $tblTestDivisionSubject->Student = new Panel('Alle Schüler','aus der Klasse',Panel::PANEL_TYPE_INFO);
                    $tblTestDivisionSubject->Student = '';

                    $tblTestDivisionSubject->Subject = new Panel($tblTestDivisionSubject->getServiceTblSubject()->getName(),
                        $StudentTableCount.' / '.$StudentTableCount.' Schüler aus der Klasse', Panel::PANEL_TYPE_INFO);

                    $tblDivisionTeachersList = Division::useService()->getSubjectTeacherByDivisionSubject($tblTestDivisionSubject);
                    $TeacherArray = array();
                    if ($tblDivisionTeachersList) {
                        foreach ($tblDivisionTeachersList as $tblDivisionTeachers) {
                            $TeacherArray[] = $tblDivisionTeachers->getServiceTblPerson()->getFullName();
                        }
                    }
                    $SubjectTeacherPanel = new Panel('Fachlehrer', $TeacherArray, Panel::PANEL_TYPE_INFO,
                        new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                            array('Id'                => $tblDivision->getId(),
                                  'DivisionSubjectId' => $tblTestDivisionSubject->getId())));

                    $tblDivisionSubjectTestList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblTestDivisionSubject->getServiceTblSubject(), $tblTestDivisionSubject->getTblDivision());

                    if (count($tblDivisionSubjectTestList) > 1) {
                        $Grouparray = array();
                        $TeacherPanelArray = '';
                        $TeacherGroupList = array(new Bold('Gruppenlehrer:'));
                        $StudentsGroupCount = 0;
                        $StudentPanel = '';
                        /** @var TblDivisionSubject $tblDivisionSubjectTest */
                        foreach ($tblDivisionSubjectTestList as $tblDivisionSubjectTest) {
                            if ($tblDivisionSubjectTest->getTblSubjectGroup()) {
                                $TeachersArray = array();
                                $StudentArray = array();
                                $tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectTest);
                                if ($tblSubjectTeacherList) {
                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                        $TeachersArray[] = $tblSubjectTeacher->getServiceTblPerson()->getFullName();
                                    }
                                }
                                if ($TeachersArray) {
                                    foreach ($TeachersArray as $Teachers) {
                                        $TeacherGroupList[] = $Teachers;
                                    }
                                }
                                $TeacherPanelArray .= New Panel($tblDivisionSubjectTest->getTblSubjectGroup()->getName(),
                                    $TeachersArray, Panel::PANEL_TYPE_INFO,
                                    new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                                        array('Id'                => $tblDivision->getId(),
                                              'DivisionSubjectId' => $tblDivisionSubjectTest->getId())));
                                $Grouparray[] = $tblDivisionSubjectTest->getTblSubjectGroup()->getName();

                                $tblSubjectStudentsList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectTest);
                                if ($tblSubjectStudentsList) {
                                    /** @var TblSubjectStudent $tblSubjectStudents */
                                    foreach ($tblSubjectStudentsList as $tblSubjectStudents) {
                                        $StudentArray[] = $tblSubjectStudents->getServiceTblPerson()->getFullName();
                                        $StudentsGroupCount = $StudentsGroupCount + 1;
                                    }
                                }
                                $StudentPanel .= new Panel($tblDivisionSubjectTest->getTblSubjectGroup()->getName(), $StudentArray, Panel::PANEL_TYPE_INFO,
                                    new Standard('Schüler', '/Education/Lesson/Division/SubjectStudent/Add', new Pencil(),
                                        array('Id'                => $tblDivision->getId(),
                                              'DivisionSubjectId' => $tblDivisionSubjectTest->getId())));
                            }
                        }

//                        $StudentPanel = new Panel($StudentsGroupCount.' Schüler', 'der Klasse in '.$tblDivisionSubjectTest->getServiceTblSubject()->getName(), Panel::PANEL_TYPE_INFO).$StudentPanel;
                        if ($StudentTableCount > $StudentsGroupCount) {
                            $tblTestDivisionSubject->Subject = new Panel($tblTestDivisionSubject->getServiceTblSubject()->getName(),
                                new \SPHERE\Common\Frontend\Text\Repository\Warning($StudentsGroupCount.' / '.$StudentTableCount.' Schüler aus der Klasse'), Panel::PANEL_TYPE_INFO);
                        }

//                        $TeacherGroupList = array_unique($TeacherGroupList);
//                        if (count($TeacherGroupList) > 1) {
//                            $TeacherGroupPanel = new Listing($TeacherGroupList);
//                        } else {
//                            $TeacherGroupPanel = '';
//                        }


                        $tblTestDivisionSubject->Group = new Panel('Gruppen', $Grouparray, Panel::PANEL_TYPE_INFO,
                            new Standard('Gruppen', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array('Id'                => $tblDivision->getId(),
                                      'DivisionSubjectId' => $tblTestDivisionSubject->getId())));

                        $tblTestDivisionSubject->GroupTeacher = $TeacherPanelArray;
                        $tblTestDivisionSubject->SubjectTeacher = $SubjectTeacherPanel; //.$TeacherGroupPanel;
                        $tblTestDivisionSubject->Student = (new Accordion())
                            ->addItem('Enthaltene Schüler', $StudentPanel, false);
                    } else {
                        $tblTestDivisionSubject->Group = new Panel('Gruppen',
                            new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array('Id'                => $tblDivision->getId(),
                                      'DivisionSubjectId' => $tblTestDivisionSubject->getId())), Panel::PANEL_TYPE_INFO);

                        $tblTestDivisionSubject->SubjectTeacher = $SubjectTeacherPanel;
                    }
                }

            } else {
                $tblTestDivisionSubjectList = array();
            }


            if ($tblDivision->getTblLevel()) {
                $TitleClass = 'Schüler in der Klasse '.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
            } else {
                $TitleClass = 'Schüler in der Klasse '.$tblDivision->getName();
            }

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $Stage->setContent(
//                new Layout(array(
//                    new LayoutGroup(array(
//                            new LayoutRow($tblDivisionTeacherList),
//                        )
//                    ),
//                ))
//                .new Layout(
//                    new LayoutGroup(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                    new TableData($tblDivisionStudentList, $TitleClass
//                                        , array('FirstName' => 'Vorname',
//                                                'LastName'  => 'Nachname',
////                                                'Group'     => 'Zuweisung(en)',
////                                                'Option'    => 'Option'
//                                        ), false),
//                                    new TableData($tblDivisionSubjectList, $TitleSubject
//                                        , array('Subject' => 'Fach',
//                                                'Teacher' => 'Fachlehrer',
//                                                'Student' => 'Schüler',
//                                        ), false))
//
//                                , 6),
//                            new LayoutColumn(
//                                new TableData($tblDivisionSubjectGroupList, $TitleSubjectGroup
//                                    , array('SubjectGroup' => 'Fach',
//                                            'TeacherGroup' => 'Gruppen/Fachlehrer',
//                                            'StudentGroup' => 'Schüler',
//                                    ), false)
//                                , 6),
//                        ))
//                    )).
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                ( ( !empty( $tblDivisionStudentList ) ) ?
                                    new TableData($tblDivisionStudentList, null
                                        , array('FirstName' => 'Vorname',
                                                'LastName'  => 'Nachname',
                                        ), false)
                                    : new Warning('Keine Schüer der Klasse zugewiesen') )
                            ,
                            ), 6),
                            new LayoutColumn($tblDivisionTeacherList, 5)
                        )), new Title($TitleClass)
                    )
                ).
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                ( ( !empty( $tblTestDivisionSubjectList ) ) ?
                                    new TableData($tblTestDivisionSubjectList, null,
                                        array('Subject'        => 'Fach',
                                              'SubjectTeacher' => 'Fachlehrer',
                                              'Group'          => 'Gruppen',
                                              'GroupTeacher'   => 'Gruppenlehrer',
                                              'Student'        => 'Gruppen Schüler',
                                        ), false)
                                    :
                                    new Warning('Keine Fächer der Klasse zugewiesen') )
                            )
                        )
                    )
                )
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
    public function frontendShowSubjectTeacher($Id)
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
