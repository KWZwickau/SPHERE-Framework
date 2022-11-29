<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradebookOld;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineGradebook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Graduation\Gradebook\ScoreRule\Frontend as FrontendScoreRule;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
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
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend extends FrontendScoreRule
{

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

                    if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject()
                        && $tblDivisionSubject->getTblDivision() && $tblDivisionSubject->getHasGrading()
                    ) {
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
                                if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject()
                                    && $tblDivisionSubject->getTblDivision() && $tblDivisionSubject->getHasGrading()
                                ) {
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

            $studentViewLinkButton[] = new Standard(
                'Schülerübersichten',
                '/Education/Graduation/Gradebook/Gradebook/Teacher/Division',
                null,
                array(),
                'Anzeige aller Noten eines Schülers über alle Fächer'
            );
            $studentViewLinkButton[] = new Standard(
                'Mindestnoten-Auswertung',
                '/Education/Graduation/Gradebook/Gradebook/MinimumGradeCount/Teacher/Reporting',
                null,
                array(
                    'PersonId' => $tblPerson ? $tblPerson->getId() : 0
                ),
                'Auswertung über die Erfüllung der Mindesnotenanzahl'
            );
        } else {
            $studentViewLinkButton = false;
        }

        $BackwardInfo = false;
        if($IsAllYears){
            $BackwardInfo = 'IsAllYears';
        }
        if($YearId){
            $BackwardInfo = $YearId;
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
                                                'DivisionSubjectId' => $subValue,
                                                'BackwardInfo' => $BackwardInfo
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
                                            'DivisionSubjectId' => $value,
                                            'BackwardInfo' => $BackwardInfo
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
                        new LayoutColumn(
                            $studentViewLinkButton
                                ? $studentViewLinkButton
                                : null
                        )
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
                                'order'      => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2)
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
                        if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()
                            && $tblDivisionSubject->getHasGrading()
                        ) {
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

        $BackwardInfo = false;
        if($IsAllYears){
            $BackwardInfo = 'IsAllYears';
        }
        if($YearId){
            $BackwardInfo = $YearId;
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
                                                'DivisionSubjectId' => $subValue,
                                                'BackwardInfo' => $BackwardInfo
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
                                            'DivisionSubjectId' => $value,
                                            'BackwardInfo' => $BackwardInfo
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
                            new Standard(
                                'Schülerübersichten',
                                '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division',
                                null,
                                array(),
                                'Anzeige aller Noten eines Schülers über alle Fächer'
                            ),
                            new Standard(
                                'Mindestnoten-Auswertung',
                                '/Education/Graduation/Gradebook/Gradebook/MinimumGradeCount/Headmaster/Reporting',
                                null,
                                array(),
                                'Auswertung über die Erfüllung der Mindesnotenanzahl'
                            )
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
                                'order'      => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                    array('orderable' => false, 'targets' => -1),
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
     * @param bool|string $BackwardInfo
     *
     * @return Stage|string
     */
    public function frontendTeacherSelectedGradebook($DivisionSubjectId = null, $BackwardInfo = false)
    {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Teacher',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Teacher', $BackwardInfo);

        return $Stage;
    }

    /**
     * @param Stage              $Stage
     * @param TblDivisionSubject $tblDivisionSubject
     * @param string             $BasicRoute
     * @param bool|string         $BackwardInfo
     *
     * @return Stage
     */
    private function contentSelectedGradeBook(
        Stage $Stage,
        TblDivisionSubject $tblDivisionSubject,
        $BasicRoute,
        $BackwardInfo = false
    ) {

        $linkArray = array();
        if($BackwardInfo == 'IsAllYears'){
            $linkArray = array('IsAllYears' => 1);
        } elseif($BackwardInfo){
            $linkArray = array('YearId' => $BackwardInfo);
        }

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft(), $linkArray));
        $Stage->addButton(
            new External(
                'Notenbuch herunterladen',
                '/Api/Document/Standard/Gradebook/Create',
                new Download(),
                array(
                    'DivisionSubjectId' => $tblDivisionSubject->getId(),
                ), false
            )
        );

        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblSubject = $tblDivisionSubject->getServiceTblSubject();
        $tblLevel = $tblDivision->getTblLevel();

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        if ($tblDivision
            && (strpos($BasicRoute, 'Headmaster') !== false
                || ($tblPerson && Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                        $tblPerson)))
        ) {
            $Stage->addButton(
                new External(
                    'Alle Notenbücher dieser Klasse herunterladen',
                    '/Api/Document/Standard/MultiGradebook/Create',
                    new Download(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                    ), false
                )
            );
        }

        if ($tblLevel
            && ($tblType = $tblLevel->getServiceTblType())
            && $tblType->getName() == 'Mittelschule / Oberschule'
            && intval($tblLevel->getName()) > 6
        ) {
            $showCourse = true;
        } else {
            $showCourse = false;
        }

        // Berechnungsvorschrift und Berechnungssystem der ausgewählten Fach-Klasse ermitteln
        $tblScoreRule = false;
        $scoreRuleText = array();
        $showPriority = false;
        if ($tblDivision && $tblSubject) {

            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblSubject,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
            if ($tblScoreRule) {
                $scoreRuleText[] = new Bold($tblScoreRule->getName());

                if (($tblScoreConditionList = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule))) {
                    if (count($tblScoreConditionList) > 1) {
                        $showPriority = true;
                        $tblScoreConditionList = $this->getSorter($tblScoreConditionList)->sortObjectBy(TblScoreCondition::ATTR_PRIORITY);
                        /** @var TblScoreCondition $tblScoreCondition */
                        foreach ($tblScoreConditionList as $tblScoreCondition) {
                            $requirements = Gradebook::useService()->getRequirementsForScoreCondition($tblScoreCondition, true);
                            $scoreRuleText[] = 'Priorität ' . $tblScoreCondition->getPriority() . ': ' .  $tblScoreCondition->getName()
                                . ($requirements ? ' (' . $requirements . ')' : '');
                        }
                    }
                } else {
                    $scoreRuleText[] = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                        new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                    ));
                }
            }
        }

        // Mindestnotenanzahlen
        if ($tblDivision) {
            if ($tblLevel
                && ($levelName = $tblLevel->getName())
                && ($levelName == '11' || $levelName == '12')
            ) {
                $isSekII = true;
            } else {
                $isSekII = false;
            }

            $tblMinimumGradeCountList = Gradebook::useService()->getMinimumGradeCountAllByDivisionSubject($tblDivisionSubject, $isSekII);
            $minimumGradeCountPanel = $this->getMinimumGradeCountPanel($tblMinimumGradeCountList, $isSekII);
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

        $YearString = '';
        $tblYear = $tblDivision->getServiceTblYear();
        if ($tblYear) {
            $YearString = $tblYear->getYear();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblDivision);
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

        // Vornoten für Schüler die in einer anderen Klasse deaktiviert sind
        if ($tblStudentList) {
            $gradeListFromAnotherDivision = Gradebook::useService()->getGradesFromAnotherDivision($tblDivision,
                $tblSubject, $tblStudentList);
        } else {
            $gradeListFromAnotherDivision = false;
        }

        $dataList = array();
        $columnDefinition = array();
        $periodListCount = array();
        $columnDefinition['Number'] = '#';
        $columnDefinition['Student'] = "Schüler";
        $columnDefinition['Picture'] = "Foto";
        $columnDefinition['Integration'] = "Integration";
        if ($showCourse) {
            $columnDefinition['Course'] = new ToolTip('Bg', 'Bildungsgang');
        }
        $countPeriod = 0;
        $countMinimumGradeCount = 1;
        $testCountTotal = 0;
        // Positionsermittlung
        $DisplayStudentNameCount = false;
        if($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'AddNameRowAtCount')){
            $DisplayStudentNameCount = $tblSetting->getValue();
        }

        // Tabellenkopf mit Test-Code und Datum erstellen
        $PeriodWithExtraName = false;
        if ($tblPeriodList) {
            $PeriodCount = 1;
            /** @var TblPeriod $tblPeriod */
            foreach ($tblPeriodList as $tblPeriod) {
                $count = 0;
                if ($gradeListFromAnotherDivision && isset($gradeListFromAnotherDivision[$tblPeriod->getId()])) {
                    $count++;
                    $columnDefinition['ExtraGrades' . $tblPeriod->getId()] = 'Vornoten';
                }

                if ($tblDivisionSubject->getServiceTblSubject()) {
                    $countPeriod++;
                    $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblTestType,
                        $tblPeriod,
                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                    );
                    if ($tblTestList) {
                        $testCountTotal += count($tblTestList);
                        // wird der Name erneut angezeigt muss auch der richtige 2.te Header breiter werden.
                        if($countPeriod > 1 && $DisplayStudentNameCount && $DisplayStudentNameCount <= $testCountTotal){
                            $PeriodWithExtraName = true;
                            $columnDefinition['againStudent'] = 'Schüler';
                        }

                        $tblTestList = Evaluation::useService()->sortTestList($tblTestList);

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
                                    ? (new ToolTip($text, htmlspecialchars($tblTest->getDescription())))->enableHtml()
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
                    } else {
                        $count++;
                        $columnDefinition['Period' . $tblPeriod->getId()] = "";
                    }

                    $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216; '.$PeriodCount++.'. HJ';
                    $count++;
                    if (isset($MinimumGradeCountSortedList[$countPeriod])) {
                        /**@var TblMinimumGradeCount $tblMinimumGradeCount **/
                        foreach ($MinimumGradeCountSortedList[$countPeriod] as $tblMinimumGradeCount) {
                            $columnDefinition['MinimumGradeCount' . $tblMinimumGradeCount->getId()] = '#' . $countMinimumGradeCount++;
                            $count++;
                        }
                    }

                    $periodListCount[$tblPeriod->getId()] = $count;
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

        $averages = array();
        // Tabellen-Inhalt erstellen
        if ($tblStudentList) {
            $studentCount = 0;
            // Ermittlung der Zensuren zu den Schülern
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $isStrikeThrough = isset($addStudentList[$tblPerson->getId()]);
                $studentCount++;
                $data = array();
                $number = $studentCount % 5 == 0 ? new Bold($studentCount) : $studentCount;
                $data['Number'] = $isStrikeThrough ? new Strikethrough($number) : $number;
                $data['againStudent'] = $data['Student'] = $isStrikeThrough
                    ? new Strikethrough($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName();
                $data['Picture'] = '';
                if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                    $data['Picture'] = new Center((new Link($tblPersonPicture->getPicture(), $tblPerson->getId()))
                        ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
                }

                if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $Integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                } else {
                    $Integration = '';
                }
                $data['Integration'] = $Integration;
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

                                    $displayGrade = ($tblTest->getServiceTblGradeType()
                                        ? ($tblTest->getServiceTblGradeType()->isHighlighted()
                                            ? new Bold($tblGrade->getDisplayGrade()) : $tblGrade->getDisplayGrade() . ' ')
                                        : $tblGrade->getDisplayGrade() . ' ');

                                    // öffentlicher Kommentar
                                    $displayGrade .= ($tblGrade->getPublicComment() != '')
                                        ? new ToolTip(' ' . new \SPHERE\Common\Frontend\Icon\Repository\Info(), $tblGrade->getPublicComment())
                                        : '';

                                    $data[$column] = $displayGrade . ($displayGradeDate
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
                                    $tblPerson, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null, $tblPeriod,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                    false,
                                    $gradeListFromAnotherDivision
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

                                    // für Fach-Klassen-Durchschnitt;
                                    if ($average !== false) {
                                        if (isset($averages[$column])) {
                                            $averages[$column]['Count']++;
                                            $averages[$column]['Sum'] += $average;
                                        } else {
                                            $averages[$column]['Count'] = 1;
                                            $averages[$column]['Sum'] = $average;
                                        }
                                    }
                                }
                                $data[$column] = $showPriority
                                    ? new ToolTip(new Bold($average), 'Priorität ' . $priority)
                                    : new Bold($average);
                            }
                        } elseif (strpos($column, 'YearAverage') !== false) {
                            /*
                            * Calc Average
                            */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null, null,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                false, $gradeListFromAnotherDivision
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

                                // für Fach-Klassen-Durchschnitt;
                                if ($average !== false) {
                                    if (isset($averages[$column])) {
                                        $averages[$column]['Count']++;
                                        $averages[$column]['Sum'] += $average;
                                    } else {
                                        $averages[$column]['Count'] = 1;
                                        $averages[$column]['Sum'] = $average;
                                    }
                                }
                            }
                            $data[$column] = $showPriority
                                ? new ToolTip(new Bold($average), 'Priorität ' . $priority)
                                : new Bold($average);
                        } elseif (strpos($column, 'Period') !== false) {
                            // keine Tests in der Periode vorhanden
                            $data[$column] = '';
                        } elseif (strpos($column, 'MinimumGradeCount') !== false) {
                            $minimumGradeCountId = str_replace('MinimumGradeCount', '', $column);
                            if (($tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($minimumGradeCountId))) {
                                $data[$column] = Gradebook::useService()->getMinimumGradeCountInfo($tblDivisionSubject,
                                    $tblPerson, $tblMinimumGradeCount);
                            }
                        // Vornoten
                        } elseif (strpos($column, 'ExtraGrades') !== false) {
                            $periodId = str_replace('ExtraGrades', '', $column);
                            if ($gradeListFromAnotherDivision && isset($gradeListFromAnotherDivision[$periodId][$tblPerson->getId()])) {
                                $data[$column] = implode(', ', $gradeListFromAnotherDivision[$periodId][$tblPerson->getId()])
                                    . '&nbsp;'
                                    . ApiGradebookOld::receiverModal()
                                    . (new Standard('', '#', new EyeOpen()))->ajaxPipelineOnClick(ApiGradebookOld::pipelineOpenExtraGradesModal(
                                        $tblDivision->getId(), $tblSubject->getId(), $periodId, $tblPerson->getId()
                                    ));
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
                    } elseif (strpos($column, 'Average') !== false && isset($averages[$column])) {
                        $data[$column] = new Muted(round($averages[$column]['Sum'] / $data[$column] = $averages[$column]['Count'], 2));
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
                    // nicht alle Filter Icons verschwinden
//                    array(
//                        "orderable" => false,
//                        "targets"   => '_all',
//                    ),
                    array('width' => '1%', 'targets' => 0),
                    array('width' => '1%', 'targets' => 2),
                    array('width' => '2%', 'targets' => 3),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'responsive' => false,
                'ordering' => false
            )
        );

        // oberste Tabellen-Kopf-Zeile erstellen
        $headTableColumnList = array();
        $headTableColumnList[] = new TableColumn('', $showCourse ? 5 : 4, '20%');
        if (!empty($periodListCount)) {
            $countTemp = 0;
            foreach ($periodListCount as $periodId => $count) {
                $countTemp++;
                if ($countTemp > 1 && $PeriodWithExtraName) {
                    $headTableColumnList[] = new TableColumn('');
                }
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
            ApiSupportReadOnly::receiverOverViewModal()
            .ApiPersonPicture::receiverModal()
            .new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Fach-Klasse',
                                array(
                                    'Klasse: ' . $tblDivision->getDisplayName() . ' - ' .
                                    ($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '') .
                                    ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                        ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                    'Schuljahr: '.$YearString,
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
                    )),
                )),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        $minimumGradeCountPanel ? new LayoutColumn($minimumGradeCountPanel) : null,
                        new LayoutColumn(
                            $tableData
                        )
                    )),
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null        $DivisionSubjectId
     * @param bool|string $BackwardInfo
     *
     * @return Stage|string
     */
    public function frontendHeadmasterSelectedGradeBook($DivisionSubjectId = null, $BackwardInfo = false)
    {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Headmaster',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Headmaster', $BackwardInfo);

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

            $yearButtonList = array();
            if ($YearId && Term::useService()->getYearById($YearId)) {
                $tblSelectedYear = Term::useService()->getYearById($YearId);
                $yearButtonList[] = new Standard('Aktuelles Schuljahr', '/Education/Graduation/Gradebook/Type/Select',
                    null, array('Id' => $tblScoreType->getId()));
            } elseif($YearId) {
                // Id kann kein Jahr finden
                $tblSelectedYear = false;
                $yearButtonList[] = new Standard(
                    new Info(new Edit() . ' Aktuelles Schuljahr'),
                    '/Education/Graduation/Gradebook/Type/Select', null, array('Id' => $tblScoreType->getId()));
            } else {
                // Aktuelles Schuljahr
                $tblSelectedYear = null;
                $yearButtonList[] = new Standard(
                    new Info(new Edit() . ' Aktuelles Schuljahr'),
                '/Education/Graduation/Gradebook/Type/Select', null, array('Id' => $tblScoreType->getId()));
            }

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
            if ($tblSelectedYear !== false) {
                if($tblSelectedYear === null) {
                    $tblDivisionList = array();
                    if(($tblYearList = Term::useService()->getYearByNow())){
                        foreach($tblYearList as $tblYear){
                            if (($tblDivisionListTemp = Division::useService()->getDivisionByYear($tblYear))) {
                                $tblDivisionList = array_merge($tblDivisionList, $tblDivisionListTemp);
                            }
                        }
                    }
                } else {
                    $tblDivisionList = Division::useService()->getDivisionByYear($tblSelectedYear);
                }

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

                                // Bewertungssystem nicht mehr bearbeitbar, nachdem Zensuren mit dem TestType "TEST" vergeben wurden
                                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                                if (Gradebook::useService()->existsGrades($tblDivision, $tblSubject, $tblTestType)) {
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
                        } else {
                            // Keine Fächer bei dieser Klasse angelegt

                            $message = new Warning('Keine Fächer verfügbar', new Exclamation());

                            $panel = new Panel(
                                new Bold('Klasse ' . $tblDivision->getDisplayName()),
                                $message,
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

            if (!empty($formGroupList)) {
                $formGroupList[] = new FormGroup(new FormRow(new FormColumn(new HiddenField('Data[IsSubmit]'))));
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
                                    ? new Warning('Im Schuljahr ' . ($tblSelectedYear ? $tblSelectedYear->getDisplayName() : '')
                                    . ' sind keine Klassen vorhanden.', new Exclamation())
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
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherDivisionList($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Klasse des Schülers Auswählen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Gradebook', new ChevronLeft())
        );

        $buttonList = Prepare::useService()->setYearGroupButtonList('/Education/Graduation/Gradebook/Gradebook/Teacher/Division',
            $IsAllYears, $IsGroup, $YearId, $tblYear);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            if ($IsGroup) {
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    if (($tblGroupAll = Group::useService()->getTudorGroupAll($tblPerson))) {
                        foreach ($tblGroupAll as $tblGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                $table = new TableData($divisionTable, null, array(
                    'Group' => 'Gruppe',
                    'Option' => ''
                ), array(
                    'order'      => array(
                        array('0', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 0)
                    )
                ));
            } else {
                $tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
                if ($tblDivisionTeacherList) {
                    foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                        if (($tblDivision = $tblDivisionTeacher->getTblDivision())) {
                            // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                            /** @var TblYear $tblYear */
                            if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                                && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                            ) {
                                continue;
                            }

                            $divisionTable[] = array(
                                'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                'Type' => $tblDivision->getTypeName(),
                                'Division' => 'Klasse ' . $tblDivision->getDisplayName(),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student',
                                    new Select(),
                                    array(
                                        'DivisionId' => $tblDivision->getId()
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                $table = new TableData($divisionTable, null, array(
                    'Year' => 'Schuljahr',
                    'Type' => 'Schulart',
                    'Division' => 'Klasse/Gruppe',
                    'Option' => ''
                ), array(
                    'order'      => array(
                        array('0', 'desc'),
                        array('2', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 2)
                    )
                ));
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterDivisionList($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Klasse des Schülers Auswählen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster', new ChevronLeft())
        );

        $buttonList = Prepare::useService()->setYearGroupButtonList('/Education/Graduation/Gradebook/Gradebook/Headmaster/Division',
            $IsAllYears, $IsGroup, $YearId, $tblYear);

        $divisionTable = array();
        if ($IsGroup) {
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Year' => '',
                        'Type' => '',
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option'   => ''
            ), array(
                'order'      => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('orderable' => false, 'targets' => -1),
                )
            ));
        } else {
            if ($tblDivisionList = Division::useService()->getDivisionAll()) {
                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionList as $tblDivision) {

                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                        && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }

                    $divisionTable[] = array(
                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $tblDivision->getTypeName(),
                        'Division' => $tblDivision->getDisplayName(),
                        'Option' => new Standard(
                            '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student', new Select(),
                            array(
                                'DivisionId' => $tblDivision->getId()
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Year'     => 'Schuljahr',
                'Type'     => 'Schulart',
                'Division' => 'Klasse',
                'Option'   => ''
            ), array(
                'order'      => array(
                    array('0', 'desc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2),
                    array('orderable' => false, 'targets' => -1),
                )
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select().' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     *
     * @return Stage|string
     */
    public function frontendHeadmasterSelectStudent($DivisionId = null, $GroupId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division', new ChevronLeft()
        ));

        return $this->setSelectStudentStage($Stage, $DivisionId, $GroupId, true);
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     *
     * @return Stage|string
     */
    public function frontendTeacherSelectStudent($DivisionId = null, $GroupId = null)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division', new ChevronLeft()
        ));

        return $this->setSelectStudentStage($Stage, $DivisionId, $GroupId, false);
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $PersonId
     * @param bool $IsParentView
     *
     * @return Stage|string
     */
    public function frontendHeadmasterStudentOverview($DivisionId = null, $GroupId = null, $PersonId = null, $IsParentView = false)
    {
        $Stage = new Stage('Schülerübersicht', 'Schüler anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student', new ChevronLeft(), array(
                'DivisionId' => $DivisionId,
                'GroupId' => $GroupId
            )
        ));

        return $this->setStudentOverviewStage(
            $DivisionId,
            $GroupId,
            $PersonId,
            $Stage,
            '/Education/Graduation/Gradebook/Gradebook/Headmaster/Division/Student/Overview',
            $IsParentView
        );
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $PersonId
     * @param bool $IsParentView
     *
     * @return Stage|string
     */
    public function frontendTeacherStudentOverview($DivisionId = null, $GroupId = null, $PersonId = null, $IsParentView = false)
    {

        $Stage = new Stage('Schülerübersicht', 'Schüler anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student', new ChevronLeft(), array(
                'DivisionId' => $DivisionId,
                'GroupId' => $GroupId
            )
        ));

        return $this->setStudentOverviewStage(
            $DivisionId,
            $GroupId,
            $PersonId,
            $Stage,
            '/Education/Graduation/Gradebook/Gradebook/Teacher/Division/Student/Overview',
            $IsParentView
        );
    }

    /**
     * @param $tblMinimumGradeCountList
     * @param $isSekII
     *
     * @return bool|Panel
     */
    private function getMinimumGradeCountPanel($tblMinimumGradeCountList, $isSekII)
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
                    'Course' => $tblMinimumGradeCount->getCourseDisplayName(),
                    'Count' => $tblMinimumGradeCount->getCount()
                );
            }

            if (!empty($minimumGradeCountContent)) {
                if  ($isSekII) {
                    $columns = array(
                        'Number' => 'Nummer',
                        'SchoolType' => 'Schulart',
                        'Level' => 'Klassenstufe',
                        'Subject' => 'Fach',
                        'GradeType' => 'Zensuren-Typ',
                        'Period' => 'Zeitraum',
                        'Course' => 'SEKII - Kurs',
                        'Count' => 'Anzahl',
                    );
                } else {
                    $columns = array(
                        'Number' => 'Nummer',
                        'SchoolType' => 'Schulart',
                        'Level' => 'Klassenstufe',
                        'Subject' => 'Fach',
                        'GradeType' => 'Zensuren-Typ',
                        'Period' => 'Zeitraum',
                        'Count' => 'Anzahl',
                    );
                }

                return new Panel(
                    'Mindesnotenanzahl',
                    '<div style="margin-top: -18px;">'.
                    new TableData(
                        $minimumGradeCountContent,
                        null,
                        $columns,
                        array(
                            // nicht alle Filter Icons verschwinden
//                            "columnDefs" => array(
//                                array(
//                                    "orderable" => false,
//                                    "targets" => '_all'
//                                ),
//                            ),
                            'pageLength' => -1,
                            'paging' => false,
                            'info' => false,
                            'searching' => false,
                            'responsive' => false,
                            'ordering' => false
                        )
                    ).'</div>',
                    Panel::PANEL_TYPE_INFO

                );
            }
        }

        return false;
    }

    /**
     * @param TblTest $tblTest
     * @param TblGrade|null $tblGrade
     * @param array $subTableHeaderList
     * @param array $subTableDataList
     * @param bool $isShownDivisionSubjectScore
     * @param bool $isShownGradeMirror
     * @param bool $showDivisionInToolTip
     */
    private function addTest(TblTest $tblTest,?TblGrade $tblGrade, array &$subTableHeaderList, array &$subTableDataList,
        bool $isShownDivisionSubjectScore, bool $isShownGradeMirror, bool $showDivisionInToolTip
    ) {
        if ($tblTest->isContinues()) {
            if ($tblGrade && $tblGrade->getDate()) {
                $date = $tblGrade->getDate();
            } else {
                $date = $tblTest->getFinishDate();
            }
        } else {
            $date = $tblTest->getDate();
        }
        $dateTime = new DateTime($date);
        if (strlen($date) > 6) {
            $date = substr($date, 0, 6);
        }

        $gradeMirror = array();
        $testAverage = Gradebook::useService()->getAverageByTest($tblTest, $gradeMirror);
        $description = $tblTest->getDescription();
        $tblGradeTypeTest = $tblTest->getServiceTblGradeType();
        $text = new Small(new Muted($date)) . '<br>';
        if ($tblGradeTypeTest) {
            $alternativeDescription = '';
            $text .= $tblGradeTypeTest->isHighlighted()
                    ? $tblGradeTypeTest->getCode()
                    : new Muted($tblGradeTypeTest->getCode());
        } else {
            // Stichtagsnote
            $text .= 'SN';
            $alternativeDescription = 'Stichtagsnote';
        }

        if ($showDivisionInToolTip && ($tblDivision = $tblTest->getServiceTblDivision())) {
            $toolTip = 'Klasse: ' . $tblDivision->getDisplayName() . '<br />';
        } else {
            $toolTip = '';
        }
        $toolTip .= $description ? 'Thema: ' . $description : $alternativeDescription;
        if ($isShownGradeMirror) {
            if (!empty($gradeMirror)) {
                $toolTip .= ($toolTip ? '<br />' : '');
                $line[0] = '';
                $line[1] = '';
                foreach ($gradeMirror as $key => $value) {
                    $space = ($value > 9 && $key < 10) ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                    $line[0] .= $space . $key;
                    $space = ($value < 9 && $key > 9) ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                    $line[1] .= $space . $value;
                }
                $toolTip .= $line[0] . '<br />' . $line[1];
            }
        }
        if ($isShownDivisionSubjectScore) {
            if ($testAverage) {
                $toolTip .= ($toolTip ? '<br />' : '') . '&#216; ' . $testAverage;
            }
        }

        $subTableHeaderList['Test' . $tblTest->getId()] = array(
            'Date' => $dateTime->format('Y.m.d'),
            'Text' => (new ToolTip($text, htmlspecialchars($toolTip)))->enableHtml()
        );

        if ($tblGrade) {
            $gradeValue = $tblGrade->getGrade();
            if ($gradeValue === null) {
                $gradeValue = 'n.t.';
            } else if ($gradeValue !== '') {
                $trend = $tblGrade->getTrend();
                if (TblGrade::VALUE_TREND_PLUS === $trend) {
                    $gradeValue .= '+';
                } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                    $gradeValue .= '-';
                }
            }

            if (($tblGradeType = $tblGrade->getTblGradeType())
                && $tblGradeType->isHighlighted()
            ) {
                $gradeValue = new Bold($gradeValue);
            }
        } else {
            $gradeValue = null;
        }

        if ($gradeValue !== null && $gradeValue !== '') {
            $displayGrade = $gradeValue . (($tblGrade && ($tblGrade->getPublicComment() != ''))
                ? new ToolTip(' ' . new \SPHERE\Common\Frontend\Icon\Repository\Info(), $tblGrade->getPublicComment())
                : '');
        } else {
            $displayGrade = '&nbsp;';
        }

        $subTableDataList[0]['Test' . $tblTest->getId()] = $displayGrade;
    }

    /**
     * Achtung wird auch für den Eltern/Schüler-Zugang verwendet
     *
     * @param TblYear $tblYear
     * @param TblPerson $tblPerson
     * @param $divisionList
     * @param $rowList
     * @param $tblPeriodList
     * @param $tblTestType
     * @param $isShownAverage
     * @param $isShownDivisionSubjectScore
     * @param $isShownGradeMirror
     * @param $tableHeaderList
     * @param $isParentView
     * @param $isShownAppointedDateGrade
     */
    public function setGradeOverview(
        TblYear $tblYear,
        TblPerson $tblPerson,
        $divisionList,
        &$rowList,
        $tblPeriodList,
        $tblTestType,
        $isShownAverage,
        $isShownDivisionSubjectScore,
        $isShownGradeMirror,
        $tableHeaderList,
        $isParentView,
        $isShownAppointedDateGrade,
        $isScoreRuleShown
    ) {

        /** @var TblDivision $tblDivision */
        foreach ($divisionList as $tblDivision) {
            $tblTestTypeAppointedDateTask = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
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
                            // Fächer ohne Benotung ignorieren
                            if (!$tblDivisionSubject->getHasGrading()) {
                                continue;
                            }

                            $yearGradeList = array();
                            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject()) && $tblDivisionSubject->getTblDivision()) {
                                $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblSubject,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                );
                                if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                    $hasStudentSubject = false;
                                    $tblDivisionSubjectWhereGroup =
                                        Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                            $tblDivision,
                                            $tblSubject
                                        );
                                    if ($tblDivisionSubjectWhereGroup) {
                                        foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                            if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                                $tblPerson)
                                            ) {
                                                $hasStudentSubject = true;

                                                // Es mus die Berechnungsvorschrift der Gruppe verwendet werden
                                                $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                                    $tblDivision,
                                                    $tblSubject,
                                                    $tblDivisionSubjectGroup->getTblSubjectGroup() ? $tblDivisionSubjectGroup->getTblSubjectGroup() : null
                                                );
                                            }
                                        }
                                    } else {
                                        $hasStudentSubject = true;
                                    }
                                    if ($hasStudentSubject) {
                                        $tableDataList[$tblSubject->getId()]['Subject'] = $tblSubject->getName()
                                            . ($isScoreRuleShown && $tblScoreRule
                                                ? (new Standard('', ApiOnlineGradebook::getEndpoint(), new \SPHERE\Common\Frontend\Icon\Repository\Info(), array(),
                                                    'Berechnungsvorschrift für dieses Fach anzeigen'))
                                                    ->ajaxPipelineOnClick(ApiOnlineGradebook::pipelineOpenScoreRuleModal($tblScoreRule->getId()))
                                                : '');

                                        if ($tblPeriodList) {
                                            if ($isParentView && $tblTestTypeAppointedDateTask) {
                                                $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestTypeAppointedDateTask);
                                                if ($tblTaskList) {
                                                    $tblTaskList = $this->getSorter($tblTaskList)->sortObjectBy('Date', new DateTimeSorter(), Sorter::ORDER_DESC);
                                                }
                                            } else {
                                                $tblTaskList = false;
                                            }

                                            // Stichtagsnoten anzeigen
                                            if ($isShownAppointedDateGrade) {
                                                $appointedDateGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                                    $tblPerson,
                                                    $tblYear,
                                                    $tblSubject,
                                                    $tblTestTypeAppointedDateTask
                                                );
                                            } else {
                                                $appointedDateGradeList = false;
                                            }

                                            /**@var TblPeriod $tblPeriod **/
                                            foreach ($tblPeriodList as $tblPeriod) {
                                                $gradeListForAverage = array();
                                                $tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                                    $tblPerson,
                                                    $tblYear,
                                                    $tblSubject,
                                                    $tblTestType,
                                                    $tblPeriod
                                                );

                                                // Stichtagsnoten den Halbjahren zuordnen
                                                if ($appointedDateGradeList) {
                                                    if (!$tblGradeList) {
                                                        $tblGradeList = array();
                                                    }
                                                    /**@var TblGrade $appointedDateGrade **/
                                                    foreach ($appointedDateGradeList as $appointedDateGrade) {
                                                        if (Gradebook::useService()->isAppointedDateGradeInPeriod($appointedDateGrade, $tblPeriod)) {
                                                            $tblGradeList[] = $appointedDateGrade;
                                                        }
                                                    }
                                                }

                                                $subTableHeaderList = array();
                                                $subTableDataList = array();

                                                if ($tblGradeList) {
                                                    // Sortieren der Zensuren
                                                    $gradeListSorted = $this->getSorter($tblGradeList)->sortObjectBy('DateForSorter', new DateTimeSorter());

                                                    $appointedDateTask = false;
                                                    if ($isParentView && $tblTaskList) {
                                                        /** @var TblTask $tblTask */
                                                        foreach ($tblTaskList as $tblTask) {
                                                            if (($date = $tblTask->getDate())
                                                                && ($toDatePeriod = $tblPeriod->getToDate())
                                                                && ($dateTimeTask = new DateTime($date))
                                                                && ($toDateTimePeriod = new DateTime($toDatePeriod))
                                                                && $dateTimeTask < $toDateTimePeriod
                                                            ) {
                                                                $appointedDateTask = $tblTask;
                                                                break;
                                                            }
                                                        }
                                                    }

                                                    /**@var TblGrade $tblGrade **/
                                                    foreach ($gradeListSorted as $key => $tblGrade) {
                                                        $tblTest = $tblGrade->getServiceTblTest();
                                                        if ($tblTest) {
                                                            $isAddTest = false;
                                                            if ($isParentView) {
                                                                // fortlaufendes Datum
                                                                if ($tblTest->isContinues()) {
                                                                    if ($tblGrade->getDate()) {
                                                                        $gradeDate = (new DateTime($tblGrade->getDate()))->format("Y-m-d");
                                                                        $now = (new DateTime('now'))->format("Y-m-d");
                                                                        if ($gradeDate <= $now) {

                                                                            // Test anzeigen
                                                                            $isAddTest = true;
                                                                        }
                                                                    } elseif ($tblTest->getFinishDate()) {
                                                                        // continues grades without date can be view if finish date is arrived
                                                                        $testFinishDate = (new DateTime($tblTest->getFinishDate()))->format("Y-m-d");
                                                                        $now = (new DateTime('now'))->format("Y-m-d");
                                                                        if ($testFinishDate <= $now) {

                                                                            // Test anzeigen
                                                                            $isAddTest = true;
                                                                        }
                                                                    }
                                                                } elseif ($tblTest->getServiceTblGradeType()) {
                                                                    if ($tblTest->getReturnDate()) {
                                                                        $testReturnDate = (new DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                                                                        $now = (new DateTime('now'))->format("Y-m-d");
                                                                        if ($testReturnDate <= $now) {

                                                                            // Test anzeigen
                                                                            $isAddTest = true;
                                                                        }
                                                                    } else {
                                                                        // automatische Bekanntgabe durch den Stichtagsnotenauftrag
                                                                        if ($appointedDateTask) {
                                                                            if ($tblTest->getDate()
                                                                                && ($testDate = (new DateTime($tblTest->getDate())))
                                                                                && ($toDateTimeTask = new DateTime($appointedDateTask->getToDate()))
                                                                                && ($nowDateTime = (new DateTime('now')))
                                                                                && $testDate <= $toDateTimeTask
                                                                                && $toDateTimeTask < $nowDateTime
                                                                            ) {
                                                                                // Test anzeigen
                                                                                $isAddTest = true;
                                                                            }
                                                                        }
                                                                        // automatische Bekanntgabe nach X Tagen
                                                                        if (!$isAddTest && ($tblSetting = Consumer::useService()->getSetting(
                                                                            'Education', 'Graduation', 'Evaluation', 'AutoPublicationOfTestsAfterXDays'))
                                                                        ) {
                                                                            if (($days = intval($tblSetting->getValue()))
                                                                                && $tblTest->getDate()
                                                                            ) {
                                                                                $testDate = (new DateTime($tblTest->getDate()));
                                                                                $autoTestReturnDate = $testDate->add(
                                                                                    new \DateInterval('P' . $days . 'D')
                                                                                );
                                                                                $autoTestReturnDate = $autoTestReturnDate->format("Y-m-d");
                                                                                $now = (new DateTime('now'))->format("Y-m-d");
                                                                                if ($autoTestReturnDate <= $now) {

                                                                                    // Test anzeigen
                                                                                    $isAddTest = true;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $isAddTest = true;
                                                            }

//                                                            if ($isAddTest && ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '')) {
                                                            if ($isAddTest && $tblGrade->getGrade() !== '') {
                                                                if (($tblDivisionTest = $tblTest->getServiceTblDivision())
                                                                    && $tblDivision->getId() != $tblDivisionTest->getId()
                                                                ) {
                                                                    $showDivisionInToolTip = true;
                                                                } else {
                                                                    $showDivisionInToolTip = false;
                                                                }

                                                                $this->addTest(
                                                                    $tblTest,
                                                                    $tblGrade,
                                                                    $subTableHeaderList,
                                                                    $subTableDataList,
                                                                    $isShownDivisionSubjectScore,
                                                                    $isShownGradeMirror,
                                                                    $showDivisionInToolTip
                                                                );

                                                                if ($tblGrade->getTblGradeType()) {
                                                                    $gradeListForAverage[] = $tblGrade;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                // fettmarkierte Tests wie Klassenarbeiten anzeigen
                                                if (($tblSetting = Consumer::useService()->getSetting(
                                                        'Education', 'Graduation', 'Gradebook', 'ShowHighlightedTestsInGradeOverview'))
                                                    && $tblSetting->getValue()
                                                    && ($tblTestList = Evaluation::useService()->getHighlightedTestList(
                                                    $tblDivision, $tblSubject, $tblPeriod
                                                ))) {
                                                    /** @var TblTest $tblTestItem */
                                                    foreach ($tblTestList as $tblTestItem) {
                                                        // Prüfung ob der Schüler in der Fach-Gruppe ist
                                                        $isAddTest = false;
                                                        if (($tblSubjectGroup = $tblTestItem->getServiceTblSubjectGroup())) {
                                                            if (($tblDivisionSubjectTemp = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                                                    $tblDivision,
                                                                    $tblSubject,
                                                                    $tblSubjectGroup
                                                                ))
                                                                && Division::useService()->exitsSubjectStudent($tblDivisionSubjectTemp,
                                                                    $tblPerson)
                                                            ) {
                                                                $isAddTest = true;
                                                            }
                                                        } else {
                                                            $isAddTest = true;
                                                        }

                                                        if ($isAddTest && !isset($subTableHeaderList['Test' . $tblTestItem->getId()])) {
                                                            $this->addTest(
                                                                $tblTestItem,
                                                                null,
                                                                $subTableHeaderList,
                                                                $subTableDataList,
                                                                false,
                                                                false,
                                                                false
                                                            );
                                                        }
                                                    }
                                                }

                                                if (!empty($subTableHeaderList)) {
                                                    // nach Datum Sortieren
                                                    uasort($subTableHeaderList, function ($a, $b)
                                                    {
                                                        return strnatcmp($a['Date'], $b['Date']);
                                                    });

                                                    $tempList = array();
                                                    foreach ($subTableHeaderList as $key => $array) {
                                                        $tempList[$key] = $array['Text'];
                                                    }
                                                    $subTableHeaderList = $tempList;

                                                    if ($isShownAverage) {
                                                        $subTableHeaderList['Average'] = '&#216;';
                                                        /*
                                                        * Calc Average
                                                        */
                                                        $average = Gradebook::useService()->calcStudentGrade(
                                                            $tblPerson, $tblDivisionSubject->getTblDivision(),
                                                            $tblSubject,
                                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                            $tblScoreRule ? $tblScoreRule : null, $tblPeriod,
                                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                            false, false, $gradeListForAverage
                                                        );

                                                        if (is_array($average)) {
                                                            $average = 'Fehler';
                                                        } elseif (is_string($average) && strpos($average,
                                                                '(')
                                                        ) {
                                                            $average = substr($average, 0,
                                                                strpos($average, '('));
                                                        }

                                                        $subTableDataList[0]['Average'] = new Bold($average);
                                                    }

                                                    $headerColumns = array();
                                                    $bodyColumns = array();
                                                    foreach ($subTableHeaderList as $key => $item) {
                                                        $headerColumns[] = new TableColumn($item, 1, $key == 'Average' ? '1%' : 'auto');

                                                        if (isset($subTableDataList[0][$key])) {
                                                            $displayValue = $subTableDataList[0][$key];
                                                        } else {
                                                            $displayValue = '&nbsp;';
                                                        }

                                                        $bodyColumns[] = new TableColumn($displayValue, 1, $key == 'Average' ? '1%' : 'auto');
                                                    }
                                                    // durch die Nachträgliche Sortierung der Tests nach Datum -> stimmt dann die Zuordnung des Inhalts nicht mehr
//                                                    foreach ($subTableDataList[0] as $key => $item) {
//                                                        $bodyColumns[] = new TableColumn($item, 1, $key == 'Average' ? '1%' : 'auto');
//                                                    }
                                                    $table = new Table(new TableHead(new TableRow($headerColumns)), new TableBody(new TableRow($bodyColumns)));

                                                    $tableDataList[$tblSubject->getId()]['Period' . $tblPeriod->getId()] = $table;
                                                } else {
                                                    $tableDataList[$tblSubject->getId()]['Period' . $tblPeriod->getId()] = '';
                                                }

                                                // gesamte Zensurenliste für den Gesamtdurchschnitt
                                                $yearGradeList = array_merge($yearGradeList, $gradeListForAverage);
                                            }
                                        }

                                        $average = Gradebook::useService()->calcStudentGrade(
                                            $tblPerson, $tblDivisionSubject->getTblDivision(),
                                            $tblSubject,
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreRule ? $tblScoreRule : null, null,
                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                            false, false, $yearGradeList
                                        );

                                        if (is_array($average)) {
                                            $average = 'Fehler';
                                        } elseif (is_string($average) && strpos($average,
                                                '(')
                                        ) {
                                            $average = substr($average, 0,
                                                strpos($average, '('));
                                        }
                                        $tableDataList[$tblSubject->getId()]['Average'] = new Bold($average);
                                    }
                                }
                            }
                        }
                    }

                    $interActive =  array(
                        "paging" => false, // Deaktiviert Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktiviert Suche
                        "info" => false, // Deaktiviert Such-Info)
                        "responsive" => false, // Deaktiviert RWD
                    );
                    if (isset($tableHeaderList['Average'])) {
                        $countHeader = count( $tableHeaderList);
                        $interActive["columnDefs"] = array(array('width' => '1%', 'targets' => $countHeader - 1));
                    }

                    $rowList[] = new LayoutRow(new LayoutColumn(
                        !empty($tableDataList)
                            ? (new TableData($tableDataList, null, $tableHeaderList, // null
                                $interActive, true
                            ))->setHash(__NAMESPACE__ . '\Student\Gradebook' . $tblDivision->getId() . $tblPerson->getId())
                            : new Warning('Aktuell sind keine Noten verfügbar (Keine Fächer vorhanden)'
                            , new Exclamation())
                    ));
                    $rowList[] = new LayoutRow(new LayoutColumn(new Header('&nbsp;'), 12));
                }
            }
        }
    }

    /**
     * @param Stage $Stage
     * @param $DivisionId
     * @param null $GroupId
     * @param bool $IsHeadmaster
     *
     * @return string
     */
    private function setSelectStudentStage(Stage $Stage, $DivisionId = null, $GroupId = null, $IsHeadmaster = false)
    {

        $tblGroup = false;
        $tblGroupTudor = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR);
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            || ($tblGroup = Group::useService()->getGroupById($GroupId))
        ) {

            $isWithSubjectGroup = false;
            $personData = array();
            $tableHeaderList['Number'] = 'Nummer';
            $tableHeaderList['Name'] = 'Name';
            $tableHeaderList['Picture'] = 'Foto';
            $tableHeaderList['Integration'] = 'Integration';
            $tblDivisionList = array();
            $sumSubjectAverage = array();
            $countSubjectAverage = array();
            $averageData = array(
                'Number' => '',
                'Name' => new Muted('&#216; Fach-Klasse'),
                'Integration' => ''
            );

            if ($tblGroup) {
                $tableHeaderList['Division'] = 'Klasse';
                $averageData['Division'] = '';
                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    foreach ($tblPersonList as $tblPerson) {
                        if (($tblDivisionListByPerson = Student::useService()->getCurrentDivisionListByPerson($tblPerson))) {
                            foreach ($tblDivisionListByPerson as $tblDivisionSearch) {
                                $tblDivisionList[$tblDivisionSearch->getId()] = $tblDivisionSearch;
                                if (($tblLevel = $tblDivisionSearch->getTblLevel())
                                    && !$tblLevel->getIsChecked()
                                ) {
                                    $personData[$tblPerson->getId()]['Division'] = $tblDivisionSearch->getDisplayName();
                                }
                            }
                        }
                    }
                }
            } else {
                $tblYear = $tblDivision->getServiceTblYear();
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

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
                }
            }

            $showCourse = $tblGroup;
            if ($tblDivision
                && ($tblLevel = $tblDivision->getTblLevel())
                && ($tblType = $tblLevel->getServiceTblType())
                && $tblType->getName() == 'Mittelschule / Oberschule'
                && intval($tblLevel->getName()) > 6
            ) {
                $showCourse = true;
            }
            if ($showCourse) {
                $tableHeaderList['Course'] = 'Bildungsgang';
                $averageData['Course'] = '';
            }

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
                    $tableHeaderList[$SubjectId . 'Id'] = $Acronym;
                    $averageData[$SubjectId . 'Id'] = '';
                    $sumSubjectAverage[$SubjectId . 'Id'] = 0;
                    $countSubjectAverage[$SubjectId . 'Id'] = 0;
                }
            }

            $tableHeaderList['Option'] = '';

            $studentTable = array();
            if ($tblPersonList) {
                $count = 1;
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    // tudoren überspringen
                    if ($tblGroup && $tblGroupTudor && Group::useService()->existsGroupPerson($tblGroupTudor, $tblPerson)) {
                        continue;
                    }

                    $data = array();
                    $data['Number'] = $count++;
                    $data['Name'] = $tblPerson->getLastFirstName();
                    $data['Picture'] = '';
                    if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                        $data['Picture'] = new Center((new Link($tblPersonPicture->getPicture(), $tblPerson->getId()))
                            ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
                    }

                    if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                        $Integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                            ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    } else {
                        $Integration = '';
                    }
                    $data['Integration'] = $Integration;
                    $data['Division'] = $tblGroup && isset($personData[$tblPerson->getId()]['Division'])
                        ? $personData[$tblPerson->getId()]['Division'] : '';
                    $data['Course'] = '';
                    $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                    if ($tblCourse) {
                        $data['Course'] = $tblCourse->getName();
                    }

                    $data['Option'] = new Standard(
                        '',
                        '/Education/Graduation/Gradebook/Gradebook/'
                        . ($IsHeadmaster ? 'Headmaster': 'Teacher')
                        . '/Division/Student/Overview',
                        new EyeOpen(),
                        array(
                            'DivisionId' => $tblDivision ? $tblDivision->getId() : null,
                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                            'PersonId' => $tblPerson->getId()
                        ),
                        'Schülerübersicht anzeigen'
                    );

                    if (!empty($tblDivisionList)) {
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                        /** @var TblDivision $tblDivisionLoop */
                        foreach ($tblDivisionList as $tblDivisionLoop) {
                            if (!($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivisionLoop,$tblPerson))) {
                                continue;
                            }
                            if ($tblDivisionStudent->isInActive()) {
                                continue;
                            }

                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivisionLoop,
                                $isWithSubjectGroup);
                            if ($tblDivisionSubjectList && ($tblYear = $tblDivisionLoop->getServiceTblYear())) {
                                // deactivated: value in ToolTip is not sortable
//                                $tblSubjectStudentList = Division::useService()->getSubjectStudentByPersonAndDivision($tblPerson,
//                                    $tblDivisionLoop);
                                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                    $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                                    if ($tblSubject) {
                                        $data[$tblSubject->getId() . 'Id'] = '';
                                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                            $tblDivisionLoop,
                                            $tblSubject
                                        );

                                        if (($tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                            $tblPerson,
                                            $tblYear,
                                            $tblSubject,
                                            $tblTestType
                                            ))
                                        ) {
                                            /*
                                            * Calc Average
                                            */
                                            $average = Gradebook::useService()->calcStudentGrade(
                                                $tblPerson,
                                                $tblDivisionLoop,
                                                $tblSubject,
                                                $tblTestType,
                                                $tblScoreRule ? $tblScoreRule : null,
                                                null,
                                                null,
                                                false,
                                                false,
                                                $tblGradeList
                                            );
                                        } else {
                                            $average = '';
                                        }

                                        if (is_array($average)) {
                                            $average = 'Fehler';
                                        } elseif (is_string($average) && strpos($average, '(')) {
                                            $average = substr($average, 0, strpos($average, '('));

                                            $sumSubjectAverage[$tblSubject->getId() . 'Id'] += intval($average);
                                            $countSubjectAverage[$tblSubject->getId() . 'Id']++;
                                        } elseif ($average || $average === (float) 0) {
                                            $sumSubjectAverage[$tblSubject->getId() . 'Id'] += intval($average);
                                            $countSubjectAverage[$tblSubject->getId() . 'Id']++;
                                        }

                                        // Anzeige Notendurchschnitt genau 0
                                        if ($average === (float) 0) {
                                            $averageString = '&empty; 0';

                                        } else {
                                            $averageString = ($average != '' ? '&empty; ' . $average : '');
                                        }
                                        $data[$tblSubject->getId() . 'Id'] = $averageString;
                                        // deactivated: value in ToolTip is not sortable
