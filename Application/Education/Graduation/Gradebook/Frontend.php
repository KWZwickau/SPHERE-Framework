<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend extends Extension implements IFrontendInterface
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

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        if (!$tblTestType || !($tblGradeTypeAllTest = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllTest = array();
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if (!$tblTestType || !($tblGradeTypeAllBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllBehavior = array();
        }
        $tblGradeTypeAll = array_merge($tblGradeTypeAllTest, $tblGradeTypeAllBehavior);

        $TableContent = array();
        if (!empty($tblGradeTypeAll)) {
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
                $Item['Description'] = $tblGradeType->getDescription();
                $Item['Option'] = (new Standard('', '/Education/Graduation/Gradebook/GradeType/Edit', new Edit(), array(
                        'Id' => $tblGradeType->getId()
                    ), 'Zensuren-Typ bearbeiten'))
                    . (new Standard('', '/Education/Graduation/Gradebook/GradeType/Destroy', new Remove(),
                        array('Id' => $tblGradeType->getId()), 'Löschen'));

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
                                'Category' => 'Kategorie',
                                'DisplayName' => 'Name',
                                'DisplayCode' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                'Option' => ''
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
     * @return Stage
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

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
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
                    if ($tblDivisionTeacher->getTblDivision()) {
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
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    foreach ($subjectList as $subjectId => $value) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if (is_array($value)) {
                            foreach ($value as $subjectGroupId => $subValue) {
                                /** @var TblSubjectGroup $item */
                                $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => $item->getName(),
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject, $item
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Gradebook/Selected', new Select(), array(
                                        'DivisionSubjectId' => $subValue
                                    ),
                                        'Auswählen'
                                    )
                                );
                            }
                        } else {
                            $divisionSubjectTable[] = array(
                                'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                'Type' => $tblDivision->getTypeName(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getName(),
                                'SubjectGroup' => '',
                                'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                    $tblDivision, $tblSubject
                                ),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Gradebook/Gradebook/Selected', new Select(), array(
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
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
    public function frontendHeadmasterGradeBook()
    {

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl aller Notenbücher.'
        );

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
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
                        if (is_array($value)) {
                            foreach ($value as $subjectGroupId => $subValue) {
                                $item = Division::useService()->getSubjectGroupById($subjectGroupId);

                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => $item->getName(),
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject, $item
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Headmaster/Gradebook/Selected',
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
                                'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                'Type' => $tblDivision->getTypeName(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getName(),
                                'SubjectGroup' => '',
                                'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                    $tblDivision, $tblSubject
                                ),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Gradebook/Headmaster/Gradebook/Selected', new Select(),
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
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
    public function frontendSelectedGradeBook($DivisionSubjectId = null)
    {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject, '/Education/Graduation/Gradebook/Gradebook');

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

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblSubject = $tblDivisionSubject->getServiceTblSubject();
        $tblScoreRule = false;
        $scoreRuleText = array();
        if ($tblDivision && $tblSubject) {
            $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                $tblDivision,
                $tblSubject
            );
            if ($tblScoreRuleDivisionSubject) {
                if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                    $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
                    if ($tblScoreRule) {
                        $scoreRuleText[] = new Bold($tblScoreRule->getName());
                        $tblScoreRuleConditionListByRule = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                        if ($tblScoreRuleConditionListByRule) {
                            $tblScoreRuleConditionListByRule =
                                $this->getSorter($tblScoreRuleConditionListByRule)->sortObjectList('Priority');

                            /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                            foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                                $scoreRuleText[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . 'Priorität: '
                                    . $tblScoreRuleConditionList->getTblScoreCondition()->getPriority()
                                    . '&nbsp;&nbsp;&nbsp;' . $tblScoreRuleConditionList->getTblScoreCondition()->getName();
                            }
                        } else {
                            $scoreRuleText[] = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                            ));
                        }
                    }
                }
            }
        }

        $grades = array();
        $rowList = array();
        $errorRowList = array();

        $tblYear = $tblDivision->getServiceTblYear();
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        } else {
            $tblPeriodList = false;
        }
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblStudentList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        } else {
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        }

        if ($tblStudentList) {
            foreach ($tblStudentList as $key => $row) {
                $name[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstSecondName());
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $tblStudentList);

            foreach ($tblStudentList as $tblPerson) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    $grades[$tblPerson->getId()] = Gradebook::useService()->getGradesByStudent(
                        $tblPerson,
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblTestType
                    );
                }
            }
        }

        $gradePositions = array();
        $columnList[] = new LayoutColumn(new Title(new Bold('Schüler')), 2);
        if ($tblPeriodList) {
            $width = floor(9 / count($tblPeriodList));
            foreach ($tblPeriodList as $tblPeriod) {
                $columnList[] = new LayoutColumn(
                    new Title(new Bold($tblPeriod->getDisplayName()))
                    , $width
                );
            }
            // Gesamtjahr
            $columnList[] = new LayoutColumn(
                new Title(new Bold('GJ'))
                , 1
            );
            $rowList[] = new LayoutRow($columnList);
            $columnList = array();
            $columnList[] = new LayoutColumn(new Header(' '), 2);
            $columnSecondList[] = new LayoutColumn(new Header(' '), 2);
            foreach ($tblPeriodList as $tblPeriod) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblTestType,
                        $tblPeriod,
                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                    );
                    if ($tblTestList) {
                        $columnSubList = array();
                        $columnSecondSubList = array();
                        $pos = 0;
                        foreach ($tblTestList as $tblTest) {
                            if ($tblTest->getServiceTblGradeType()) {
                                $gradePositions[$tblPeriod->getId()][$pos++] = $tblTest->getId();
                                $columnSubList[] = new LayoutColumn(
                                    new Header(
                                        $tblTest->getServiceTblGradeType()->isHighlighted()
                                            ? new Bold($tblTest->getServiceTblGradeType()->getCode()) : $tblTest->getServiceTblGradeType()->getCode())
                                    , 1);
                                $date = $tblTest->getDate();
                                if (strlen($date) > 6) {
                                    $date = substr($date, 0, 6);
                                }
                                $columnSecondSubList[] = new LayoutColumn(
                                    new Header(
                                        $tblTest->getServiceTblGradeType()->isHighlighted()
                                            ? new Bold(new Small($date)) : new Small($date))
                                    , 1);
                            }
                        }
                        $columnSubList[] = new LayoutColumn(new Header(new Bold('&#216;')), 1);
                        $columnSubList[] = new LayoutColumn(new Header(new Bold('P')), 1);
                        $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                            $width);
                        $columnSecondList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSecondSubList))),
                            $width);
                    } else {
                        $columnList[] = new LayoutColumn(new Header(' '), $width);
                        $columnSecondList[] = new LayoutColumn(new Header(' '), $width);
                    }
                }
            }

            $columnSubList = array();
            $columnSubList[] = new LayoutColumn(new Header(new Bold('&#216;')), 6);
            $columnSubList[] = new LayoutColumn(new Header(new Bold('P')), 6);
            $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),1);

            $rowList[] = new LayoutRow($columnSecondList);
            $rowList[] = new LayoutRow($columnList);

            if (!empty($grades)) {
                foreach ($grades as $personId => $gradeList) {
                    $tblPerson = Person::useService()->getPersonById($personId);
                    $columnList = array();
                    if ($tblDivisionSubject->getServiceTblSubject()) {
                        $columnList[] = new LayoutColumn(new Container($tblPerson->getLastFirstName()), 2);
                        foreach ($tblPeriodList as $tblPeriod) {
                            $columnSubList = array();
                            if (isset($gradePositions[$tblPeriod->getId()])) {
                                foreach ($gradePositions[$tblPeriod->getId()] as $pos => $testId) {
                                    $hasFound = false;
                                    /** @var TblGrade $grade */
                                    if ($gradeList) {
                                        foreach ($gradeList as $grade) {
                                            if ($grade->getServiceTblTest()) {
                                                $gradeValue = $grade->getGrade();
                                                $trend = $grade->getTrend();
                                                if (TblGrade::VALUE_TREND_PLUS === $trend) {
                                                    $gradeValue .= '+';
                                                } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                                                    $gradeValue .= '-';
                                                }
                                                if ($testId === $grade->getServiceTblTest()->getId()) {
                                                    $columnSubList[] = new LayoutColumn(
                                                        new Container($grade->getTblGradeType() ? ($grade->getTblGradeType()->isHighlighted()
                                                            ? new Bold($gradeValue) : $gradeValue) : $gradeValue)
                                                        , 1);
                                                    $hasFound = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    if (!$hasFound) {
                                        $columnSubList[] = new LayoutColumn(
                                            new Container(' '), 1
                                        );
                                    }
                                }
                            } else {
                                $columnSubList[] = new LayoutColumn(
                                    new Container(' '), 12
                                );
                            }

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
                            $priority = '';
                            if (is_array($average)) {
                                $errorRowList = $average;
                                $average = '';
                            } else {
                                $posStart = strpos($average, '(');
                                if ($posStart !== false) {
                                    $posEnd = strpos($average, ')');
                                    if ($posEnd !== false) {
                                        $priority = substr($average, $posStart + 1, $posEnd - ($posStart + 1));
                                    }
                                    $average = substr($average, 0, $posStart);
                                }
                            }

                            $columnSubList[] = new LayoutColumn(new Container(new Bold($average)), 1);
                            $columnSubList[] = new LayoutColumn(new Container(new Bold($priority)), 1);

                            $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                                $width);
                        }

                        // total average (Gesamtjahr)
                        $average = Gradebook::useService()->calcStudentGrade(
                            $tblPerson,
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblTestType,
                            $tblScoreRule ? $tblScoreRule : null,
                            null,
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        $priority = '';
                        if (is_array($average)) {
                            $errorRowList = $average;
                            $average = '';
                        } else {
                            $posStart = strpos($average, '(');
                            if ($posStart !== false) {
                                $posEnd = strpos($average, ')');
                                if ($posEnd !== false) {
                                    $priority = substr($average, $posStart + 1, $posEnd - ($posStart + 1));
                                }
                                $average = substr($average, 0, $posStart);
                            }
                        }

                        $columnSubList = array();
                        $columnSubList[] = new LayoutColumn(new Container(new Bold($average)), 6);
                        $columnSubList[] = new LayoutColumn(new Container(new Bold($priority)), 6);

                        $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                            1);

                        $rowList[] = new LayoutRow($columnList);
                    }
                }
            }
        }

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
                            )
                        ),
                    )
                ),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null)
            ))
            . new Layout(new LayoutGroup($rowList))
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
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Headmaster/Gradebook',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Headmaster/Gradebook');

        return $Stage;
    }

    /**
     * @param null $YearId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendStudentGradebook($YearId = null, $Select = null)
    {

        $Stage = new Stage('Notenübersicht', 'Schüler/Eltern');
        $Stage->setMessage(
            'Anzeige der Zensuren für die Schüler und Eltern. <br>
            Der angemeldete Schüler sieht nur seine eigenen Zensuren. <br>
            Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Schützlinge. <br>'
        );

        $tblYearAll = Term::useService()->getYearAll();
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $rowList = array();

        if ($YearId !== null) {
            $tblYear = Term::useService()->getYearById($YearId);
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }

            if ($tblPerson) {

                $rowList = $this->createContentForStudentGradebook($tblYear, $tblPerson,
                    $rowList, $tblTestType);

                $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblPersonRelationshipList) {
                    foreach ($tblPersonRelationshipList as $relationship) {
                        if ($relationship->getTblType()->getName() == 'Sorgeberechtigt' && $relationship->getServiceTblPersonTo()) {
                            $rowList[] = new LayoutRow(new LayoutColumn(new Header('&nbsp;'), 12));
                            $rowList = $this->createContentForStudentGradebook($tblYear,
                                $relationship->getServiceTblPersonTo(), $rowList, $tblTestType);
                        }
                    }
                }
            }
        } else {
            $tblYear = new TblYear();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Well(
                            Gradebook::useService()->getYear(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new SelectBox('Select[Year]', 'Schuljahr',
                                                    array('{{Name}}' => $tblYearAll)),
                                                12
                                            ),
                                        )),
                                    ))
                                    , new Primary('Auswählen', new Select())
                                ), $Select, '/Education/Graduation/Gradebook/Student/Gradebook'
                            )
                        )
                    ),
                    ($YearId !== null ? new LayoutColumn(
                        new Panel('Schuljahr', $tblYear->getName(), Panel::PANEL_TYPE_INFO)
                    ) : null)
                ))),
                ($YearId !== null ? new LayoutGroup($rowList) : null)
            ))
        );
        return $Stage;
    }

    /**
     * @param $tblYear
     * @param $tblPerson
     * @param $rowList
     * @param $tblTestType
     *
     * @return array
     */
    private function createContentForStudentGradebook(
        TblYear $tblYear,
        TblPerson $tblPerson,
        $rowList,
        $tblTestType
    ) {

        $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
        if ($tblDivisionStudentList) {

            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                $tblDivision = $tblDivisionStudent->getTblDivision();
                if ($tblDivision && $tblDivision->getServiceTblYear()) {
                    if ($tblDivision->getServiceTblYear()->getId() == $tblYear->getId()) {

                        // Header
                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                        $columnList = array();
                        $columnList[] = new LayoutColumn(new Title($tblPerson->getLastFirstName()
                            . new Small(new Muted(' Klasse ' . $tblDivision->getDisplayName()))),
                            12);
                        if ($tblPeriodList) {
                            $columnList[] = new LayoutColumn(new Header(new Bold('Fach')), 2);
                            $width = (12 - 2) / count($tblPeriodList);
                            foreach ($tblPeriodList as $tblPeriod) {
                                $columnList[] = new LayoutColumn(new Header(new Bold($tblPeriod->getDisplayName())),
                                    $width);
                            }
                        }
                        $rowList[] = new LayoutRow($columnList);

                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                        if ($tblDivisionSubjectList) {
                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                                    if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                        $tblDivisionSubjectWhereGroup =
                                            Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                                $tblDivision, $tblDivisionSubject->getServiceTblSubject()
                                            );
                                        $columnList = array();
                                        if (!$tblDivisionSubjectWhereGroup) {

                                            $totalAverage = '';
                                            $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                                $tblDivisionSubject->getTblDivision(),
                                                $tblDivisionSubject->getServiceTblSubject()
                                            );
                                            if ($tblScoreRuleDivisionSubject) {
                                                if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                                                    $totalAverage = Gradebook::useService()->calcStudentGrade(
                                                        $tblPerson,
                                                        $tblDivision,
                                                        $tblDivisionSubject->getServiceTblSubject(),
                                                        $tblTestType,
                                                        $tblScoreRuleDivisionSubject->getTblScoreRule(),
                                                        null,
                                                        null,
                                                        true
                                                    );

                                                    if (is_array($totalAverage)) {
//                                                    $errorRowList = $totalAverage;
                                                        $totalAverage = '';
                                                    } else {
                                                        $posStart = strpos($totalAverage, '(');
                                                        if ($posStart !== false) {
                                                            $totalAverage = substr($totalAverage, 0, $posStart);
                                                        }
                                                    }
                                                }
                                            }

                                            $columnList[] = new LayoutColumn(
                                                new Container($tblDivisionSubject->getServiceTblSubject()->getName()
                                                    . ($totalAverage != '' ? ' ' . new Bold('&#216;' . $totalAverage) : '')),
                                                2);

                                            if ($tblPeriodList) {
                                                $width = (12 - 2) / count($tblPeriodList);
                                                foreach ($tblPeriodList as $tblPeriod) {
                                                    $tblGradeList = Gradebook::useService()->getGradesByStudent(
                                                        $tblPerson,
                                                        $tblDivision,
                                                        $tblDivisionSubject->getServiceTblSubject(),
                                                        $tblTestType,
                                                        $tblPeriod
                                                    );
                                                    $subColumnList = array();
                                                    if ($tblGradeList) {
                                                        foreach ($tblGradeList as $tblGrade) {
                                                            $tblTest = $tblGrade->getServiceTblTest();
                                                            if ($tblTest) {
                                                                if ($tblTest->getReturnDate()) {
                                                                    $testDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                                                                    $now = (new \DateTime('now'))->format("Y-m-d");
                                                                    if ($testDate <= $now) {
                                                                        $gradeValue = $tblGrade->getGrade();
                                                                        if ($gradeValue) {
                                                                            $trend = $tblGrade->getTrend();
                                                                            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                                                                                $gradeValue .= '+';
                                                                            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                                                                                $gradeValue .= '-';
                                                                            }
                                                                        }
                                                                        if ($tblGrade->getGrade()) {
                                                                            $subColumnList[] = new LayoutColumn($tblGrade->getGrade() ? $gradeValue : ' ',
                                                                                1);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    /*
                                                   * Calc Average
                                                   */
                                                    $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                                        $tblDivisionSubject->getTblDivision(),
                                                        $tblDivisionSubject->getServiceTblSubject()
                                                    );
                                                    if ($tblScoreRuleDivisionSubject) {
                                                        if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                                                            $average = Gradebook::useService()->calcStudentGrade(
                                                                $tblPerson,
                                                                $tblDivision,
                                                                $tblDivisionSubject->getServiceTblSubject(),
                                                                $tblTestType,
                                                                $tblScoreRuleDivisionSubject->getTblScoreRule(),
                                                                $tblPeriod,
                                                                null,
                                                                true
                                                            );
                                                            if (is_array($average)) {
                                                                $average = '';
                                                            } else {
                                                                $posStart = strpos($average, '(');
                                                                if ($posStart !== false) {
                                                                    $average = substr($average, 0, $posStart);
                                                                }
                                                            }

                                                            if ($average != '') {
                                                                $subColumnList[] = new LayoutColumn(new Container(new Bold('&#216;' . $average)),
                                                                    1);
                                                            }
                                                        }
                                                    }

                                                    $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($subColumnList))),
                                                        $width);
                                                }
                                            }

                                            $rowList[] = new LayoutRow($columnList);
                                        } else {
                                            foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                                if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                                    $tblPerson)
                                                ) {
                                                    $totalAverage = '';
                                                    $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                                        $tblDivisionSubject->getTblDivision(),
                                                        $tblDivisionSubject->getServiceTblSubject()
                                                    );
                                                    if ($tblScoreRuleDivisionSubject) {
                                                        if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                                                            $totalAverage = Gradebook::useService()->calcStudentGrade(
                                                                $tblPerson,
                                                                $tblDivision,
                                                                $tblDivisionSubject->getServiceTblSubject(),
                                                                $tblTestType,
                                                                $tblScoreRuleDivisionSubject->getTblScoreRule(),
                                                                null,
                                                                null,
                                                                true
                                                            );

                                                            if (is_array($totalAverage)) {
                                                                $totalAverage = '';
                                                            } else {
                                                                $posStart = strpos($totalAverage, '(');
                                                                if ($posStart !== false) {
                                                                    $totalAverage = substr($totalAverage, 0, $posStart);
                                                                }
                                                            }
                                                        }
                                                    }

                                                    $columnList[] = new LayoutColumn(
                                                        new Container($tblDivisionSubject->getServiceTblSubject()->getName()
                                                            . ($totalAverage != '' ? ' ' . new Bold('&#216;' . $totalAverage) : '')),
                                                        2);

                                                    if ($tblPeriodList) {
                                                        $width = (12 - 2) / count($tblPeriodList);
                                                        foreach ($tblPeriodList as $tblPeriod) {
                                                            $tblGradeList = Gradebook::useService()->getGradesByStudent(
                                                                $tblPerson,
                                                                $tblDivision,
                                                                $tblDivisionSubjectGroup->getServiceTblSubject(),
                                                                $tblTestType,
                                                                $tblPeriod
                                                            );
                                                            $subColumnList = array();
                                                            if ($tblGradeList) {
                                                                foreach ($tblGradeList as $tblGrade) {
                                                                    $tblTest = $tblGrade->getServiceTblTest();
                                                                    if ($tblTest) {
                                                                        if ($tblTest->getReturnDate()) {
                                                                            $testDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                                                                            $now = (new \DateTime('now'))->format("Y-m-d");
                                                                            if ($testDate <= $now) {
                                                                                $gradeValue = $tblGrade->getGrade();
                                                                                if ($gradeValue) {
                                                                                    $trend = $tblGrade->getTrend();
                                                                                    if (TblGrade::VALUE_TREND_PLUS === $trend) {
                                                                                        $gradeValue .= '+';
                                                                                    } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                                                                                        $gradeValue .= '-';
                                                                                    }
                                                                                }
                                                                                if ($tblGrade->getGrade()) {
                                                                                    $subColumnList[] = new LayoutColumn($tblGrade->getGrade() ? $gradeValue : ' ',
                                                                                        1);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            /*
                                                             * Calc Average
                                                             */
                                                            $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                                                $tblDivisionSubject->getTblDivision(),
                                                                $tblDivisionSubject->getServiceTblSubject()
                                                            );
                                                            if ($tblScoreRuleDivisionSubject) {
                                                                if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                                                                    $average = Gradebook::useService()->calcStudentGrade(
                                                                        $tblPerson,
                                                                        $tblDivision,
                                                                        $tblDivisionSubject->getServiceTblSubject(),
                                                                        $tblTestType,
                                                                        $tblScoreRuleDivisionSubject->getTblScoreRule(),
                                                                        $tblPeriod,
                                                                        null,
                                                                        true
                                                                    );
                                                                    if (is_array($average)) {
                                                                        $average = '';
                                                                    } else {
                                                                        $posStart = strpos($average, '(');
                                                                        if ($posStart !== false) {
                                                                            $average = substr($average, 0, $posStart);
                                                                        }
                                                                    }

                                                                    if ($average != '') {
                                                                        $subColumnList[] = new LayoutColumn(new Container(new Bold('&#216;' . $average)),
                                                                            1);
                                                                    }
                                                                }
                                                            }

                                                            $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($subColumnList))),
                                                                $width);
                                                        }
                                                    }

                                                    $rowList[] = new LayoutRow($columnList);
                                                    $columnList = array();
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
            return $rowList;
        }
        return $rowList;
    }

    /**
     * @param null $ScoreRule
     *
     * @return Stage
     */
    public function frontendScore(
        $ScoreRule = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvorschriften, für die automatische Durchschnittsberechnung der Zensuren, verwaltet. <br>
            Die Berechnungsvorschrift bildet die 1. Ebene und setzt sich aus einer oder mehrerer Berechnungsvarianten
            zusammen.'
        );

        $this->setScoreStageMenuButtons($Stage);

        $tblScoreRuleAll = Gradebook::useService()->getScoreRuleAll();
        if ($tblScoreRuleAll) {
            foreach ($tblScoreRuleAll as &$tblScoreRule) {

                $structure = array();
                if ($tblScoreRule->getDescription() != '') {
                    $structure[] = 'Beschreibung: ' . $tblScoreRule->getDescription() . '<br>';
                }

                $tblScoreConditions = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                if ($tblScoreConditions) {
                    $tblScoreConditions = $this->getSorter($tblScoreConditions)->sortObjectList('Priority');
                    $count = 1;
                    /** @var TblScoreRuleConditionList $tblScoreCondition */
                    foreach ($tblScoreConditions as $tblScoreCondition) {
                        $structure[] = $count++ . '. Berechnungsvariante: ' . $tblScoreCondition->getTblScoreCondition()->getName()
                            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Priortität: '
                            . $tblScoreCondition->getTblScoreCondition()->getPriority();

                        $tblScoreConditionGradeTypeListByCondition = Gradebook::useService()->getScoreConditionGradeTypeListByCondition(
                            $tblScoreCondition->getTblScoreCondition()
                        );
                        if ($tblScoreConditionGradeTypeListByCondition) {
                            $list = array();
                            foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                if ($tblScoreConditionGradeTypeList->getTblGradeType()) {
                                    $list[] = $tblScoreConditionGradeTypeList->getTblGradeType()->getName();
                                }
                            }

                            $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Bedingungen: ' . implode(', ',
                                    $list);
                        }

                        $tblScoreConditionGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition(
                            $tblScoreCondition->getTblScoreCondition()
                        );
                        if ($tblScoreConditionGroupListByCondition) {
                            foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroupList) {
                                $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Zensuren-Gruppe: '
                                    . $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                                    . $tblScoreConditionGroupList->getTblScoreGroup()->getMultiplier();

                                $tblGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListByGroup(
                                    $tblScoreConditionGroupList->getTblScoreGroup()
                                );
                                if ($tblGradeTypeList) {
                                    foreach ($tblGradeTypeList as $tblGradeType) {
                                        $structure[] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9702;&nbsp;&nbsp;'
                                            . 'Zensuren-Typ: '
                                            . ($tblGradeType->getTblGradeType() ? $tblGradeType->getTblGradeType()->getName() : '')
                                            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                                            . $tblGradeType->getMultiplier();
                                    }
                                } else {
                                    $structure[] = new Warning('Kein Zenuren-Typ hinterlegt.', new Ban());
                                }
                            }
                        } else {
                            $structure[] = new Warning('Keine Zenuren-Gruppe hinterlegt.', new Ban());
                        }
                        $structure[] = ' ';
                    }
                } else {
                    $structure[] = new Warning('Keine Berechnungsvariante hinterlegt.', new Ban());
                }

                if (empty($structure)) {
                    $tblScoreRule->Structure = '';
                } else {
                    $tblScoreRule->Structure = implode('<br>', $structure);
                }

                $tblScoreRule->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Edit', new Edit(),
                        array('Id' => $tblScoreRule->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Select', new Listing(),
                        array('Id' => $tblScoreRule->getId()), 'Berechnungsvarianten auswählen'));
            }
        }

        $Form = $this->formScoreRule()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblScoreRuleAll, null, array(
                                'Name' => 'Name',
                                'Structure' => '',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreRule($Form, $ScoreRule))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formScoreRule()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreRule[Name]', '', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('ScoreRule[Description]', '', 'Beschreibung'), 8
                ),
            ))
        )));
    }

    /**
     * @param null $ScoreCondition
     *
     * @return Stage
     */
    public function frontendScoreCondition(
        $ScoreCondition = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvarianten verwaltet. <br>
            Die Berechnungsvariante bildet die 2. Ebene der Berechnungsvorschriften und setzt sich aus einer Priorität
            , Zensuren-Gruppen und Bedingungen (Zensuren-Typen) zusammen. <br>
            Die Priorität gibt an, in welcher Reihenfolge die Berechnungsvarianten
            (falls eine Berechnungsvorschrift mehrere Berechnungsvarianten enthält) berücksichtigt werden.
            Dabei hat die Berechnungsvariante mit der niedrigsten Zahl die höchste Priorität. <br>
            Die Bedingungen (Zensuren-Typen) geben an, ob für die Durchschnittsberechnung die Berechnungsvariante gewählt wird.
            Ist keine Bedingung hinterlegt, wird diese Berechnungsvariante immer gewählt. Hingegen wenn eine oder mehrere
            Bedingung(en) hinterlegt sind, wird diese Berechnungsvariante nur gewählt, wenn alle Zensuren-Typen bei den Zensuren
            des Schülers vorhanden sind.'
        );
        $this->setScoreStageMenuButtons($Stage);

        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as &$tblScoreCondition) {
                $scoreGroups = '';
                $tblScoreGroups = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if ($tblScoreGroups) {
                    foreach ($tblScoreGroups as $tblScoreGroup) {
                        $scoreGroups .= $tblScoreGroup->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getTblScoreGroup()->getMultiplier() . ')')) . ', ';
                    }
                }
                if (($length = strlen($scoreGroups)) > 2) {
                    $scoreGroups = substr($scoreGroups, 0, $length - 2);
                }

                $gradeTypes = array();
                $tblGradeTypes = Gradebook::useService()->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
                if ($tblGradeTypes) {
                    foreach ($tblGradeTypes as $tblGradeType) {
                        if ($tblGradeType->getTblGradeType()) {
                            $gradeTypes[] = $tblGradeType->getTblGradeType()->getName();
                        }
                    }
                }
                if (empty($gradeTypes)) {
                    $gradeTypes = '';
                } else {
                    $gradeTypes = implode(', ', $gradeTypes);
                }

                $tblScoreCondition->ScoreGroups = $scoreGroups;
                $tblScoreCondition->GradeTypes = $gradeTypes;
                $tblScoreCondition->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Edit', new Edit(),
                        array('Id' => $tblScoreCondition->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Group/Select', new Listing(),
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Gruppen auswählen')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/GradeType/Select',
                        new Equalizer(),
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Typen (Bedingungen) auswählen'));
            }
        }

        $Form = $this->formScoreCondition()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblScoreConditionAll, null, array(
                                'Name' => 'Name',
                                'ScoreGroups' => 'Zensuren-Gruppen',
                                'GradeTypes' => 'Zensuren-Typen (Bedingungen)',
                                'Priority' => 'Priorität',
//                                'Round' => 'Runden',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreCondition($Form, $ScoreCondition))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formScoreCondition()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreCondition[Name]', 'Klassenarbeit 60% : Rest 40%', 'Name'), 10
                ),
