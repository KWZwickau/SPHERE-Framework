<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
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
     * @param null $Year
     *
     * @return Stage
     */
    public function frontendCreateLevelDivision($Level = null, $Division = null, $Year = null)
    {

        $Stage = new Stage('Klassen', 'Aktuelle Übersicht');

        $DivisionList = array();
        if (isset( $Year ) && $Year !== '0') {
            $tblYear = Term::useService()->getYearById($Year);
            $TempList = Division::useService()->getDivisionByYear($tblYear);
            if ($TempList) {
                foreach ($TempList as $Temp) {
                    $DivisionList[] = $Temp;
                }
            }
        } else {
            $tblYearList = Term::useService()->getYearByNow();
            if (!empty( $tblYearList )) {
                foreach ($tblYearList as $tblYear) {
                    $TempList = Division::useService()->getDivisionByYear($tblYear);
                    if ($TempList) {
                        foreach ($TempList as $Temp) {
                            $DivisionList[] = $Temp;
                        }
                    }
                }
            }
        }
        if (isset( $Year ) && $Year !== '0') {
            $tblYear = Term::useService()->getYearById($Year);
            if ($tblYear) {
                $Stage->setDescription('Übersicht '.new \SPHERE\Common\Frontend\Text\Repository\Info(new Bold($tblYear->getName())));
            }
        }

        $Stage->addButton(
            new Standard('Aktuelle Übersicht',
                new Route(__NAMESPACE__), new PersonGroup())
        );

        $YearAll = Term::useService()->getYearAllSinceYears(2);
        if (!empty( $YearAll )) {
            foreach ($YearAll as $key => $row) {
                $name[$key] = strtoupper($row->getName());
            }
            array_multisort($name, SORT_ASC, $YearAll);

            /** @noinspection PhpUnusedParameterInspection */
            array_walk($YearAll, function (TblYear &$tblYear) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblYear->getName(),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Year' => $tblYear->getId()
                        ), $tblYear->getDescription())
                );
            });
        }

        $tblDivisionAll = $DivisionList;

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Temp['Year'] = $tblDivision->getServiceTblYear()->getName();
                if ($tblDivision->getTblLevel()) {
                    $Temp['ClassGroup'] = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
                    $Temp['SchoolType'] = $tblDivision->getTblLevel()->getServiceTblType()->getName();
                } else {
                    $Temp['ClassGroup'] = $tblDivision->getName();
                    $Temp['SchoolType'] = '';
                }

                $tblPeriodAll = $tblDivision->getServiceTblYear()->getTblPeriodAll();
                $Period = array();
                if ($tblPeriodAll) {
                    foreach ($tblPeriodAll as $tblPeriod) {
                        $Period[] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    }
                    $Temp['Period'] = new Listing($Period);
                } else {
                    $Temp['Period'] = 'fehlt';
                }

                $SubjectUsedCount = Division::useService()->countDivisionSubjectUsedByDivision($tblDivision);
                $Temp['Description'] = $tblDivision->getDescription();
                $Temp['StudentList'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                $Temp['TeacherList'] = Division::useService()->countDivisionTeacherAllByDivision($tblDivision);
                $SubjectCount = Division::useService()->countDivisionSubjectAllByDivision($tblDivision);

                if ($SubjectUsedCount > 1) {
                    $Temp['SubjectList'] = $SubjectCount
                        .new PullRight(new Small(new Small(new Muted('('.new Danger($SubjectUsedCount).') Fachlehrer fehlen'))));
                } elseif ($SubjectUsedCount == 1) {
                    $Temp['SubjectList'] = $SubjectCount
                        .new PullRight(new Small(new Small(new Muted('('.new Danger($SubjectUsedCount).') Fachlehrer fehlt'))));
                } else {
                    $Temp['SubjectList'] = $SubjectCount;
                }
                $Temp['Option'] = new Standard('&nbsp;Klassenansicht', '/Education/Lesson/Division/Show',
                        new EyeOpen(), array('Id' => $tblDivision->getId()), 'Klasse einsehen')
                    .new Standard('', '/Education/Lesson/Division/Change/Division', new Pencil(),
                        array('Id' => $tblDivision->getId()), 'Beschreibung bearbeiten')
                    .new Standard('', '/Education/Lesson/Division/Destroy/Division', new Remove(),
                        array('Id' => $tblDivision->getId()), 'Klasse entfernen');

                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Year'        => 'Schuljahr',
                                    'Period'      => 'Zeitraum',
                                    'SchoolType'  => 'Schultyp',
                                    'ClassGroup'  => 'Schulklasse',
                                    'Description' => 'Beschreibung',
                                    'StudentList' => 'Schüler',
                                    'TeacherList' => 'Klassenlehrer',
                                    'SubjectList' => 'Fächer',
                                    'Option'      => '',
                                )
                            )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->createLevelDivision(
                                    $this->formLevelDivision()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Level, $Division
                                )
                            ))
                    ), new Title(new PlusSign().' Hinzufügen')
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
            $Global->POST['Division']['Year'] = ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : 0 );
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblYearAll = Term::useService()->getYearAllSinceYears(0);

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klassenstufe',
                            array(
                                new SelectBox('Level[Type]', 'Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education()),
                                new CheckBox('Level[Check]', 'jahrgangübergreifende Klasse anlegen', 1, array(
                                    'Level[Name]'
                                )),
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
            if ($tblDivision->getTblLevel()) {
                $Stage->setMessage('Liste aller Schüler die im Schuljahr '.$tblDivision->getServiceTblYear()->getName()
                    .' noch keiner Klasse zugeordnet sind.');
            } else {
                $Stage->setMessage('Liste aller Schüler im Schuljahr '.$tblDivision->getServiceTblYear()->getName().'.');
            }

            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));

            if ($tblDivision && null !== $StudentId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($StudentId) )) {
                if ($Remove) {
                    Division::useService()->removeStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich entfernt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich hinzugefügt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
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

                    $Entity->Name = $Entity->getFullName();
                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($Entity);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty( $tblAddressAll )) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }
                    if (isset( $tblAddress ) && $tblAddress) {
                        $Entity->Address = $tblAddress;
                    } else {
                        $Entity->Address = new Warning('Keine Adresse hinterlegt');
                    }

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                            '/Education/Lesson/Division/Student/Add', new Minus(),
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

                    $Entity->Name = $Entity->getFullName();
                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($Entity);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty( $tblAddressAll )) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }
                    if (isset( $tblAddress ) && $tblAddress) {
                        $Entity->Address = $tblAddress;
                    } else {
                        $Entity->Address = new Warning('Keine Adresse hinterlegt');
                    }

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                            '/Education/Lesson/Division/Student/Add', new Plus(),
                            array(
                                'Id'        => $Id,
                                'StudentId' => $Entity->getId()
                            ))
                    );
                });
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('Ausgewählt', 'Schüler'),
                                ( empty( $tblDivisionStudentActive )
                                    ? new Warning('Keine Schüler zugewiesen')
                                    : new TableData($tblDivisionStudentActive, null,
                                        array(
                                            'Name'    => 'Schüler',
                                            'Address' => 'Adresse',
                                            'Option'  => ''
                                        ))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Verfügbar', 'Schüler'),
                                ( empty( $tblStudentAvailable )
                                    ? new Info('Keine weiteren Schüler verfügbar')
                                    : new TableData($tblStudentAvailable, null,
                                        array(
                                            'Name'    => 'Schüler',
                                            'Address' => 'Adresse',
                                            'Option'  => ''
                                        ))
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
     * @param null $Description
     *
     * @return Stage
     */
    public function frontendTeacherAdd($Id, $TeacherId = null, $Remove = null, $Description = null)
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
                        new Success('Klassenlehrer erfolgreich entfernt')
                        .new Redirect('/Education/Lesson/Division/Teacher/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addDivisionTeacher($tblDivision, $tblPerson, $Description);
                    $Stage->setContent(
                        new Success('Klassenlehrer erfolgreich hinzugefügt')
                        .new Redirect('/Education/Lesson/Division/Teacher/Add', Redirect::TIMEOUT_SUCCESS,
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
                array_walk($tblDivisionTeacherActive, function (TblPerson &$Entity) use ($Id, $tblDivision) {

                    /** @noinspection PhpUndefinedFieldInspection */
                    $Entity->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                            '/Education/Lesson/Division/Teacher/Add', new Minus(),
                            array(
                                'Id'        => $Id,
                                'TeacherId' => $Entity->getId(),
                                'Remove'    => true
                            ))
                    );
                    $Entity->Description = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                        $Entity)->getDescription();
                });
            }

            /** @noinspection PhpUnusedParameterInspection */
            if (isset( $tblDivisionTeacherAll ) && !empty( $tblDivisionTeacherAll )) {
                array_walk($tblDivisionTeacherAll, function (TblPerson &$Entity) use ($Id) {

                    $Entity->Options = (new Form(
                        new FormGroup(
                            new FormRow(array(
                                new FormColumn(
                                    new TextField('Description', 'z.B.: Stellvertreter', '', new Person()
                                    )
                                    , 7),
                                new FormColumn(
                                    new Primary('Hinzufügen',
                                        new Plus())
                                    , 5)
                            ))
                        ), null,
                        '/Education/Lesson/Division/Teacher/Add',
                        array(
                            'Id'        => $Id,
                            'TeacherId' => $Entity->getId()
                        )
                    ))->__toString();
                });
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('Ausgewählt', 'Lehrer'),
                                ( empty( $tblDivisionTeacherActive )
                                    ? new Warning('Keine Lehrer zugewiesen')
                                    : new TableData($tblDivisionTeacherActive, null,
                                        array(
                                            'FirstName'   => 'Vorname',
                                            'LastName'    => 'Nachname',
                                            'Description' => 'Beschreibung',
                                            'Option'      => ''
                                        ))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Verfügbar', 'Lehrer'),
                                ( empty( $tblTeacherAvailable )
                                    ? new Info('Keine weiteren Lehrer verfügbar')
                                    : new TableData($tblTeacherAvailable, null,
                                        array(
                                            'FirstName' => 'Vorname',
                                            'LastName'  => 'Nachname',
                                            'Options'   => 'Beschreibung'
                                        ))
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
                        new Success('Fach erfolgreich entfernt')
                        .new Redirect('/Education/Lesson/Division/Subject/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addSubjectToDivision($tblDivision, $Subject);
                    $Stage->setContent(
                        new Success('Fach erfolgreich hinzugefügt')
                        .new Redirect('/Education/Lesson/Division/Subject/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }

            $tblSubjectUsedList = Division::useService()->getSubjectAllByDivision($tblDivision);
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
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                            '/Education/Lesson/Division/Subject/Add', new Minus(),
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
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                            '/Education/Lesson/Division/Subject/Add', new Plus(),
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
                                new Title('Ausgewählt', 'Fach'),
                                ( empty( $tblSubjectUsedList )
                                    ? new Warning('Keine Fächer zugewiesen')
                                    : new TableData($tblSubjectUsedList, null,
                                        array(
                                            'Acronym'     => 'Kürzel',
                                            'Name'        => 'Fach',
                                            'Description' => 'Beschreibung',
                                            'Option'      => ''
                                        ))
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Verfügbar', 'Fach'),
                                ( empty( $tblSubjectAvailable )
                                    ? new \SPHERE\Common\Frontend\Message\Repository\Info('Keine weiteren Fächer verfügbar')
                                    : new TableData($tblSubjectAvailable, null,
                                        array(
                                            'Acronym'     => 'Kürzel',
                                            'Name'        => 'Fach',
                                            'Description' => 'Beschreibung',
                                            'Option'      => ''
                                        ))
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
                    $Titel = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
                } else {
                    $Titel = $tblDivision->getName();
                }
                $Stage = new Stage('Schüler', 'Klasse '.new Bold($Titel));
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                    array('Id' => $Id)));
                $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning('"Schüler in Gelb"')
                    .' sind bereits in einer anderen Gruppe in diesem Fach angelegt.');

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel('Fach - Gruppe', array(
                                        'Fach: '.new Bold($tblDivisionSubject->getServiceTblSubject()->getName()),
                                        'Gruppe: '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName())
                                    ), Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Well(
                                        Division::useService()->addSubjectStudent(
                                            $this->formSubjectStudentAdd($tblDivisionSubject)
                                                ->appendFormButton(new Primary('Speichern', new Save))
                                                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                            , $DivisionSubjectId, $Student, $Id
                                        )
                                    )
                                )
                            ), new Title(new Check().' Zuordnen')
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
                getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(),
                    $tblDivisionSubject->getTblDivision());
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
                    new FormColumn(
                        new Panel('Schüler', $tblStudentList, Panel::PANEL_TYPE_INFO)
                        , 12),
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
                    $Title = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
                } else {
                    $Title = $tblDivision->getName();
                }
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $Fach = new Bold($tblDivisionSubject->getServiceTblSubject()->getName())
                        .' und die Gruppe '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName());
                } else {
                    $Fach = new Bold($tblDivisionSubject->getServiceTblSubject()->getName());
                }

                $Stage = new Stage('Fachlehrer ', 'Klasse '.new Bold($Title));
                $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                    array('Id' => $tblDivision->getId())));

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel('Fachlehrer für das Fach', $Fach, Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Well(
                                        Division::useService()->addSubjectTeacher(
                                            $this->formSubjectTeacherAdd($tblDivisionSubject)
                                                ->appendFormButton(new Primary('Speichern', new Save()))
                                                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                            , $SubjectTeacher, $Id, $DivisionSubjectId
                                        )
                                    )
                                )
                            ), new Title(new Check().' Zuordnen')
                        )
                    )
                );
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
                        , 12)
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

        $Stage = new Stage('FachGruppe', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $Id)));
        if (Division::useService()->getDivisionById($Id) && Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getServiceTblSubject()) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            $tblSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getServiceTblSubject();

            if ($tblDivision->getTblLevel()) {
                $Titel = $tblDivision->getTblLevel()->getName().$tblDivision->getName();
            } else {
                $Titel = $tblDivision->getName();
            }
            $Stage->setDescription('Klasse '.new Bold($Titel));
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
                $tblDivision);
            $TableContent = array();
            if (!empty( $tblDivisionSubjectList )) {
                array_walk($tblDivisionSubjectList, function (TblDivisionSubject $tblDivisionSubject) use (&$TableContent, $tblDivision, $tblSubject) {

                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $Temp['Name'] = $tblDivisionSubject->getServiceTblSubject()->getName();
                        $Temp['Description'] = $tblDivisionSubject->getTblSubjectGroup()->getDescription();
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            $Temp['GroupName'] = $tblDivisionSubject->getTblSubjectGroup()->getName();
                        } else {
                            $Temp['GroupName'] = '';
                        }
                        $Temp['Option'] = new Standard('Bearbeiten',
                                '/Education/Lesson/Division/SubjectGroup/Change', new Pencil(),
                                array(
                                    'Id'                => $tblDivisionSubject->getTblSubjectGroup()->getId(),
                                    'DivisionId'        => $tblDivision->getId(),
                                    'SubjectId'         => $tblSubject->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ))
                            .new Standard('Löschen', '/Education/Lesson/Division/SubjectGroup/Remove', new Remove(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                    'SubjectGroupId'    => $tblDivisionSubject->getTblSubjectGroup()->getId()
                                ));
                        array_push($TableContent, $Temp);
                    }
                });
                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);
            }

            $Stage->setContent(
                ( ( !empty( $tblDivisionSubjectList ) ) ?
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new TableData($TableContent, null,
                                        array(
                                            'Name'        => 'Fach',
                                            'GroupName'   => 'Gruppe',
                                            'Description' => 'Beschreibung',
                                            'Option'      => '',
                                        ), false)
                                )
                            ), new Title(new ListingTable().' Übersicht')
                        )
                    ) : null )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Division::useService()->addSubjectToDivisionWithGroup(
                                        $this->formSubjectGroupAdd()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblDivision, $tblSubject, $Group, $DivisionSubjectId)
                                )
                            )
                        ), new Title(new PlusSign().' Hinzufügen einer '.$tblSubject->getName().'-Gruppe')
                    )
                )
            );
        } else {
            $Stage->setContent(new Warning('Fach nicht gefunden'));
        }
        return $Stage;
    }

    /**
     * @return Form
     */
    public function formSubjectGroupAdd()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                        new FormColumn(
                            new Panel('Gruppe',
                                new TextField('Group[Name]', '', 'Gruppenname'),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new FormColumn(
                            new Panel('Sonstiges',
                                new TextField('Group[Description]', '', 'Beschreibung'),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    )
                )
            )
        );
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

        $Stage = new Stage('Gruppe', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectGroup/Add', new ChevronLeft(),
            array(
                'Id'                => $DivisionId,
                'DivisionSubjectId' => $DivisionSubjectId
            )));

        $tblSubjectGroup = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup();
        if ($tblSubjectGroup) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Id'] ) && $DivisionSubjectId) {
                $Global->POST['Group']['Name'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getName();
                $Global->POST['Group']['Description'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getDescription();
                $Global->savePost();
            }

            $tblSubject = Subject::useService()->getSubjectById($SubjectId);

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Fach - Gruppe', $tblSubject->getName()
                                    .' - '.Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getName()
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Division::useService()->changeSubjectGroup(
                                        $this->formSubjectGroupAdd()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $Group, $Id, $DivisionId, $DivisionSubjectId)
                                )
                            )
                        ), new Title(new Edit().' Bearbeiten')
                    )
                )
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
                            .new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_SUCCESS, array('Id' => $Id)));
                    } else {
                        $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Danger('Gruppe konnte nicht entfernt werden')
                            .new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR, array('Id' => $Id)));
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
     * @param int  $Id
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendDivisionChange($Id, $Division = null)
    {

        $Stage = new Stage('Klassengruppe', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
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
            $PanelShow = new Panel('Beschreibung für', array(
                    $tblDivision->getTblLevel()->getServiceTblType()->getName()
                    .' - '.$tblDivision->getTblLevel()->getName().$tblDivision->getName()
                    .' - '.$tblDivision->getServiceTblYear()->getName(),
                    $tblDivision->getDescription()
                )
                , Panel::PANEL_TYPE_INFO);
        } else {
            $PanelShow = new Panel('',
                $tblDivision->getServiceTblYear()->getName()
                .' - '.$tblDivision->getName()
                , Panel::PANEL_TYPE_INFO);
        }

        if ($tblDivision) {
            $Info = new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelShow
                        )
                    )
                )
            );
        } else {
            $Info = null;
        }
        $Stage->setContent($Info.
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->changeDivision($this->formDivision()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Division, $Id))
                        )
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );

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

        $Stage = new Stage('Klassenansicht', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            if ($tblDivision->getTblLevel()) {
                $Stage->setDescription('Übersicht '.new Bold($tblDivision->getTblLevel()->getName().$tblDivision->getName()));
            } else {
                $Stage->setDescription('Übersicht '.new Bold($tblDivision->getName()));
            }
            $Stage->setMessage($tblDivision->getDescription());
            $Stage->addButton(new Standard('Fächer', '/Education/Lesson/Division/Subject/Add',
                new Book(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $Stage->addButton(new Standard('Klassenlehrer', '/Education/Lesson/Division/Teacher/Add',
                new Person(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $Stage->addButton(new Standard('Schüler', '/Education/Lesson/Division/Student/Add',
                new \SPHERE\Common\Frontend\Icon\Repository\Group(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $StudentTableCount = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
            $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentList) {
                /** @var TblPerson $row */
                foreach ($tblDivisionStudentList as $key => $row) {
                    $LastName[$key] = strtoupper($row->getLastName());
                    $FirstName[$key] = strtoupper($row->getFirstName());
                }
                array_multisort($LastName, SORT_ASC, $FirstName, SORT_ASC, $tblDivisionStudentList);

                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivisionStudent->FullName = $tblDivisionStudent->getLastName().', '.$tblDivisionStudent->getFirstName().' '.
                        $tblDivisionStudent->getSecondName();
                    $tblCommon = Common::useService()->getCommonByPerson($tblDivisionStudent);
                    if ($tblCommon) {
                        $tblDivisionStudent->Birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                    } else {
                        $tblDivisionStudent->Birthday = 'Nicht hinterlegt';
                    }

                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblDivisionStudent);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty( $tblAddressAll )) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }
                    if (isset( $tblAddress ) && $tblAddress) {
                        $tblDivisionStudent->Address = $tblAddress;
                    } else {
                        $tblDivisionStudent->Address = new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                    }

//                    $tblSubjectStudentList = Division::useService()->getSubjectStudentByPerson($tblDivisionStudent);
//                    if ($tblSubjectStudentList) {
//                        $GroupList = array();
//                        /** @var TblSubjectStudent $tblSubjectStudent */
//                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
//                            $GroupList[] = $tblSubjectStudent->getTblDivisionSubject()->getServiceTblSubject()->getName();
//                        }
//                        asort($GroupList);
//                        $tblDivisionStudent->Group = implode(', ', $GroupList);
//                    } else {
//                        $tblDivisionStudent->Group = 'keine Zuordnung';
//                    }
                }
            } else {
                $tblDivisionStudentList = array();
            }
            $tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);
            if ($tblPersonList) {
                $TeacherList = array();
                foreach ($tblPersonList as &$tblPerson) {
                    $Description = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                        $tblPerson)->getDescription();
                    $TeacherList[] = $tblPerson->getFullName().' '.new Muted($Description);
                }
                $tblPersonList = new Panel('Klassenlehrer', $TeacherList, Panel::PANEL_TYPE_INFO);
            } else {
                $tblPersonList = new Warning('Kein Klassenlehrer festgelegt');
            }
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);

            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $Index => $tblDivisionSubject) {
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $tblDivisionSubjectList[$Index] = false;
                    }
                }
                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);

                /** @var TblDivisionSubject $row */
                foreach ($tblDivisionSubjectList as $key => $row) {
                    $name[$key] = strtoupper($row->getServiceTblSubject()->getName());
                    $Acronym[$key] = strtoupper($row->getServiceTblSubject()->getAcronym());
                }
                array_multisort($name, SORT_ASC, $Acronym, SORT_ASC, $tblDivisionSubjectList);

                /** @var TblDivisionSubject $tblDivisionSubject */
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {

                    $tblDivisionSubject->GroupTeacher = '';
//                    $tblDivisionSubject->Student = new Panel('Alle Schüler','aus der Klasse',Panel::PANEL_TYPE_INFO);
                    $tblDivisionSubject->Student = '';

                    $tblDivisionSubject->Subject = new Panel($tblDivisionSubject->getServiceTblSubject()->getName(),
                        $StudentTableCount.' / '.$StudentTableCount.' Schüler aus der Klasse', Panel::PANEL_TYPE_INFO);

                    $tblDivisionTeachersList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                    $TeacherArray = array();
                    if ($tblDivisionTeachersList) {
                        foreach ($tblDivisionTeachersList as $tblDivisionTeachers) {
                            $TeacherArray[] = $tblDivisionTeachers->getServiceTblPerson()->getFullName();
                        }
                    }
                    $SubjectTeacherPanel = new Panel('Fachlehrer', $TeacherArray, Panel::PANEL_TYPE_INFO,
                        new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                            array(
                                'Id'                => $tblDivision->getId(),
                                'DivisionSubjectId' => $tblDivisionSubject->getId()
                            ), 'Fachleher festlegen'));

                    $tblDivisionSubjectTestList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(),
                        $tblDivisionSubject->getTblDivision());

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
                                    new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add',
                                        new Pencil(),
                                        array(
                                            'Id'                => $tblDivision->getId(),
                                            'DivisionSubjectId' => $tblDivisionSubjectTest->getId()
                                        ), 'Gruppenlehrer festlegen'));
                                $Grouparray[] = $tblDivisionSubjectTest->getTblSubjectGroup()->getName();

                                $tblSubjectStudentsList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectTest);
                                if ($tblSubjectStudentsList) {
                                    /** @var TblSubjectStudent $tblSubjectStudents */
                                    foreach ($tblSubjectStudentsList as $tblSubjectStudents) {
                                        $StudentArray[] = $tblSubjectStudents->getServiceTblPerson()->getFullName();
                                        $StudentsGroupCount = $StudentsGroupCount + 1;
                                    }
                                }
                                $StudentPanel .= new Panel($tblDivisionSubjectTest->getTblSubjectGroup()->getName(),
                                    $StudentArray, Panel::PANEL_TYPE_INFO,
                                    new Standard('Schüler', '/Education/Lesson/Division/SubjectStudent/Add',
                                        new Pencil(),
                                        array(
                                            'Id'                => $tblDivision->getId(),
                                            'DivisionSubjectId' => $tblDivisionSubjectTest->getId()
                                        ), 'Schüler zuordnen'));
                            }
                        }

                        if ($StudentTableCount > $StudentsGroupCount) {
                            $tblDivisionSubject->Subject = new Panel($tblDivisionSubject->getServiceTblSubject()->getName(),
                                new \SPHERE\Common\Frontend\Text\Repository\Warning($StudentsGroupCount.' / '.$StudentTableCount.' Schüler aus der Klasse'),
                                Panel::PANEL_TYPE_INFO);
                        }

                        $tblDivisionSubject->Group = new Panel('Gruppen', $Grouparray, Panel::PANEL_TYPE_INFO,
                            new Standard('Gruppen', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ), 'Gruppen bearbeiten'));

                        $tblDivisionSubject->GroupTeacher = $TeacherPanelArray;
                        $tblDivisionSubject->SubjectTeacher = $SubjectTeacherPanel;
                        $tblDivisionSubject->Student = (new Accordion())
                            ->addItem('Enthaltene Schüler', $StudentPanel, false);
                    } else {
                        $tblDivisionSubject->Group = new Panel('Gruppen', '', Panel::PANEL_TYPE_INFO,
                            new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ), 'Gruppe erstellen'));

                        $tblDivisionSubject->SubjectTeacher = $SubjectTeacherPanel;
                    }
                }

            } else {
                $tblDivisionSubjectList = array();
            }

            if ($tblDivision->getTblLevel()) {
                $TitleClass = new \SPHERE\Common\Frontend\Icon\Repository\Group().' Schüler in der Klasse '.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
            } else {
                $TitleClass = new \SPHERE\Common\Frontend\Icon\Repository\Group().' Schüler in der Klasse '.$tblDivision->getName();
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                ( ( !empty( $tblDivisionStudentList ) ) ?
                                    new TableData($tblDivisionStudentList, null
                                        , array(
//                                            'LastName'  => 'Nachname',
//                                            'FirstName' => 'Vorname',
                                            'FullName' => 'Schüler',
                                            'Address'  => 'Adresse',
                                            'Birthday' => 'Geburtsdatum'
                                        ), false)
                                    : new Warning('Keine Schüer der Klasse zugewiesen') )
                            ,
                            ), 6),
                            new LayoutColumn($tblPersonList, 5)
                        )), new Title($TitleClass)
                    )
                ).
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                ( ( !empty( $tblDivisionSubjectList ) ) ?
                                    new TableData($tblDivisionSubjectList, null,
                                        array(
                                            'Subject'        => 'Fach',
                                            'SubjectTeacher' => 'Fachlehrer',
                                            'Group'          => 'Gruppen',
                                            'GroupTeacher'   => 'Gruppenlehrer',
                                            'Student'        => 'Gruppen Schüler',
                                        ), array("bPaginate" => false))
                                    :
                                    new Warning('Keine Fächer der Klasse zugewiesen') )
                            )
                        ), new Title(new Book().' Fächer')
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
    public function frontendSubjectTeacherShow($Id)
    {

        $Stage = new Stage('Lehrer', 'Auswahl');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
                array('Id' => $tblDivision->getId())));
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {

                    $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()->getName();
                    $tblDivisionSubject->Acronym = $tblDivisionSubject->getServiceTblSubject()->getAcronym();
                    $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/SubjectTeacher/Add',
                        new Plus(), array(
                            'Id'                => $Id,
                            'DivisionTeacherId' => $tblDivisionSubject->getId()
                        ));

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
                                new TableData($tblDivisionSubjectList, null, array(
                                        'Acronym' => 'Kürzel',
                                        'Name'    => 'Name',
                                        'Teacher' => 'Lehrer',
                                        'Option'  => 'Lehrer Zuweisung'
                                    )
                                )
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

