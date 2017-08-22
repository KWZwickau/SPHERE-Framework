<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Graduation\Gradebook\ScoreRule\Frontend as FrontendScoreRule;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Label;
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
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend extends FrontendScoreRule
{

    /**
     * @param null $GradeType
     *
     * @return Stage
     */
    public function frontendGradeType($GradeType = null)
    {

        $Stage = new Stage('Zensuren-Typ', 'Übersicht');
        $Stage->setMessage('Hier werden die Zensuren-Typen verwaltet. Bei den Zensuren-Typen wird zwischen den beiden
            Kategorien: Kopfnote (z.B. Betragen, Mitarbeit, Fleiß usw.) und Leistungsüberprüfung
            (z.B. Klassenarbeit, Leistungskontrolle usw.) unterschieden.');

        $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAll();

        $TableContent = array();
        if ($tblGradeTypeAll) {
            array_walk($tblGradeTypeAll, function (TblGradeType $tblGradeType) use (&$TableContent) {

                if ($tblGradeType->isHighlighted()) {
                    $Item = array(
                        'DisplayName' => new Bold($tblGradeType->getName()),
                        'DisplayCode' => new Bold($tblGradeType->getCode()),
                        'Category' => new Bold($tblGradeType->getServiceTblTestType() ? $tblGradeType->getServiceTblTestType()->getName() : ''),
                    );
                } else {
                    $Item = array(
                        'DisplayName' => $tblGradeType->getName(),
                        'DisplayCode' => $tblGradeType->getCode(),
                        'Category' => $tblGradeType->getServiceTblTestType() ? $tblGradeType->getServiceTblTestType()->getName() : '',
                    );
                }
                $Item['Status'] = $tblGradeType->isActive()
                    ? new \SPHERE\Common\Frontend\Text\Repository\Success(new PlusSign() . ' aktiv')
                    : new \SPHERE\Common\Frontend\Text\Repository\Warning(new MinusSign() . ' inaktiv');
                $Item['Description'] = $tblGradeType->getDescription();
                $Item['Option'] =
                    (new Standard('', '/Education/Graduation/Gradebook/GradeType/Edit', new Edit(), array(
                        'Id' => $tblGradeType->getId()
                    ), 'Zensuren-Typ bearbeiten'))
                    . ($tblGradeType->isActive()
                        ? (new Standard('', '/Education/Graduation/Gradebook/GradeType/Activate', new MinusSign(),
                            array('Id' => $tblGradeType->getId()), 'Deaktivieren'))
                        : (new Standard('', '/Education/Graduation/Gradebook/GradeType/Activate', new PlusSign(),
                            array('Id' => $tblGradeType->getId()), 'Aktivieren')))
                    . ($tblGradeType->isUsed()
                        ? ''
                        : (new Standard('', '/Education/Graduation/Gradebook/GradeType/Destroy', new Remove(),
                            array('Id' => $tblGradeType->getId()), 'Löschen')));

                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Status' => 'Status',
                                'Category' => 'Kategorie',
                                'DisplayName' => 'Name',
                                'DisplayCode' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                    array('2', 'asc'),
                                )
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->createGradeType($Form, $GradeType))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGradeType()
    {

        $type = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $typeList[$type->getId()] = $type->getName();
        $type = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        $typeList[$type->getId()] = $type->getName();

        $typeList = Evaluation::useService()->getTestTypesForGradeTypes();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('GradeType[Type]', 'Kategorie', array('Name' => $typeList)), 3
                ),
                new FormColumn(
                    new TextField('GradeType[Code]', 'LK', 'Abk&uuml;rzung'), 3
                ),
                new FormColumn(
                    new TextField('GradeType[Name]', 'Leistungskontrolle', 'Name'), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 12
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 2
                )
            )),
        )));
    }

    /**
     * @param null $Id
     * @param      $GradeType
     *
     * @return Stage|string
     */
    public function frontendEditGradeType($Id = null, $GradeType = null)
    {

        $Stage = new Stage('Zensuren-Typ', 'Bearbeiten');

        $tblGradeType = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
        );

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            if ($tblGradeType->getServiceTblTestType()) {
                $Global->POST['GradeType']['Type'] = $tblGradeType->getServiceTblTestType()->getId();
            }
            $Global->POST['GradeType']['Name'] = $tblGradeType->getName();
            $Global->POST['GradeType']['Code'] = $tblGradeType->getCode();
            $Global->POST['GradeType']['IsHighlighted'] = $tblGradeType->isHighlighted();
            $Global->POST['GradeType']['Description'] = $tblGradeType->getDescription();

            $Global->savePost();
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Zensuren-Typ',
                                $tblGradeType->getName() . ' (' . $tblGradeType->getCode() . ')' .
                                ($tblGradeType->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblGradeType->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->updateGradeType($Form, $Id, $GradeType))
                        ),
                    ))
                ), new Title(new Edit() . ' Bearbeiten'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendGradeBook()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherGradebook();
            } else {
                return $this->frontendHeadmasterGradeBook();
            }
        } else {
            return $this->frontendTeacherGradebook();
        }
    }

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherGradebook($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Graduation/Gradebook/Gradebook/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Graduation/Gradebook/Gradebook/Headmaster'));
        }

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Graduation/Gradebook/Gradebook/Teacher',
            $IsAllYears, $YearId, $tblYear);

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivisionSubject && $tblDivisionSubject->getTblDivision()
                        && $tblDivisionSubject->getTblDivision()->getServiceTblYear()
                        && $tblDivisionSubject->getTblDivision()->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }

                    if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                            [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                = $tblDivisionSubject->getId();
                        } else {
                            $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                $tblDivisionSubject->getTblDivision(),
                                $tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()
                            );
                            if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                    [$item->getTblSubjectGroup()->getId()]
                                        = $item->getId();
                                }
                            } else {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()->getId()]
                                    = $tblSubjectTeacher->getTblDivisionSubject()->getId();
                            }
                        }
                    }
                }
            }

            // Klassenlehrer
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    if (($tblDivision = $tblDivisionTeacher->getTblDivision())) {
                        // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                        /** @var TblYear $tblYear */
                        if ($tblYear && $tblDivision  && $tblDivision->getServiceTblYear()
                            && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                        ) {
                            continue;
                        }

                        $tblDivisionSubjectAllByDivision
                            = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
                        if ($tblDivisionSubjectAllByDivision) {
                            foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                                if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                            = $tblDivisionSubject->getId();
                                    } else {
                                        $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                            = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                            $tblDivisionSubject->getTblDivision(),
                                            $tblDivisionSubject->getServiceTblSubject()
                                        );
                                        if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                            /** @var TblDivisionSubject $item */
                                            foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                                [$item->getTblSubjectGroup()->getId()]
                                                    = $item->getId();
                                            }
                                        } else {
                                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                                = $tblDivisionSubject->getId();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $studentViewLinkButton = new Standard(
                'Schülerübersichten',
                '/Education/Graduation/Gradebook/Gradebook/Teacher/Division',
                null,
                array(),
                'Anzeige aller Noten eines Schülers über alle Fächer'
            );
        } else {
            $studentViewLinkButton = false;
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    foreach ($subjectList as $subjectId => $value) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if ($tblSubject) {
                            if (is_array($value)) {
                                foreach ($value as $subjectGroupId => $subValue) {
                                    /** @var TblSubjectGroup $item */
                                    $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                                    $divisionSubjectTable[] = array(
                                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                        'Type' => $tblDivision->getTypeName(),
                                        'Division' => $tblDivision->getDisplayName(),
                                        'Subject' => $tblSubject->getName(),
                                        'SubjectGroup' => $item->getName(),
                                        'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                            $tblDivision, $tblSubject, $item
                                        ),
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected',
                                            new Select(),
                                            array(
                                                'DivisionSubjectId' => $subValue
                                            ),
                                            'Auswählen'
                                        )
                                    );
                                }
                            } else {
                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => '',
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected', new Select(),
                                        array(
                                            'DivisionSubjectId' => $value
                                        ),
                                        'Auswählen'
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $studentViewLinkButton
                                ? $studentViewLinkButton
                                : null,
                        ))
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
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
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterGradeBook($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl aller Notenbücher.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Gradebook/Gradebook/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Graduation/Gradebook/Gradebook/Headmaster', new Edit()));
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Graduation/Gradebook/Gradebook/Headmaster',
            $IsAllYears, $YearId, $tblYear);

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                /** @var TblYear $tblYear */
                if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                    && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                ) {
                    continue;
                }

                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectAllByDivision) {
                    foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                    = $tblDivisionSubject->getId();
                            } else {
                                $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                    = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                );
                                if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                    foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$item->getTblSubjectGroup()->getId()]
                                            = $item->getId();
                                    }
                                } else {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        = $tblDivisionSubject->getId();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    foreach ($subjectList as $subjectId => $value) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if ($tblSubject) {
                            if (is_array($value)) {
                                foreach ($value as $subjectGroupId => $subValue) {
                                    $item = Division::useService()->getSubjectGroupById($subjectGroupId);

                                    $divisionSubjectTable[] = array(
                                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                        'Type' => $tblDivision->getTypeName(),
                                        'Division' => $tblDivision->getDisplayName(),
                                        'Subject' => $tblSubject->getName(),
                                        'SubjectGroup' => $item->getName(),
                                        'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                            $tblDivision, $tblSubject, $item
                                        ),
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Selected',
                                            new Select(),
                                            array(
                                                'DivisionSubjectId' => $subValue
                                            ),
                                            'Auswählen'
                                        )
                                    );
                                }
                            } else {
                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => '',
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Selected',
                                        new Select(),
                                        array(
                                            'DivisionSubjectId' => $value
                                        ),
                                        'Auswählen'
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $studentViewLinkButton = new Standard(
            'Schülerübersichten',
            '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division',
            null,
            array(),
            'Anzeige aller Noten eines Schülers über alle Fächer'
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $studentViewLinkButton
                                ? $studentViewLinkButton
                                : null,
                        ))
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
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
     * @param null $DivisionSubjectId
     *
     * @return Stage|string
     */
    public function frontendTeacherSelectedGradebook($DivisionSubjectId = null)
    {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Teacher',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Teacher');

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $BasicRoute
     * @return Stage
     */
    private function contentSelectedGradeBook(
        Stage $Stage,
        TblDivisionSubject $tblDivisionSubject,
        $BasicRoute
    ) {

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft(), array(), 'Zurück zur Klassenauswahl'));

        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblSubject = $tblDivisionSubject->getServiceTblSubject();

        // Berechnungsvorschrift und Berechnungssystem der ausgewählten Fach-Klasse ermitteln
        $tblScoreRule = false;
        $scoreRuleText = array();
        if ($tblDivision && $tblSubject) {

            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblSubject,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
            if ($tblScoreRule) {
                $scoreRuleText[] = new Bold($tblScoreRule->getName());
                $tblScoreConditionsByRule = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditionsByRule) {

                } else {
                    $scoreRuleText[] = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                        new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                    ));
                }
            }
        }

        // Mindestnotenanzahlen
        if ($tblDivisionSubject) {
            $tblMinimumGradeCountList = Gradebook::useService()->getMinimumGradeCountAllByDivisionSubject($tblDivisionSubject);
            $minimumGradeCountPanel = $this->getMinimumGradeCountPanel($tblMinimumGradeCountList);
            if ($tblMinimumGradeCountList) {
                foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {
                    $MinimumGradeCountSortedList[$tblMinimumGradeCount->getPeriod()][] = $tblMinimumGradeCount;
                }
            }
        } else {
            $minimumGradeCountPanel = false;
            $tblMinimumGradeCountList = false;
        }

        $errorRowList = array();

        $tblYear = $tblDivision->getServiceTblYear();
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        } else {
            $tblPeriodList = false;
        }
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $addStudentList = array();
        $studentArray = array();
        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblStudentList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        } else {
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        }
        if ($tblStudentList) {
            foreach ($tblStudentList as $tblPersonStudent) {
                $studentArray[$tblPersonStudent->getId()] = $tblPersonStudent;
            }
        }

        $dataList = array();
        $columnDefinition = array();
        $periodListCount = array();
        $columnDefinition['Number'] = '#';
        $columnDefinition['Student'] = "Schüler";
        $columnDefinition['Course'] = new ToolTip('Bg', 'Bildungsgang');
        $countPeriod = 0;
        $countMinimumGradeCount = 1;
        // Tabellenkopf mit Test-Code und Datum erstellen
        if ($tblPeriodList) {
            /** @var TblPeriod $tblPeriod */
            foreach ($tblPeriodList as $tblPeriod) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    $count = 0;
                    $countPeriod++;
                    $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblTestType,
                        $tblPeriod,
                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                    );
                    if ($tblTestList) {

                        // Sortierung der Tests nach Datum
                        $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());

                        /** @var TblTest $tblTest */
                        foreach ($tblTestList as $tblTest) {
                            if ($tblTest->getServiceTblGradeType()) {
                                $count++;
                                $date = $tblTest->getDate();
                                if (strlen($date) > 6) {
                                    $date = substr($date, 0, 6);
                                }

                                $text = new Small(new Muted($date)) . '<br>'
                                    . ($tblTest->getServiceTblGradeType()->isHighlighted()
                                        ? $tblTest->getServiceTblGradeType()->getCode()
                                        : new Muted($tblTest->getServiceTblGradeType()->getCode()));

                                $columnDefinition['Test' . $tblTest->getId()] = $tblTest->getDescription()
                                    ? (new ToolTip($text, $tblTest->getDescription()))->enableHtml()
                                    : $text;

                                // für Schüler, welche nicht mehr in der Klasse sind
                                $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                                if ($tblGradeList) {
                                    foreach ($tblGradeList as $tblGradeItem) {
                                        if (($tblPersonItem = $tblGradeItem->getServiceTblPerson())
                                            && !isset($studentArray[$tblPersonItem->getId()])
                                        ) {
                                            $addStudentList[$tblPersonItem->getId()] = $tblPersonItem;
                                        }
                                    }
                                }
                            }
                        }
                        $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216;';
                        $count++;
                        if (isset($MinimumGradeCountSortedList[$countPeriod])) {
                            /**@var TblMinimumGradeCount $tblMinimumGradeCount **/
                            foreach ($MinimumGradeCountSortedList[$countPeriod] as $tblMinimumGradeCount) {
                                $columnDefinition['MinimumGradeCount' . $tblMinimumGradeCount->getId()] = '#' . $countMinimumGradeCount++;
                                $count++;
                            }
                        }
                        $periodListCount[$tblPeriod->getId()] = $count;
                    } else {
                        $periodListCount[$tblPeriod->getId()] = 1;
                        $columnDefinition['Period' . $tblPeriod->getId()] = "";
                    }
                }
            }
            $columnDefinition['YearAverage'] = '&#216;';
            if (isset($MinimumGradeCountSortedList[SelectBoxItem::PERIOD_FULL_YEAR])) {
                /** @var TblMinimumGradeCount $item */
                foreach ($MinimumGradeCountSortedList[SelectBoxItem::PERIOD_FULL_YEAR] as $item) {
                    $columnDefinition['MinimumGradeCount' . $item->getId()] = '#' . $countMinimumGradeCount++;
                }
            }
        }

        if (!empty($addStudentList)) {
            if ($tblStudentList) {
                $tblStudentList = array_merge(array_values($tblStudentList), array_values($addStudentList));
            } else {
                $tblStudentList = $addStudentList;
            }
        }

        // Tabellen-Inhalt erstellen
        if ($tblStudentList) {

            $count = 1;
            $countPeriod = 0;
            // Ermittlung der Zensuren zu den Schülern
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $countPeriod++;
                $data = array();
                $data['Number'] = $count % 5 == 0 ? new Bold($count) : $count;
                $count++;
                $data['Student'] = isset($addStudentList[$tblPerson->getId()])
                    ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName();
                $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                $CourseName = '';
                if ($tblCourse) {
                    if ($tblCourse->getName() == 'Gymnasium') {
                        $CourseName = 'GYM';
                    } elseif ($tblCourse->getName() == 'Realschule') {
                        $CourseName = 'RS';
                    } elseif ($tblCourse->getName() == 'Hauptschule') {
                        $CourseName = 'HS';
                    }
                }
                $data['Course'] = $CourseName;

                // Zensur des Schülers zum Test zuordnen und Durchschnitte berechnen
                if (!empty($columnDefinition)) {
                    foreach ($columnDefinition as $column => $value) {
                        if (strpos($column, 'Test') !== false) {
                            $testId = substr($column, strlen('Test'));
                            $tblTest = Evaluation::useService()->getTestById($testId);
                            if ($tblTest) {
                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                                if ($tblGrade) {
                                    $displayGradeDate = false;
                                    if ($tblTest->isContinues() && $tblGrade->getDate()) {
                                        if (strlen($tblGrade->getDate()) > 6) {
                                            $displayGradeDate = substr($tblGrade->getDate(), 0, 6);
                                        }
                                    } elseif ($tblTest->isContinues() && $tblTest->getFinishDate()) {
                                        if (strlen($tblTest->getFinishDate()) > 6) {
                                            $displayGradeDate = substr($tblTest->getFinishDate(), 0, 6);
                                        }
                                    }

                                    $data[$column] =
                                        ($tblTest->getServiceTblGradeType()
                                            ? ($tblTest->getServiceTblGradeType()->isHighlighted()
                                                ? new Bold($tblGrade->getDisplayGrade()) : $tblGrade->getDisplayGrade())
                                            : $tblGrade->getDisplayGrade())
                                        . ($displayGradeDate
                                            ? new Small(new Muted(' (' . $displayGradeDate . ')'))
                                            : '');
                                } else {
                                    $data[$column] = '';
                                }
                            }
                        } elseif (strpos($column, 'PeriodAverage') !== false) {
                            $periodId = substr($column, strlen('PeriodAverage'));
                            $tblPeriod = Term::useService()->getPeriodById($periodId);
                            if ($tblPeriod) {
                                /*
                                * Calc Average
                                */
                                $average = Gradebook::useService()->calcStudentGrade(
                                    $tblPerson,
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null,
                                    $tblPeriod,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                );
//                                $priority = '';
                                if (is_array($average)) {
                                    $errorRowList = $average;
                                    $average = '';
                                } else {
                                    $posStart = strpos($average, '(');
                                    if ($posStart !== false) {
                                        $posEnd = strpos($average, ')');
                                        if ($posEnd !== false) {
//                                            $priority = substr($average, $posStart + 1, $posEnd - ($posStart + 1));
                                        }
                                        $average = substr($average, 0, $posStart);
                                    }
                                }
                                $data[$column] = new Bold($average);
                            }
                        } elseif (strpos($column, 'YearAverage') !== false) {

                            /*
                            * Calc Average
                            */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null,
                                null,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if (is_array($average)) {
                                $errorRowList = $average;
                                $average = '';
                            } else {
                                $posStart = strpos($average, '(');
                                if ($posStart !== false) {
                                    $average = substr($average, 0, $posStart);
                                }
                            }
                            $data[$column] = new Bold($average);
                        } elseif (strpos($column, 'Period') !== false) {
                            // keine Tests in der Periode vorhanden
                            $data[$column] = '';
                        } elseif (strpos($column, 'MinimumGradeCount') !== false) {
                            $minimumGradeCountId = str_replace('MinimumGradeCount', '', $column);
                            if (($tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($minimumGradeCountId))) {
                                $data[$column] = Gradebook::useService()->getMinimumGradeCountInfo($tblDivisionSubject,
                                    $tblPerson, $tblMinimumGradeCount);
                            }
                        }
                    }
                }

                $dataList[] = $data;
            }

            // Fach-Klassendurchschnitt pro Test
            $data = array();
            if (!empty($columnDefinition)) {
                foreach ($columnDefinition as $column => $value) {
                    if (strpos($column, 'Test') !== false) {
                        $testId = substr($column, strlen('Test'));
                        $tblTest = Evaluation::useService()->getTestById($testId);
                        if ($tblTest) {
                            $average = Gradebook::useService()->getAverageByTest($tblTest);
                            $data[$column] = new Muted($average ? $average : '');
                        }
                    } elseif (strpos($column, 'Number') !== false) {
//                        $data[$column] = new Muted('&#216;');
                    } elseif (strpos($column, 'Student') !== false) {
                        $data[$column] = new Muted('&#216; Fach-Klasse');
                    } else {
                        $data[$column] = '';
                    }
                }
            }
            $dataList[] = $data;
        }

        $tableData = new TableData(
            $dataList, null, $columnDefinition,
            array(
                "columnDefs" => array(
                    array(
                        "orderable" => false,
                        "targets"   => '_all',
                    ),
                    array('width' => '1%', 'targets' => 0),
                    array('width' => '2%', 'targets' => 2),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'responsive' => false
            )
        );

        // oberste Tabellen-Kopf-Zeile erstellen
        $headTableColumnList = array();
        $headTableColumnList[] = new TableColumn('', 3, '20%');
        if (!empty($periodListCount)) {
            foreach ($periodListCount as $periodId => $count) {
                $tblPeriod = Term::useService()->getPeriodById($periodId);
                if ($tblPeriod) {
                    $headTableColumnList[] = new TableColumn($tblPeriod->getDisplayName(), $count);
                }
            }
            $headTableColumnList[] = new TableColumn('Gesamt',
                $tblMinimumGradeCountList ? count($tblMinimumGradeCountList) + 1 : 1);
        }
        $tableData->prependHead(
            new TableHead(
                new TableRow(
                    $headTableColumnList
                )
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                        new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Fach-Klasse',
                                        array(
                                            'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                            ($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '') .
                                            ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                                ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                            'Fachlehrer: ' . Division::useService()->getSubjectTeacherNameList(
                                                $tblDivision, $tblSubject, $tblDivisionSubject->getTblSubjectGroup()
                                                ? $tblDivisionSubject->getTblSubjectGroup() : null
                                            )
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ),
                                    6
                                ),
                                new LayoutColumn(new Panel(
                                    'Berechnungsvorschrift',
                                    $tblScoreRule ? $scoreRuleText : new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                        new Ban() . ' Keine Berechnungsvorschrift hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                                    )),
                                    Panel::PANEL_TYPE_INFO
                                ), 6),
                                $minimumGradeCountPanel ? new LayoutColumn($minimumGradeCountPanel) : null,
                                new LayoutColumn(
                                    $tableData
                                )
                            )
                        ),
                    )
                ),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null)
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionSubjectId
     *
     * @return Stage|string
     */
    public function frontendHeadmasterSelectedGradeBook(
        $DivisionSubjectId = null
    ) {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Headmaster',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Headmaster');

        return $Stage;
    }

    /**
     * @param null $YearId
     *
     * @return Stage|string
     */
    public function frontendStudentGradebook($YearId = null)
    {

        $Stage = new Stage('Notenübersicht', 'Schüler/Eltern');
        $Stage->setMessage(
            'Anzeige der Zensuren für die Schüler und Eltern. <br>
            Der angemeldete Schüler sieht nur seine eigenen Zensuren. <br>
            Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Schützlinge. <br>'
        );

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $rowList = array();
        $tblDisplayYearList = array();
        $data = array();

        $tblPersonList = $this->getPersonListForStudent();

        // Jahre ermitteln, in denen Schüler in einer Klasse ist
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {

                    /** @var TblDivisionStudent $tblDivisionStudent */
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        $tblDivision = $tblDivisionStudent->getTblDivision();
                        if ($tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
                            $tblDisplayYearList[$tblYear->getId()] = $tblYear;
                            $data[$tblYear->getId()][$tblPerson->getId()][$tblDivision->getId()] = $tblDivision;
                        }
                    }
                }
            }
        }

        if (!empty($tblDisplayYearList)) {
            $tblDisplayYearList = $this->getSorter($tblDisplayYearList)->sortObjectBy('DisplayName');
            $lastYear = end($tblDisplayYearList);
            /** @var TblYear $year */
            foreach ($tblDisplayYearList as $year) {
                $Stage->addButton(
                    new Standard(
                        ($YearId === null && $year->getId() == $lastYear->getId()) ? new Info(new Bold($year->getDisplayName())) : $year->getDisplayName(),
                        '/Education/Graduation/Gradebook/Student/Gradebook',
                        null,
                        array(
                            'YearId' => $year->getId()
                        )
                    )
                );
            }

            if ($YearId === null) {
                $YearId = $lastYear->getId();
            }
        }

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $tableHeaderList = array();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if ($tblPeriodList) {
                $tableHeaderList['Subject'] = 'Fach';
                foreach ($tblPeriodList as $tblPeriod) {
                    $tableHeaderList['Period' . $tblPeriod->getId()] = new Bold($tblPeriod->getDisplayName());
                }
            }

            if (!empty($data)) {
                if (isset($data[$tblYear->getId()])) {
                    foreach ($data[$tblYear->getId()] as $personId => $divisionList) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        if ($tblPerson && is_array($divisionList)) {
                            $isShownAverage = false;
                            if (($tblSetting = Consumer::useService()->getSetting(
                                'Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview'
                            ))
                                && $tblSetting->getValue()
                            ) {
                                $isShownAverage = true;
                            }

                            /** @var TblDivision $tblDivision */
                            foreach ($divisionList as $tblDivision) {
                                if ($tblDivision && $tblDivision->getServiceTblYear()) {
                                    // alle Klassen zum aktuellen Jahr
                                    if ($tblDivision->getServiceTblYear()->getId() == $tblYear->getId()) {
                                        $rowList[] = new LayoutRow(new LayoutColumn(new Title($tblPerson->getLastFirstName()
                                            . new Small(new Muted(' Klasse ' . $tblDivision->getDisplayName()))),
                                            12));
                                        $tableDataList = array();
                                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                                        if ($tblDivisionSubjectList) {
                                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                                if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                                                    if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                                        $hasStudentSubject = false;
                                                        $tblDivisionSubjectWhereGroup =
                                                            Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                                                $tblDivision,
                                                                $tblDivisionSubject->getServiceTblSubject()
                                                            );
                                                        if ($tblDivisionSubjectWhereGroup) {
                                                            foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                                                if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                                                    $tblPerson)
                                                                ) {
                                                                    $hasStudentSubject = true;
                                                                }
                                                            }
                                                        } else {
                                                            $hasStudentSubject = true;
                                                        }
                                                        if ($hasStudentSubject) {
                                                            $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Subject'] = $tblDivisionSubject->getServiceTblSubject()->getName();

                                                            if ($tblPeriodList) {
                                                                foreach ($tblPeriodList as $tblPeriod) {
                                                                    $tblGradeList = Gradebook::useService()->getGradesByStudent(
                                                                        $tblPerson,
                                                                        $tblDivision,
                                                                        $tblDivisionSubject->getServiceTblSubject(),
                                                                        $tblTestType,
                                                                        $tblPeriod
                                                                    );

                                                                    $subTableHeaderList = array();
                                                                    $subTableDataList = array();

                                                                    if ($tblGradeList) {
                                                                        foreach ($tblGradeList as $tblGrade) {
                                                                            $tblTest = $tblGrade->getServiceTblTest();
                                                                            if ($tblTest) {
                                                                                if ($tblTest->isContinues() && $tblGrade->getDate()) {
                                                                                    $gradeDate = (new \DateTime($tblGrade->getDate()))->format("Y-m-d");
                                                                                    $now = (new \DateTime('now'))->format("Y-m-d");
                                                                                    if ($gradeDate <= $now) {

                                                                                        // Test anzeigen
                                                                                        $this->addTest($tblTest,
                                                                                            $tblGrade,
                                                                                            $subTableHeaderList,
                                                                                            $subTableDataList,
                                                                                            $isShownAverage
                                                                                        );
                                                                                    }
                                                                                } elseif ($tblTest->getServiceTblGradeType() && $tblTest->getReturnDate()) {
                                                                                    $testReturnDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                                                                                    $now = (new \DateTime('now'))->format("Y-m-d");
                                                                                    if ($testReturnDate < $now) {

                                                                                        // Test anzeigen
                                                                                        $this->addTest($tblTest,
                                                                                            $tblGrade,
                                                                                            $subTableHeaderList,
                                                                                            $subTableDataList,
                                                                                            $isShownAverage
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if (!empty($subTableHeaderList)) {
                                                                        if ($isShownAverage) {
                                                                            $subTableHeaderList['Average'] = '&#216;';

                                                                            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                                                                $tblDivisionSubject->getTblDivision(),
                                                                                $tblDivisionSubject->getServiceTblSubject(),
                                                                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                                                            );

                                                                            /*
                                                                            * Calc Average
                                                                            */
                                                                            $average = Gradebook::useService()->calcStudentGrade(
                                                                                $tblPerson,
                                                                                $tblDivisionSubject->getTblDivision(),
                                                                                $tblDivisionSubject->getServiceTblSubject(),
                                                                                Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                                                $tblScoreRule ? $tblScoreRule : null,
                                                                                $tblPeriod,
                                                                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                                                true
                                                                            );

                                                                            if (is_array($average)) {
                                                                                $average = 'Fehler';
                                                                            } elseif (is_string($average) && strpos($average,
                                                                                    '(')
                                                                            ) {
                                                                                $average = substr($average, 0,
                                                                                    strpos($average, '('));
                                                                            }

                                                                            $subTableDataList[0]['Average'] = $average;
                                                                        }

                                                                        $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Period' . $tblPeriod->getId()] = new TableData(
                                                                            $subTableDataList, null,
                                                                            $subTableHeaderList,
                                                                            false
                                                                        );
                                                                    } else {
                                                                        $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Period' . $tblPeriod->getId()] = '';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $rowList[] = new LayoutRow(new LayoutColumn(
                                            !empty($tableDataList)
                                                ? new TableData(
                                                $tableDataList, null, $tableHeaderList, null
                                            )
                                                : new Warning('Keine Fächer vorhanden.', new Exclamation())
                                        ));
                                        $rowList[] = new LayoutRow(new LayoutColumn(new Header('&nbsp;'), 12));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        ($YearId !== null ?
                            new Panel('Schuljahr', $tblYear->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            : '')
                    )
                ))),
                ($YearId !== null ? new LayoutGroup($rowList) : null)
            ))
        );
        return $Stage;
    }

    /**
     * @return false|TblPerson[]
     */
    private function getPersonListForStudent()
    {
        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $tblPersonList = array();
        if ($tblPerson) {
            $tblPersonList[] = $tblPerson;

            $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
            if ($tblPersonRelationshipList) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if ($relationship->getTblType()->getName() == 'Sorgeberechtigt' && $relationship->getServiceTblPersonTo()) {
                        $tblPersonList[] = $relationship->getServiceTblPersonTo();
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }


    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typ von einer Berechnungsvariante entfernen');

        $tblScoreConditionGradeTypeList = Gradebook::useService()->getScoreConditionGradeTypeListById($Id);
        if ($tblScoreConditionGradeTypeList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyGradeType(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Zensuren-Type', 'Löschen');

        $tblGradeType = Gradebook::useService()->getGradeTypeById($Id);
        if ($tblGradeType) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                                new Panel(
                                    'Zensuren-Typ',
                                    $tblGradeType->getName()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblGradeType->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Diesen Zensuren-Typ wirklich löschen?',
                                    array(
                                        $tblGradeType->getName(),
                                        $tblGradeType->getDescription() ? $tblGradeType->getDescription() : null
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Graduation/Gradebook/GradeType/Destroy', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Graduation/Gradebook/GradeType', new Disable())
                                )
                            )
                        )
                    )))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Gradebook::useService()->destroyGradeType($tblGradeType)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Der Zensuren-Typ wurde gelöscht')
                                : new Danger(new Ban() . ' Der Zensuren-Typ konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendScoreType()
    {

        $Stage = new Stage('Bewertungssystem', 'Übersicht');
        $Stage->setMessage(
            'Hier werden alle verfügbaren Bewertungssysteme angezeigt. Nach der Auswahl eines Bewertungssystems können dem
            Bewertungssystem die entsprechenden Fach-Klassen zugeordnet werden.'
        );

        $tblScoreTypeAll = Gradebook::useService()->getScoreTypeAll();
        if ($tblScoreTypeAll) {
            foreach ($tblScoreTypeAll as &$tblScoreType) {
                $tblScoreType->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Type/Select',
                        new Equalizer(),
                        array('Id' => $tblScoreType->getId()), 'Fach-Klassen zuordnen'));
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData(
                                $tblScoreTypeAll, null, array(
                                    'Name' => 'Name',
                                    'Option' => ''
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendScoreTypeSelect(
        $Id = null,
        $YearId = null,
        $Data = null
    ) {

        $Stage = new Stage('Bewertungssystem', 'Fach-Klassen einem Bewertungssystem zuordnen');
        $Stage->setMessage('Hier können dem ausgewählten Bewertungssystem Fach-Klassen zugeordnet werden.' . '<br>'
            . new Bold(new Exclamation() . ' Hinweis:') . ' Sobald Zensuren für eine Fach-Klasse vergeben wurden,
        kann das Bewertungssystem dieser Fach-Klasse nicht mehr geändert werden. Außerdem kann die Fach-Klasse immer nur ein Bewertungssystem besitzen.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Type', new ChevronLeft()));

        $tblScoreType = Gradebook::useService()->getScoreTypeById($Id);
        if ($tblScoreType) {


            if ($YearId && ($tblSelectedYear = Term::useService()->getYearById($YearId))) {
            } else {
                if (($tblYearAllByNow = Term::useService()->getYearByNow())) {
                    $tblSelectedYear = current($tblYearAllByNow);
                } else {
                    $tblSelectedYear = false;
                }
            }

            $yearButtonList = array();
            $tblYearList = Term::useService()->getYearAllSinceYears(3);
            if ($tblYearList) {
                $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
                /** @var TblYear $tblYear */
                foreach ($tblYearList as $tblYear) {
                    $yearButtonList[] = new Standard(
                        ($tblSelectedYear && $tblYear->getId() == $tblSelectedYear->getId())
                            ? new Info(new Edit() . ' ' . $tblYear->getDisplayName())
                            : $tblYear->getDisplayName(),
                        '/Education/Graduation/Gradebook/Type/Select',
                        null,
                        array(
                            'Id' => $tblScoreType->getId(),
                            'YearId' => $tblYear->getId()
                        )
                    );
                }
            }

            $formGroupList = array();
            $rowList = array();
            $columnList = array();
            if ($tblSelectedYear) {
                $tblDivisionList = Division::useService()->getDivisionByYear($tblSelectedYear);
                if ($tblDivisionList) {
                    $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName');
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivision) {
                        $subjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                        if ($subjectList) {

                            // set Post
                            if ($Data == null) {
                                $Global = $this->getGlobal();
                                /** @var TblSubject $subject */
                                foreach ($subjectList as $subject) {
                                    $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                        $tblDivision, $subject
                                    );
                                    if ($tblScoreRuleDivisionSubject) {
                                        if ($tblScoreRuleDivisionSubject->getTblScoreType()
                                            && $tblScoreRuleDivisionSubject->getTblScoreType()->getId() == $tblScoreType->getId()
                                        ) {
                                            $Global->POST['Data'][$tblDivision->getId()][$subject->getId()] = 1;
                                        }
                                    }
                                }
                                $Global->savePost();
                            }

//                            $tblNewSubject = new TblSubject();
//                            $tblNewSubject->setId(-1);
//                            $tblNewSubject->setName('Alle wählbaren Fächer');
//                            array_unshift($subjectList, $tblNewSubject);

                            $countSubject = 0;
                            $subjectList = $this->getSorter($subjectList)->sortObjectBy('Acronym');

                            /** @var TblSubject $tblSubject */
                            foreach ($subjectList as &$tblSubject) {
                                $isDisabled = false;
                                if ($tblSubject->getId() === -1) {
                                    $name = new Italic((
                                        $tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName()
                                    );
                                } else {
                                    $name = ($tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName();
                                }

                                $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                    $tblDivision, $tblSubject
                                );
                                if ($tblScoreRuleDivisionSubject) {
                                    if ($tblScoreRuleDivisionSubject->getTblScoreType()
                                        && $tblScoreRuleDivisionSubject->getTblScoreType()->getId() != $tblScoreType->getId()
                                    ) {
                                        $isDisabled = true;
                                        $name .= ' ' . new Label($tblScoreRuleDivisionSubject->getTblScoreType()->getName(),
                                                Label::LABEL_TYPE_PRIMARY);
                                    }
                                }

                                // Bewertungssystem nicht mehr bearbeitbar, nachdem Zensuren vergeben wurden
                                if (Gradebook::useService()->existsGrades($tblDivision, $tblSubject)) {
                                    $isDisabled = true;
                                }

                                $checkBox = new CheckBox(
                                    'Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . ']',
                                    $name,
                                    1
                                );
                                $tblSubject = $isDisabled ? $checkBox->setDisabled() : $checkBox;
                                if (!$isDisabled) {
                                    $countSubject++;
                                }
                            }

                            if ($countSubject > 0) {
                                $tblNewSubject = new CheckBox(
                                    'Data[' . $tblDivision->getId() . '][-1]',
                                    new Italic('Alle  verfügbaren Fächer'),
                                    1
                                );

                                array_unshift($subjectList, $tblNewSubject);
                            }

                            $panel = new Panel(
                                new Bold('Klasse ' . $tblDivision->getDisplayName()),
                                $subjectList,
                                Panel::PANEL_TYPE_INFO
                            );

                            if ($tblDivision->getTblLevel()) {
                                $schoolTypeId = $tblDivision->getTblLevel()->getServiceTblType()->getId();
                            } else {
                                $schoolTypeId = 0;
                            }
                            $columnList[$schoolTypeId][] = new FormColumn($panel, 3);
                            if (count($columnList[$schoolTypeId]) == 4) {
                                $rowList[$schoolTypeId][] = new FormRow($columnList[$schoolTypeId]);
                                $columnList[$schoolTypeId] = array();
                            }
                        }
                    }

                    foreach ($columnList as $schoolTypeId => $list) {
                        if (!empty($list)) {
                            $rowList[$schoolTypeId][] = new FormRow($list);
                        }
                    }

                    foreach ($rowList as $schoolTypeId => $list) {
                        $tblSchoolType = Type::useService()->getTypeById($schoolTypeId);
                        $formGroupList[] = new FormGroup($list,
                            new \SPHERE\Common\Frontend\Form\Repository\Title($tblSchoolType
                                ? new Building() . ' ' . $tblSchoolType->getName()
                                : 'Keine Schulart'));
                    }
                }
            }


            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Bewertungssystem',
                                    new Bold($tblScoreType->getName()),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                            new LayoutColumn($yearButtonList),
                            new LayoutColumn('<br>')
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                empty($formGroupList)
                                    ? new Warning('Keine Klassen vorhanden.', new Exclamation())
                                    : new Well(
                                    Gradebook::useService()->updateScoreTypeDivisionSubject(
                                        (new Form(
                                            $formGroupList
                                        ))->appendFormButton(new Primary('Speichern', new Save())), $tblScoreType,
                                        $tblSelectedYear ? $tblSelectedYear : null, $Data
                                    )
                                )
                            )
                        )
                    )),
                ))
            );
        } else {
            $Stage->setContent(new Danger('Berechnungsvorschrift nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendTeacherDivisionList()
    {

        $Stage = new Stage('Schülerübersicht', 'Klasse des Schülers Auswählen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Gradebook', new ChevronLeft())
        );

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $tblDivisionList = array();
        if ($tblPerson) {
            $tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherList) {
                foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                    if ($tblDivisionTeacher->getTblDivision()) {
                        $tblDivisionList[] = $tblDivisionTeacher->getTblDivision();
                    }
                }
            }
        }

        $divisionTable = array();
        if (!empty($tblDivisionList)) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student', new Select(),
                        array(
                            'DivisionId' => $tblDivision->getId()
                        ),
                        'Auswählen'
                    )
                );
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
     * @return Stage
     */
    public function frontendHeadmasterDivisionList()
    {
        $Stage = new Stage('Schülerübersicht', 'Klasse des Schülers Auswählen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster', new ChevronLeft())
        );

        $tblDivisionList = Division::useService()->getDivisionAll();

        $divisionTable = array();
        if (!empty( $tblDivisionList ) || $tblDivisionList !== false) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year'     => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type'     => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option'   => new Standard(
                        '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student', new Select(),
                        array(
                            'DivisionId' => $tblDivision->getId()
                        ),
                        'Auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year'     => 'Schuljahr',
                                'Type'     => 'Schulart',
                                'Division' => 'Klasse',
                                'Option'   => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select().' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendHeadmasterSelectStudent($DivisionId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if (!$tblDivision) {
            return $Stage.new Danger('Klasse nicht gefunden.', new Ban());
        }

        $tblYear = $tblDivision->getServiceTblYear();
        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $tblDivisionList = array();
        $tableHeaderList = array();
        $isWithSubjectGroup = false;

        // Jahre ermitteln, in denen Schüler in einer Klasse ist
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {

                    /** @var TblDivisionStudent $tblDivisionStudent */
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        $tblDivisionSearch = $tblDivisionStudent->getTblDivision();
                        if ($tblDivision && ($tblYearDivision = $tblDivisionSearch->getServiceTblYear())) {
                            if ($tblYear
                                && $tblYearDivision
                                && $tblDivisionSearch
                                && $tblYearDivision->getId() == $tblYear->getId()
                            ) {
                                $tblDivisionList[$tblDivisionSearch->getId()] = $tblDivisionSearch;
                            }
                        }
                    }
                }
            }

            $tableHeaderList['Number'] = 'Nummer';
            $tableHeaderList['Name'] = 'Name';
            $tableHeaderList['Course'] = 'Bildungsgang';

            $SubjectList = array();
            // definition of dynamic SubjectTableHead
            if (!empty($tblDivisionList)) {
                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionList as $tblDivisionLoop) {
                    $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivisionLoop,
                        $isWithSubjectGroup);
                    if ($tblDivisionSubjectList) {
                        /** @var TblDivisionSubject $tblDivisionSubject */
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (($tblSubjectHeader = $tblDivisionSubject->getServiceTblSubject())) {
                                $SubjectList[$tblSubjectHeader->getAcronym()] = $tblSubjectHeader->getId();
                            }
                        }
                    }
                }
            }
            // sort by SubjectAcronym
            ksort($SubjectList);
            if (!empty($SubjectList)) {
                foreach ($SubjectList as $Acronym => $SubjectId) {
                    $tableHeaderList[$SubjectId.'Id'] = $Acronym;
                }
            }

            $tableHeaderList['Option'] = '';
        }

        $studentTable = array();
        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        if ($tblStudentList) {
            $count = 1;
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $data = array();
                $data['Number'] = $count++;
                $data['Name'] = $tblPerson->getLastFirstName();
                $data['Course'] = '';
                $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                if ($tblCourse) {
                    $data['Course'] = $tblCourse->getName();
                }
                $data['Option'] = new Standard(
                    '',
                    '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student/Overview',
                    new EyeOpen(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'PersonId'   => $tblPerson->getId()
                    ),
                    'Schülerübersicht anzeigen'
                );

                if (!empty($tblDivisionList)) {
                    foreach ($tblDivisionList as $tblDivisionLoop) {
                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivisionLoop,
                            $isWithSubjectGroup);
                        if ($tblDivisionSubjectList) {
                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByPersonAndDivision($tblPerson,
                                $tblDivisionLoop);
                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $data[$tblSubject->getId().'Id'] = '';
                                    $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                        $tblDivisionLoop,
                                        $tblSubject
                                    );
                                    /*
                                    * Calc Average
                                    */
                                    $average = Gradebook::useService()->calcStudentGrade(
                                        $tblPerson,
                                        $tblDivisionLoop,
                                        $tblSubject,
                                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                        $tblScoreRule ? $tblScoreRule : null
                                    );

                                    if (is_array($average)) {
                                        $average = 'Fehler';
                                    } elseif (is_string($average) && strpos($average, '(')) {
                                        $average = substr($average, 0, strpos($average, '('));
                                    }

                                    $data[$tblSubject->getId().'Id'] = ($average != '' ? '&empty; '.$average : '');
                                    // add ToolTip if Student is in Group
                                    if ($tblSubjectStudentList) {
                                        /** @var TblSubjectStudent $tblSubjectStudent */
                                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                            if ($tblSubjectStudent) {
                                                if (($tblDivisionSubjectStudent = $tblSubjectStudent->getTblDivisionSubject())) {
                                                    if (($tblSubjectFromStudent = $tblDivisionSubjectStudent->getServiceTblSubject())) {
                                                        if ($tblSubjectFromStudent->getId() == $tblSubject->getId()) {
                                                            if (($tblSubjectGroup = $tblDivisionSubjectStudent->getTblSubjectGroup())) {
                                                                $data[$tblSubject->getId().'Id'] = (new ToolTip(($average != '' ? '&empty; '.$average : '')
                                                                    , $tblSubjectGroup->getName()))->enableHtml();
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
                }
                $studentTable[] = $data;
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Klasse',
                                $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                        new LayoutColumn(array(
                            new TableData($studentTable, null, $tableHeaderList,
                                array(
                                    "columnDefs" => array(
                                        array('width' => '6%', 'targets' => 2),
                                    ),
                                    'pageLength' => -1
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
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendTeacherSelectStudent($DivisionId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblDivisionList = array();
            $tableHeaderList = array();
            $isWithSubjectGroup = false;

            // Jahre ermitteln, in denen Schüler in einer Klasse ist
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                    if ($tblDivisionStudentList) {

                        /** @var TblDivisionStudent $tblDivisionStudent */
                        foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                            $tblDivisionSearch = $tblDivisionStudent->getTblDivision();
                            if ($tblDivision && ($tblYearDivision = $tblDivisionSearch->getServiceTblYear())) {
                                if ($tblYear
                                    && $tblYearDivision
                                    && $tblDivisionSearch
                                    && $tblYearDivision->getId() == $tblYear->getId()
                                ) {
                                    $tblDivisionList[$tblDivisionSearch->getId()] = $tblDivisionSearch;
                                }
                            }
                        }
                    }
                }

                $tableHeaderList['Number'] = 'Nummer';
                $tableHeaderList['Name'] = 'Name';
                $tableHeaderList['Course'] = 'Bildungsgang';


                $SubjectList = array();
                // definition of dynamic SubjectTableHead
                if (!empty($tblDivisionList)) {
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivisionLoop) {
                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivisionLoop,
                            $isWithSubjectGroup);
                        if ($tblDivisionSubjectList) {
                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                if (($tblSubjectHeader = $tblDivisionSubject->getServiceTblSubject())) {
                                    $SubjectList[$tblSubjectHeader->getAcronym()] = $tblSubjectHeader->getId();
                                }
                            }
                        }
                    }
                }
                // sort by SubjectAcronym
                ksort($SubjectList);
                if (!empty($SubjectList)) {
                    foreach ($SubjectList as $Acronym => $SubjectId) {
                        $tableHeaderList[$SubjectId.'Id'] = $Acronym;
                    }
                }

                $tableHeaderList['Option'] = '';
            }

            $studentTable = array();
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblStudentList) {
                $count = 1;
                /** @var TblPerson $tblPerson */
                foreach ($tblStudentList as $tblPerson) {
                    $data = array();
                    $data['Number'] = $count++;
                    $data['Name'] = $tblPerson->getLastFirstName();
                    $data['Course'] = '';
                    $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                    if ($tblCourse) {
                        $data['Course'] = $tblCourse->getName();
                    }
                    $data['Option'] = new Standard(
                        '',
                        '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student/Overview',
                        new EyeOpen(),
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'PersonId'   => $tblPerson->getId()
                        ),
                        'Schülerübersicht anzeigen'
                    );

                    if (!empty($tblDivisionList)) {
                        foreach ($tblDivisionList as $tblDivisionLoop) {
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivisionLoop,
                                $isWithSubjectGroup);
                            if ($tblDivisionSubjectList) {
                                $tblSubjectStudentList = Division::useService()->getSubjectStudentByPersonAndDivision($tblPerson,
                                    $tblDivisionLoop);
                                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                    $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                                    if ($tblSubject) {
                                        $data[$tblSubject->getId().'Id'] = '';
                                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                            $tblDivisionLoop,
                                            $tblSubject
                                        );
                                        /*
                                        * Calc Average
                                        */
                                        $average = Gradebook::useService()->calcStudentGrade(
                                            $tblPerson,
                                            $tblDivisionLoop,
                                            $tblSubject,
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreRule ? $tblScoreRule : null
                                        );
                                        if (is_string($average) && strpos($average, '(')) {
                                            $average = substr($average, 0, strpos($average, '('));
                                        }

                                        $data[$tblSubject->getId().'Id'] = ($average != '' ? '&empty; '.$average : '');
                                        // add ToolTip if Student is in Group
                                        if ($tblSubjectStudentList) {
                                            /** @var TblSubjectStudent $tblSubjectStudent */
                                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                                if ($tblSubjectStudent) {
                                                    if (($tblDivisionSubjectStudent = $tblSubjectStudent->getTblDivisionSubject())) {
                                                        if (($tblSubjectFromStudent = $tblDivisionSubjectStudent->getServiceTblSubject())) {
                                                            if ($tblSubjectFromStudent->getId() == $tblSubject->getId()) {
                                                                if (($tblSubjectGroup = $tblDivisionSubjectStudent->getTblSubjectGroup())) {
                                                                    $data[$tblSubject->getId().'Id'] = (new ToolTip(($average != '' ? '&empty; '.$average : '')
                                                                        , $tblSubjectGroup->getName()))->enableHtml();
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
                    }
                    $studentTable[] = $data;
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            )),
                            new LayoutColumn(array(
                                new TableData($studentTable, null, $tableHeaderList,
                                    array(
                                        "columnDefs" => array(
                                            array('width' => '6%', 'targets' => 2),
                                        ),
                                        'pageLength' => -1
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $DivisionId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendHeadmasterStudentOverview($DivisionId = null, $PersonId = null)
    {
        $Stage = new Stage('Schülerübersicht', 'Schüler anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student', new ChevronLeft(), array(
                'DivisionId' => $DivisionId
            )
        ));
        $Stage->addButton(new External(
            'Herunterladen','SPHERE\Application\Api\Document\Standard\GradebookOverview\Create',
            new Download(), array('PersonId' => $PersonId,'DivisionId' => $DivisionId), 'Notenübersicht herunterladen'
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if (!$tblDivision) {
            return $Stage
            .new Danger('Klasse nicht gefunden.', new Ban());
        }

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage
            .new Danger('Schüler nicht gefunden.', new Ban());
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $dataList = array();
        $columnDefinition = array();
        $columnDefinition['Subject'] = 'Fach';
        $periodListCount = array();
        if (( $tblYear = $tblDivision->getServiceTblYear() )) {
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                $tblPerson, $tblYear
            );
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if ($tblPeriodList && $tblDivisionSubjectList) {
                foreach ($tblPeriodList as $tblPeriod) {
                    $maxGradeCount = 0;
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if (( $tblSubject = $tblDivisionSubject->getServiceTblSubject() )) {
                            $dataList[$tblDivisionSubject->getId()]['Subject'] = $tblSubject->getAcronym()
                                .' '.new Muted($tblSubject->getName());
                            $count = 0;
                            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblPeriod,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if ($tblTestList) {

                                // Sortierung der Tests nach Datum
                                $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date',
                                    new DateTimeSorter());

                                /** @var TblTest $tblTest */
                                foreach ($tblTestList as $tblTest) {
                                    if ($tblTest->getServiceTblGradeType()
                                        && ( $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                            $tblTest, $tblPerson
                                        ) )
                                    ) {
                                        if ($tblGrade->getGrade()) {
                                            $count++;
                                            if (!isset( $columnDefinition['Grade'.$tblPeriod->getId().$count] )) {
                                                $columnDefinition['Grade'.$tblPeriod->getId().$count] = '';
                                            }
                                            $value = $tblGrade->getDisplayGrade()
                                                .' ('.$tblGrade->getTblGradeType()->getCode().')';
                                            $dataList[$tblDivisionSubject->getId()]['Grade'.$tblPeriod->getId().$count] =
                                                $tblGrade->getTblGradeType()->isHighlighted() ? new Bold($value) : $value;
                                        }
                                    }
                                }
                            }
                            if ($count > $maxGradeCount) {
                                $maxGradeCount = $count;
                            }
                        }
                    }
                    $columnDefinition['PeriodAverage'.$tblPeriod->getId()] = '&#216;';
                    $maxGradeCount++;
                    $periodListCount[$tblPeriod->getId()] = $maxGradeCount;
                }
                $columnDefinition['YearAverage'] = '&#216;';
            }
        }

        if (!empty( $dataList )) {
            foreach ($dataList as $divisionSubjectId => $array) {
                foreach ($columnDefinition as $column => $name) {
                    if (( $tblDivisionSubject = Division::useService()->getDivisionSubjectById($divisionSubjectId) )) {
                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                            $tblDivisionSubject->getTblDivision(),
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        if (strpos($column, 'PeriodAverage') !== false) {
                            $periodId = substr($column, strlen('PeriodAverage'));
                            $tblPeriod = Term::useService()->getPeriodById($periodId);
                            if ($tblPeriod) {
                                /*
                                * Calc Average
                                */
                                $average = Gradebook::useService()->calcStudentGrade(
                                    $tblPerson,
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null,
                                    $tblPeriod,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                );
                                if (is_array($average)) {
                                    $average = '';
                                } else {
                                    $posStart = strpos($average, '(');
                                    if ($posStart !== false) {
                                        $average = substr($average, 0, $posStart);
                                    }
                                }
                                $dataList[$tblDivisionSubject->getId()][$column] = new Bold($average);
                            }
                        } elseif (strpos($column, 'YearAverage') !== false) {

                            /*
                            * Calc Average
                            */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null,
                                null,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if (is_array($average)) {
                                $average = '';
                            } else {
                                $posStart = strpos($average, '(');
                                if ($posStart !== false) {
                                    $average = substr($average, 0, $posStart);
                                }
                            }
                            $dataList[$tblDivisionSubject->getId()][$column] = new Bold($average);
                        } elseif (strpos($column, 'Grade') !== false) {
                            if (!isset( $dataList[$tblDivisionSubject->getId()][$column] )) {
                                $dataList[$tblDivisionSubject->getId()][$column] = '';
                            }
                        }
                    }
                }
            }
        }

        $tableData = new TableData(
            $dataList, null, $columnDefinition,
            array(
                "columnDefs" => array(
                    array(
                        "orderable" => false,
                        "targets"   => '_all'
                    ),
                ),
                'pageLength' => -1
            )
        );

        // oberste Tabellen-Kopf-Zeile erstellen
        $headTableColumnList = array();
        $headTableColumnList[] = new TableColumn('', 1, '20%');
        if (!empty( $periodListCount )) {
            foreach ($periodListCount as $periodId => $value) {
                $tblPeriod = Term::useService()->getPeriodById($periodId);
                if ($tblPeriod) {
                    $headTableColumnList[] = new TableColumn($tblPeriod->getDisplayName(), $value);
                }
            }
            $headTableColumnList[] = new TableColumn('Gesamt');
        }
        $tableData->prependHead(
            new TableHead(
                new TableRow(
                    $headTableColumnList
                )
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Klasse',
                                $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                $tblPerson->getLastFirstName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(
                            $tableData
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendTeacherStudentOverview($DivisionId = null, $PersonId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student', new ChevronLeft(), array(
                'DivisionId' => $DivisionId
            )
        ));
        $Stage->addButton(new External(
            'Herunterladen','SPHERE\Application\Api\Document\Standard\GradebookOverview\Create',
            new Download(), array('PersonId' => $PersonId,'DivisionId' => $DivisionId), 'Notenübersicht herunterladen'
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if (!$tblDivision) {
            return $Stage
            . new Danger('Klasse nicht gefunden.', new Ban());
        }

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage
            . new Danger('Schüler nicht gefunden.', new Ban());
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $dataList = array();
        $columnDefinition = array();
        $columnDefinition['Subject'] = 'Fach';
        $periodListCount = array();
        if (($tblYear = $tblDivision->getServiceTblYear())) {
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                $tblPerson, $tblYear
            );
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if ($tblPeriodList && $tblDivisionSubjectList) {
                foreach ($tblPeriodList as $tblPeriod) {
                    $maxGradeCount = 0;
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                            $dataList[$tblDivisionSubject->getId()]['Subject'] = $tblSubject->getAcronym()
                                . ' ' . new Muted($tblSubject->getName());
                            $count = 0;
                            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblPeriod,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if ($tblTestList) {

                                // Sortierung der Tests nach Datum
                                $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date',
                                    new DateTimeSorter());

                                /** @var TblTest $tblTest */
                                foreach ($tblTestList as $tblTest) {
                                    if ($tblTest->getServiceTblGradeType()
                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                            $tblTest, $tblPerson
                                        ))
                                    ) {
                                        if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '') {
                                            $count++;
                                            if (!isset($columnDefinition['Grade' . $tblPeriod->getId() . $count])) {
                                                $columnDefinition['Grade' . $tblPeriod->getId() . $count] = '';
                                            }
                                            $value = $tblGrade->getDisplayGrade()
                                                . ' (' . $tblGrade->getTblGradeType()->getCode() . ')';
                                            $dataList[$tblDivisionSubject->getId()]['Grade' . $tblPeriod->getId() . $count] =
                                                $tblGrade->getTblGradeType()->isHighlighted() ? new Bold($value) : $value;
                                        }
                                    }
                                }
                            }
                            if ($count > $maxGradeCount) {
                                $maxGradeCount = $count;
                            }
                        }
                    }
                    $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216;';
                    $maxGradeCount++;
                    $periodListCount[$tblPeriod->getId()] = $maxGradeCount;
                }
                $columnDefinition['YearAverage'] = '&#216;';
            }
        }

        if (!empty($dataList)) {
            foreach ($dataList as $divisionSubjectId => $array) {
                foreach ($columnDefinition as $column => $name) {
                    if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($divisionSubjectId))) {
                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                            $tblDivisionSubject->getTblDivision(),
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        if (strpos($column, 'PeriodAverage') !== false) {
                            $periodId = substr($column, strlen('PeriodAverage'));
                            $tblPeriod = Term::useService()->getPeriodById($periodId);
                            if ($tblPeriod) {
                                /*
                                * Calc Average
                                */
                                $average = Gradebook::useService()->calcStudentGrade(
                                    $tblPerson,
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null,
                                    $tblPeriod,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                );
                                if (is_array($average)) {
                                    $average = '';
                                } else {
                                    $posStart = strpos($average, '(');
                                    if ($posStart !== false) {
                                        $average = substr($average, 0, $posStart);
                                    }
                                }
                                $dataList[$tblDivisionSubject->getId()][$column] = new Bold($average);
                            }
                        } elseif (strpos($column, 'YearAverage') !== false) {

                            /*
                            * Calc Average
                            */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null,
                                null,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if (is_array($average)) {
                                $average = '';
                            } else {
                                $posStart = strpos($average, '(');
                                if ($posStart !== false) {
                                    $average = substr($average, 0, $posStart);
                                }
                            }
                            $dataList[$tblDivisionSubject->getId()][$column] = new Bold($average);
                        } elseif (strpos($column, 'Grade') !== false) {
                            if (!isset($dataList[$tblDivisionSubject->getId()][$column])) {
                                $dataList[$tblDivisionSubject->getId()][$column] = '';
                            }
                        }
                    }
                }
            }
        }

        $tableData = new TableData(
            $dataList, null, $columnDefinition,
            array(
                "columnDefs" => array(
                    array(
                        "orderable" => false,
                        "targets" => '_all'
                    ),
                ),
                'pageLength' => -1
            )
        );

        // oberste Tabellen-Kopf-Zeile erstellen
        $headTableColumnList = array();
        $headTableColumnList[] = new TableColumn('', 1, '20%');
        if (!empty($periodListCount)) {
            foreach ($periodListCount as $periodId => $value) {
                $tblPeriod = Term::useService()->getPeriodById($periodId);
                if ($tblPeriod) {
                    $headTableColumnList[] = new TableColumn($tblPeriod->getDisplayName(), $value);
                }
            }
            $headTableColumnList[] = new TableColumn('Gesamt');
        }
        $tableData->prependHead(
            new TableHead(
                new TableRow(
                    $headTableColumnList
                )
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Klasse',
                                $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                $tblPerson->getLastFirstName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(
                            $tableData
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $tblMinimumGradeCountList
     *
     * @return false|Panel
     */
    private function getMinimumGradeCountPanel($tblMinimumGradeCountList)
    {

        if ($tblMinimumGradeCountList) {

            $minimumGradeCountContent = array();
            $count = 1;

            /** @var TblMinimumGradeCount $tblMinimumGradeCount */
            foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {

                $minimumGradeCountContent[] = array(
                    'Number' => '#' . $count++,
                    'SchoolType' => $tblMinimumGradeCount->getSchoolTypeDisplayName(),
                    'Level' => $tblMinimumGradeCount->getLevelDisplayName(),
                    'Subject' => $tblMinimumGradeCount->getSubjectDisplayName(),
                    'GradeType' => $tblMinimumGradeCount->getGradeTypeDisplayName(),
                    'Period' => $tblMinimumGradeCount->getPeriodDisplayName(),
                    'Count' => $tblMinimumGradeCount->getCount()
                );
            }

            if (!empty($minimumGradeCountContent)) {

                return new Panel(
                    'Mindesnotenanzahl',
                    new TableData($minimumGradeCountContent, null,
                        array(
                            'Number' => 'Nummer',
                            'SchoolType' => 'Schulart',
                            'Level' => 'Klassenstufe',
                            'Subject' => 'Fach',
                            'GradeType' => 'Zensuren-Typ',
                            'Period' => 'Zeitraum',
                            'Count' => 'Anzahl',
                        ),
                        array(
                            "columnDefs" => array(
                                array(
                                    "orderable" => false,
                                    "targets" => '_all'
                                ),
                            ),
                            'pageLength' => -1,
                            'paging' => false,
                            'info' => false,
                            'searching' => false,
                            'responsive' => false
                        )
                    ),
                    Panel::PANEL_TYPE_INFO
                );
            }
        }

        return false;
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendActivateGradeType(
        $Id = null
    ) {

        $Route = '/Education/Graduation/Gradebook/GradeType';

        $Stage = new Stage('Zensuren-Typ', 'Aktivieren/Deaktivieren');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', $Route, new ChevronLeft())
        );

        if (($tblGradeType = Gradebook::useService()->getGradeTypeById($Id))) {
            $IsActive = !$tblGradeType->isActive();
            if ((Gradebook::useService()->setGradeTypeActive($tblGradeType, $IsActive))) {

                return $Stage . new Success('Die Zensuren-Typ wurde '
                        . ($IsActive ? 'aktiviert.' : 'deaktiviert.')
                        , new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS);
            } else {

                return $Stage . new Danger('Die Zensuren-Typ konnte nicht '
                        . ($IsActive ? 'aktiviert' : 'deaktiviert') . ' werden.'
                        , new Ban())
                    . new Redirect($Route, Redirect::TIMEOUT_ERROR);
            }
        } else {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden.', new Ban())
                . new Redirect($Route, Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblTest $tblTest
     * @param TblGrade $tblGrade
     * @param $subTableHeaderList
     * @param $subTableDataList
     * @param $isShownAverage
     */
    private function addTest(TblTest $tblTest,TblGrade $tblGrade, &$subTableHeaderList, &$subTableDataList, $isShownAverage)
    {
        $date = $tblGrade->getDate() ? $tblGrade->getDate() : $tblTest->getDate();
        if (strlen($date) > 6) {
            $date = substr($date, 0, 6);
        }

        $gradeMirror = array();
        $testAverage = Gradebook::useService()->getAverageByTest($tblTest, $gradeMirror);
        $description = $tblTest->getDescription();
        $text = new Small(new Muted($date)) . '<br>'
            . ($tblTest->getServiceTblGradeType()->isHighlighted()
                ? $tblTest->getServiceTblGradeType()->getCode()
                : new Muted($tblTest->getServiceTblGradeType()->getCode()));


        $toolTip = $description ? 'Thema: ' . $description : '';
        if ($isShownAverage) {
            if (!empty($gradeMirror)) {
                $toolTip .= ($toolTip ? '<br />' : '');
                $line[0] = '';
                $line[1] = '';
                foreach ($gradeMirror as $key => $value) {
                    $space = ($value > 9 && $key < 10) ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                    $line[0] .= $space . $key;
                    $line[1] .= '&nbsp;' . $value;
                }
                $toolTip .= $line[0] . '<br />' . $line[1];
            }
            if ($testAverage) {
                $toolTip .= ($toolTip ? '<br />' : '') . '&#216; ' . $testAverage;
            }
        }

        $subTableHeaderList['Test' . $tblTest->getId()] =
            (new ToolTip($text, $toolTip))->enableHtml();

        $gradeValue = $tblGrade->getGrade();
        if ($gradeValue) {
            $trend = $tblGrade->getTrend();
            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                $gradeValue .= '+';
            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                $gradeValue .= '-';
            }
        }

        $subTableDataList[0]['Test' . $tblTest->getId()] = $gradeValue ? $gradeValue : '';
    }
}