//                new FormColumn(
//                    new TextField('ScoreCondition[Round]', '', 'Rundung'), 2
//                ),
                new FormColumn(
                    new NumberField('ScoreCondition[Priority]', '1', 'Priorität'), 2
                )
            ))
        )));
    }

    /**
     * @param null $ScoreGroup
     *
     * @return Stage
     */
    public function frontendScoreGroup(
        $ScoreGroup = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Zensuren-Gruppen verwaltet. <br>
            Die Zensuren-Gruppe bildet die 3. Ebene der Berechnungsvorschriften und setzt sich aus einem Faktor
            und Zensuren-Typen zusammen. <br>
            Der Faktor gibt an, wie die Zensuren-Gruppe als ganzes zu anderen Zensuren-Gruppen gewichtet wird. <br>'
        );
        $this->setScoreStageMenuButtons($Stage);

        $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as &$tblScoreGroup) {
                $gradeTypes = '';
                $tblScoreGroupGradeTypes = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                if ($tblScoreGroupGradeTypes) {
                    foreach ($tblScoreGroupGradeTypes as $tblScoreGroupGradeType) {
                        if ($tblScoreGroupGradeType->getTblGradeType()) {
                            $gradeTypes .= $tblScoreGroupGradeType->getTblGradeType()->getName() . ', ';
                        }
                    }
                }
                if (($length = strlen($gradeTypes)) > 2) {
                    $gradeTypes = substr($gradeTypes, 0, $length - 2);
                }
                $tblScoreGroup->GradeTypes = $gradeTypes;
                $tblScoreGroup->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/Edit', new Edit(),
                        array('Id' => $tblScoreGroup->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/GradeType/Select', new Listing(),
                        array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen auswählen'));
            }
        }

        $Form = $this->formScoreGroup()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblScoreGroupAll, null, array(
                                'Name' => 'Name',
                                'Multiplier' => 'Faktor',
                                'GradeTypes' => 'Zensuren-Typen',
//                                'Round' => 'Runden',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreGroup($Form, $ScoreGroup))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formScoreGroup()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreGroup[Name]', 'Rest', 'Name'), 10
                ),