//                                        // add ToolTip if Student is in Group
//                                        if ($tblSubjectStudentList) {
//                                            /** @var TblSubjectStudent $tblSubjectStudent */
//                                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
//                                                if ($tblSubjectStudent) {
//                                                    if (($tblDivisionSubjectStudent = $tblSubjectStudent->getTblDivisionSubject())) {
//                                                        if (($tblSubjectFromStudent = $tblDivisionSubjectStudent->getServiceTblSubject())) {
//                                                            if ($tblSubjectFromStudent->getId() == $tblSubject->getId()) {
//                                                                if (($tblSubjectGroup = $tblDivisionSubjectStudent->getTblSubjectGroup())) {
//                                                                    $data[$tblSubject->getId() . 'Id'] = (new ToolTip($averageString
//                                                                        , htmlspecialchars($tblSubjectGroup->getName())))->enableHtml();
//                                                                }
//                                                            }
//                                                        }
//                                                    }
//                                                }
//                                            }
//                                        }
                                    }
                                }
                            }
                        }
                    }
                    $studentTable[] = $data;
                }
            }

            foreach($sumSubjectAverage as $key => $value) {
                if (isset($countSubjectAverage[$key])) {
                    $averageData[$key] = $countSubjectAverage[$key] > 0
                        ? '&#216; ' . round($value / $countSubjectAverage[$key], 2) : '';
                }
            }
            $studentTable[] = $averageData;

            $Stage->addButton(new External(
                'Alle Schülerübersichten dieser Klasse herunterladen', '/Api/Document/Standard/MultiGradebookOverview/Create', new Download(),
                array('DivisionId' => $DivisionId, 'GroupId' => $GroupId), false
            ));

            $Stage->setContent(
                 ApiSupportReadOnly::receiverOverViewModal()
                .ApiPersonPicture::receiverModal()
                .new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    $tblGroup ? 'Gruppe' : 'Klasse',
                                    $tblGroup ? $tblGroup->getName() : $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            )),
                            new LayoutColumn(array(
                                new TableData($studentTable, null, $tableHeaderList,
                                    array(
                                        "columnDefs" => array(
                                            array('width' => '3%', 'targets' => 2),
                                            array('width' => '6%', 'targets' => 3),
                                            array('orderable' => false, 'targets' => -1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        ),
                                        'pageLength' => -1,
                                        'responsive' => false,
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Klasse/Gruppe nicht gefunden.', new Ban());
        }
    }

    /**
     * @param $DivisionId
     * @param $GroupId
     * @param $PersonId
     * @param Stage $Stage
     * @param string $Route
     * @param bool $IsParentView
     *
     * @return Stage|string
     */
    private function setStudentOverviewStage(
        $DivisionId,
        $GroupId,
        $PersonId,
        Stage $Stage,
        $Route,
        $IsParentView
    ) {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (!$tblDivision && !$tblGroup) {
            return $Stage
                . new Danger('Klasse/Gruppe nicht gefunden.', new Ban());
        }

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage
                . new Danger('Schüler nicht gefunden.', new Ban());
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        if ($IsParentView) {
            $rowList[] = new LayoutRow(
                new LayoutColumn(array(
                    new Standard('Ansicht: Alle Zensuren',
                        $Route,
                        null,
                        array(
                            'DivisionId' => $DivisionId,
                            'PersonId' => $PersonId,
                            'IsParentView' => false,
                        )
                    ),
                    new Standard(new Info(new Bold('Ansicht: Eltern/Schüler')),
                        $Route,
                        new Edit(),
                        array(
                            'DivisionId' => $DivisionId,
                            'PersonId' => $PersonId,
                            'IsParentView' => true,
                        )
                    ),
                    '<br /><br />'
                ))
            );
        } else {
            $rowList[] = new LayoutRow(
                new LayoutColumn(array(
                    new Standard(new Info(new Bold('Ansicht: Alle Zensuren')),
                        $Route,
                        new Edit(),
                        array(
                            'DivisionId' => $DivisionId,
                            'PersonId' => $PersonId,
                            'IsParentView' => false,
                        )
                    ),
                    new Standard('Ansicht: Eltern/Schüler',
                        $Route,
                        null,
                        array(
                            'DivisionId' => $DivisionId,
                            'PersonId' => $PersonId,
                            'IsParentView' => true,
                        )
                    ),
                    '<br /><br />'
                ))
            );
        }

        $rowList[] = new LayoutRow(array(
            new LayoutColumn(array(
                new Panel(
                    $tblGroup ? 'Gruppe' : 'Klasse',
                    $tblGroup ? $tblGroup->getName() : $tblDivision->getDisplayName(),
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
        ));

        if ($tblGroup) {
            if (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))) {
                $DivisionId = $tblDivision->getId();
            } else {
                return $Stage
                    . new Warning('Der Schüler befindet sich im aktuellen Schuljahr in keiner Klasse.', new Ban());
            }
        }

        $Stage->addButton(new External(
            'Schülerübersicht herunterladen', 'SPHERE\Application\Api\Document\Standard\GradebookOverview\Create',
            new Download(), array('PersonId' => $PersonId, 'DivisionId' => $DivisionId, 'Notenübersicht herunterladen')
            , false
        ));

        // Button's nur anzeigen, wenn Integrationen hinterlegt sind
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson && Student::useService()->getIsSupportByPerson($tblPerson)) {
            $Stage->addButton((new Standard('Integration', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId())));
        }

        if ($IsParentView) {
             list($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown)
                 = $this->getConsumerSettingsForGradeOverview();
            $isShownAppointedDateGrade = false;
        } else {
            $isShownAverage = true;
            $isShownDivisionSubjectScore = true;
            $isShownGradeMirror = true;
            $isShownAppointedDateGrade = true;
            $tblSchoolTypeList = false;
            $startYear = '';
            $isScoreRuleShown = false;
        }

        if (($tblYear = $tblDivision->getServiceTblYear())) {
            $tableHeaderList = array();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblDivision);
            if ($tblPeriodList) {
                $tableHeaderList['Subject'] = 'Fach';
                foreach ($tblPeriodList as $tblPeriod) {
                    $tableHeaderList['Period' . $tblPeriod->getId()] = new Bold($tblPeriod->getDisplayName());
                }

                if ($isShownAverage) {
                    $tableHeaderList['Average'] = '&#216;';
                }
            }

            if ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson)) {
                /** @var TblDivisionStudent $tblDivisionStudent */
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    if (!$tblDivisionStudent->isInActive()
                        && ($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                        && ($tblYearTemp = $tblDivisionTemp->getServiceTblYear())
                    ) {
                        // Schulart Prüfung nur, wenn auch Schularten in den Einstellungen erlaubt werden.
                        if($tblSchoolTypeList && ($tblLevelTemp = $tblDivisionTemp->getTblLevel())){
                            if(($tblSchoolType = $tblLevelTemp->getServiceTblType())){
                                if(!in_array($tblSchoolType->getId(), $tblSchoolTypeList)){
                                    // Klassen werden nicht angezeigt, wenn die Schulart nicht freigeben ist.
                                    continue;
                                }
                            }
                        }

                        // Anzeige nur für Schuljahre die nach dem "Startschuljahr"(Veröffentlichung) liegen
                        if($tblYearTemp->getYear() >= $startYear){
                            $tblDisplayYearList[$tblYearTemp->getId()] = $tblYearTemp;
                            $data[$tblYearTemp->getId()][$tblPerson->getId()][$tblDivisionTemp->getId()] = $tblDivisionTemp;
                        }
                    }
                }
            }

            if (!empty($data)) {
                if (isset($data[$tblYear->getId()])) {
                    foreach ($data[$tblYear->getId()] as $personId => $divisionList) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        if ($tblPerson && is_array($divisionList)) {

                            $this->setGradeOverview($tblYear, $tblPerson, $divisionList, $rowList, $tblPeriodList,
                                $tblTestType, $isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror,
                                $tableHeaderList, $IsParentView, $isShownAppointedDateGrade, $isScoreRuleShown);
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            ApiSupportReadOnly::receiverOverViewModal()
                . ApiOnlineGradebook::receiverModal()
                . new Layout(array(
                    new LayoutGroup($rowList)
                ))
        );

        return $Stage;
    }

    /**
     * Achtung wird auch für den Eltern/Schüler-Zugang verwendet
     *
     * @return array
     */
    public function getConsumerSettingsForGradeOverview(): array
    {
        // Mandant-Einstellungen für Notenübersicht (Schüler/Eltern) und Schülerübersicht (Ansicht: Eltern/Schüler)
        // !!!! wichtig: immer beide anpassen bei einer neuen Einstellung !!!!!!

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownAverage = true;
        } else {
            $isShownAverage = false;
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownDivisionSubjectScoreInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownDivisionSubjectScore = true;
        } else {
            $isShownDivisionSubjectScore = false;
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownGradeMirrorInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownGradeMirror = true;
        } else {
            $isShownGradeMirror = false;
        }

        // erlaubte Schularten:
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
        $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
        if($tblSchoolTypeList){
            // erzeuge eine Id Liste, wenn Schularten blockiert werden
            foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                $tblSchoolTypeControl = $tblSchoolTypeControl->getId();
            }
        }

        // Schuljahre Anzeigen ab:
        $startYear = '';
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'YearOfUserView');
        if($tblSetting){
            $YearTempId = $tblSetting->getValue();
            if ($YearTempId && ($tblYearTemp = Term::useService()->getYearById($YearTempId))){
                $startYear = ($tblYearTemp->getYear() ? $tblYearTemp->getYear() : $tblYearTemp->getName());
            }
        }

        if (($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'OnlineGradebook', 'OnlineGradebook' , 'IsScoreRuleShown'))
            && $tblSetting->getValue()
        ) {
            $isScoreRuleShown = true;
        } else {
            $isScoreRuleShown = false;
        }

        return array($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown);
    }
}