//    /**
//     * @param int $Id
//     *
//     * @return Stage|string
//     */
//    public function frontendDivisionDestroy($Id)
//    {
//
//        $Stage = new Stage('Klassengruppe', 'entfernen');
//        $tblDivision = Division::useService()->getDivisionById($Id);
//        if ($tblDivision) {
//            $Stage->setContent(Division::useService()->destroyDivision($tblDivision));
//        } else {
//            return $Stage.new Warning('Klassengruppe nicht gefunden!')
//            .new Redirect('/Education/Lesson/Division/Create/LevelDivision');
//        }
//        return $Stage;
//    }

    /**
     * @param int        $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDivisionDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Klassengruppe', 'entfernen');
        if ($Id) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diese Klasse wirklich löschen?', array(
                            $tblDivision->getDisplayName().' '.$tblDivision->getDescription()
//                            , new Muted(new Small($tblDivision->getDisplayName()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Lesson/Division/Destroy/Division', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Education/Lesson/Division', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Remove Group-Member
//                $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
//                if ($tblPersonAll) {
//                    array_walk($tblPersonAll, function (TblPerson $tblPerson) use ($tblGroup) {
//
//                        Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
//                    });
//                }

                // Destroy Division
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Division::useService()->destroyDivision($tblDivision)
                                ? new Success('Die Klasse wurde gelöscht')
                                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_SUCCESS)
                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Die Klasse konnte nicht gelöscht werden')
                                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            return $Stage.new Warning('Klasse nicht gefunden!')
            .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        return $Stage;
    }
}