//                new FormColumn(
//                    new TextField('ScoreGroup[Round]', '', 'Rundung'), 2
//                ),
                new FormColumn(
                    new TextField('ScoreGroup[Multiplier]', 'z.B. 40 für 40%', 'Faktor'), 2
                )
            ))
        )));
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeSelect(
        $Id = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
            if (empty($tblScoreGroup)) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreGroupGradeTypeListByGroup = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                $tblGradeTypeAllByGroup = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeType */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType) {
                        if ($tblScoreGroupGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByGroup[] = $tblScoreGroupGradeType->getTblGradeType();
                        }
                    }
                }

                if (!empty($tblGradeTypeAllByGroup) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByGroup,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreGroupGradeTypeListByGroup) {
                    foreach ($tblScoreGroupGradeTypeListByGroup as &$tblScoreGroupGradeTypeList) {
                        if ($tblScoreGroupGradeTypeList->getTblGradeType()) {
                            $tblScoreGroupGradeTypeList->Name = $tblScoreGroupGradeTypeList->getTblGradeType()->getName();
                            $tblScoreGroupGradeTypeList->Option =
                                (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Gradebook/Score/Group/GradeType/Remove',
                                    new Minus(), array(
                                    'Id' => $tblScoreGroupGradeTypeList->getId()
                                )))->__toString();
                        } else {
                            $tblScoreGroupGradeTypeList = false;
                        }
                    }
                    $tblScoreGroupGradeTypeListByGroup = array_filter($tblScoreGroupGradeTypeListByGroup);
                }

                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $tblGradeType->Option =
                            (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('GradeType[Multiplier]', 'Faktor', '', new Quantity()
                                            )
                                            , 7),
                                        new FormColumn(
                                            new Primary('Hinzufügen',
                                                new Plus())
                                            , 5)
                                    ))
                                ), null,
                                '/Education/Graduation/Gradebook/Score/Group/GradeType/Add', array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
                                    'tblGradeTypeId' => $tblGradeType->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Zensuren-Gruppe',
                                        $tblScoreGroup->getName()
                                        . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getMultiplier() . ')')),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($tblScoreGroupGradeTypeListByGroup, null,
                                        array(
                                            'Name' => 'Name',
                                            'Multiplier' => 'Faktor',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($tblGradeTypeAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Faktor'
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblGradeTypeId
     * @param null $GradeType
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeAdd(
        $tblScoreGroupId = null,
        $tblGradeTypeId = null,
        $GradeType = null
    ) {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ einer Zenuseren-Gruppe hinzufügen');

        if ($tblScoreGroupId === null || $tblGradeTypeId === null) {
            return $Stage;
        }

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if ($GradeType['Multiplier'] == '') {
            $multiplier = 1;
        } else {
            $multiplier = $GradeType['Multiplier'];
        }

        if ($tblScoreGroup && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup,
                $multiplier));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeRemove(
        $Id = null
    ) {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ von einer Zenuseren-Gruppe entfernen');

        $tblScoreGroupGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListById($Id);
        if ($tblScoreGroupGradeTypeList) {
            $Stage->setContent(Gradebook::useService()->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {

            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
            if (!$tblScoreCondition) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
                $tblScoreGroupAllByCondition = array();
                if ($tblScoreConditionGroupListByCondition) {
                    /** @var TblScoreConditionGroupList $tblScoreConditionGroup */
                    foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroup) {
                        $tblScoreGroupAllByCondition[] = $tblScoreConditionGroup->getTblScoreGroup();
                    }
                }

                if (!empty($tblScoreGroupAllByCondition) && $tblScoreGroupAll) {
                    $tblScoreGroupAll = array_udiff($tblScoreGroupAll, $tblScoreGroupAllByCondition,
                        function (TblScoreGroup $ObjectA, TblScoreGroup $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGroupListByCondition) {
                    foreach ($tblScoreConditionGroupListByCondition as &$tblScoreConditionGroupList) {
                        $tblScoreConditionGroupList->Name = $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreConditionGroupList->getTblScoreGroup()->getMultiplier() . ')'));
                        $tblScoreConditionGroupList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/Group/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreConditionGroupList->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreGroupAll) {
                    foreach ($tblScoreGroupAll as $tblScoreGroup) {
                        $tblScoreGroup->DisplayName = $tblScoreGroup->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getMultiplier() . ')'));
                        $tblScoreGroup->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/Group/Add',
                                new Plus(),
                                array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreConditionGroupListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreGroupAll, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreGroupAdd(
        $tblScoreGroupId = null,
        $tblScoreConditionId = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe einer Berechnungsvariante hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreGroup && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGroupList($tblScoreCondition,
                $tblScoreGroup));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe von einer Berechnungsvariante entfernen');

        $tblScoreConditionGroupList = Gradebook::useService()->getScoreConditionGroupListById($Id);
        if ($tblScoreConditionGroupList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGroupList($tblScoreConditionGroupList));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $ScoreRule
     * @return Stage|string
     */
    public function frontendEditScore($Id = null, $ScoreRule = null)
    {

        $Stage = new Stage('Berechnungsvorschrift', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score', new ChevronLeft())
        );

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
        if ($tblScoreRule) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreRule']['Name'] = $tblScoreRule->getName();
                $Global->POST['ScoreRule']['Description'] = $tblScoreRule->getDescription();
                $Global->savePost();
            }

            $Form = $this->formScoreRule()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvorschrift',
                                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreRule($Form, $Id, $ScoreRule))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $ScoreGroup
     * @return Stage|string
     */
    public function frontendEditScoreGroup($Id = null, $ScoreGroup = null)
    {

        $Stage = new Stage('Zensuren-Gruppe', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft())
        );

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
        if ($tblScoreGroup) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreGroup']['Name'] = $tblScoreGroup->getName();
                $Global->POST['ScoreGroup']['Round'] = $tblScoreGroup->getRound();
                $Global->POST['ScoreGroup']['Multiplier'] = $tblScoreGroup->getMultiplier();

                $Global->savePost();
            }

            $Form = $this->formScoreGroup()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zensuren-Gruppe',
                                    $tblScoreGroup->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreGroup($Form, $Id, $ScoreGroup))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $ScoreCondition
     * @return Stage|string
     */
    public function frontendEditScoreCondition($Id = null, $ScoreCondition = null)
    {

        $Stage = new Stage('Berechnungsvariante', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft())
        );

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
        if ($tblScoreCondition) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreCondition']['Name'] = $tblScoreCondition->getName();
                $Global->POST['ScoreCondition']['Round'] = $tblScoreCondition->getRound();
                $Global->POST['ScoreCondition']['Priority'] = $tblScoreCondition->getPriority();

                $Global->savePost();
            }

            $Form = $this->formScoreCondition()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvariante',
                                    $tblScoreCondition->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreCondition($Form, $Id, $ScoreCondition))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Stage
     */
    private function setScoreStageMenuButtons(Stage $Stage)
    {

        $Stage->addButton(
            new Standard('Berechnungsvorschriften', '/Education/Graduation/Gradebook/Score', new ListingTable(), null,
                'Erstellen/Berarbeiten')
        );
        $Stage->addButton(
            new Standard('Berechnungsvarianten', '/Education/Graduation/Gradebook/Score/Condition', new ListingTable(),
                null,
                'Erstellen/Berarbeiten')
        );
        $Stage->addButton(
            new Standard('Zensuren-Gruppen', '/Education/Graduation/Gradebook/Score/Group', new ListingTable(), null,
                'Erstellen/Berarbeiten')
        );
        $Stage->addButton(
            new Standard('Fach-Klassen', '/Education/Graduation/Gradebook/Score/Division', new ListingTable(), null,
                'Erstellen/Berarbeiten')
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
            if (empty($tblScoreCondition)) {
                $Stage->setContent(new Warning('Die Berechnungsvariante konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGradeTypeListByCondition = Gradebook::useService()->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                $tblGradeTypeAllByCondition = array();
                if ($tblScoreConditionGradeTypeListByCondition) {
                    /** @var TblScoreConditionGradeTypeList $tblScoreConditionGradeType */
                    foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeType) {
                        if ($tblScoreConditionGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByCondition[] = $tblScoreConditionGradeType->getTblGradeType();
                        }
                    }
                }

                if (!empty($tblGradeTypeAllByCondition) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByCondition,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGradeTypeListByCondition) {
                    foreach ($tblScoreConditionGradeTypeListByCondition as &$tblScoreConditionGradeTypeList) {
                        if ($tblScoreConditionGradeTypeList->getTblGradeType()) {
                            $tblScoreConditionGradeTypeList->Name = $tblScoreConditionGradeTypeList->getTblGradeType()->getName();
                            $tblScoreConditionGradeTypeList->Option =
                                (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/GradeType/Remove',
                                    new Minus(), array(
                                    'Id' => $tblScoreConditionGradeTypeList->getId()
                                )))->__toString();
                        } else {
                            $tblScoreConditionGradeTypeList = false;
                        }
                    }
                    $tblScoreConditionGradeTypeListByCondition = array_filter($tblScoreConditionGradeTypeListByCondition);
                }

                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $tblGradeType->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/GradeType/Add',
                                new Plus(),
                                array(
                                    'tblScoreConditionId' => $tblScoreCondition->getId(),
                                    'tblGradeTypeId' => $tblGradeType->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($tblScoreConditionGradeTypeListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($tblGradeTypeAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Faktor'
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreConditionId
     * @param null $tblGradeTypeId
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeAdd(
        $tblScoreConditionId = null,
        $tblGradeTypeId = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typ einer Berechnungsvariante hinzufügen');

        if ($tblScoreConditionId === null || $tblGradeTypeId === null) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if ($tblScoreCondition && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGradeTypeList($tblGradeType,
                $tblScoreCondition));
        }

        return $Stage;
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
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvarianten auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
            if (empty($tblScoreRule)) {
                $Stage->setContent(new Warning('Die Berechnungsvorschrift konnte nicht abgerufen werden'));
            } else {
                $tblScoreRuleConditionListByRule = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
                $tblScoreConditionAllByRule = array();
                if ($tblScoreRuleConditionListByRule) {
                    /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                    foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                        $tblScoreConditionAllByRule[] = $tblScoreRuleConditionList->getTblScoreCondition();
                    }
                }

                if (!empty($tblScoreConditionAllByRule) && $tblScoreConditionAll) {
                    $tblScoreConditionAll = array_udiff($tblScoreConditionAll, $tblScoreConditionAllByRule,
                        function (TblScoreCondition $ObjectA, TblScoreCondition $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreRuleConditionListByRule) {
                    foreach ($tblScoreRuleConditionListByRule as &$tblScoreRuleCondition) {
                        $tblScoreRuleCondition->Name = $tblScoreRuleCondition->getTblScoreCondition()->getName();
                        $tblScoreRuleCondition->Priority = $tblScoreRuleCondition->getTblScoreCondition()->getPriority();
                        $tblScoreRuleCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreRuleCondition->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreConditionAll) {
                    foreach ($tblScoreConditionAll as $tblScoreCondition) {
                        $tblScoreCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/Add',
                                new Plus(),
                                array(
                                    'tblScoreRuleId' => $tblScoreRule->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreRule->getName(), Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Berechnungsvarianten'),
                                    new TableData($tblScoreRuleConditionListByRule, null,
                                        array(
                                            'Name' => 'Name',
                                            'Priority' => 'Priorität',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Berechnungsvarianten'),
                                    new TableData($tblScoreConditionAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Priority' => 'Priorität',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreRuleId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionAdd(
        $tblScoreRuleId = null,
        $tblScoreConditionId = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante einer Berechnungsvorschrift hinzufügen');

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($tblScoreRuleId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreRule && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante von einer Berechnungsvorschrift entfernen');

        $tblScoreRuleCondition = Gradebook::useService()->getScoreRuleConditionListById($Id);
        if ($tblScoreRuleCondition) {
            $Stage->setContent(Gradebook::useService()->removeScoreRuleConditionList($tblScoreRuleCondition));
        }

        return $Stage;
    }

    /**
     * @param $Data
     * @return Stage
     */
    public function frontendScoreDivision($Data = null)
    {

        $Stage = new Stage('Fach-Klassen', 'Berechnungsvorschrift und Bewertungssystem einer Fach-Klasse zuordnen');
        $Stage->setMessage(
            'Hier können den Fach-Klassen eine Berechnungsvorschrift und ein Bewertungssystem zugeordnet werden. <br>
            Ist keine Berechnungsvorschrift bei einer Fach-Klasse zugeordnet, werden für diese Fach-Klasse alle Zensuren
            bei der Durchschnittsberechnung gleichgewichtet. <br>
            Das Bewertungssystem bestimmt, welche Zensuren (Noten, Punkte oder verbale Bewertung) bei der Fach-Klasse
            eingegeben werden können und die Anzeige des Notenspiegels.'
        );

        $this->setScoreStageMenuButtons($Stage);

        $tblScoreTypeAll = Gradebook::useService()->getScoreTypeAll();
        if ($tblScoreTypeAll) {
            array_push($tblScoreTypeAll, new TblScoreType());
        } else {
            $tblScoreTypeAll = array(new TblScoreType());
        }
        $tblScoreRuleAll = Gradebook::useService()->getScoreRuleAll();
        if ($tblScoreRuleAll) {
            array_push($tblScoreRuleAll, new TblScoreRule());
        } else {
            $tblScoreRuleAll = array(new TblScoreRule());
        }

        $tblDivisionSubjectList = array();
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionAllByYear) {
                    foreach ($tblDivisionAllByYear as $tblDivision) {
                        $tblDivisionSubjectListByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                        if ($tblDivisionSubjectListByDivision) {
                            foreach ($tblDivisionSubjectListByDivision as $tblDivisionSubject) {
                                if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                    $tblDivisionSubjectList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tblDivisionSubjectList)) {

            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                $tblDivision = $tblDivisionSubject->getTblDivision();
                $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                if ($tblDivision && $tblSubject) {
                    $tblDivisionSubject->DisplayDivision = $tblDivision->getDisplayName();
                    $tblDivisionSubject->Year = $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '';
                    $tblDivisionSubject->Type = $tblDivision->getTypeName();
                    $tblDivisionSubject->DisplaySubject = $tblSubject ? $tblSubject->getName() : '';
                    $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                        $tblDivision,
                        $tblSubject
                    );
                    if ($tblScoreRuleDivisionSubject) {
                        $Global = $this->getGlobal();

                        if (!isset($Global->POST['Data'][$tblDivision->getId()][$tblSubject->getId()])) {

                            $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
                            if ($tblScoreRule) {
                                $Global->POST['Data'][$tblDivision->getId()][$tblSubject->getId()]['Rule'] = $tblScoreRule->getId();
                            }
                            $tblScoreType = $tblScoreRuleDivisionSubject->getTblScoreType();
                            if ($tblScoreType) {
                                $Global->POST['Data'][$tblDivision->getId()][$tblSubject->getId()]['Type'] = $tblScoreType->getId();
                                $Global->POST['Data'][$tblDivision->getId()][$tblSubject->getId()]['TypeName'] = $tblScoreType->getName();
                            }
                        } else {
                            $tblScoreType = $tblScoreRuleDivisionSubject->getTblScoreType();
                            if ($tblScoreType) {
                                $Global->POST['Data'][$tblDivision->getId()][$tblSubject->getId()]['TypeName'] = $tblScoreType->getName();
                            }
                        }

                        $Global->savePost();
                    }
                    $tblDivisionSubject->ScoreRule = new SelectBox('Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . '][Rule]'
                        , null, array('Name' => $tblScoreRuleAll));

                    // Bewertungssystem nicht mehr bearbeitbar, nachdem Zensuren vergeben wurden
                    if (Gradebook::useService()->existsGrades($tblDivision, $tblSubject)) {
                        $tblDivisionSubject->ScoreType = (new TextField('Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . '][TypeName]'
                            , '', ''))->setDisabled();
                    } else {
                        $tblDivisionSubject->ScoreType = new SelectBox('Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . '][Type]'
                            , null, array('Name' => $tblScoreTypeAll));
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Gradebook::useService()->updateScoreRuleDivisionSubject(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(
                                            new FormColumn(
                                                new TableData(
                                                    $tblDivisionSubjectList,
                                                    null,
                                                    array(
                                                        'DisplayDivision' => 'Klasse',
                                                        'Year' => 'Schuljahr',
                                                        'Type' => 'Schulart',
                                                        'DisplaySubject' => 'Fach',
                                                        'ScoreRule' => 'Berechnungsvorschrift',
                                                        'ScoreType' => 'Bewertungssystem',
                                                    ),
                                                    null
                                                )
                                            )
                                        ),
                                    ))
                                    , new Primary('Speichern', new Save()))
                                , $Data
                            )
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage
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
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
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
}
