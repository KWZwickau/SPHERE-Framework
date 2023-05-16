<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\BeGs;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekII;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\LeavePoints;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\Editor;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends TechnicalSchool\Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        $hasDiplomaRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Diploma');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } elseif ($hasDiplomaRight) {
            return $this->frontendDiplomaSelectDivision();
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }


    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDiplomaSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::DIPLOMA);

        $buttonList = Prepare::useService()->setYearGroupButtonList('/Education/Certificate/Prepare/Diploma',
            $IsAllYears, $IsGroup, $YearId, $tblYear, true);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $divisionTable = array();
        if ($IsGroup) {
            $Stage->setDescription('Gruppe auswählen');
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', '/Education/Certificate/Prepare/Prepare', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'Route' => 'Diploma'
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('width' => '1%', 'targets' => 1),
                    array('orderable' => false, 'targets' => 1),
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivision->getServiceTblYear()
                        && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }

                    // nur Mittelschule Klasse 9 und 10
                    if (($tblLevel = $tblDivision->getTblLevel())
                        && ($tblSchoolType = $tblLevel->getServiceTblType())
                        && (($tblSchoolType->getName() == 'Mittelschule / Oberschule'
                                && ($tblLevel->getName() == '09' || $tblLevel->getName() == '9' || $tblLevel->getName() == '10'))
                            || (($tblSchoolType->getName() == 'Gymnasium'
                                && $tblLevel->getName() == '12'))
                            || $tblSchoolType->getName() == 'Förderschule'
                            || $tblSchoolType->getName() == 'Berufsfachschule'
                            || $tblSchoolType->getName() == 'Fachschule'
                            || $tblSchoolType->getName() == 'Berufsgrundbildungsjahr'
                            || (($tblSchoolType->getName() == 'Fachoberschule'
                                && $tblLevel->getName() == '12'))
                        )
                    ) {
                        $divisionTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'Route' => 'Diploma'
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
                'Division' => 'Klasse',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2),
                    array('width' => '1%', 'targets' => 3),
                    array('orderable' => false, 'targets' => 3),
                ),
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            $table
                        ))
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
    public function frontendTeacherSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::TEACHER);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $buttonList = Prepare::useService()->setYearGroupButtonList('/Education/Certificate/Prepare/Teacher',
            $IsAllYears, $IsGroup, $YearId, $tblYear, false);

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            if ($IsGroup) {
                $Stage->setDescription('Gruppe auswählen');
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    if (($tblGroupAll = Group::useService()->getTudorGroupAll($tblPerson))) {
                        foreach ($tblGroupAll as $tblGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                        'Route' => 'Teacher'
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
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 0)
                    ),
                ));
            } else {
                if (($tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                    foreach ($tblDivisionList as $tblDivisionTeacher) {
                        $tblDivision = $tblDivisionTeacher->getTblDivision();

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
                                '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'Route' => 'Teacher'
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }

                $table = new TableData($divisionTable, null, array(
                    'Year' => 'Schuljahr',
                    'Type' => 'Schulart',
                    'Division' => 'Klasse',
                    'Option' => ''
                ), array(
                    'order' => array(
                        array('0', 'desc'),
                        array('1', 'asc'),
                        array('2', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 2)
                    ),
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
    public function frontendHeadmasterSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::HEADMASTER);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $buttonList = Prepare::useService()->setYearGroupButtonList('/Education/Certificate/Prepare/Headmaster',
            $IsAllYears, $IsGroup, $YearId, $tblYear);

        $divisionTable = array();
        if ($IsGroup) {
            $Stage->setDescription('Gruppe auswählen');
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', '/Education/Certificate/Prepare/Prepare', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'Route' => 'Headmaster'
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0)
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                        && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }

                    if ($tblDivision) {
                        $divisionTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'Route' => 'Headmaster'
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
                'Division' => 'Klasse',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2)
                ),
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
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $Route
     * @return Stage|string
     */
    public function frontendPrepare($DivisionId = null, $GroupId = null, $Route = 'Teacher')
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/' . $Route, new ChevronLeft(), array('IsGroup' => $GroupId ? true : false)
        ));

        // Tudor
        if ($GroupId != null) {
            if (($tblGroup = Group::useService()->getGroupById($GroupId))) {
                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    $tblDivisionList = array();
                    foreach ($tblPersonList as $tblPerson) {
                        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
                            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                                if (($tblDivision = $tblDivisionStudent->getTblDivision())) {
                                    $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                                }
                            }
                        }
                    }

                    $tableData = array();
                    foreach ($tblDivisionList as $tblDivision) {
                        $tableData = $this->setPrepareDivisionSelectData($tblDivision, $Route, $tableData, $GroupId);
                    }

                    $Stage->setContent(
                        new Layout(array(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Panel(
                                                'Gruppe',
                                                $tblGroup->getName(),
                                                Panel::PANEL_TYPE_INFO
                                            )
                                        ))
                                    ))
                                )),
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new TableData($tableData, null, array(
                                                'Date' => 'Zeugnisdatum',
                                                'Type' => 'Typ',
                                                'Name' => 'Name',
                                                'Option' => ''
                                            ),
                                                array(
                                                    'order' => array(
                                                        array(0, 'desc')
                                                    ),
                                                    'columnDefs' => array(
                                                        array('type' => 'de_date', 'targets' => 0),
                                                        array('width' => '1%', 'targets' => 3),
                                                        array('orderable' => false, 'targets' => 3)
                                                    )
                                                )
                                            )
                                        ))
                                    ))
                                ), new Title(new ListingTable() . ' Übersicht')),
                            )
                        )
                    );
                }

                return $Stage;
            } else {

                return $Stage . new Danger('Gruppe nicht gefunden.', new Ban());
            }
        }

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {

            $tableData = array();
            $tableData = $this->setPrepareDivisionSelectData($tblDivision, $Route, $tableData);

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'Date' => 'Zeugnisdatum',
                                        'Type' => 'Typ',
                                        'Name' => 'Name',
                                        'Option' => ''
                                    ),
                                        array(
                                            'order' => array(
                                                array(0, 'desc')
                                            ),
                                            'columnDefs' => array(
                                                array('type' => 'de_date', 'targets' => 0),
                                                array('width' => '10%', 'targets' => 3),
                                                array('orderable' => false, 'targets' => 3),
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }


    /**
     * @param $tblDivision
     * @param $Route
     * @param $tableData
     * @param bool|int $GroupId
     *
     * @return array
     */
    private function setPrepareDivisionSelectData($tblDivision, $Route, $tableData, $GroupId = false)
    {

        /** @var TblDivision $tblDivision */
        $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
        if ($tblPrepareAllByDivision) {
            foreach ($tblPrepareAllByDivision as $tblPrepareCertificate) {
                $tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate();
                $tblCertificateType = $tblGenerateCertificate ? $tblGenerateCertificate->getServiceTblCertificateType() : false;
                $tblSchoolType = $tblDivision->getType();

                if ($tblCertificateType) {
                    if ($Route != 'Diploma') {
                        // Abschlusszeugnisse überspringen
                        if ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                            // Ausnahme bei HOGA sollen die Klassenlehrer die Abschlusszeugnisse bearbeiten können, todo später Mandanteneintstellung
                            && !(Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA') && $Route == 'Teacher' && $tblSchoolType
                                && ($tblSchoolType->getShortName() == 'OS' || $tblSchoolType->getShortName() == 'FOS'))
                        ) {
                            continue;
                        }
                    } else {
                        // alle außer Abschlusszeugnisse überspringen
                        if ($tblCertificateType->getIdentifier() != 'DIPLOMA') {
                            continue;
                        }
                    }
                } else {
                    continue;
                }

                if ($Route == 'Diploma'
                    && $tblSchoolType
                    && $tblSchoolType->getName() == 'Gymnasium'
                ) {
                    $options = new Standard(
                        '', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new EyeOpen(),
                        array(
                            'PrepareId' => $tblPrepareCertificate->getId(),
                            'Route' => $Route,
                            'GroupId' => $GroupId
                        )
                        , 'Einstellungen und Vorschau der Zeugnisse'
                    );
                } else {
                    $options = (new Standard(
                        '', '/Education/Certificate/Prepare/Prepare'
                        . ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                            ? '/Diploma' . ($tblSchoolType
                                && ($tblSchoolType->getName() == 'Fachschule'
                                    || $tblSchoolType->getName() == 'Berufsgrundbildungsjahr'
//                                Fachoberschule, Berufsfachschule ist wie Oberschule
//                                    || $tblSchoolType->getName() == 'Fachoberschule'
//                                    || $tblSchoolType->getName() == 'Berufsfachschule'
                                )
                                ? '/Technical' : '')
                            : '')
                        . '/Setting', new Setup(),
                        array(
                            'PrepareId' => $tblPrepareCertificate->getId(),
                            'Route' => $Route,
                            'GroupId' => $GroupId
                        )
                        , 'Einstellungen'
                    ))
                    . (new Standard(
                        '', '/Education/Certificate/Prepare/Prepare/Preview', new EyeOpen(),
                        array(
                            'PrepareId' => $tblPrepareCertificate->getId(),
                            'Route' => $Route,
                            'GroupId' => $GroupId
                        )
                        , 'Vorschau der Zeugnisse'
                    ));
                }

                $tableData[$tblGenerateCertificate ? $tblGenerateCertificate->getId() : 0] = array(
                    'Date' => $tblPrepareCertificate->getDate(),
                    'Type' => $tblCertificateType ? $tblCertificateType->getName()
                        : '',
                    'Name' => $tblPrepareCertificate->getName(),
                    'Option' => $options
                );
            }
        }

        return $tableData;
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param string $Route
     * @param null $GradeTypeId
     * @param null $IsNotGradeType
     * @param null $Data
     * @param null $CertificateList
     * @param null $Page
     *
     * @return Stage|string
     */
    public function frontendPrepareSetting(
        $PrepareId = null,
        $GroupId = null,
        $Route = 'Teacher',
        $GradeTypeId = null,
        $IsNotGradeType = null,
        $Data = null,
        $CertificateList = null,
        $Page = null
    ) {

        $description = '';
        $tblPrepareList = false;
        $tblGroup = false;
        $headTableColumnList = array();
        $isAbsenceHour = false;
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {

                    $description = 'Klasse ' . $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            if ($tblPrepareList) {
                $tblDivision = $tblPrepare->getServiceTblDivision();
                $useMultipleBehaviorTasks = false;
                $tblTaskList = false;
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');

                if (($tblSetting = ConsumerSetting::useService()->getSetting(
                        'Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks'))
                    && $tblSetting->getValue()
                    && $tblDivision
                    && $tblTestType
                ) {
                    if (($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType))) {
                        $useMultipleBehaviorTasks = true;
                    }
                }

                if (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Graduation', 'Evaluation',
                    'ShowProposalBehaviorGrade'))
                ) {
                    $showProposalBehaviorGrade = $tblSetting->getValue();
                } else {
                    $showProposalBehaviorGrade = false;
                }

                if (($tblSettingAbsence = ConsumerSetting::useService()->getSetting(
                    'Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
                ) {
                    $useClassRegisterForAbsence = $tblSettingAbsence->getValue();
                } else {
                    $useClassRegisterForAbsence = false;
                }

                $tblTestList = false;
                foreach ($tblPrepareList as $tblPrepareCertificateItem) {
                    if (($tblPrepareCertificateItem->getServiceTblBehaviorTask())
                        && ($tblPrepareDivision = $tblPrepareCertificateItem->getServiceTblDivision())
                        && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepareCertificateItem->getServiceTblBehaviorTask(),
                            $tblPrepareDivision))
                    ) {
                        break;
                    }
                }

                // Kopfnoten festlegen
                if (!$IsNotGradeType
                    && $tblDivision
                    && (($tblGenerateCertificate->getServiceTblBehaviorTask()
                            && $tblTestList)
                        || $useMultipleBehaviorTasks)
                ) {
                    $Stage = new Stage('Zeugnisvorbereitung', 'Kopfnoten festlegen');

                    if ($tblGroup) {
                        $Stage->addButton(new Standard(
                            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'Route' => $Route
                            )
                        ));
                    } else {
                        $Stage->addButton(new Standard(
                            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                            array(
                                'DivisionId' => $tblDivision->getId(),
                                'Route' => $Route
                            )
                        ));
                    }

                    $hasPreviewGrades = false;
                    $tblCurrentGradeType = false;
                    $tblNextGradeType = false;
                    $tblGradeTypeList = array();
                    if ($useMultipleBehaviorTasks && $tblTaskList) {
                        /** @var TblTask $tblTaskTemp */
                        $tblTaskTemp = reset($tblTaskList);
                        $tblTestTempList = Evaluation::useService()->getTestAllByTask($tblTaskTemp);
                    } else {
                        $tblTestTempList = $tblTestList;
                    }

                    if ($tblTestTempList) {
                        foreach ($tblTestTempList as $tblTest) {
                            if (($tblGradeTypeItem = $tblTest->getServiceTblGradeType())) {
                                if (!isset($tblGradeTypeList[$tblGradeTypeItem->getId()])) {
                                    $tblGradeTypeList[$tblGradeTypeItem->getId()] = $tblGradeTypeItem;
                                    if ($tblCurrentGradeType && !$tblNextGradeType) {
                                        $tblNextGradeType = $tblGradeTypeItem;
                                    }
                                    if ($GradeTypeId && $GradeTypeId == $tblGradeTypeItem->getId()) {
                                        $tblCurrentGradeType = $tblGradeTypeItem;
                                    }
                                }
                            }
                        }
                        if (!$tblCurrentGradeType && !empty($tblGradeTypeList)) {
                            $tblCurrentGradeType = current($tblGradeTypeList);
                            if (count($tblGradeTypeList) > 1) {
                                $tblNextGradeType = next($tblGradeTypeList);
                            }
                        }
                    }

                    $buttonList = array();
                    /** @var TblGradeType $tblGradeType */
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        if ($tblCurrentGradeType->getId() == $tblGradeType->getId()) {
                            $name = new Info(new Bold($tblGradeType->getName()));
                            $icon = new Edit();
                        } else {
                            $name = $tblGradeType->getName();
                            $icon = null;
                        }

                        $buttonList[] = new Standard($name,
                            '/Education/Certificate/Prepare/Prepare/Setting', $icon, array(
                                'PrepareId' => $tblPrepare->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'Route' => $Route,
                                'GradeTypeId' => $tblGradeType->getId()
                            )
                        );
                    }

                    // Erstellt zusätzliche "Tabs" für weitere Sonstige Informationen und die Fehlzeiten
                    /** @noinspection PhpUnusedLocalVariableInspection */
                    list($informationPageList, $pageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList)
                        = Prepare::useService()->getCertificateInformationPages($tblPrepareList, $tblGroup, $useClassRegisterForAbsence);

                    $buttonList[] = new Standard('Sonstige Informationen',
                        '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                            'PrepareId' => $tblPrepare->getId(),
                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                            'Route' => $Route,
                            'IsNotGradeType' => true
                        )
                    );
                    foreach ($pageList as $item) {
                        if ($item == 'Absence') {
                            $text = 'Fehlzeiten';
                        } else {
                            $text = 'Sonstige Informationen (Seite ' . $item . ')';
                        }

                        $buttonList[] = new Standard($text,
                            '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                                'PrepareId' => $tblPrepare->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'Route' => $Route,
                                'IsNotGradeType' => true,
                                'Page' => $item
                            )
                        );
                    }

                    $studentTable = array();
                    $columnTable = array(
                        'Number' => '#',
                        'Name' => 'Name',
                        'Course' => 'Bildungsgang',
                        'Grades' => 'Einzelnoten in ' . ($tblCurrentGradeType ? $tblCurrentGradeType->getName() : ''),
                        'Average' => '&#216;',
                        'Data' => 'Zensur'
                    );

                    $selectListWithTrend[-1] = '';
                    for ($i = 1; $i < 5; $i++) {
                        $selectListWithTrend[$i . '+'] = (string)($i . '+');
                        $selectListWithTrend[$i] = (string)$i;
                        $selectListWithTrend[$i . '-'] = (string)($i . '-');
                    }
                    $selectListWithTrend[5] = "5";

                    $selectListWithOutTrend[-1] = '';
                    for ($i = 1; $i < 5; $i++) {
                        $selectListWithOutTrend[$i] = (string)$i;
                    }
                    $selectListWithOutTrend[5] = "5";

                    $tabIndex = 1;

                    foreach ($tblPrepareList as $tblPrepareItem) {
                        if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                            && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                        ) {
                            foreach ($tblStudentList as $tblPerson) {
                                if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                                    $studentTable[$tblPerson->getId()] = array(
                                        'Number' => (count($studentTable) + 1) . ' '
                                            . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                                ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                                    'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                                : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                        'Name' => $tblPerson->getLastFirstName()
                                            . ($tblGroup
                                                ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                                    );

                                    if (($tblType = $tblDivisionItem->getType())) {
                                        $isTechnicalSchool = $tblType->isTechnical();
                                    } else {
                                        $isTechnicalSchool = false;
                                    }

                                    // Bildungsgang
                                    $courseName = '';
                                    if ($isTechnicalSchool) {
                                        $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                                    } else {
                                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                                        ) {
                                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                                $tblTransferType);
                                            if ($tblStudentTransfer) {
                                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                                $courseName = $tblCourse ? $tblCourse->getName() : '';
                                            }
                                        }
                                    }
                                    $studentTable[$tblPerson->getId()]['Course'] = $courseName;

                                    if ($tblCurrentGradeType) {
                                        $subjectGradeList = array();
                                        $gradeList = array();
                                        $gradeListString = '';
                                        $averageStudent = false;
                                        if ($useMultipleBehaviorTasks) {
                                            if (($tblDivisionList = Division::useService()->getOtherDivisionsByStudent($tblDivisionItem, $tblPerson))) {
                                                foreach ($tblDivisionList as $item) {
                                                    if (($tblGradeList = Gradebook::useService()->getGradesByStudentAndGradeType(
                                                        $tblPerson, $item, $tblCurrentGradeType
                                                    ))
                                                    ) {
                                                        foreach ($tblGradeList as $tblGrade) {
                                                            if (($tblTestItem = $tblGrade->getServiceTblTest())
                                                                && ($tblTaskItem = $tblTestItem->getTblTask())
                                                                && ($tblSubject = $tblTestItem->getServiceTblSubject())
                                                            ) {
                                                                $subjectGradeList[$tblTaskItem->getId()][$tblSubject->getAcronym()] = $tblGrade;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            $averageList = array();
                                            // Zusammensetzen (für Anzeige) der vergebenen mehrfachen Kopfnoten
                                            /** @var TblGrade $grade */
                                            foreach ($subjectGradeList as $taskId => $subjectTaskGradeList) {
                                                $subString = '';
                                                if (($tblTaskItem = Evaluation::useService()->getTaskById($taskId))) {
                                                    ksort($subjectTaskGradeList);
                                                    foreach ($subjectTaskGradeList as $subjectAcronym => $grade) {
                                                        $tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym);
                                                        if ($tblSubject) {
                                                            if ($grade->getGrade() && is_numeric($grade->getGrade())) {
                                                                $gradeList[$taskId][] = floatval($grade->getGrade());
                                                            }

                                                            if (empty($subString)) {
                                                                $subString =
                                                                    $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                                            } else {
                                                                $subString .= ' | '
                                                                    . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                                            }
                                                        }
                                                    }
                                                    if ($showProposalBehaviorGrade) {
                                                        if (($tblProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade(
                                                                $tblDivisionItem, $tblTaskItem, $tblCurrentGradeType, $tblPerson
                                                            )) && ($proposalGrade = $tblProposalBehaviorGrade->getDisplayGrade())
                                                        ) {
                                                            $subString .= ' | (KL-Vorschlag:' . $proposalGrade . ')';
                                                        }
                                                    }
                                                    if (!empty($subString) && isset($gradeList[$taskId])) {
                                                        $count = count($gradeList[$taskId]);
                                                        $average = $count > 0 ? round(array_sum($gradeList[$taskId]) / $count, 2) : '';
                                                        if ($average) {
                                                            $averageList[$taskId] = $average;
                                                            $average = number_format($average, 2, ',', '.');
                                                        }
                                                        $gradeListString .= $tblTaskItem->getDate() . '&nbsp;&nbsp;&nbsp;'
                                                            . new Bold('&#216;' . $average) . '&nbsp;&nbsp;&nbsp;' . $subString
                                                        . '<br>';
                                                    }
                                                }
                                            }
                                            $countAverages = count($averageList);
                                            $average = $countAverages > 0 ? round(array_sum($averageList) / $countAverages, 2) : '';
                                            $studentTable[$tblPerson->getId()]['Average'] = $average;
                                            $averageStudent = $average;
                                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;
                                        } elseif (($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(), $tblDivisionItem))) {
                                            foreach ($tblTestList as $tblTest) {
                                                if (($tblGradeType = $tblTest->getServiceTblGradeType())
                                                    && $tblGradeType->getId() == $tblCurrentGradeType->getId()
                                                ) {
                                                    if (($tblSubject = $tblTest->getServiceTblSubject())
                                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                                            $tblPerson))
                                                    ) {
                                                        $subjectGradeList[$tblSubject->getAcronym()] = $tblGrade;
                                                    }
                                                }
                                            }

                                            if (!empty($subjectGradeList)) {
                                                ksort($subjectGradeList);
                                            }

                                            // Zusammensetzen (für Anzeige) der vergebenen Kopfnoten
                                            /** @var TblGrade $grade */
                                            foreach ($subjectGradeList as $subjectAcronym => $grade) {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym);
                                                if ($tblSubject) {
                                                    if ($grade->getGrade() && is_numeric($grade->getGrade())) {
                                                        $gradeList[] = floatval($grade->getGrade());
                                                    }
                                                    if (empty($gradeListString)) {
                                                        $gradeListString =
                                                            $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                                    } else {
                                                        $gradeListString .= ' | '
                                                            . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                                    }
                                                }
                                            }
                                            if ($showProposalBehaviorGrade && $tblPrepare->getServiceTblBehaviorTask()) {
                                                if (($tblProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade(
                                                    $tblDivisionItem, $tblPrepare->getServiceTblBehaviorTask(), $tblCurrentGradeType, $tblPerson
                                                    )) && ($proposalGrade = $tblProposalBehaviorGrade->getDisplayGrade())
                                                ) {
                                                    $gradeListString .= ' | (KL-Vorschlag:' . $proposalGrade . ')';
                                                }
                                            }
                                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;

                                            // calc average
                                            if (!empty($gradeList)) {
                                                $count = count($gradeList);
                                                $average = $count > 0 ? round(array_sum($gradeList) / $count, 2) : '';
                                                $studentTable[$tblPerson->getId()]['Average'] = $average;
                                                if ($average) {
                                                    $averageStudent = $average;
                                                }
                                            } else {
                                                $studentTable[$tblPerson->getId()]['Average'] = '';
                                            }
                                        }

                                        // Post setzen
                                        if ($Data === null
                                            && $tblPrepareStudent
                                            && $tblTestType
                                            && $tblCurrentGradeType
                                        ) {
                                            $Global = $this->getGlobal();
                                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                                $tblPrepareItem, $tblPerson, $tblDivisionItem, $tblTestType,
                                                $tblCurrentGradeType
                                            );
                                            if ($tblPrepareGrade) {
                                                $gradeValue = $tblPrepareGrade->getGrade();
                                                $Global->POST['Data'][$tblPrepareStudent->getId()] = $gradeValue;
                                            } elseif ($averageStudent && !$tblPrepareStudent->isApproved()) {
                                                // Noten aus dem Notendurchschnitt als Vorschlag eintragen
                                                if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                    $hasPreviewGrades = true;
                                                }
                                                $Global->POST['Data'][$tblPrepareStudent->getId()] = round($averageStudent, 0);
                                            }

                                            $Global->savePost();
                                        }

                                        if ($tblPrepareStudent
                                            && $tblPrepareStudent->getServiceTblCertificate()
                                        ) {
                                            if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                                && $tblCertificate->isInformation()
                                            ) {
                                                $selectList = $selectListWithTrend;
                                            } else {
                                                $selectList = $selectListWithOutTrend;
                                            }

                                            $selectComplete = (new SelectCompleter('Data[' . $tblPrepareStudent->getId() . ']',
                                                '', '', $selectList))
                                                ->setTabIndex($tabIndex++);
                                            if ($tblPrepareStudent->isApproved()) {
                                                $selectComplete->setDisabled();
                                            }

                                            $studentTable[$tblPerson->getId()]['Data'] = $selectComplete;
                                        } else {
                                            // keine Zeugnisvorlage ausgewählt
                                            $studentTable[$tblPerson->getId()]['Data'] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $columnDef = array(
                        array(
                            "width" => "18px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "80px",
                            "targets" => 2
                        ),
                        array(
                            "width" => "50px",
                            "targets" => array(4)
                        ),
                        array(
                            "width" => "80px",
                            "targets" => array(5)
                        ),
                    );

                    $tableData = new TableData($studentTable, null, $columnTable,
                        array(
                            "columnDefs" => $columnDef,
                            'order' => array(
                                array('0', 'asc'),
                            ),
                            "paging" => false, // Deaktivieren Blättern
                            "iDisplayLength" => -1,    // Alle Einträge zeigen
                            "searching" => false, // Deaktivieren Suchen
                            "info" => false,  // Deaktivieren Such-Info
                            "sort" => false,
                            "responsive" => false
                        )
                    );

                    $form = new Form(
                        new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    $tableData
                                ),
                                new FormColumn(new HiddenField('Data[IsSubmit]'))
                            )),
                        ))
                        , new Primary('Speichern', new Save())
                    );

                    $Stage->setContent(
                        new Layout(array(
                            new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(array(
                                        new Panel(
                                            'Zeugnis',
                                            array(
                                                $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                            ),
                                            Panel::PANEL_TYPE_INFO
                                        ),
                                    ), 6),
                                    new LayoutColumn(array(
                                        new Panel(
                                            $tblGroup ? 'Gruppe' : 'Klasse',
                                            $description,
                                            Panel::PANEL_TYPE_INFO
                                        ),
                                    ), 6),
                                    new LayoutColumn($buttonList),
                                    $hasPreviewGrades
                                        ? new LayoutColumn(new Warning(
                                        'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                                    ))
                                        : null,
                                )),
                            )),
                            new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(array(
                                        Prepare::useService()->updatePrepareBehaviorGrades(
                                            $form,
                                            $tblPrepare,
                                            $tblGroup ? $tblGroup : null,
                                            $tblCurrentGradeType,
                                            $tblNextGradeType ? $tblNextGradeType : null,
                                            $Route,
                                            $Data
                                        )
                                    ))
                                ))
                            ))
                        ))
                    );

                    return $Stage;

                    // Sonstige Informationen
                } elseif (($tblDivision = $tblPrepare->getServiceTblDivision())
                    && (($IsNotGradeType
                            || (!$IsNotGradeType && !$tblPrepare->getServiceTblBehaviorTask()))
                        || (!$IsNotGradeType && $tblPrepare->getServiceTblBehaviorTask()
                            && !Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                                $tblDivision)))
                ) {
                    $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');

                    if ($tblGroup) {
                        $Stage->addButton(new Standard(
                            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'Route' => $Route
                            )
                        ));
                    } else {
                        $Stage->addButton(new Standard(
                            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                            array(
                                'DivisionId' => $tblDivision->getId(),
                                'Route' => $Route
                            )
                        ));
                    }

                    if ($tblPrepare->getServiceTblBehaviorTask()
                        && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                            $tblDivision))
                    ) {
                        $tblCurrentGradeType = false;
                        $tblNextGradeType = false;
                        $tblGradeTypeList = array();
                        foreach ($tblTestList as $tblTest) {
                            if (($tblGradeTypeItem = $tblTest->getServiceTblGradeType())) {
                                if (!isset($tblGradeTypeList[$tblGradeTypeItem->getId()])) {
                                    $tblGradeTypeList[$tblGradeTypeItem->getId()] = $tblGradeTypeItem;
                                    if ($tblCurrentGradeType && !$tblNextGradeType) {
                                        $tblNextGradeType = $tblGradeTypeItem;
                                    }
                                    if ($GradeTypeId && $GradeTypeId == $tblGradeTypeItem->getId()) {
                                        $tblCurrentGradeType = $tblGradeTypeItem;
                                    }
                                }
                            }
                        }

                        $buttonList = array();
                        /** @var TblGradeType $tblGradeType */
                        foreach ($tblGradeTypeList as $tblGradeType) {
                            $buttonList[] = new Standard($tblGradeType->getName(),
                                '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                    'Route' => $Route,
                                    'GradeTypeId' => $tblGradeType->getId()
                                )
                            );
                        }
                    }

                    // Aufteilung der Sonstigen Informationen auf mehrere Seiten
                    list($informationPageList, $pageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList)
                        = Prepare::useService()->getCertificateInformationPages($tblPrepareList, $tblGroup, $useClassRegisterForAbsence);

                    if ($Page == null) {
                        $buttonList[] = new Standard(new Info(new Bold('Sonstige Informationen')),
                            '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                                'PrepareId' => $tblPrepare->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'Route' => $Route,
                                'IsNotGradeType' => true
                            )
                        );
                    } else {
                        $buttonList[] = new Standard('Sonstige Informationen',
                            '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                                'PrepareId' => $tblPrepare->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'Route' => $Route,
                                'IsNotGradeType' => true
                            )
                        );
                    }

                    $nextPage = null;
                    $isCurrentPage = $Page == null;
                    foreach ($pageList as $item) {
                        if ($item == 'Absence') {
                            $text = 'Fehlzeiten';
                        } else {
                            $text = 'Sonstige Informationen (Seite ' . $item . ')';
                        }

                        if ($Page == $item) {
                            $buttonList[] = new Standard(new Info(new Bold($text)),
                                '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                    'Route' => $Route,
                                    'IsNotGradeType' => true,
                                    'Page' => $item
                                )
                            );
                            $isCurrentPage = true;
                        } else {
                            $buttonList[] = new Standard($text,
                                '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                    'Route' => $Route,
                                    'IsNotGradeType' => true,
                                    'Page' => $item
                                )
                            );

                            if ($isCurrentPage) {
                                $nextPage = $item;
                                $isCurrentPage = false;
                            }
                        }
                    }

                    $studentTable = array();
                    if ($Page == 'Absence') {
                        $Stage->setDescription('Fehlzeiten festlegen');
                        if ($useClassRegisterForAbsence) {
                            $Stage->setMessage(
                                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold('Hinweis: ')
                                    . new Container('Die Fehlzeiten werden im Klassenbuch erfasst. Hier können für fehlende
                                    Unterrichtseinheiten zusätzliche Tage eingeben werden. Die Fehlzeiten auf dem Zeugnis
                                    ergeben sich dann aus den <b>"Fehltagen im Klassenbuch"</b> + <b>"die hier eingebenen
                                    Zusatz-Tage für fehlende Unterrichtseinheiten"</b>.')
                                    . new Container('Es können nur Zusatz-Tage für Unterrichtseinheiten bei einem Schüler
                                    erfasst werden, wenn der Schüler eine Zeugnisvorlage mit Fehlzeiten besitzt und im
                                    Klassenbuch auch entsprechende fehlende Unterrichtseinheiten erfasst wurden.')
                                    . new Container('&nbsp;')
                                    . new Container(new Bold(new Exclamation() . ' Bitte speichern Sie die Fehlzeiten erst,
                                    wenn alle Fehlzeiten im Klassenbuch erfasst wurden. Sie können diese Seite mit
                                    "Ohne Speichern weiter" überspringen.'))
                                )
                            );
                        } else {
                            $Stage->setMessage(
                                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold('Hinweis: ')
                                    . new Container('Die Fehlzeiten für die Zeugnisse werden hier erfasst. Bitte tragen
                                    Sie hier die Fehlzeiten-Tage für das Zeugnis ein. Wurden schon Fehltage im Klassenbuch
                                    erfasst, werden diese unter <b>"Ganze Tage"</b> angezeigt. Wurden schon fehlende
                                    Unterrichtseinheiten im Klassenbuch erfasst, werden diese unter <b>"UE"</b> angezeigt.')
                                    . new Container('Es können nur Fehltage für das Zeugnis erfasst werden, wenn der Schüler
                                    eine Zeugnisvorlage mit Fehlzeiten besitzt.')
                                )
                            );
                        }

                        foreach ($tblPrepareList as $tblPrepareItem) {
                            if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                                && !$isAbsenceHour
                                && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                            ) {
                                foreach ($tblStudentList as $tblPerson) {
                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)){
                                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);
                                        if ($tblPrepareStudent && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                                            if ($tblCertificate->getName() == 'Berufsfachschule Jahreszeugnis' && $tblCertificate->getDescription() == 'Generalistik') {
                                                $isAbsenceHour = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                break;
                            }
                        }

                        $headTableColumnList[] = new TableColumn('Schüler', 4);
                        if($isAbsenceHour){
                            // Zusatzspalte für Stunden
                            $headTableColumnList[] = new TableColumn('Entschuldigte Fehlzeiten', 4);
                            $headTableColumnList[] = new TableColumn('Unentschuldigte Fehlzeiten', 4);
                        } else {
                            // Standard
                            $headTableColumnList[] = new TableColumn('Entschuldigte Fehlzeiten', 3);
                            $headTableColumnList[] = new TableColumn('Unentschuldigte Fehlzeiten', 3);
                        }

                        if ($useClassRegisterForAbsence) {
                            $columnTable = array(
                                'Number' => '#',
                                'Name' => 'Name',
                                'IntegrationButton' => 'Integration',
                                'Course' => 'Bildungsgang',

                                'ExcusedDays' => 'Ganze Tage',
                                'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten'),
                                'ExcusedDaysFromLessons' => new ToolTip('Zusatz-Tage für Unterrichts&shy;einheiten',
                                    'Für fehlende Unterrichtseinheiten können zusätzliche Fehltage erfasst werden'),

                                'UnexcusedDays' => 'Ganze Tage',
                                'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten'),
                                'UnexcusedDaysFromLessons' => new ToolTip('Zusatz-Tage für Unterrichts&shy;einheiten',
                                    'Für fehlende Unterrichtseinheiten können zusätzliche Fehltage erfasst werden')
                            );
                        } else {
                            if($isAbsenceHour){
                                // Zusatzspalte für Stunden
                                $columnTable = array(
                                    'Number' => '#',
                                    'Name' => 'Name',
                                    'IntegrationButton' => 'Integration',
                                    'Course' => 'Bildungsgang',

                                    'ExcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                                    'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                                    'ExcusedDaysInHours' => new ToolTip('Stunden','Gesamt: 1 Tag = 8 Stunden'),
                                    'ExcusedDays' => 'Stunden auf dem Zeugnis',

                                    'UnexcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                                    'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                                    'UnexcusedDaysInHours' => new ToolTip('Stunden','Gesamt: 1 Tag = 8 Stunden'),
                                    'UnexcusedDays' => 'Stunden auf dem Zeugnis',
                                );
                            } else {
                                // Standard
                                $columnTable = array(
                                    'Number' => '#',
                                    'Name' => 'Name',
                                    'IntegrationButton' => 'Integration',
                                    'Course' => 'Bildungsgang',

                                    'ExcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                                    'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                                    'ExcusedDays' => 'Tage auf dem Zeugnis',

                                    'UnexcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                                    'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                                    'UnexcusedDays' => 'Tage auf dem Zeugnis',
                                );
                            }

                        }
                    } else {
                        $columnTable = array(
                            'Number' => '#',
                            'Name' => 'Name',
                            'IntegrationButton' => 'Integration',
                            'Course' => 'Bildungsgang'
                        );
                    }

                    foreach ($tblPrepareList as $tblPrepareItem) {
                        if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                            && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                        ) {
                            foreach ($tblStudentList as $tblPerson) {
                                if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);
                                    $studentTable[$tblPerson->getId()] = array(
                                        'Number' => (count($studentTable) + 1) . ' '
                                            . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                                ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                                    'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                                : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                        'Name' => $tblPerson->getLastFirstName()
                                            . ($tblGroup
                                                ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                                    );

                                    if (($tblType = $tblDivisionItem->getType())) {
                                        $isTechnicalSchool = $tblType->isTechnical();
                                    } else {
                                        $isTechnicalSchool = false;
                                    }

                                    // Bildungsgang
                                    $courseName = '';
                                    if ($isTechnicalSchool) {
                                        $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                                    } else {
                                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                                        ) {
                                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                                $tblTransferType);
                                            if ($tblStudentTransfer) {
                                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                                $courseName = $tblCourse ? $tblCourse->getName() : '';
                                            }
                                        }
                                    }
                                    $studentTable[$tblPerson->getId()]['Course'] = $courseName;

                                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem,
                                        $tblPerson);

                                    /**
                                     * Post-Data
                                     */
                                    if ($Data === null && $tblPrepareStudent) {
                                        $Global = $this->getGlobal();

                                        if ($Page == null) {
                                            /*
                                            * Individuelle Zeugnisse EVGSM Meerane Klassename vorsetzen
                                            */
                                            if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EVGSM')
                                                && ($tblCertificateStudent = $tblPrepareStudent->getServiceTblCertificate())
                                                && strpos($tblCertificateStudent->getCertificate(), 'EVGSM') !== false
                                            ) {
                                                $Global->POST['Data'][$tblPrepareStudent->getId()]['DivisionName']
                                                    = $tblDivisionItem->getDisplayName();
                                            }
                                        }

                                        $Global->savePost();
                                    }

                                    if ($Page == 'Absence') {
                                        $excusedDays = 0;
                                        $excusedLessons = 0;
                                        $excusedDaysFromClassRegister = 0;
                                        $unexcusedDays = 0;
                                        $unexcusedLessons = 0;
                                        $unexcusedDaysFromClassRegister = 0;

                                        if ($tblPrepareStudent) {
                                            if ($tblGenerateCertificate && $tblGenerateCertificate->getAppointedDateForAbsence()) {
                                                $date = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
                                            } else {
                                                $date = new DateTime($tblPrepareItem->getDate());
                                            }

                                            $excusedDays =  $tblPrepareStudent->getExcusedDays();
                                            $excusedDaysFromClassRegister = Absence::useService()->getExcusedDaysByPerson(
                                                $tblPerson,
                                                $tblDivisionItem,
                                                $date,
                                                $excusedLessons
                                            );

                                            $unexcusedDays =  $tblPrepareStudent->getUnexcusedDays();
                                            $unexcusedDaysFromClassRegister = Absence::useService()->getUnexcusedDaysByPerson(
                                                $tblPerson,
                                                $tblDivisionItem,
                                                $date,
                                                $unexcusedLessons
                                            );

                                            /*
                                             * Post Fehlzeiten
                                             */
                                            if ($Data === null && $tblPrepareStudent) {
                                                $Global = $this->getGlobal();
                                                if ($Global) {
                                                    if ($useClassRegisterForAbsence) {
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['ExcusedDaysFromLessons']
                                                            = $tblPrepareStudent->getExcusedDaysFromLessons();
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['UnexcusedDaysFromLessons']
                                                            = $tblPrepareStudent->getUnexcusedDaysFromLessons();
                                                    } else {
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['ExcusedDays']
                                                            = $excusedDays;
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['UnexcusedDays']
                                                            = $unexcusedDays;
                                                    }
                                                }
                                                $Global->savePost();
                                            }
                                        }

                                        if ($tblPrepareStudent
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                            && (isset($CertificateHasAbsenceList[$tblCertificate->getId()]))
                                        ) {
                                            if ($useClassRegisterForAbsence) {
                                                // Fehlzeiten werden im Klassenbuch gepflegt
                                                if ($excusedDays === null) {
                                                    $excusedDays = $excusedDaysFromClassRegister;
                                                }
                                                if ($unexcusedDays === null) {
                                                    $unexcusedDays = $unexcusedDaysFromClassRegister;
                                                }

                                                $studentTable[$tblPerson->getId()]['ExcusedDays'] = $excusedDays;
                                                $studentTable[$tblPerson->getId()]['ExcusedDaysInHours'] = $excusedDays * 8 + $excusedLessons;
                                                $studentTable[$tblPerson->getId()]['ExcusedLessons'] = $excusedLessons;

                                                $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $unexcusedDays;
                                                $studentTable[$tblPerson->getId()]['UnexcusedDaysInHours'] = $unexcusedDays * 8 + $unexcusedLessons;
                                                $studentTable[$tblPerson->getId()]['UnexcusedLessons'] = $unexcusedLessons;

                                                // Eingabe nur möglich wenn UEs beim Schüler erfasst wurden
                                                if (isset($StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_EXCUSED])) {
                                                    $inputExcusedDaysFromLessons = new NumberField(
                                                        'Data[' . $tblPrepareStudent->getId() . '][ExcusedDaysFromLessons]',
                                                        '',
                                                        ''
                                                    );

                                                    if ($tblPrepareStudent->isApproved()) {
                                                        $inputExcusedDaysFromLessons->setDisabled();
                                                    }
                                                    $studentTable[$tblPerson->getId()]['ExcusedDaysFromLessons'] = $inputExcusedDaysFromLessons;
                                                }
                                                if (isset($StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_UNEXCUSED])) {
                                                    $inputUnexcusedDaysFromLessons = new NumberField(
                                                        'Data[' . $tblPrepareStudent->getId() . '][UnexcusedDaysFromLessons]',
                                                        '',
                                                        ''
                                                    );
                                                    if ($tblPrepareStudent->isApproved()) {
                                                        $inputUnexcusedDaysFromLessons->setDisabled();
                                                    }
                                                    $studentTable[$tblPerson->getId()]['UnexcusedDaysFromLessons'] = $inputUnexcusedDaysFromLessons;
                                                }
                                            } else {
                                                // Fehlzeiten werden hier (Zeugnisvorbereitung) gepflegt
                                                $studentTable[$tblPerson->getId()]['ExcusedDaysInClassRegister'] = $excusedDaysFromClassRegister;
                                                $studentTable[$tblPerson->getId()]['ExcusedDaysInHours'] = $excusedDaysFromClassRegister * 8 + $excusedLessons;
                                                $studentTable[$tblPerson->getId()]['ExcusedLessons'] = $excusedLessons;

                                                $studentTable[$tblPerson->getId()]['UnexcusedDaysInClassRegister'] = $unexcusedDaysFromClassRegister;
                                                $studentTable[$tblPerson->getId()]['UnexcusedDaysInHours'] = $unexcusedDaysFromClassRegister * 8 + $unexcusedLessons;
                                                $studentTable[$tblPerson->getId()]['UnexcusedLessons'] = $unexcusedLessons;

                                                $inputExcusedDays = new NumberField(
                                                    'Data[' . $tblPrepareStudent->getId() . '][ExcusedDays]',
                                                    '',
                                                    ''
                                                );
                                                $inputUnexcusedDays = new NumberField(
                                                    'Data[' . $tblPrepareStudent->getId() . '][UnexcusedDays]',
                                                    '',
                                                    ''
                                                );
                                                if ($tblPrepareStudent->isApproved()) {
                                                    $inputExcusedDays->setDisabled();
                                                    $inputUnexcusedDays->setDisabled();
                                                }
                                                $studentTable[$tblPerson->getId()]['ExcusedDays'] = $inputExcusedDays;
                                                $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $inputUnexcusedDays;
                                            }

                                        }
                                    }

                                    // Integration ReadOnlyButton
                                    if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                                        $studentTable[$tblPerson->getId()]['IntegrationButton'] = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                                            ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                                    } else {
                                        $studentTable[$tblPerson->getId()]['IntegrationButton'] = '';
                                    }

                                    /*
                                     * Sonstige Informationen der Zeugnisvorlage
                                     */
                                    $this->getTemplateInformation(
                                        $tblPrepareItem,
                                        $tblPerson,
                                        $studentTable,
                                        $columnTable,
                                        $Data,
                                        $CertificateList,
                                        $Page,
                                        $informationPageList,
                                        $GroupId
                                    );

                                    // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                                    foreach ($columnTable as $columnKey => $columnName) {
                                        foreach ($studentTable as $personId => $value) {
                                            if (!isset($studentTable[$personId][$columnKey])) {
                                                $studentTable[$personId][$columnKey] = '';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $SpaceWithFalseTable = '';
                    // if using false table, need to be space between buttons & table
                    // same consumer as those who using Editor ad inputfield
                    if($tblConsumer
                        && ($tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'REF') || $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EVAB'))
                    ) {
                        $Interactive = false;
                        $SpaceWithFalseTable = new Container('&nbsp;');
                    } else {
                        $Interactive = array(
                            "columnDefs" => array(
                                array(
                                    "width" => "18px",
                                    "targets" => 0
                                ),
                                array(
                                    "width" => "200px",
                                    "targets" => 1
                                ),
                                array(
                                    "width" => "80px",
                                    "targets" => 2
                                ),
                            ),
                            'order' => array(
                                array('0', 'asc'),
                            ),
                            "paging" => false, // Deaktivieren Blättern
                            "iDisplayLength" => -1,    // Alle Einträge zeigen
                            "searching" => false, // Deaktivieren Suchen
                            "info" => false,  // Deaktivieren Such-Info
                            "sort" => false,
                            "responsive" => false
                        );
                    }

                    $tableData = new TableData($studentTable, null, $columnTable,
                        $Interactive, true
                    );

                    $formButtons[] = new Primary('Speichern', new Save());
                    if ($Page == 'Absence' && !empty($headTableColumnList)) {
                        $tableData->prependHead(
                            new TableHead(
                                new TableRow(
                                    $headTableColumnList
                                )
                            )
                        );

                        $formButtons[] = new Standard('Ohne Speichern weiter', '/Education/Certificate/Prepare/Prepare/Preview',
                            null, array('PrepareId' => $PrepareId, 'Route' => $Route = 'Teacher', 'GroupId' => $GroupId));
                    }

                    $form = new Form(
                        new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    $tableData
                                ),
                                new FormColumn(new HiddenField('Data[IsSubmit]'))
                            )),
                            new FormRow(new FormColumn($formButtons))
                        ))
                    );

                    $Stage->setContent(
                        ApiPrepare::receiverModal()
                        .new Layout(array(
                            new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(array(
                                        new Panel(
                                            'Zeugnis',
                                            array(
                                                $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                            ),
                                            Panel::PANEL_TYPE_INFO
                                        ),
                                    ), 6),
                                    new LayoutColumn(array(
                                        new Panel(
                                            $tblGroup ? 'Gruppe' : 'Klasse',
                                            $description,
                                            Panel::PANEL_TYPE_INFO
                                        ),
                                    ), 6),
                                    new LayoutColumn($buttonList),
                                )),
                            )),
                            new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(array(
                                        ApiSupportReadOnly::receiverOverViewModal(),
                                        $SpaceWithFalseTable,
                                        Prepare::useService()->updatePrepareInformationList($form, $tblPrepare,
                                            $tblGroup ? $tblGroup : null, $Route, $Data, $CertificateList, $nextPage)
                                    ))
                                ))
                            ))
                        ))
                    );

                    return $Stage;
                }
            }

        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param array $studentTable
     * @param array $columnTable
     * @param array|null $Data
     * @param array|null $CertificateList
     * @param null|integer $Page
     * @param null|array $informationPageList
     * @param null $GroupId
     */
    protected function getTemplateInformation(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable,
        &$Data,
        &$CertificateList,
        $Page = null,
        $informationPageList = null,
        $GroupId = null
    ) {

        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowOrientationsInCertificateRemark'))) {
            $showOrientationsInCertificateRemark = $tblSetting->getValue();
        } else {
            $showOrientationsInCertificateRemark = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowTeamsInCertificateRemark'))) {
            $showTeamsInCertificateRemark = $tblSetting->getValue();
        } else {
            $showTeamsInCertificateRemark = false;
        }

        if ($tblCertificate && ($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
            $Certificate = null;
            if ($tblCertificate) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {
                    // Wahlbereich gibt es nur bei der Oberschule
                    $showOrientationsInCertificateRemark = $showOrientationsInCertificateRemark
                        && ($tblSchoolType = $tblCertificate->getServiceTblSchoolType())
                        && ($tblSchoolType->getShortName() == 'OS');

                    if (($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
                        $tblLevel = $tblDivision->getTblLevel();
                    } else {
                        $tblLevel = false;
                    }

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblDivision ? $tblDivision : null);

                    // create Certificate with Placeholders
                    $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
                    $Certificate->createCertificate($Data, $pageList);

                    $CertificateList[$tblPerson->getId()] = $Certificate;

                    $FormField = Generator::useService()->getFormField();
                    $FormLabel = Generator::useService()->getFormLabel(($tblType = $tblDivision->getType()) ? $tblType : null);

                    if ($Data === null) {
                        $Global = $this->getGlobal();
                        $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepareCertificate,
                            $tblPerson);
                        $hasTransfer = false;
                        $isTeamSet = false;
                        $hasRemarkText = false;
                        $hasEducationDateFrom = false;
                        $isSubjectAreaSet = false;
                        $tblStudent = $tblPerson->getStudent();
                        if ($tblPrepareInformationAll) {
                            foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                if ($tblPrepareInformation->getField() == 'Team' || $tblPrepareInformation->getField() == 'TeamExtra') {
                                    $isTeamSet = true;
                                }

                                if ($tblPrepareInformation->getField() == 'Remark' || $tblPrepareInformation->getField() == 'RemarkWithoutTeam') {
                                    $hasRemarkText = true;
                                }

                                if ($tblPrepareInformation->getField() == 'EducationDateFrom') {
                                    $hasEducationDateFrom = true;
                                }

                                if ($tblPrepareInformation->getField() == 'SubjectArea') {
                                    $isSubjectAreaSet = true;
                                }

                                if ($tblPrepareInformation->getField() == 'SchoolType'
                                    && method_exists($Certificate, 'selectValuesSchoolType')
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesSchoolType());
                                } elseif ($tblPrepareInformation->getField() == 'Type'
                                    && method_exists($Certificate, 'selectValuesType')
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesType());
                                }  elseif ($tblPrepareInformation->getField() == 'Success'
                                    && method_exists($Certificate, 'selectValuesSuccess')
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesSuccess());
                                } elseif ($tblPrepareInformation->getField() == 'Transfer'
                                    && method_exists($Certificate, 'selectValuesTransfer')
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(), $Certificate->selectValuesTransfer());
                                        $hasTransfer = true;
                                } elseif ($tblPrepareInformation->getField() == 'Job_Grade_Text'
                                    && method_exists($Certificate, 'selectValuesJobGradeText')
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesJobGradeText());
//                                } elseif ($tblPrepareInformation->getField() == 'FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                                    && method_exists($Certificate, 'selectValuesFoesAbsText')
//                                ) {
//                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] =
//                                        array_search($tblPrepareInformation->getValue(),
//                                            $Certificate->selectValuesFoesAbsText());
                                } elseif (strpos($tblPrepareInformation->getField(), '_GradeText')
                                    && ($tblGradeText = Gradebook::useService()->getGradeTextByName($tblPrepareInformation->getValue()))
                                ) {
                                    // Zeugnistext umwandeln
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()] = $tblGradeText->getId();
                                } elseif ($tblPrepareInformation->getField() == 'AdditionalRemarkFhr') {
                                    // Checkbox
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()]
                                        = $tblPrepareInformation->getValue() ? 1 : 0;
                                } else {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareInformation->getField()]
                                        = $tblPrepareInformation->getValue();
                                }
                            }
                        }

                        // Coswig Versetzungsvermerk in die Bemerkung vorsetzten
                        if (!$hasRemarkText
                            && Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVSC')
                            && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                            && $tblCertificateType->getIdentifier() == 'YEAR'
                        ) {
                            $nextLevel = 'x';
                            if ($tblLevel
                                && is_numeric($tblLevel->getName())
                            ) {
                                $nextLevel = floatval($tblLevel->getName()) + 1;
                            }
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] =
                                $tblPerson->getFirstSecondName() . ' wird versetzt in Klasse ' . $nextLevel . '.';
                        }

                        // Arbeitsgemeinschaften aus der Schülerakte laden
                        if (!$isTeamSet && $showTeamsInCertificateRemark) {
                            if ($tblStudent
                                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                    $tblStudent, $tblSubjectType
                                ))
                            ) {
                                $tempList = array();
                                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                    if ($tblStudentSubject->getServiceTblSubject()) {
                                        $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                                    }
                                }
                                if (!empty($tempList)) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Team'] = implode(', ', $tempList);
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['TeamExtra'] = implode(', ', $tempList);
                                }
                            }
                        }

                        // Wahlbereich aus der Schülerakte laden
                        if ($showOrientationsInCertificateRemark) {
                            if ($tblStudent
                                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                    $tblStudent, $tblSubjectType
                                ))
                            ) {
                                /** @var TblStudentSubject $tblStudentSubject */
                                $tblStudentSubject = reset($tblStudentSubjectList);
                                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Orientation']
                                        = $tblPerson->getFirstSecondName().' hat im Rahmen des Wahlbereiches am Kurs "'
                                        .$tblSubject->getName().'" teilgenommen.';
                                }
                            }
                        }

                        // Vorsetzen auf Versetzungsvermerk: wird versetzt
                        if (!$hasTransfer) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['Transfer'] = 1;
                        }

                        // SSW-340 Halbjahreszeugnis Klasse 10 OS -> abgewählte Fächer in die Bemerkung vorsetzen
                        if (!$hasRemarkText
                            && (($Certificate->getCertificateEntity()->getCertificate() == 'MsHjRs')
                                || ($tblLevel
                                    && intval($tblLevel->getName()) == 10
                                    && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\MsHjZ')
                        )) {
                            if (($tblDroppedSubjectList = Prepare::useService()->getAutoDroppedSubjects($tblPerson, $tblDivision))) {
                                $countDroppedSubjects = count($tblDroppedSubjectList);
                                if ($countDroppedSubjects == 1) {
                                    $text = current($tblDroppedSubjectList) . ' wurde in der Klassenstufe 9 abgeschlossen.';
                                } else {
                                    $countItem = 0;
                                    $text = '';
                                    foreach ($tblDroppedSubjectList as $name) {
                                        $countItem++;
                                        if ($countItem == 1) {
                                            $text .= $name;
                                        } elseif ($countItem == $countDroppedSubjects) {
                                            $text .= ' und ' . $name;
                                        } else {
                                            $text .= ', ' . $name;
                                        }
                                    }

                                    $text .=  ' wurden in der Klassenstufe 9 abgeschlossen.';
                                }

                                $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] = $text;
                            }
                        }

                        // GTA setzen, werden in der Schülerakte als Arbeitsgemeinschaften gepflegt
                        if (($tblStudent = $tblPerson->getStudent())
                            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                $tblStudent, $tblSubjectType
                            ))
                        ) {
                            $tempList = array();
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getServiceTblSubject()) {
                                    $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                                }
                            }

                            $textGTA = $tblPerson->getFirstSecondName() . ' besuchte in diesem Schuljahr ';

                            switch (count($tempList)) {
                                case 1: $textGTA .= 'das GTA ' . $tempList[0] . '.';
                                    break;
                                case 2: $textGTA .= 'die GTA ' . $tempList[0]
                                    . ' und ' . $tempList[1] . '.';
                                    break;
                                case 3: $textGTA .= 'die GTA ' . $tempList[0]
                                    . ', ' . $tempList[1]
                                    . ' und ' . $tempList[2] . '.';
                                    break;
                                case 4: $textGTA .= 'die GTA ' . $tempList[0]
                                    . ', ' . $tempList[1]
                                    . ', ' . $tempList[2]
                                    . ' und ' . $tempList[3] . '.';
                                    break;
                                case 5: $textGTA .= 'die GTA ' . $tempList[0]
                                    . ', ' . $tempList[1]
                                    . ', ' . $tempList[2]
                                    . ', ' . $tempList[3]
                                    . ' und ' . $tempList[4] . '.';
                                    break;
                            }

                            $Global->POST['Data'][$tblPrepareStudent->getId()]['GTA'] = $textGTA;
                        }

                        // Vorsetzen des Schulbesuchsjahrs
                        if (($tblStudent = $tblPerson->getStudent())
                        && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['SchoolVisitYear'])) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['SchoolVisitYear'] = $tblStudent->getSchoolAttendanceYear(false);
                        }

                        $isSupportForPrimarySchool = false;
                        // Seelitz Förderbedarf-Satz in die Bemerkung vorsetzen
                        if (!$hasRemarkText
                            && Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'ESRL')
                        ) {
                           $isSupportForPrimarySchool = true;
                        // staatliche und pseudostaatliche Grundschulzeugnisse Förderbedarf-Satz in die Bemerkung vorsetzen
                        } elseif (!$hasRemarkText
                            && ($Certificate->getCertificateEntity()->getCertificate() == 'GsHjInformation'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'GsHjOneInfo'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'GsJa'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'GsJOne'

                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheHjInfoGs'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheHjInfoGsOne'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheJGs'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESZC\CheJGsOne'
                            )
                        ) {
                            $isSupportForPrimarySchool = true;
                        } elseif (!$hasRemarkText
                            && ($Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsHjInformation'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsHjOneInfo'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsJa'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'ESBD\EsbdGsJOne'
                            )
                        ) {
                            $isSupportForPrimarySchool = true;
                        }

                        if ($isSupportForPrimarySchool) {
                            $textSupport = '';
                            if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                                && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
                            ) {
                                if ($tblPrimaryFocus->getName() == 'Lernen') {
                                    $textSupport = $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
                                        . ' wurde inklusiv nach den Lehrplänen der Schule mit dem Förderschwerpunkt Lernen unterrichtet.';
                                }
                                if ($tblPrimaryFocus->getName() == 'Geistige Entwicklung') {
                                    $textSupport = $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
                                        . ' wurde inklusiv nach den Lehrplänen der Schule mit dem Förderschwerpunkt geistige Entwicklung unterrichtet.';
                                }
                            }

                            // Seelitz
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $textSupport;
                            // staatliche GS-Zeugnisse
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['Remark'] = $textSupport;
                        }

                        // Fachschule
                        if (!$hasRemarkText
                            && ($Certificate->getCertificateEntity()->getCertificate() == 'FsAbs'
                                || $Certificate->getCertificateEntity()->getCertificate() == 'FsAbsFhr')
                        ) {
                            $technicalCourseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = 'Der Abschluss '
                                . $technicalCourseName . ' ist im Deutschen und Europäischen Qualifikationsrahmen dem Niveau 6 zugeordnet.';
                        }
                        // Vorsetzen der Fachrichtung bei Fachschulen
                        if (!$isSubjectAreaSet
                            && $tblStudent
                            && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                            && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                        ) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['SubjectArea'] = $tblTechnicalSubjectArea->getName();
                        }

                        // Berufsfachschule
                        if (!$hasRemarkText
                            && $Certificate->getCertificateEntity()->getCertificate() == 'BfsAbs'
                        ) {
                            $technicalCourseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = 'Der Abschluss '
                                . $technicalCourseName . ' ist im Deutschen und Europäischen Qualifikationsrahmen dem Niveau 4 zugeordnet.';
                        }

                        // Berufsfachschule Pflegeberufe
                        if (!$hasRemarkText
                            && $Certificate->getCertificateEntity()->getCertificate() == 'BfsPflegeJ'
                            && $tblLevel->getName() == 3
                        ) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $tblPerson->getFullName()
                                . ' ' . ' hat regelmässig am theoretischen und praktischen Unterricht sowie der praktischen Ausbildung in den Klassenstufen 1 bis 3 teilgenommen.';
                        }
                        if($Page == 2
                        && $tblCertificate->getName() == 'Berufsfachschule Jahreszeugnis'
                        && $tblCertificate->getDescription() == 'Generalistik'
                        && ($tblYear = $tblDivision->getServiceTblYear())
                        && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier(TblTestType::TEST))
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        && ($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                        && ($tblTechnicalCourse = $tblTechnicalSchool->getServiceTblTechnicalCourse())
                        ){
                            if(($tblCertificateSubjectList = Setting::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse))){
                                $GradeSum = 0;
                                $GradeSumCount = 0;
                                $GradePracticalSum = 0;
                                $GradePracticalCount = 0;
                                foreach($tblCertificateSubjectList as $tblCertificateSubject){
                                    if(($tblSubject = $tblCertificateSubject->getServiceTblSubject())
                                    && $tblCertificateSubject->getRanking() != 15){
                                        if(($tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblTestType))){
                                            /** @var TblGrade $tblGrade */
                                            foreach($tblGradeList as $tblGrade){
                                                if($tblGrade->getGrade() !== null){
                                                    $GradeSum += (int)$tblGrade->getGrade();
                                                    $GradeSumCount++;
                                                }
                                            }
                                        }
                                    } elseif(($tblSubject = $tblCertificateSubject->getServiceTblSubject())
                                    && $tblCertificateSubject->getRanking() == 15){
                                        if(($tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblTestType))){
                                            /** @var TblGrade $tblGrade */
                                            foreach($tblGradeList as $tblGrade){
                                                if($tblGrade->getGrade() !== null) {
                                                    $GradePracticalSum += (int)$tblGrade->getGrade();
                                                    $GradePracticalCount++;
                                                }
                                            }
                                        }
                                    }
                                }

                                if($GradeSumCount
                                && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAverageLesson_Average'])){
                                    $Calc = round($GradeSum/$GradeSumCount, 2);
                                    $Calc = number_format($Calc, 2, ",", ".");
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAverageLesson_Average'] = $Calc;
                                }
                                if($GradePracticalCount
                                && !isset($Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAveragePractical_Average'])){
                                    $Calc = round($GradePracticalSum/$GradePracticalCount, 2);
                                    $Calc = number_format($Calc, 2, ",", ".");
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['YearGradeAveragePractical_Average'] = $Calc;
                                }
                            }
                        }

                        // Fachoberschule HOGA Jahreszeugnis für Klassenstufe 12
                        if (!$hasRemarkText
                            && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\FosJ'
                            && $tblLevel->getName() == 12
                        ) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['RemarkWithoutTeam'] = $tblPerson->getFullName()
                                . ' wurde zur Abschlussprüfung nicht zugelassen / hat die Abschlussprüfung nicht bestanden und kann erst nach erfolgreicher Wiederholung der Klassenstufe erneut an der Abschlussprüfung teilnehmen.';
                        }

                        // HOGA Beginn der Ausbildung bei Fachoberschule Abschlusszeugnissen
                        if (!$hasEducationDateFrom
                            && $Certificate->getCertificateEntity()->getCertificate() == 'HOGA\FosAbs'
                            && $tblStudent
                            && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                                $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE')
                            ))
                            && ($transferDate = $tblStudentTransfer->getTransferDate())
                        ) {
                            $Global->POST['Data'][$tblPrepareStudent->getId()]['EducationDateFrom'] = $transferDate;
                        }

                        $Global->savePost();
                    }

                    // bei der Aufteilung der sonstigen Informationen auf mehrere Seite müssen, diese auf der 1. Seite ignoriert werden
                    $ignoreInformationOnFirstPage = array();
                    if ($Page == null && isset($informationPageList[$tblCertificate->getId()])) {
                        foreach($informationPageList[$tblCertificate->getId()] as $pageList) {
                            foreach ($pageList as $pageItem) {
                                $ignoreInformationOnFirstPage[$pageItem] = $pageItem;
                            }
                        }
                    }

                    // Create Form, Additional Information from Template
                    $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                    // Arbeitsgemeinschaften stehen extra und nicht in den Bemerkungen
                    $hasTeamExtra = false;
                    if ($PlaceholderList) {
                        array_walk($PlaceholderList,
                            function ($Placeholder) use (
                                $Certificate,
                                $FormField,
                                $FormLabel,
                                &$columnTable,
                                &$studentTable,
                                $tblPerson,
                                $tblPrepareStudent,
                                $tblCertificate,
                                &$hasTeamExtra,
                                $Page,
                                $ignoreInformationOnFirstPage,
                                $informationPageList,
                                $tblPrepareCertificate,
                                $GroupId,
                                $showTeamsInCertificateRemark,
                                $showOrientationsInCertificateRemark
                            ) {

                                $PlaceholderList = explode('.', $Placeholder);
                                $Identifier = array_slice($PlaceholderList, 1);
                                if (isset($Identifier[0])) {
                                    unset($Identifier[0]);
                                }

                                $FieldName = $PlaceholderList[0] . '[' . implode('][', $Identifier) . ']';

                                $dataFieldName = str_replace('Content[Input]', 'Data[' . $tblPrepareStudent->getId() . ']',
                                    $FieldName);

                                $PlaceholderName = str_replace('.P' . $tblPerson->getId(), '', $Placeholder);

                                $Type = array_shift($Identifier);
                                $key = str_replace('Content.Input.', '', $PlaceholderName);

                                // Entscheidung ob das Field auf der aktuelle Seite der sonstige Informationen angezeigt wird
                                $addField = true;
                                if ($Page == null) {
                                    if (isset($ignoreInformationOnFirstPage[$key])) {
                                        $addField = false;
                                    }
                                } else {
                                    $addField = isset($informationPageList[$tblCertificate->getId()][$Page][$key]);
                                }

                                if ($addField && !method_exists($Certificate, 'get' . $Type)) {
                                    if (isset($FormField[$PlaceholderName])) {
                                        if (isset($FormLabel[$PlaceholderName])) {
                                            $Label = $FormLabel[$PlaceholderName];
                                        } else {
                                            $Label = $PlaceholderName;
                                        }

                                        $isApiField = false;
                                        $isAdded = isset($columnTable[$key]);
                                        // ApiButton
                                        if (method_exists($Certificate, 'getApiModalColumns')) {
                                            /** @var BeGs $Certificate */
                                            if (isset($Certificate->getApiModalColumns()[$key])) {
                                                $isApiField = true;
                                                if (!$isAdded) {
                                                    $columnTable[$key] = $Label
                                                        . new PullRight(
                                                            (new Standard('Alle bearbeiten', ApiPrepare::getEndpoint()))
                                                                ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenInformationModal(
                                                                    $tblPrepareCertificate->getId(),
                                                                    $GroupId,
                                                                    $key,
                                                                    $tblCertificate->getCertificate()
                                                                ))
                                                        );

                                                    $isAdded = true;
                                                }
                                            }
                                        }
                                        if (!$isAdded) {
                                            $columnTable[$key] = $Label;
                                        }

                                        if ($key == 'TeamExtra' /*|| isset($columnTable['TeamExtra'])*/) {
                                            $hasTeamExtra = true;
                                        }

                                        if (isset($FormField[$PlaceholderName])) {
                                            $Field = '\SPHERE\Common\Frontend\Form\Repository\Field\\' . $FormField[$PlaceholderName];
                                            if ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\SelectBox') {
                                                $selectBoxData = array();
                                                if ($PlaceholderName == 'Content.Input.SchoolType'
                                                    && method_exists($Certificate, 'selectValuesSchoolType')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesSchoolType();
                                                } elseif ($PlaceholderName == 'Content.Input.Type'
                                                    && method_exists($Certificate, 'selectValuesType')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesType();
                                                } elseif ($PlaceholderName == 'Content.Input.Success'
                                                    && method_exists($Certificate, 'selectValuesSuccess')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesSuccess();
                                                } elseif ($PlaceholderName == 'Content.Input.Transfer'
                                                    && method_exists($Certificate, 'selectValuesTransfer')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesTransfer();
                                                } elseif ($PlaceholderName == 'Content.Input.Job_Grade_Text'
                                                    && method_exists($Certificate, 'selectValuesJobGradeText')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesJobGradeText();
//                                                } elseif ($PlaceholderName == 'Content.Input.FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                                                    && method_exists($Certificate, 'selectValuesFoesAbsText')
//                                                ) {
//                                                    $selectBoxData = $Certificate->selectValuesFoesAbsText();
                                                } elseif (strpos($PlaceholderName, '_GradeText') !== false) {
                                                    if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
                                                        $selectBoxData = array(TblGradeText::ATTR_NAME => $tblGradeTextList);
                                                    }
                                                }
                                                $selectBox = new SelectBox($dataFieldName, '', $selectBoxData);
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key] = $selectBox->setDisabled();
                                                } else {
                                                    if ($isApiField) {
                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
                                                            $selectBox,
                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
                                                        );
                                                    } else {
                                                        $studentTable[$tblPerson->getId()][$key] = $selectBox;
                                                    }
                                                }
                                            } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter') {
                                                // Zensurenfeld
                                                $selectCompleterData[-1] = '';
                                                if (strpos($PlaceholderName, '_Average') !== false) {
                                                    // _Average
                                                    for ($i = 1; $i < 6; $i++) {
                                                        for ($j = 0; $j < 10; $j++) {
                                                            $value = $i . ',' . $j;
                                                            $selectCompleterData[$value] = $value;
                                                        }
                                                    }
                                                    $selectCompleterData['6,0'] = '6,0';
                                                } else {
                                                    // _Grade
                                                    for ($i = 1; $i <= 6; $i++) {
                                                        $selectCompleterData[$i] = (string)($i);
                                                    }
                                                }
                                                $selectCompleter = new SelectCompleter($dataFieldName, '', '', $selectCompleterData);
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key] = $selectCompleter->setDisabled();
                                                } else {
                                                    // noch kein Api verfügbar
//                                                    if ($isApiField) {
//                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
//                                                            $selectCompleter,
//                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
//                                                        );
//                                                    } else {
                                                        $studentTable[$tblPerson->getId()][$key] = $selectCompleter;
//                                                    }
                                                }
                                            } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\CheckBox') {
                                                // funktioniert aktuell nur für das Feld AdditionalRemarkFhr
                                                // auch noch kein Api verfügbar
                                                $checkBox = new CheckBox($dataFieldName, 'Erfolglose Teilnahme', 1);
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key] = $checkBox->setDisabled();
                                                } else {
//                                                    if ($isApiField) {
//                                                        $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
//                                                            $checkBox,
//                                                            'ChangeInformation_' . $key . '_' . $tblPerson->getId()
//                                                        );
//                                                    } else {
                                                        $studentTable[$tblPerson->getId()][$key] = $checkBox;
//                                                    }
                                                }
                                            } elseif ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\Editor') {
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()){
                                                    $Editor = (new Editor($dataFieldName))->setDisabled();
                                                } else {
                                                    $Editor = new Editor($dataFieldName);
                                                }
                                                $studentTable[$tblPerson->getId()][$key] = $Editor;
                                            } else {
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new $Field($dataFieldName, '', ''))->setDisabled();
                                                } else {
                                                    // Arbeitsgemeinschaften beim Bemerkungsfeld
                                                    if ($showTeamsInCertificateRemark
                                                        && !$hasTeamExtra
                                                        && $key == 'Remark'
                                                    ) {
                                                        if (!isset($columnTable['Team'])) {
                                                            $columnTable['Team'] = 'Arbeitsgemeinschaften';
                                                        }
                                                        $studentTable[$tblPerson->getId()]['Team']
                                                            = (new TextField('Data[' . $tblPrepareStudent->getId() . '][Team]',
                                                            '', ''));
                                                    }

                                                    // Wahlbereiche beim Bemerkungsfeld
                                                    if ($showOrientationsInCertificateRemark
                                                        && $key == 'Remark'
                                                    ) {
                                                        if (!isset($columnTable['Orientation'])) {
                                                            $columnTable['Orientation'] =
                                                                (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName();
                                                        }
                                                        $studentTable[$tblPerson->getId()]['Orientation']
                                                            = (new TextArea('Data[' . $tblPrepareStudent->getId() . '][Orientation]',
                                                            '', ''));
                                                    }

                                                    // TextArea Zeichen begrenzen
                                                    if ($FormField[$PlaceholderName] == 'TextArea'
                                                        && (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
                                                            $tblCertificate, $key, !$hasTeamExtra
                                                        )))
                                                    ) {
                                                        /** @var TextArea $Field */
                                                        $studentTable[$tblPerson->getId()][$key]
                                                            = (new TextArea($dataFieldName, '', ''))->setMaxLengthValue(
                                                            $CharCount, true
                                                        );
                                                    } else {
                                                        if ($isApiField) {
                                                            $studentTable[$tblPerson->getId()][$key] = ApiPrepare::receiverContent(
                                                                new $Field($dataFieldName, '', ''),
                                                                'ChangeInformation_' . $key . '_' . $tblPerson->getId()
                                                            );
                                                        } else  {
                                                            $studentTable[$tblPerson->getId()][$key] = (new $Field($dataFieldName, '', ''));
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key]
                                                    = (new TextField($FieldName, '', ''))->setDisabled();
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key]
                                                    = (new TextField($FieldName, '', ''));
                                            }
                                        }
                                    }
                                }
                            });
                    }

                    if ($Page == null) {
                        // für Förderzeugnisse Lernen extra Spalte Inklusive Unterrichtung
                        $isSupportLearningCertificate = false;
                        if (strpos($tblCertificate->getCertificate(), 'FsLernen') !== false) {
                            $isSupportLearningCertificate = true;
                        }

                        if ($isSupportLearningCertificate && $tblPrepareStudent) {
                            if (!isset($columnTable['Support'])) {
                                $columnTable['Support'] = 'Inklusive Unterrichtung';
                            }

                            $textArea = new TextArea('Data[' . $tblPrepareStudent->getId() . '][Support]', '', '');
                            if ($tblPrepareStudent->isApproved()) {
                                $textArea->setDisabled();
                            }

                            $studentTable[$tblPerson->getId()]['Support'] = $textArea;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     * @param bool|int $GroupId
     *
     * @return Stage|string
     */
    public function frontendPreparePreview(
        $PrepareId = null,
        $Route = 'Teacher',
        $GroupId = false
    ) {

        $Stage = new Stage('Zeugnisvorbereitung', 'Vorschau');

        $isDiploma = $Route == 'Diploma';

        $countBehavior = 0;
        if (($tblPrepareCertificate = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblBehaviorTask = false;
            if ($tblPrepareCertificate->getServiceTblBehaviorTask()) {
                $tblBehaviorTask = $tblPrepareCertificate->getServiceTblBehaviorTask();
            } else {
                $tblTestTypeBehaviorTask = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
                if (($tblSetting = ConsumerSetting::useService()->getSetting(
                        'Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks'))
                    && $tblSetting->getValue()
                    && $tblPrepareCertificate->getServiceTblDivision()
                    && $tblTestTypeBehaviorTask
                ) {
                    if (($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblPrepareCertificate->getServiceTblDivision(),
                        $tblTestTypeBehaviorTask))
                    ) {
                        $tblBehaviorTask = end($tblTaskList);
                    }
                }
            }

            $tblPersonSigner = $tblPrepareCertificate->getServiceTblPersonSigner();
            $tblGradeTypeList = array();
            if ($tblBehaviorTask) {
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblBehaviorTask,
                    $tblDivision = $tblPrepareCertificate->getServiceTblDivision() ? $tblPrepareCertificate->getServiceTblDivision() : null);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                            if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                            }
                        }
                    }
                }
            }
            $countBehavior = count($tblGradeTypeList);
        } else {
            $tblPersonSigner = false;
            $tblBehaviorTask = false;
        }

        $description = '';
        $tblPrepareList = false;
        $tblGroup = false;
        if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                    'GroupId' => $tblGroup->getId(),
                    'Route' => $Route
                )
            ));

            $description = 'Gruppe ' . $tblGroup->getName();
            if (($tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate())) {
                $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
            }
        } elseif ($tblPrepareCertificate) {
            if (($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $description = 'Klasse ' . $tblDivision->getDisplayName();
                $tblPrepareList = array(0 => $tblPrepareCertificate);
            }
        }

        if ($isDiploma) {
            if ($tblGroup) {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'Integration',
                    'Course' => 'Bildungs&shy;gang',
                    'SubjectGrades' => 'Fachnoten',
                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis'
                );
            } else {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Division' => 'Klasse',
                    'IntegrationButton' => 'Integration',
                    'Course' => 'Bildungs&shy;gang',
                    'SubjectGrades' => 'Fachnoten',
                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis'
                );
            }
        } else {
            if ($tblGroup) {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Division' => 'Klasse',
                    'IntegrationButton' => 'Integration',
                    'Course' => 'Bildungs&shy;gang',
                    'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                    'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                    'SubjectGrades' => 'Fachnoten',
                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis',
                    'BehaviorGrades' => 'Kopfnoten',
                );
            } else {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'Integration',
                    'Course' => 'Bildungs&shy;gang',
                    'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                    'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                    'SubjectGrades' => 'Fachnoten',
                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis',
                    'BehaviorGrades' => 'Kopfnoten',
                );
            }
        }

        if ($tblPrepareCertificate && ($tblDivision = $tblPrepareCertificate->getServiceTblDivision())
            && ($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() != 'OS')
        {
            unset($columnTable['Course']);
        }

        $hasSetIsPrepareButton = false;
        $hasResetIsPrepareButton = false;
        $personSignerList = array();
        if ($tblPrepareList) {
            $studentTable = array();
            foreach ($tblPrepareList as $tblPrepare) {
                $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepare);
                $checkSubjectList = Prepare::useService()->checkCertificateSubjectsForStudents($tblPrepare);
                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                ) {
                    $certificateList = [];
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            if (($tblType = $tblDivision->getType())) {
                                $isTechnicalSchool = $tblType->isTechnical();
                            } else {
                                $isTechnicalSchool = false;
                            }

                            $isMuted = $isCourseMainDiploma;
                            $course = '';
                            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                                if ($isTechnicalSchool) {
                                    $course = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                                } else {
                                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    if ($tblTransferType) {
                                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                            $tblTransferType);
                                        if ($tblStudentTransfer) {
                                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                            if ($tblCourse) {
                                                $course = $tblCourse->getName();
                                                if ($course == 'Hauptschule') {
                                                    $isMuted = false;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            // Fächer zählen
                            if ($tblDivision->getServiceTblYear()) {
                                $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                                    $tblPerson, $tblDivision->getServiceTblYear()
                                );
                            } else {
                                $tblDivisionSubjectList = false;
                            }
                            if ($tblDivisionSubjectList) {
                                $countSubjects = count($tblDivisionSubjectList);
                            } else {
                                $countSubjects = 0;
                            }

                            $countSubjectGrades = 0;
                            // Zensuren zählen
                            if ($isDiploma && ($tblType && ($tblType->getShortName() == 'OS' || $tblType->getShortName() == 'FOS' || $tblType->getShortName() == 'BFS'))) {
                                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                                    && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                                        $tblPrepare,
                                        $tblPerson,
                                        $tblPrepareAdditionalGradeType
                                    ))
                                ) {
                                    foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                        if ($tblPrepareAdditionalGrade->getGrade() !== null && $tblPrepareAdditionalGrade->getGrade() !== '') {
                                            $countSubjectGrades++;
                                        }
                                    }
                                }
                            } else {
                                if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                                    && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask,
                                        $tblDivision))
                                ) {
                                    foreach ($tblTestList as $tblTest) {
                                        if (($tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                                $tblPerson))
                                            && $tblTest->getServiceTblSubject()
                                            && (($tblGradeItem->getGrade() !== null && $tblGradeItem->getGrade() !== '')
                                                || $tblGradeItem->getTblGradeText() != null)
                                        ) {
                                            $countSubjectGrades++;
                                        }
                                    }
                                }
                            }

                            if ($tblBehaviorTask) {
                                $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                                    $tblPrepare, $tblPerson, $tblBehaviorTask->getTblTestType()
                                );
                            } else {
                                $tblPrepareGradeBehaviorList = false;
                            }
                            if ($tblPrepareGradeBehaviorList) {
                                $countBehaviorGrades = count($tblPrepareGradeBehaviorList);
                            } else {
                                $countBehaviorGrades = 0;
                            }

                            if ($tblPrepare->getServiceTblAppointedDateTask()) {
                                $subjectGradesText = $countSubjectGrades . ' von ' . $countSubjects; // . ' Zensuren&nbsp;';
                            } else {
                                $subjectGradesText = 'Kein Stichtagsnotenauftrag ausgewählt';
                            }

                            if ($tblBehaviorTask) {
                                $behaviorGradesText = $countBehaviorGrades . ' von ' . $countBehavior; // . ' Zensuren&nbsp;';
                            } else {
                                $behaviorGradesText = 'Kein Kopfnoten ausgewählt';
                            }

                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                            $signer = '';
                            if ($tblPrepareStudent) {
                                $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                                // überschreiben für Gruppen
                                if ($tblPrepareStudent->getServiceTblPersonSigner()) {
//                                    $tblPersonSigner = $tblPrepareStudent->getServiceTblPersonSigner();
                                    $personSignerList[$tblPrepareStudent->getServiceTblPersonSigner()->getId()]
                                        = $tblPrepareStudent->getServiceTblPersonSigner()->getFullName();
                                    $signer = $tblPrepareStudent->getServiceTblPersonSigner()->getFullName();
                                } elseif ($tblPersonSigner && $tblPrepareStudent->getServiceTblCertificate()) {
                                    $personSignerList[$tblPersonSigner->getId()]
                                        = $tblPersonSigner->getFullName();
                                    $signer = $tblPersonSigner->getFullName();
                                }
                            } else {
                                $tblCertificate = false;
                            }

                            if (($tblSettingAbsence = ConsumerSetting::useService()->getSetting(
                                'Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
                            ) {
                                $useClassRegisterForAbsence = $tblSettingAbsence->getValue();
                            } else {
                                $useClassRegisterForAbsence = false;
                            }

                            $excusedDays = null;
                            $unexcusedDays = null;
                            if ($tblPrepareStudent && $tblCertificate) {
                                if ($tblPrepareStudent->getIsPrepared()) {
                                    $hasResetIsPrepareButton = true;
                                    $prepareStatus = new Success(new Check());
                                } else {
                                    $hasSetIsPrepareButton = true;
                                    $prepareStatus = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Unchecked());
                                }

                                if (isset($certificateList[$tblCertificate->getId()])) {
                                    $hasCertificateAbsence = $certificateList[$tblCertificate->getId()];
                                } else {
                                    $hasCertificateAbsence = Prepare::useService()->hasCertificateAbsence($tblCertificate,
                                        $tblDivision, $tblPerson);
                                    $certificateList[$tblCertificate->getId()] = $hasCertificateAbsence;
                                }

                                if ($hasCertificateAbsence) {
                                    // Fehlzeiten nur Anzeigen, wenn Fehltage auf der Zeugnisvorlage sind
                                    $excusedDays = $tblPrepareStudent->getExcusedDays();
                                    $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
                                    if ($useClassRegisterForAbsence) {
                                        if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                                            && $tblGenerateCertificate->getAppointedDateForAbsence()
                                        ) {
                                            $date = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
                                        } else {
                                            $date = new DateTime($tblPrepare->getDate());
                                        }

                                        if ($excusedDays === null) {
                                            $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                                $tblDivision, $date);
                                        }
                                        if ($unexcusedDays === null) {
                                            $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                                $tblDivision, $date);
                                        }
                                        // Zusatz-Tage von fehlenden Unterrichtseinheiten
                                        $excusedDaysFromLessons = $tblPrepareStudent->getExcusedDaysFromLessons();
                                        $unexcusedDaysFromLessons = $tblPrepareStudent->getUnexcusedDaysFromLessons();
                                        if ($excusedDaysFromLessons) {
                                            $excusedDays = new ToolTip((string)($excusedDays + $excusedDaysFromLessons),
                                                'Fehltage: ' . $excusedDays . ' + Zusatz-Tage: ' . $excusedDaysFromLessons);
                                        }
                                        if ($unexcusedDaysFromLessons) {
                                            $unexcusedDays = new ToolTip((string)($unexcusedDays + $unexcusedDaysFromLessons),
                                                'Fehltage: ' . $unexcusedDays . ' + Zusatz-Tage: ' . $unexcusedDaysFromLessons);
                                        }
                                    } else {
                                        if ($excusedDays === null) {
                                            $excusedDays = new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()),
                                                'Keine entschuldigten Fehltage erfasst');
                                        }
                                        if ($unexcusedDays === null) {
                                            $unexcusedDays = new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()),
                                                'Keine unentschuldigten Fehltage erfasst');
                                        }
                                    }
                                } else {
                                    $excusedDays = '&nbsp;';
                                    $unexcusedDays = '&nbsp;';
                                }
                            } else {
                                $excusedDays = '&nbsp;';
                                $unexcusedDays = '&nbsp;';
                                $prepareStatus = '&nbsp;';
                            }

                            $number = count($studentTable) + 1;
                            $name = $tblPerson->getLastFirstName();
                            $subjectGradesDisplayText = ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate())
                                ? ($countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                                    ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $subjectGradesText)
                                    : new Success(new Enable() . ' ' . $subjectGradesText))
                                : '';
                            $behaviorGradesDisplayText = ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate())
                                ? ($countBehaviorGrades < $countBehavior || !$tblBehaviorTask
                                    ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $behaviorGradesText)
                                    : new Success(new Enable() . ' ' . $behaviorGradesText))
                                : '';

                            if (isset($checkSubjectList[$tblPerson->getId()])) {
                                $checkSubjectsString = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban() . ' '
                                    . implode(', ', $checkSubjectList[$tblPerson->getId()])
                                    . (count($checkSubjectList[$tblPerson->getId()]) > 1 ? ' fehlen' : ' fehlt') . ' auf Zeugnisvorlage');
                            } elseif ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
                                $checkSubjectsString = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                                    ' alles ok');
                            } else {
                                $checkSubjectsString = '';
                            }
                            // Integration ReadOnlyButton
                            if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                                $IntegrationButton = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                                    ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                            } else {
                                $IntegrationButton = '';
                            }

                            $studentTable[$tblPerson->getId()] = array(
                                'Number' => $isDiploma && $isMuted ? new Muted($number) : $number,
                                'Name' => ($isDiploma && $isMuted ? new Muted($name) : $name),
                                'Division' => $tblDivision->getDisplayName(),
                                'Course' => $isDiploma && $isMuted ? new Muted($course) : $course,
                                'ExcusedAbsence' => $excusedDays . ' ',
                                'UnexcusedAbsence' => $unexcusedDays . ' ',
                                'IntegrationButton' => $IntegrationButton,
                                'SubjectGrades' => $isDiploma && $isMuted ? '' : $subjectGradesDisplayText,
                                'BehaviorGrades' => $behaviorGradesDisplayText,
                                'CheckSubjects' => $checkSubjectsString,
                                'Signer' => $signer,
                                'PrepareStatus' => $prepareStatus,
                                'Option' =>
                                    $isDiploma && $isMuted ? '' : ($tblCertificate
                                        ? (new Standard(
                                            '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                            array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                                'PersonId' => $tblPerson->getId(),
                                                'Route' => $Route
                                            ),
                                            'Zeugnisvorschau anzeigen'))
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
                                        // Mittelschule Abschlusszeugnis Realschule
                                        . ((strpos($tblCertificate->getCertificate(), 'MsAbsRs') !== false
                                            && $tblPrepareStudent
                                            && !$tblPrepareStudent->isApproved())
                                            ? new Standard(
                                                '', '/Education/Certificate/Prepare/DroppedSubjects',
                                                new CommodityItem(),
                                                array(
                                                    'PrepareId' => $tblPrepare->getId(),
                                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                                    'PersonId' => $tblPerson->getId(),
                                                    'Route' => $Route
                                                ),
                                                'Abgewählte Fächer verwalten')
                                            : '')
                                        : '')
                            );

                            // Vorlagen informationen
                            if (!($isDiploma && $isMuted)) {
                                $this->getTemplateInformationForPreview($tblPrepare, $tblPerson, $studentTable,
                                    $columnTable);
                            }

                            // Noten vom Vorjahr ermitteln (abgeschlossene Fächer) und speichern
                            // Mittelschule Abschlusszeugnis Realschule
                            if (!($isDiploma && $isMuted)
                                && $tblCertificate && (strpos($tblCertificate->getCertificate(), 'MsAbsRs') !== false)
                                && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                            ) {
                                if (!isset($columnTable['DroppedSubjects'])) {
                                    $columnTable['DroppedSubjects'] = 'Abgewählte Fächer';
                                }

                                // automatisch vom letzten Schuljahr setzen
                                $gradeString = '';
                                if (!Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson,
                                    $tblPrepareAdditionalGradeType)
                                ) {
                                    $gradeString = Prepare::useService()->setAutoDroppedSubjects($tblPrepare,
                                        $tblPerson);
                                }

                                if ($gradeString) {
                                    $studentTable[$tblPerson->getId()]['DroppedSubjects'] = $gradeString;
                                } elseif (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                                    $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType))
                                ) {
                                    $gradeString = '';
                                    foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                        if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                            $gradeString .= $tblSubject->getAcronym() . ':' . $tblPrepareAdditionalGrade->getGrade() . ' ';
                                        }
                                    }
                                    $studentTable[$tblPerson->getId()]['DroppedSubjects'] = $gradeString;
                                } else {
                                    $studentTable[$tblPerson->getId()]['DroppedSubjects'] = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                        new Exclamation() . ' nicht erledigt'
                                    );
                                }
                            }

                            if (isset($columnTable['DroppedSubjects'])
                                && !isset($studentTable[$tblPerson->getId()]['DroppedSubjects'])
                            ) {
                                $studentTable[$tblPerson->getId()]['DroppedSubjects'] = '';
                            }
                        }
                    }
                }
            }

            $buttonSigner = new Standard(
                'Unterzeichner auswählen',
                '/Education/Certificate/Prepare/Signer',
                new Select(),
                array(
                    'PrepareId' => $tblPrepareCertificate ? $tblPrepareCertificate->getId() : null,
                    'GroupId'=> $tblGroup ? $tblGroup->getId() : null,
                    'Route' => $Route
                ),
                'Unterzeichner auswählen'
            );

            if (!empty($personSignerList)) {
                $hasPersonSigner = true;
                if ($tblPrepareCertificate->getServiceTblGenerateCertificate()
                    && $tblPrepareCertificate->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
                    && count($personSignerList) > 1
                ) {
                    $columnTable['Signer'] = 'Unterzeichner';
                }
                $personSignerDisplayData = $personSignerList;
            } else {
                $hasPersonSigner = false;
                $personSignerDisplayData[] = new Exclamation() . ' Kein Unterzeichner ausgewählt';
            }
            $personSignerDisplayData[] = $buttonSigner;

            $columnTable['PrepareStatus'] = 'Zeugnis&shy;vorbereitung';
            $columnTable['Option'] = '';

            $columnDef = array(
                array(
                    "width" => "7px",
                    "targets" => 0
                ),
                array(
                    "width" => "200px",
                    "targets" => 1
                ),
                array(
                    "width" => "80px",
                    "targets" => 2
                ),
                array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 1),
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepareCertificate
                                            ? $tblPrepareCertificate->getName() . ' '
                                            . new Small(new Muted($tblPrepareCertificate->getDate())) : '',
                                        $description
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            $tblPrepareCertificate->getServiceTblGenerateCertificate()
                            && $tblPrepareCertificate->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
                                ? new LayoutColumn(array(
                                new Panel(
                                    'Unterzeichner',
                                    $personSignerDisplayData,
                                    !empty($hasPersonSigner)
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6)
                                : null,
                            new LayoutColumn(array(
                                $tblPrepareCertificate->getServiceTblAppointedDateTask()
                                    ? new Standard(
                                    'Fachnoten ansehen',
                                    '/Education/Certificate/Prepare/Prepare/Preview/SubjectGrades',
                                    null,
                                    array(
                                        'PrepareId' => $PrepareId,
                                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                        'Route' => $Route
                                    )
                                ) : null,
                                new External(
                                    'Alle Zeugnisse als Muster herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepareCertificate->getId(),
                                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                        'Name' => 'Musterzeugnis'
                                    ),
                                    false
                                ),
                                $hasSetIsPrepareButton
                                    ? new Standard(
                                        'Zeugnisvorbereitung abgeschlossen',
                                        '/Education/Certificate/Prepare/SetIsPrepared',
                                        new Check(),
                                        array(
                                            'PrepareId' => $PrepareId,
                                            'GroupId' => $GroupId,
                                            'Route' => $Route,
                                            'IsPrepared' => 1
                                        )
                                    ) : null,
                                $hasResetIsPrepareButton
                                    ? new Standard(
                                    'Zeugnisvorbereitung abgeschlossen entfernen',
                                    '/Education/Certificate/Prepare/SetIsPrepared',
                                    new Remove(),
                                    array(
                                        'PrepareId' => $PrepareId,
                                        'GroupId' => $GroupId,
                                        'Route' => $Route,
                                        'IsPrepared' => 0
                                    )
                                ) : null,
                            ))
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                ApiSupportReadOnly::receiverOverViewModal(),
                                new TableData($studentTable, null, $columnTable, array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    'columnDefs' => $columnDef,
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    "responsive" => false
                                ))
                            ))
                        ))
                    ), new Title('Übersicht'))
                ))
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param $studentTable
     * @param $columnTable
     */
    private function getTemplateInformationForPreview(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable
    ) {

        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if ($tblCertificate && ($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
            $Certificate = null;
            if ($tblCertificate) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();

                if (class_exists($CertificateClass)) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblDivision);

                    // create Certificate with Placeholders
                    $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
                    $Certificate->createCertificate(array(), $pageList);

                    $CertificateList[$tblPerson->getId()] = $Certificate;

                    $FormField = Generator::useService()->getFormField();
                    $FormLabel = Generator::useService()->getFormLabel(($tblType = $tblDivision->getType()) ? $tblType : null);

                    $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();

                    if ($PlaceholderList) {
                        array_walk($PlaceholderList,
                            function ($Placeholder) use (
                                $Certificate,
                                $FormField,
                                $FormLabel,
                                &$columnTable,
                                &$studentTable,
                                $tblPerson,
                                $tblPrepareStudent
                            ) {

                                $PlaceholderList = explode('.', $Placeholder);
                                $Identifier = array_slice($PlaceholderList, 1);
                                if (isset($Identifier[0])) {
                                    unset($Identifier[0]);
                                }

                                $PlaceholderName = str_replace('.P' . $tblPerson->getId(), '', $Placeholder);

                                $Type = array_shift($Identifier);
                                if (!method_exists($Certificate, 'get' . $Type)) {
                                    if (isset($FormField[$PlaceholderName])) {
                                        if (isset($FormLabel[$PlaceholderName])) {
                                            $Label = $FormLabel[$PlaceholderName];
                                        } else {
                                            $Label = $PlaceholderName;
                                        }

                                        $key = str_replace('Content.Input.', '', $PlaceholderName);
                                        if (!isset($columnTable[$key])) {
                                            $columnTable[$key] = $Label;
                                        }

                                        if (isset($FormField[$PlaceholderName]) && $FormField[$PlaceholderName] == 'TextArea') {
                                            if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                    $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                                && trim($tblPrepareInformation->getValue())
                                            ) {
                                                $studentTable[$tblPerson->getId()][$key] =
                                                    new Success(new Enable() . ' ' . 'erledigt');
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key] =
                                                    new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                                        new Exclamation() . ' ' . 'nicht erledigt');
                                            }
                                        } else {
                                            if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                            ) {
                                                $studentTable[$tblPerson->getId()][$key] = $tblPrepareInformation->getValue();
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key] = '';
                                            }
                                        }
                                    }
                                }
                            });
                    }
                }
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendPrepareShowSubjectGrades($PrepareId = null, $GroupId = null, $Route = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');

        $description = '';
        $tblPrepareList = false;
        $tblGroup = false;
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivisionTemp = $tblPrepare->getServiceTblDivision())) {
                    $description = 'Klasse ' . $tblDivisionTemp->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                    new ChevronLeft(),
                    array(
                        'PrepareId' => $PrepareId,
                        'GroupId' => $GroupId,
                        'Route' => $Route
                    )
                )
            );

            $studentList = array();
            $tableHeaderList = array();
            $divisionList = array();
            $divisionPersonList = array();
            $averageGradeList = array();

            if ($tblPrepareList
                && $tblGenerateCertificate
                && ($tblTask = $tblGenerateCertificate->getServiceTblAppointedDateTask())
            ) {
                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision))
                    ) {
                        // Alle Klassen ermitteln in denen der Schüler im Schuljahr Unterricht hat
                        foreach ($tblDivisionStudentAll as $tblPerson) {
                            if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                                if (($tblYear = $tblDivision->getServiceTblYear())
                                    && ($tblPersonDivisionList = Student::useService()->getDivisionListByPersonAndYearAndIsNotInActive(
                                        $tblPerson,
                                        $tblYear
                                    ))
                                ) {
                                    foreach ($tblPersonDivisionList as $tblDivisionItem) {
                                        if (!isset($divisionList[$tblDivisionItem->getId()])) {
                                            $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                                        }
                                    }
                                }
                                $divisionPersonList[$tblPerson->getId()] = 1;
                            }
                        }

                        foreach ($divisionList as $tblDivisionItem) {
                            if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask,
                                $tblDivisionItem))
                            ) {
                                $tblType = $tblDivisionItem->getType();
                                $hasExams = ($Route == 'Diploma' && ($tblType && ($tblType->getShortName() == 'OS' || $tblType->getShortName() == 'FOS' || $tblType->getShortName() == 'BFS')));

                                foreach ($tblTestAllByTask as $tblTest) {
                                    $tblSubject = $tblTest->getServiceTblSubject();
                                    if ($tblSubject && $tblTest->getServiceTblDivision()) {
                                        $tableHeaderList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                                        $studentList[0][$tblSubject->getAcronym()] = '';

                                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                            $tblTest->getServiceTblDivision(),
                                            $tblSubject,
                                            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                                        );

                                        if ($tblDivisionSubject && $tblDivisionSubject->getTblSubjectGroup()) {
                                            $tblSubjectStudentAllByDivisionSubject =
                                                Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                            if ($tblSubjectStudentAllByDivisionSubject) {
                                                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        if ($tblPerson && isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                                        ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
                                                            }
                                                        }
                                                    }
                                                }

                                                // nicht vorhandene Schüler in der Gruppe auf leer setzten
                                                if ($tblDivisionStudentAll) {
                                                    foreach ($tblDivisionStudentAll as $tblPersonItem) {
                                                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPersonItem)) {
                                                            if (!isset($studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()])) {
                                                                $studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()] = '';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($tblDivisionStudentAll) {
                                                foreach ($tblDivisionStudentAll as $tblPerson) {
                                                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                                        // nur Schüler der ausgewählten Klasse
                                                        if (isset($divisionPersonList[$tblPerson->getId()])) {
                                                            if ($hasExams) {
                                                                $studentList = $this->setDiplomaGrade($tblPrepareItem,
                                                                    $tblPerson,
                                                                    $tblSubject, $studentList);
                                                            } else {
                                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                                    null,
                                                                    $tblPrepareItem,
                                                                    $averageGradeList
                                                                );
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
            }

            $count = 1;
            foreach ($studentList as $personId => $student){
                $studentList[$personId]['Number'] = $count++;
                foreach ($tableHeaderList as $column) {
                    if (!isset($studentList[$personId][$column])) {
                        $studentList[$personId][$column] = '';
                    }
                }
            }

            // Durchschnitte pro Fach-Klasse
            $studentList[0]['Number'] = '';
            $studentList[0]['Name'] = new Muted('&#216; Fach-Klasse');
            foreach ($averageGradeList as $subjectId => $grades) {
                $countGrades = count($grades);
                if (($item = Subject::useService()->getSubjectById($subjectId))) {
                    $studentList[0][$item->getAcronym()] = $countGrades > 0
                        ? round(array_sum($grades) / $countGrades, 2) : '';
                }
            }

            if (!empty($tableHeaderList)) {
                ksort($tableHeaderList);
                $prependTableHeaderList['Number'] = '#';
                $prependTableHeaderList['Name'] = 'Schüler';
                $tableHeaderList = $prependTableHeaderList + $tableHeaderList;
                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                            $description
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                )),
                                new LayoutColumn(array(
                                    new TableData(
                                        $studentList, null, $tableHeaderList, null
                                    )
                                ))
                            ))
                        ))
                    ))
                );
            }

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return  $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPrepareCertificate $tblPrepare = null,
        &$averageGradeList = array()
    ) {
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson, $tblDivision, $tblSubject, Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            ($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision)) ? $tblTaskPeriod : null, null,
            $tblTask->getDate() ? $tblTask->getDate() : false
        );
        if (is_array($average)) {
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            // Zeugnistext
            if (($tblGradeText = $tblGrade->getTblGradeText())) {
                $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblGradeText->getName();

                return $studentList;
            }

            $gradeValue = $tblGrade->getGrade();

            if ($gradeValue !== null && $gradeValue !== '') {
                $averageGradeList[$tblSubject->getId()][$tblPerson->getId()] = $gradeValue;
            }

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            $withTrend = true;
            if ($tblPrepare
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                    $tblGrade->getServiceTblPerson()))
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && !$tblCertificate->isInformation()
            ) {
                $withTrend = false;
            }
            $gradeValue = $tblGrade->getDisplayGrade($withTrend);

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = ($tblGrade->getGrade() !== null
                    ? $gradeValue : '') .
                (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $PersonId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendShowCertificate(
        $PrepareId = null,
        $GroupId = null,
        $PersonId = null,
        $Route = null
    ) {
        $Stage = new Stage('Zeugnisvorschau', 'Anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'GroupId' => $GroupId,
                'Route' => $Route
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $tblGroup = Group::useService()->getGroupById($GroupId);
            $ContentLayout = array();

            $tblCertificate = false;
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'
                        . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        $tblDivision = $tblPrepare->getServiceTblDivision();
                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Template */
                        $Template = new $CertificateClass($tblDivision ? $tblDivision : null, $tblPrepare);

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                        $personId = $tblPerson->getId();
                        if (isset($Content['P' . $personId]['Grade'])) {
                            $Template->setGrade($Content['P' . $personId]['Grade']);
                        }
                        if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                            $Template->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                        }

                        $pageList[$tblPerson->getId()] = $Template->buildPages($tblPerson);
                        $bridge = $Template->createCertificate($Content, $pageList);

                        $ContentLayout[] = $bridge->getContent();
                    }
                }
            }
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    $tblGroup ? 'Gruppe' : 'Klasse',
                                    $tblGroup ? $tblGroup->getName() : $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorlage',
                                    $tblCertificate
                                        ? ($tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : ''))
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine Zeugnisvorlage hinterlegt'),
                                    $tblCertificate
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(
                                $ContentLayout
                            ),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSigner($PrepareId = null, $GroupId = null, $Route = null, $Data = null)
    {

        $Stage = new Stage('Unterzeichner', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'GroupId' => $GroupId,
                'Route' => $Route
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : 0;
                $Global->savePost();
            }

            $personList[0] = '-[Nicht ausgewählt]-';
            if ($tblGroup) {
                // Tudors
                if (($tudors = $tblGroup->getTudors())) {
                    foreach ($tudors as $tblPerson) {
                        $personList[$tblPerson->getId()] = $tblPerson->getFullName();
                    }
                }
            } else {
                // DivisionTeacher
                if (($tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision))) {
                    foreach ($tblPersonList as $tblPerson) {
                        $personList[$tblPerson->getId()] = $tblPerson->getFullName();
                    }
                }
            }

            if (($tblPersonSigner = $tblPrepare->getServiceTblPersonSigner()) && !isset($personList[$tblPersonSigner->getId()])) {
                $personList[$tblPersonSigner->getId()] = $tblPersonSigner->getFullName();
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Unterzeichner (Klassenlehrer)',
                                $personList
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    $tblGroup ? 'Gruppe' : 'Klasse',
                                    $tblGroup ? $tblGroup->getName() : $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                !empty($personList)
                                    ? new Well(Prepare::useService()->updatePrepareSetSigner($form,
                                    $tblPrepare, $tblGroup ? $tblGroup : null, $Data, $Route))
                                    : new Warning('Für diese Klasse sind keine Klassenlehrer/Mentoren/Tutoren vorhanden.')
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $PersonId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDroppedSubjects($PrepareId = null, $GroupId = null, $PersonId = null, $Route = null, $Data = null)
    {

        if ($GroupId) {
            $tblGroup = Group::useService()->getGroupById($GroupId);
        } else {
            $tblGroup = false;
        }

        $Stage = new Stage('Abgewählte Fächer', 'Verwalten');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                'Route' => $Route,
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $contentList = array();
            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare,
                    $tblPerson, $tblPrepareAdditionalGradeType))
            ) {
                $count = 1;
                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                        $contentList[] = array(
                            'Ranking' => $count++,
                            'Acronym' => new PullClear(
                                new PullLeft(new ResizeVertical() . ' ' . $tblSubject->getAcronym())
                            ),
                            'Name' => $tblSubject->getName(),
                            'Grade' => $tblPrepareAdditionalGrade->getGrade(),
                            'Option' => (new Standard('', '/Education/Certificate/Prepare/DroppedSubjects/Destroy',
                                new Remove(),
                                array(
                                    'Id' => $tblPrepareAdditionalGrade->getId(),
                                    'Route' => $Route,
                                    'GroupId' => $tblGroup ? $tblGroup->getId() : null
                                ), 'Löschen'))
                        );
                    }
                }
            }

            $form = $this->formCreatePrepareAdditionalGrade($tblPrepare, $tblPerson);
            $form->appendFormButton(
                new Primary('Speichern', new Save()));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        $tblGroup
                                            ? 'Gruppe ' . $tblGroup->getName()
                                            : 'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
                                                ? $tblDivision->getDisplayName() : '')
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    array(
                                        $tblPerson->getLastFirstName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $contentList,
                                    null,
                                    array(
                                        'Ranking' => '#',
                                        'Acronym' => 'Kürzel',
                                        'Name' => 'Name',
                                        'Grade' => 'Zensur',
                                        'Option' => ''
                                    ),
                                    array(
                                        'rowReorderColumn' => 1,
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Education/Prepare/Reorder',
                                            'Data' => array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                                'PersonId' => $tblPerson->getId()
                                            )
                                        ),
                                        'paging' => false,
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Prepare::useService()->createPrepareAdditionalGradeForm(
                                    $form,
                                    $Data,
                                    $tblPrepare,
                                    $tblGroup ? $tblGroup : null,
                                    $tblPerson,
                                    $Route
                                ))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return Form
     */
    private function formCreatePrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ) {

        $availableSubjectList = array();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && $tblSubjectAll
            && ($tempList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepareCertificate,
                $tblPerson,
                $tblPrepareAdditionalGradeType
            ))
        ) {

            $usedSubjectList = array();
            foreach ($tempList as $item) {
                if ($item->getServiceTblSubject()) {
                    $usedSubjectList[$item->getServiceTblSubject()->getId()] = $item;
                }
            }

            foreach ($tblSubjectAll as $tblSubject) {
                if (!isset($usedSubjectList[$tblSubject->getId()])) {
                    $availableSubjectList[] = $tblSubject;
                }
            }
        } else {
            $availableSubjectList = $tblSubjectAll;
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[Subject]', 'Fach', array('DisplayName' => $availableSubjectList)), 6
                    ),
                    new FormColumn(
                        new TextField('Data[Grade]', '', 'Zensur'), 6
                    )
                ))
            ))
        ));
    }

    /**
     * @param null $Id
     * @param null $GroupId
     * @param bool|false $Confirm
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendDestroyDroppedSubjects(
        $Id = null,
        $GroupId = null,
        $Confirm = false,
        $Route = null
    ) {

        $Stage = new Stage('Abgewähltes Fach', 'Löschen');

        $tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeById($Id);
        $tblPrepare = $tblPrepareAdditionalGrade->getTblPrepareCertificate();
        $tblPerson = $tblPrepareAdditionalGrade->getServiceTblPerson();

        $parameters = array(
            'PrepareId' => $tblPrepare ? $tblPrepare->getId() : 0,
            'GroupId' => $GroupId,
            'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
            'Route' => $Route
        );

        if ($GroupId) {
            $tblGroup = Group::useService()->getGroupById($GroupId);
        } else {
            $tblGroup = false;
        }

        if ($tblPrepareAdditionalGrade) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Certificate/Prepare/DroppedSubjects', new ChevronLeft(),
                    $parameters)
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnisvorbereitung',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    $tblGroup
                                        ? 'Gruppe ' . $tblGroup->getName()
                                        : 'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
                                            ? $tblDivision->getDisplayName() : '')
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                array(
                                    $tblPerson->getLastFirstName()
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                                new Panel(
                                    'Abgewähltes Fach',
                                    ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                                        ? $tblSubject->getName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Dieses abgewählte Fach wirklich löschen?',
                                    array(
                                        $tblSubject ? 'Fach-Kürzel: ' . $tblSubject->getAcronym() : null,
                                        $tblSubject ? 'Fach-Name: ' . $tblSubject->getName() : null,
                                        'Zensur: ' . $tblPrepareAdditionalGrade->getGrade()
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Certificate/Prepare/DroppedSubjects/Destroy', new Ok(),
                                        array('Id' => $Id, 'GroupId'=> $GroupId, 'Confirm' => true, 'Route' => $Route)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Certificate/Prepare/DroppedSubjects', new Disable(),
                                        $parameters
                                    )
                                )
                            )
                        )
                    ))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Prepare::useService()->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Das abgewählte Fach wurde gelöscht')
                                : new Danger(new Ban() . ' Das abgewählte Fach konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS,
                                $parameters)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Abgewähltes Fach nicht gefunden.', new Ban())
                . new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_ERROR, $parameters);
        }

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param int $view
     */
    private function setHeaderButtonList(Stage $Stage, $view)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasDiplomaRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Diploma');
        $hasLeaveRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Leave');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }
        if ($hasDiplomaRight) {
            $countRights++;
        }
        if ($hasLeaveRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        '/Education/Certificate/Prepare/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        '/Education/Certificate/Prepare/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                        '/Education/Certificate/Prepare/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Leitung',
                        '/Education/Certificate/Prepare/Headmaster'));
                }
            }
            if ($hasDiplomaRight) {
                if ($view == View::DIPLOMA) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Abschlusszeugnisse')),
                        '/Education/Certificate/Prepare/Diploma', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Abschlusszeugnisse',
                        '/Education/Certificate/Prepare/Diploma'));
                }
            }
            if ($hasLeaveRight) {
                if ($view == View::LEAVE) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Abgangszeugnisse')),
                        '/Education/Certificate/Prepare/Leave', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Abgangszeugnisse',
                        '/Education/Certificate/Prepare/Leave'));
                }
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $SubjectId
     * @param null $Route
     * @param null $IsNotSubject
     * @param null $IsFinalGrade
     * @param null $Data
     * @param null $CertificateList
     *
     * Schulart Mittelschule / Oberschule, Fachoberschule
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaSetting(
        $PrepareId = null,
        $GroupId = null,
        $SubjectId = null,
        $Route = null,
        $IsNotSubject = null,
        $IsFinalGrade = null,
        $Data = null,
        $CertificateList = null
    ) {
        if ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId)) {
            $tblGroup = false;
            $tblPrepareList = false;
            $description = '';
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {

                    $description = 'Klasse ' . $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }

            // Fachnoten mit Prüfungsnoten festlegen
            if (!$IsNotSubject
                && $tblPrepare->getServiceTblAppointedDateTask()
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                    $tblDivision))
            ) {

                return $this->setExamsSetting($tblPrepare, $tblDivision, $tblGroup ? $tblGroup : null, $tblTestList, $SubjectId, $Route,
                    $IsFinalGrade, $Data, $IsNotSubject, $tblPrepareList, $description);

                // Sonstige Informationen
            } elseif (($tblDivision = $tblPrepare->getServiceTblDivision())
                && (($IsNotSubject
                        || (!$IsNotSubject && !$tblPrepare->getServiceTblBehaviorTask()))
                    || (!$IsNotSubject && $tblPrepare->getServiceTblBehaviorTask()
                        && !Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                            $tblDivision)))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');

                if ($tblGroup) {
                    $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                        array(
                            'GroupId' => $tblGroup->getId(),
                            'Route' => $Route
                        )
                    ));
                } else {
                    $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'Route' => $Route
                        )
                    ));
                }

                $tblCurrentSubject = false;
                $tblNextSubject = false;
                $tblSubjectList = array();

                if ($tblPrepare->getServiceTblAppointedDateTask()
                    && ($tblDivision = $tblPrepare->getServiceTblDivision())
                ) {
                    $tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                        $tblDivision);
                } else {
                    $tblTestList = array();
                }
                $buttonList = $this->createExamsButtonList(
                    $tblPrepare, $tblCurrentSubject, $tblNextSubject, $tblTestList, $SubjectId, $Route, $tblSubjectList,
                    $IsNotSubject, $tblGroup ? $tblGroup : null
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                );
                if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                    $columnTable['Course'] = 'Bildungsgang';
                }

                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                        && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                    ) {

                        $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepareItem);
                        foreach ($tblStudentList as $tblPerson) {
                            if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                $isMuted = $isCourseMainDiploma;
                                // Bildungsgang
                                $tblCourse = false;
                                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                                    && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                                ) {
                                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                        $tblTransferType);
                                    if ($tblStudentTransfer) {
                                        $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                        if ($tblCourse && $tblCourse->getName() == 'Hauptschule') {
                                            $isMuted = false;
                                        }
                                    }
                                }

                                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                                $studentTable[$tblPerson->getId()] = array(
                                    'Number' => $isMuted ? new Muted(count($studentTable) + 1) : (count($studentTable) + 1)
                                        . ' '
                                        . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                            ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                                'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                            : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                    'Name' => ($isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName())
                                        . ($tblGroup
                                            ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                                );
                                $courseName = $tblCourse ? $tblCourse->getName() : '';
                                $studentTable[$tblPerson->getId()]['Course'] = $isMuted ? new Muted($courseName) : $courseName;

                                /*
                                 * Sonstige Informationen der Zeugnisvorlage
                                 */
                                if (!$isMuted) {
                                    $this->getTemplateInformation($tblPrepareItem, $tblPerson, $studentTable,
                                        $columnTable,
                                        $Data,
                                        $CertificateList);
                                }

                                // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                                foreach ($columnTable as $columnKey => $columnName) {
                                    foreach ($studentTable as $personId => $value) {
                                        if (!isset($studentTable[$personId][$columnKey])) {
                                            $studentTable[$personId][$columnKey] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => array(
                            array(
                                "width" => "18px",
                                "targets" => 0
                            ),
                            array(
                                "width" => "200px",
                                "targets" => 1
                            ),
                            array(
                                "width" => "80px",
                                "targets" => 2
                            ),
                            array(
                                "width" => "50px",
                                "targets" => array(3, 4)
                            ),
                        ),
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktivieren Suchen
                        "info" => false,  // Deaktivieren Such-Info
                        "sort" => false,
                        "responsive" => false
                    ),
                    true
                );

                $form = new Form(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                $tableData
                            ),
                            new FormColumn(new HiddenField('Data[IsSubmit]'))
                        )),
                    ))
                    , new Primary('Speichern', new Save())
                );

                $Stage->setContent(
                    ApiPrepare::receiverModal()
                    .new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnis',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblGroup ? 'Gruppe' : 'Klasse',
                                        $description,
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn($buttonList),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    !$tblTestList
                                        ? new Warning('Die aktuelle Klasse ist nicht in dem ausgewählten Stichttagsnotenauftrag enthalten.'
                                        , new Exclamation())
                                        : null,
                                    Prepare::useService()->updatePrepareInformationList($form, $tblPrepare,
                                        $tblGroup ? $tblGroup : null, $Route, $Data, $CertificateList)
                                ))
                            ))
                        ))
                    ))
                );

                return $Stage;
            }
        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblDivision $tblDivision
     * @param TblGroup|null $tblGroup
     * @param $tblTestList
     * @param $SubjectId
     * @param $Route
     * @param $IsFinalGrade
     * @param $Data
     * @param $IsNotSubject
     * @param false|TblPrepareCertificate[] $tblPrepareList
     * @param $description
     *
     * @return Stage
     */
    private function setExamsSetting(
        TblPrepareCertificate $tblPrepare,
        TblDivision $tblDivision,
        TblGroup $tblGroup = null,
        $tblTestList,
        $SubjectId,
        $Route,
        $IsFinalGrade,
        $Data,
        $IsNotSubject,
        $tblPrepareList,
        $description
    ) {

        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten festlegen');

        if ($tblGroup) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                array(
                    'GroupId' => $tblGroup->getId(),
                    'Route' => $Route
                )
            ));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                array(
                    'DivisionId' => $tblDivision->getId(),
                    'Route' => $Route
                )
            ));
        }

        $tblCurrentSubject = false;
        $tblNextSubject = false;
        $tblSubjectList = array();

        $buttonList = $this->createExamsButtonList(
            $tblPrepare, $tblCurrentSubject, $tblNextSubject, $tblTestList, $SubjectId, $Route, $tblSubjectList,
            $IsNotSubject, $tblGroup ? $tblGroup : null
        );

        $studentTable = array();
        if (Prepare::useService()->isCourseMainDiploma($tblPrepare)) {
            // Klasse 9 Hauptschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
                'JN' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Jn',
                'LS' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ls',
                'LM' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Lm',
            );
            if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                $columnTable['Course'] = 'Bildungsgang';
            }
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'En (Endnote)';
                $columnTable['Text'] = 'oder Zeugnistext';
                $tableTitle = 'Endnote';
                if ($tblNextSubject) {
                    $textSaveButton = 'Speichern und weiter zum nächsten Fach';
                } else {
                    $textSaveButton = 'Speichern und weiter zu den sonstigen Informationen';
                }
            } else {
                $tableTitle = 'Leistungsnachweisnoten';
                $textSaveButton = 'Speichern und weiter zur Endnote';
            }
        } else {
            // Klasse 10 Realschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
            );
            if (($tblSchoolType = $tblDivision->getType()) && $tblSchoolType->getShortName() == 'OS') {
                $columnTable['Course'] = 'Bildungsgang';
            }
            $columnTable['JN'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Jn';
            $columnTable['PS'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ps';
            $columnTable['PM'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pm';
            $columnTable['PZ'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PZ'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pz';
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'En (Endnote)';
                $columnTable['Text'] = 'oder Zeugnistext';
                $tableTitle = 'Endnote';
                if ($tblNextSubject) {
                    $textSaveButton = 'Speichern und weiter zum nächsten Fach';
                } else {
                    $textSaveButton = 'Speichern und weiter zu den sonstigen Informationen';
                }
            } else {
                $tableTitle = 'Prüfungsnoten';
                $textSaveButton = 'Speichern und weiter zur Endnote';
            }
        }

        list($studentTable, $hasPreviewGrades, $missingTemplateList) = $this->createExamsContent($tblTestList,
            $IsFinalGrade, $studentTable, $tblCurrentSubject, $tblSubjectList, $tblPrepareList, $tblGroup);

        $columnDef = array(
            array(
                "width" => "18px",
                "targets" => 0
            ),
            array(
                "width" => "200px",
                "targets" => 1
            ),
            array(
                "width" => "80px",
                "targets" => 2
            ),
        );

        /** @var TblSubject $tblCurrentSubject */
        $tableTitle = $tblCurrentSubject ? $tblCurrentSubject->getAcronym() . ' - ' . $tableTitle : $tableTitle;

        $tableData = new TableData($studentTable, new \SPHERE\Common\Frontend\Table\Repository\Title($tableTitle), $columnTable,
            array(
                "columnDefs" => $columnDef,
                'order' => array(
                    array('0', 'asc'),
                ),
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            )
        );

        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                )),
            ))
            , new Primary($textSaveButton, new Save())
        );

        /** @var TblSubject $tblCurrentSubject */
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnis',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                $tblGroup ? 'Gruppe' : 'Klasse',
                                $description,
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn($buttonList),
                        $hasPreviewGrades
                            ? new LayoutColumn(new Warning(
                            'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                        ))
                            : null,
                        !empty($missingTemplateList)
                            ? new LayoutColumn(new Warning(
                            'Es wurde für die folgenden Hauptschüler keine Zeugnisvorlage ausgewählt: <br>'
                            . implode('<br>', $missingTemplateList)
                            . '<br>'
                            . 'Es können erst Zensuren eingetragen werden, wenn eine Zeugnisvorlage unter: "Zeugnisse generieren" ausgewählt wurde!'
                            , new Exclamation()
                        ))
                            : null,
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Prepare::useService()->updatePrepareExamGrades(
                                $form,
                                $tblPrepare,
                                $tblCurrentSubject,
                                $tblNextSubject ? $tblNextSubject : null,
                                $IsFinalGrade ? $IsFinalGrade : null,
                                $Route,
                                $Data,
                                $tblGroup ? $tblGroup : null
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $tblCurrentSubject
     * @param $tblNextSubject
     * @param $tblTestList
     * @param $SubjectId
     * @param $Route
     * @param $tblSubjectList
     * @param $IsNotSubject
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    private function createExamsButtonList(
        TblPrepareCertificate $tblPrepare,
        &$tblCurrentSubject,
        &$tblNextSubject,
        $tblTestList,
        $SubjectId,
        $Route,
        &$tblSubjectList,
        $IsNotSubject,
        TblGroup $tblGroup = null
    ) {

        if ($tblTestList) {
            // Sortierung der Fächer wie auf dem Zeugnis
            $tblTestList = $this->sortSubjects($tblPrepare, $tblTestList);

            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblSubjectItem = $tblTest->getServiceTblSubject())) {
                    if (!isset($tblSubjectList[$tblSubjectItem->getId()][$tblTest->getId()])) {
                        $tblSubjectList[$tblSubjectItem->getId()][$tblTest->getId()] = $tblSubjectItem;
                        if ($tblCurrentSubject && !$tblNextSubject && !$IsNotSubject) {
                            // Bei Gruppen
                            /** @var TblSubject $tblCurrentSubject */
                            if ($tblCurrentSubject->getId() != $tblSubjectItem->getId()) {
                                $tblNextSubject = $tblSubjectItem;
                            }
                        }
                        if ($SubjectId && $SubjectId == $tblSubjectItem->getId() && !$IsNotSubject) {
                            $tblCurrentSubject = $tblSubjectItem;
                        }
                    }
                }
            }
        }

        if (!$IsNotSubject && !$tblCurrentSubject && !empty($tblSubjectList)) {
            reset($tblSubjectList);
            $tblCurrentSubject = Subject::useService()->getSubjectById(key($tblSubjectList));
            if (count($tblSubjectList) > 1) {
                next($tblSubjectList);
                $tblNextSubject = Subject::useService()->getSubjectById(key($tblSubjectList));
            }
        }

        $buttonList = array();

        if (Prepare::useService()->isCourseMainDiploma($tblPrepare)) {
            $textLinkButton = ' - Leistungsnachweisnoten/Endnote';
        } else {
            $textLinkButton = ' - Prüfungsnoten/Endnote';
        }

        foreach ($tblSubjectList as $subjectId => $value) {
            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                if ($tblCurrentSubject && $tblCurrentSubject->getId() == $tblSubject->getId()) {
                    $name = new Info(new Bold($tblSubject->getAcronym()
                        . $textLinkButton
                    ));
                    $icon = new Edit();
                } else {
                    $name = $tblSubject->getAcronym();
                    $icon = null;
                }

                $buttonList[] = new Standard($name,
                    '/Education/Certificate/Prepare/Prepare/Diploma/Setting', $icon, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblSubject->getId()
                    )
                );
            }
        }

        if ($IsNotSubject) {
            $name = new Info(new Bold('Sonstige Informationen'));
            $icon = new Edit();
        } else {
            $name = 'Sonstige Informationen';
            $icon = null;
        }
        $buttonList[] = new Standard($name,
            '/Education/Certificate/Prepare/Prepare/Diploma/Setting', $icon, array(
                'PrepareId' => $tblPrepare->getId(),
                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                'Route' => $Route,
                'IsNotSubject' => true
            )
        );

        return $buttonList;
    }

    /**
     * @param $tblTestList
     * @param $IsFinalGrade
     * @param $studentTable
     * @param $tblCurrentSubject
     * @param $tblSubjectList
     * @param false|TblPrepareCertificate[] $tblPrepareList
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    private function createExamsContent(
        $tblTestList,
        $IsFinalGrade,
        $studentTable,
        $tblCurrentSubject,
        $tblSubjectList,
        $tblPrepareList,
        TblGroup $tblGroup = null
    ) {

        $hasPreviewGrades = false;
        $missingTemplateList = array();
        $tabIndex = 1;
        foreach ($tblPrepareList as $tblPrepareItem) {
            if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                && ($tblSchoolType = $tblDivisionItem->getType())
                && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
            ) {
                $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepareItem);
                foreach ($tblStudentList as $tblPerson) {
                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        $hasSubject = false;
                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                        // Bildungsgang
                        $tblCourse = false;
                        $isMuted = $isCourseMainDiploma;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                if ($tblCourse && $tblCourse->getName() == 'Hauptschule') {
                                    $isMuted = false;

                                    // SSW-640 Hinweistext keine Zeugnisvorlage ausgewählt
                                    if (!$tblPrepareStudent
                                        || ($tblPrepareStudent && !$tblPrepareStudent->getServiceTblCertificate())
                                    ) {
                                        $missingTemplateList[$tblPerson->getId()] = $tblPerson->getLastFirstName();
                                    }
                                }
                            }
                        }

                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => $isMuted ? new Muted(count($studentTable) + 1) : (count($studentTable) + 1)
                                . ' '
                                . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                    ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                        'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                    : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                            'Name' => ($isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName())
                                . ($tblGroup
                                    ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                        );
                        $courseName = $tblCourse ? $tblCourse->getName() : '';
                        $studentTable[$tblPerson->getId()]['Course'] = $isMuted ? new Muted($courseName) : $courseName;

                        if ($tblCurrentSubject) {
                            /** @var TblSubject $tblCurrentSubject */
                            $subjectGradeList = array();
                            /** @var TblTest $tblTest */
                            foreach ($tblTestList as $tblTest) {
                                if (($tblSubject = $tblTest->getServiceTblSubject())
                                    && $tblSubject->getId() == $tblCurrentSubject->getId()
                                ) {
                                    if (($tblSubject = $tblTest->getServiceTblSubject())
                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                            $tblPerson))
                                    ) {
                                        $subjectGradeList[$tblSubject->getAcronym()] = $tblGrade;
                                    }

                                    // besucht der Schüler das Fach
                                    if (($tblSubjectGroup = $tblTest->getServiceTblSubjectGroup())) {
                                        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                                $tblDivisionItem, $tblSubject, $tblSubjectGroup
                                            ))
                                            && (($tblSubjectStudent = Division::useService()->exitsSubjectStudent(
                                                $tblDivisionSubject, $tblPerson
                                            )))
                                        ) {
                                            $hasSubject = true;
                                        }
                                    } else {
                                        $hasSubject = true;
                                    }
                                }
                            }

                            // Post setzen
                            if (($tblTask = $tblPrepareItem->getServiceTblAppointedDateTask())
                                && ($tblTestType = $tblTask->getTblTestType())
                                && $tblCurrentSubject
                                && $tblPrepareStudent
                            ) {
                                if (isset($tblSubjectList[$tblCurrentSubject->getId()])) {
                                    $Global = $this->getGlobal();
                                    $gradeList = array();

                                    foreach ($tblSubjectList[$tblCurrentSubject->getId()] as $testId => $value) {
                                        if ($isCourseMainDiploma) {
                                            if (!$isMuted && (($tblTestTemp = Evaluation::useService()->getTestById($testId)))) {
                                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                                    $tblTestTemp, $tblPerson
                                                );
                                                if ($tblGrade) {
                                                    $gradeValue = $tblGrade->getDisplayGrade();
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['JN'] = $gradeValue;
                                                    if ($gradeValue && is_numeric($gradeValue)) {
                                                        $gradeList['JN'] = $gradeValue;
                                                    } else {
                                                        $gradeList['JN_TEXT'] = $gradeValue;
                                                    }
                                                }
                                            }
                                        } else {
                                            if (($tblTestTemp = Evaluation::useService()->getTestById($testId))) {
                                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                                    $tblTestTemp, $tblPerson
                                                );
                                                if ($tblGrade) {
                                                    $gradeValue = $tblGrade->getDisplayGrade();
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['JN'] = $gradeValue;
                                                    if ($gradeValue && is_numeric($gradeValue)) {
                                                        $gradeList['JN'] = $gradeValue;
                                                    } else {
                                                        $gradeList['JN_TEXT'] = $gradeValue;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                                        $tblPrepareItem,
                                        $tblPerson
                                    ))
                                    ) {
                                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                            if ($tblPrepareAdditionalGrade->getServiceTblSubject()
                                                && $tblCurrentSubject->getId() == $tblPrepareAdditionalGrade->getServiceTblSubject()->getId()
                                                && ($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                                                && $tblPrepareAdditionalGradeType->getIdentifier() != 'PRIOR_YEAR_GRADE'
                                            ) {
                                                // Zeugnistext
                                                if ($tblPrepareAdditionalGradeType->getIdentifier() == 'EN'
                                                    && ($tblGradeText = Gradebook::useService()->getGradeTextByName($tblPrepareAdditionalGrade->getGrade()))
                                                ) {
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Text']
                                                        = $tblGradeText->getId();
                                                } else {
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareAdditionalGradeType->getIdentifier()]
                                                        = $tblPrepareAdditionalGrade->getGrade();
                                                    if ($tblPrepareAdditionalGrade->getGrade()) {
                                                        $gradeList[$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // calc average --> finalGrade
                                    if ($IsFinalGrade && $tblPrepareStudent) {
                                        if ($isCourseMainDiploma) {
                                            if (!$isMuted) {
                                                $calcValue = '';
                                                if (isset($gradeList['JN'])) {
                                                    $calc = false;
                                                    if (isset($gradeList['LS']) && isset($gradeList['LM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['LS'] + $gradeList['LM']) / 3;
                                                    } elseif (isset($gradeList['LS'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['LS']) / 2;
                                                    } elseif (isset($gradeList['LM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['LM']) / 2;
                                                    }

                                                    if ($calc) {
                                                        $calcValue = round($calc, 2);
                                                    } else {
                                                        $calcValue = $gradeList['JN'];
                                                    }
                                                }

                                                $studentTable[$tblPerson->getId()]['Average'] = str_replace('.', ',',
                                                    $calcValue);

                                                if (!Prepare::useService()->getPrepareAdditionalGradeBy(
                                                        $tblPrepareItem,
                                                        $tblPerson,
                                                        $tblCurrentSubject,
                                                        Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                                    )
                                                ) {
                                                    if ($calcValue) {
                                                        if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                            $hasPreviewGrades = true;
                                                        }
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['EN'] = round($calcValue, 0);
                                                    } elseif (isset($gradeList['JN_TEXT']) && ($tblGradeTextTemp = Gradebook::useService()->getGradeTextByName($gradeList['JN_TEXT']))) {
                                                        if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                            $hasPreviewGrades = true;
                                                        }
                                                        $Global->POST['Data'][$tblPrepareStudent->getId()]['Text'] = $tblGradeTextTemp->getId();
                                                    }
                                                }
                                            }
                                        } else {
                                            $calcValue = '';
                                            if (isset($gradeList['JN'])) {
                                                $calc = false;
                                                if (isset($gradeList['PZ'])) {
                                                    if (isset($gradeList['PS'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PZ']) / 3;
                                                    } elseif (isset($gradeList['PM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PM'] + $gradeList['PZ']) / 3;
                                                    }
                                                } else {
                                                    if (isset($gradeList['PS'])) {
                                                        if (isset($gradeList['PM'])) {
                                                            // Sonderfall Englisch
                                                            $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PM']) / 3;
                                                        } else {
                                                            $calc = ($gradeList['JN'] + $gradeList['PS']) / 2;
                                                        }
                                                    } elseif (isset($gradeList['PM'])) {
                                                        $calc = ($gradeList['JN'] + $gradeList['PM']) / 2;
                                                    }
                                                }
                                                if ($calc) {
                                                    $calcValue = round($calc, 2);
                                                } else {
                                                    $calcValue = $gradeList['JN'];
                                                }
                                            }
                                            $studentTable[$tblPerson->getId()]['Average'] = str_replace('.', ',',
                                                $calcValue);

                                            if (!Prepare::useService()->getPrepareAdditionalGradeBy(
                                                    $tblPrepareItem,
                                                    $tblPerson,
                                                    $tblCurrentSubject,
                                                    Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                                )
                                            ) {
                                                if ($calcValue) {
                                                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                        $hasPreviewGrades = true;
                                                    }
                                                    // bei ,5 entscheidet die Prüfungsnote bei FOS und BFS
                                                    if (($tblSchoolType->getShortName() == 'FOS' || $tblSchoolType->getShortName() == 'BFS')
                                                        && strpos($calcValue, '.5') !== false
                                                        && $gradeList['JN'] > $calcValue
                                                    ) {
                                                        $round = PHP_ROUND_HALF_DOWN;
                                                    } else {
                                                        $round = PHP_ROUND_HALF_UP;
                                                    }
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['EN'] = round($calcValue, 0, $round);
                                                } elseif (isset($gradeList['JN_TEXT']) && ($tblGradeTextTemp = Gradebook::useService()->getGradeTextByName($gradeList['JN_TEXT']))) {
                                                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                                                        $hasPreviewGrades = true;
                                                    }
                                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Text'] = $tblGradeTextTemp->getId();
                                                }
                                            }
                                        }
                                    }

                                    $Global->savePost();
                                }
                            }

                            $tblGradeTextList = Gradebook::useService()->getGradeTextAll();

                            if ($isCourseMainDiploma && $tblPrepareStudent) {
                                // Klasse 9 Hauptschule
                                if (!$isMuted && $hasSubject) {
                                    $isApproved = $tblPrepareStudent && $tblPrepareStudent->isApproved();
                                    if ($IsFinalGrade
                                        || $isApproved
                                    ) {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LS]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LM]'))->setDisabled();
                                    } else {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setTabIndex($tabIndex++)->setDisabled();
                                        $studentTable[$tblPerson->getId()]['LS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LS]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['LM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][LM]'))->setTabIndex($tabIndex++);
                                    }

                                    if ($IsFinalGrade) {
                                        if ($isApproved) {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setDisabled();
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setDisabled();
                                            }
                                        } else {
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setTabIndex($tabIndex++);
                                            }
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                        }
                                    }
                                } else {
                                    $studentTable[$tblPerson->getId()]['JN']
                                        = $studentTable[$tblPerson->getId()]['LS']
                                        = $studentTable[$tblPerson->getId()]['LM']
                                        = $studentTable[$tblPerson->getId()]['EN'] = '';
                                }
                            } else {
                                // Klasse 10 Realschule
                                if ($hasSubject && $tblPrepareStudent) {
                                    $isApproved = $tblPrepareStudent->isApproved();
                                    if ($IsFinalGrade
                                        || $isApproved
                                    ) {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PS]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PM]'))->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PZ'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PZ]'))->setDisabled();
                                    } else {
                                        $studentTable[$tblPerson->getId()]['JN'] =
                                            (new TextField('Data[' . $tblPrepareStudent->getId() . '][JN]'))->setTabIndex($tabIndex++)->setDisabled();
                                        $studentTable[$tblPerson->getId()]['PS'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PS]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['PM'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PM]'))->setTabIndex($tabIndex++);
                                        $studentTable[$tblPerson->getId()]['PZ'] =
                                            (new NumberField('Data[' . $tblPrepareStudent->getId() . '][PZ]'))->setTabIndex($tabIndex++);
                                    }

                                    if ($IsFinalGrade) {
                                        if ($isApproved) {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setDisabled();
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setDisabled();
                                            }
                                        } else {
                                            $studentTable[$tblPerson->getId()]['EN'] =
                                                (new NumberField('Data[' . $tblPrepareStudent->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                            if ($tblGradeTextList) {
                                                $studentTable[$tblPerson->getId()]['Text'] = (new SelectBox('Data[' . $tblPrepareStudent->getId() . '][Text]',
                                                    '',
                                                    array(TblGradeText::ATTR_NAME => $tblGradeTextList)))->setTabIndex($tabIndex++);
                                            }
                                        }
                                    }
                                } else {
                                    $studentTable[$tblPerson->getId()]['JN']
                                        = $studentTable[$tblPerson->getId()]['PS']
                                        = $studentTable[$tblPerson->getId()]['PM']
                                        = $studentTable[$tblPerson->getId()]['PZ']
                                        = $studentTable[$tblPerson->getId()]['EN']
                                        = $studentTable[$tblPerson->getId()]['Text'] = '';
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($studentTable, $hasPreviewGrades, $missingTemplateList);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param $studentList
     *
     * @return array
     */
    private function setDiplomaGrade(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        $studentList
    ) {
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                $tblPrepare,
                $tblPerson,
                $tblSubject,
                $tblPrepareAdditionalGradeType
            ))
            && $tblPrepareAdditionalGrade->getGrade()
        ) {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblPrepareAdditionalGrade->getGrade();
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt');
        }

        return $studentList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $tblTestList
     * @return array
     */
    private function sortSubjects(TblPrepareCertificate $tblPrepare, $tblTestList)
    {
        $tblCertificate = false;
        // Ermittelung richtiges Zeugnis von Schülern
        if (($tblDivisionItem = $tblPrepare->getServiceTblDivision())
            && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
        ) {
            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificateStudent = $tblPrepareStudent->getServiceTblCertificate())
                ) {

                    if ($tblCertificateStudent->getTblCertificateType()->getIdentifier() == 'DIPLOMA') {
                        $tblCertificate = $tblCertificateStudent;
                        break;
                    }
                }
            }
        }

        if ($tblCertificate && $tblTestList) {
            $tblTestSortedList = array();
            $offset = 0;
            /** @var TblTest $tblTest */
            foreach ($tblTestList as $tblTest) {
                if (($tblSubjectItem = $tblTest->getServiceTblSubject())) {
                    if ($tblCertificate
                        && ($tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject($tblCertificate,
                            $tblSubjectItem))
                    ) {
                        if ($tblCertificateSubject->getLane() == 1) {
                            $index = 10 * (2 * $tblCertificateSubject->getRanking());
                        } else {
                            $index = 10 * (2 * $tblCertificateSubject->getRanking() + 1);
                        }
                    } else {
                        $offset++;
                        $index = 1000 + $offset;
                    }

                    // für Fachgruppen notwendig
                    while (isset($tblTestSortedList[$index])) {
                        $index++;
                    }
                    $tblTestSortedList[$index] = $tblTest;
                }
            }
            ksort($tblTestSortedList);
            $tblTestList = $tblTestSortedList;
        }

        return $tblTestList;
    }

    /**
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendLeaveSelectStudent($YearId = null)
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler auswählen');
        $this->setHeaderButtonList($Stage, View::LEAVE);

        $studentTable = array();
        $buttonList = array();

        $tblSelectYear = Term::useService()->getYearById($YearId);
        if (($tblYearAll = Term::useService()->getYearAll())) {
            if (!$tblSelectYear
                && ($tblYearByNowList = Term::useService()->getYearByNow())
            ) {
                $tblSelectYear = current($tblYearByNowList);
            }

            $tblYearAll = $this->getSorter($tblYearAll)->sortObjectBy('Name');
            /** @var TblYear $tblYear */
            foreach ($tblYearAll as $tblYear) {
                if ($tblSelectYear && $tblSelectYear->getId() == $tblYear->getId()) {
                    $icon = new Edit();
                    $text = new Info(new Bold($tblYear->getDisplayName()));
                } else {
                    $icon = null;
                    $text = $tblYear->getDisplayName();
                }

                $buttonList[] = new Standard($text, '/Education/Certificate/Prepare/Leave', $icon, array(
                   'YearId' => $tblYear->getId()
                ));
            }
        }

        if ($tblSelectYear) {
            if (($tblDivisionList = Division::useService()->getDivisionByYear($tblSelectYear))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblLevel = $tblDivision->getTblLevel())
                        && !$tblLevel->getIsChecked()
                        && ($tblType = $tblLevel->getServiceTblType())
                        && ($tblType->getName() == 'Mittelschule / Oberschule'
                            || $tblType->getName() == 'Gymnasium'
                            || $tblType->getName() == 'Berufsfachschule'
                            || $tblType->getName() == 'Fachschule'
                            || $tblType->getName() == 'Fachoberschule'
                            || $tblType->getName() == 'Förderschule'
                        )
                        && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
                    ) {
                        foreach ($tblPersonList as $tblPerson) {
                            $studentTable[] = array(
                                'Type' => $tblType->getName(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Name' => $tblPerson->getLastFirstName(),
                                'Option' => new Standard(
                                    '', '/Education/Certificate/Prepare/Leave/Student', new Select(),
                                    array(
                                        'PersonId' => $tblPerson->getId(),
                                        'DivisionId' => $tblDivision->getId()
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
                       new LayoutColumn($buttonList)
                   ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TableData(
                                $studentTable,
                                null,
                                array(
                                    'Type' => 'Schulart',
                                    'Division' => 'Klasse',
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                array(
                                    'order'      => array(
                                        array('0', 'asc'),
                                        array('1', 'asc'),
                                        array('2', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 1),
                                        array('width' => '1%', 'targets' => 3),
                                    ),
                                )
                            )
                        )
                    ))
                ), new Title(new Select() . ' Auswahl des Schülers'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $Data
     * @param null $ChangeCertificate
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentTemplate($PersonId = null, $DivisionId = null, $Data = null, $ChangeCertificate = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler');
            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave', new ChevronLeft()));

            $tblDivision = false;
            $tblType = false;
            $tblCourse = false;
            $tblCertificate = false;
            $subjectData = array();
            $tblLeaveStudent = false;

            $tblConsumer = Consumer::useService()->getConsumerBySession();

            if (($tblStudent = $tblPerson->getStudent())
                && ($tblDivision = Division::useService()->getDivisionById($DivisionId))
            ){
                $tblCourse = $tblStudent->getCourse();
                if (($tblLevel = $tblDivision->getTblLevel())) {
                    $tblType = $tblLevel->getServiceTblType();
                }

                // nachträgliche Änderung der Zeugnisvorlage
                if ($ChangeCertificate && $tblType) {
                    return $this->getSelectLeaveCertificateStage($tblPerson, $tblDivision, $tblType, $tblCourse ? $tblCourse : null, $Data);
                }

                if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblDivision))) {
                    $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
                } else {
                    if ($tblType) {
                        if ($tblType->getName() == 'Mittelschule / Oberschule') {
                            // Herrnhut hat ein individuelles Abgangszeugnis
                            if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('EZSH\EzshMsAbg');
                            } else {
                                // Auswahl der Zeugnisvorlage, da es mehrere gibt
                                return $this->getSelectLeaveCertificateStage($tblPerson, $tblDivision, $tblType, $tblCourse ? $tblCourse : null, $Data);
                            }
                        } elseif ($tblType->getName() == 'Gymnasium') {
                            if ($tblLevel) {
                                // Herrnhut hat ein individuelles Abgangszeugnis
                                if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')
                                    && intval($tblLevel->getName()) == 10
                                ) {
                                    $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('EZSH\EzshGymAbg');
                                } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')
                                    && intval($tblLevel->getName()) <= 10
                                ) {
                                    // HOGA hat ein individuelles Abgangszeugnis
                                    $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('HOGA\GymAbgSekI');
                                } elseif (intval($tblLevel->getName()) <= 10) {
                                    $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgSekI');
                                } else {
                                    $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgSekII');
                                    if ($tblCertificate) {
                                        $tblLeaveStudent = Prepare::useService()->createLeaveStudent(
                                            $tblPerson,
                                            $tblDivision,
                                            $tblCertificate
                                        );
                                    }
                                }
                            }
                        } elseif ($tblType->getName() == 'Berufsfachschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('BfsAbg');
                        } elseif ($tblType->getName() == 'Fachschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('FsAbg');
                        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA') && $tblType->getName() == 'Fachoberschule') {
                            // HOGA hat ein individuelles Abgangszeugnis
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('HOGA\FosAbg');
                        } elseif ($tblType->getName() == 'Förderschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('FoesAbgGeistigeEntwicklung');
                        }
                    }
                }
            }

            if ($tblCertificate && $tblCertificate->getCertificate() == 'GymAbgSekII') {
                $layoutGroups = $this->setLeaveContentForSekTwo(
                    $tblCertificate,
                    $tblLeaveStudent ? $tblLeaveStudent : null,
                    $tblDivision ? $tblDivision : null,
                    $tblPerson ? $tblPerson : null,
                    $stage,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null
                );

                $stage->setContent(
                    new Layout($layoutGroups)
                );
            } elseif ($tblCertificate
                && ($tblCertificate->getCertificate() == 'BfsAbg' || $tblCertificate->getCertificate() == 'FsAbg')
            ) {
                $layoutGroups = $this->setLeaveContentForTechnicalSchool(
                    $tblCertificate,
                    $tblLeaveStudent ? $tblLeaveStudent : null,
                    $tblDivision ? $tblDivision : null,
                    $tblPerson ? $tblPerson : null,
                    $Data,
                    $stage,
                    $subjectData,
                    $tblType ? $tblType : null,
                    $tblCertificate->getCertificate() == 'BfsAbg'
                );

                $stage->setContent(
                    new Layout($layoutGroups)
                );
            } else {
                $layoutGroups = $this->setLeaveContentForSekOne(
                    $tblCertificate ? $tblCertificate : null,
                    $tblLeaveStudent ? $tblLeaveStudent : null,
                    $tblDivision ? $tblDivision : null,
                    $tblPerson ? $tblPerson : null,
                    $Data,
                    $stage,
                    $subjectData,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null
                );

                $stage->setContent(
                    new Layout($layoutGroups)
                );
            }

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblType $tblType
     * @param TblCourse|null $tblCourse
     * @param null $Data
     *
     * @return Stage|string
     */
    private function getSelectLeaveCertificateStage(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblType $tblType,
        TblCourse $tblCourse = null,
        $Data = null
    ) {
        $stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Zeugnisvorlage auswählen');

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblDivision))
            && ($tblLeaveCertificate = $tblLeaveStudent->getServiceTblCertificate())
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['Certificate'] = $tblLeaveCertificate->getId();
            $global->savePost();

            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                'PersonId' => $tblPerson->getId(),
                'DivisionId' => $tblDivision->getId()
            )));
        } else {
            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave', new ChevronLeft()));
        }

        $list = array();
        if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
            && ($tblLeaveCertificateList = Generator::useService()->getCertificateAllByType($tblCertificateType))
        ) {
            foreach ($tblLeaveCertificateList as $tblCertificate) {
                if (($tblTypeFromCertificate = $tblCertificate->getServiceTblSchoolType())
                    && $tblTypeFromCertificate->getId() == $tblType->getId()
                ) {
                    $list[] = $tblCertificate;
                }
            }
        }

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if (!empty($list)) {
            $form = new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[Certificate]', 'Zeugnisvorlage auswählen', array('{{ Name }} - {{ Description }}' => $list))
                ),
                new FormColumn(
                    new Primary('Speichern', new Save())
                )
            ))));

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 4),
                            new LayoutColumn(
                                new Panel(
                                    'Klasse',
                                    $tblDivision
                                        ? $tblDivision->getDisplayName()
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                                    $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                                )
                                , 4),
                            new LayoutColumn(
                                new Panel(
                                    'Schulart',
                                    $tblType
                                        ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                                    $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                                )
                                , 4),
                        )),
                        ($support
                            ? new LayoutRow(new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO)))
                            : null
                        ),
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Well(Prepare::useService()->createLeaveStudentFromForm($form, $tblPerson, $tblDivision, $Data))
                    )))
                ))
            );

            return $stage;
        } else {
            return $stage . new Danger('Keine Abgangszeugnisvorlagen gefunden!', new Exclamation());
        }
    }

    /**
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblDivision|null $tblDivision
     * @param TblPerson|null $tblPerson
     * @param $Data
     * @param Stage $stage
     * @param $subjectData
     * @param TblType|null $tblType
     * @param TblCourse|null $tblCourse
     *
     * @return array
     */
    private function setLeaveContentForSekOne(
        TblCertificate $tblCertificate = null,
        TblLeaveStudent $tblLeaveStudent = null,
        TblDivision $tblDivision = null,
        TblPerson $tblPerson = null,
        $Data,
        Stage $stage,
        $subjectData,
        TblType $tblType = null,
        TblCourse $tblCourse = null
    ) {

        $hasPreviewGrades = false;
        $isApproved = false;
        $hasMissingSubjects = false;
        $hasCertificateGrades = false;
        $tblAppointedDateTask = false;

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if ($tblCertificate) {
            if ($tblLeaveStudent) {
                $isApproved = $tblLeaveStudent->isApproved();

                $stage->addButton(new External(
                    'Zeugnis als Muster herunterladen',
                    '/Api/Education/Certificate/Generator/PreviewLeave',
                    new Download(),
                    array(
                        'LeaveStudentId' => $tblLeaveStudent->getId(),
                        'Name' => 'Zeugnismuster'
                    ),
                    'Zeugnis als Muster herunterladen'));
            }

            $certificateDate = false;

            // Post setzen
            $isSetSubjectArea = false;
            if ($tblLeaveStudent) {
                $Global = $this->getGlobal();

                if (($tblLeaveGradeList = Prepare::useService()->getLeaveGradeAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveGradeList as $tblLeaveGrade) {
                        if (($tblSubject = $tblLeaveGrade->getServiceTblSubject())) {
                            if (($tblGradeText = Gradebook::useService()->getGradeTextByName($tblLeaveGrade->getGrade()))) {
                                $Global->POST['Data']['Grades'][$tblSubject->getId()]['GradeText'] = $tblGradeText->getId();
                            } else {
                                $Global->POST['Data']['Grades'][$tblSubject->getId()]['Grade'] = $tblLeaveGrade->getGrade();
                            }
                        }
                    }
                }
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        $value = $tblLeaveInformation->getValue();
                        // HOGA\FosAbg
                        if ($tblLeaveInformation->getField() == 'Job_Grade_Text') {
                            switch ($value) {
                                case 'bestanden': $value = 1; break;
                                case 'nicht bestanden': $value = 2; break;
                                default: $value = '';
                            }
                        }
                        if ($tblLeaveInformation->getField() == 'Exam_Text') {
                            switch ($value) {
                                case 'Die Abschlussprüfung wurde erstmalig nicht bestanden. Sie kann wiederholt werden.': $value = 1; break;
                                case 'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.': $value = 2; break;
                                default: $value = '';
                            }
                        }

                        $Global->POST['Data']['InformationList'][$tblLeaveInformation->getField()] = $value;

                        if ($tblLeaveInformation->getField() == 'CertificateDate' && $value != '') {
                            $certificateDate = new DateTime($value);
                        }

                        if ($tblLeaveInformation->getField() == 'SubjectArea') {
                            $isSetSubjectArea = true;
                        }
                    }
                }

                if (!$isSetSubjectArea
                    && $tblCertificate->getCertificate() == 'HOGA\FosAbg'
                    && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                    && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                ) {
                    $Global->POST['Data']['InformationList']['SubjectArea'] = $tblTechnicalSubjectArea->getName();
                }

                $Global->savePost();
            }

            if (!$certificateDate) {
                $certificateDate = new DateTime('now');
            }

            if ($tblCertificate->getCertificate() == 'MsAbgGeistigeEntwicklung'
             || $tblCertificate->getCertificate() == 'FoesAbgGeistigeEntwicklung') {
                $hasCertificateGrades = false;
            } else {
                $hasCertificateGrades = true;
            }

            if ($hasCertificateGrades) {
                // Grades
                $selectListGrades[-1] = '';
                for ($i = 1; $i < 6; $i++) {
                    $selectListGrades[$i] = (string)($i);
                }
                $selectListGrades[6] = 6;

                // Points
                $selectListPoints[-1] = '';
                for ($i = 0; $i < 16; $i++) {
                    $selectListPoints[$i] = (string)$i;
                }

                if ($certificateDate && $tblDivision) {
                    $tblAppointedDateTask = Evaluation::useService()->getTaskByDivisionAndDateAndInterval($tblDivision, $certificateDate);
                }

                if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
                    && $tblDivision
                    && ($tblYear = $tblDivision->getServiceTblYear())
                    && ($tblDivisionSubjectListByPerson = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                        $tblPerson, $tblYear))
                ) {
                    $tabIndex = 0;
                    foreach ($tblDivisionSubjectListByPerson as $tblDivisionSubject) {
                        if (($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                            && ($tblSubjectItem = $tblDivisionSubject->getServiceTblSubject())
                        ) {
                            // Fächer ohne Benotung überspringen
                            if (!$tblDivisionSubject->getHasGrading()) {
                                continue;
                            }

                            $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();
                            $gradeList = array();
                            $average = '';
                            $taskGrade = '';
                            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                $tblDivisionItem,
                                $tblSubjectItem,
                                $tblSubjectGroup ? $tblSubjectGroup : null
                            );

                            $tblScoreType = Gradebook::useService()->getScoreTypeByDivisionAndSubject(
                                $tblDivisionItem, $tblSubjectItem
                            );
                            if ($tblScoreType && $tblScoreType->getIdentifier() == 'POINTS') {
                                $selectList = $selectListPoints;
                            } else {
                                $selectList = $selectListGrades;
                            }

                            if (($tblGradeList = Gradebook::useService()->getGradesByStudent(
                                $tblPerson,
                                $tblDivisionItem,
                                $tblSubjectItem,
                                $tblTestType,
                                null,
                                $tblSubjectGroup ? $tblSubjectGroup : null)
                            )) {
                                $tblGradeList = Gradebook::useService()->sortGradeList($tblGradeList);
                                /** @var TblGrade $tblGrade */
                                foreach ($tblGradeList as $tblGrade) {
                                    $gradeValue = $tblGrade->getGrade();
                                    if (($tblGradeType = $tblGrade->getTblGradeType())
                                        && $gradeValue !== null
                                        && $gradeValue !== ''
                                    ) {
                                        $description = '';
                                        if (($tblTest = $tblGrade->getServiceTblTest())) {
                                            $description = $tblTest->getDescription();
                                        }

                                        $text = new ToolTip($tblGradeType->getCode() . ':' . $gradeValue,
                                            $tblGrade->getDateForSorter()->format('d.m.Y') . ' ' . $description);
                                        $gradeList[] = $tblGradeType->isHighlighted() ? new Bold($text) : $text;
                                    }
                                }

                                $hasNoLeaveGrade = !$tblLeaveStudent || !Prepare::useService()->getLeaveGradeBy($tblLeaveStudent, $tblSubjectItem);

                                $hasTaskGrade = false;
                                // Stichtagsnote ermitteln falls vorhanden
                                if ($tblAppointedDateTask
                                    && ($tblTestFromTask = Evaluation::useService()->getTestByTaskAndDivisionAndSubject(
                                        $tblAppointedDateTask,
                                        $tblDivisionItem,
                                        $tblSubjectItem,
                                        $tblSubjectGroup ? $tblSubjectGroup : null
                                    ))
                                    && ($tblGradeFromTask = Gradebook::useService()->getGradeByTestAndStudent(
                                        $tblTestFromTask,
                                        $tblPerson
                                    ))
                                ) {
                                    if ($hasNoLeaveGrade) {
                                        if (($value = $tblGradeFromTask->getGrade())) {
                                            $hasPreviewGrades = true;
                                            $hasTaskGrade = true;
                                            $Global = $this->getGlobal();
                                            $Global->POST['Data']['Grades'][$tblSubjectItem->getId()]['Grade'] = $value;
                                            $Global->savePost();
                                        } elseif (($gradeTextValue = $tblGradeFromTask->getTblGradeText())) {
                                            $hasPreviewGrades = true;
                                            $hasTaskGrade = true;
                                            $Global = $this->getGlobal();
                                            $Global->POST['Data']['Grades'][$tblSubjectItem->getId()]['GradeText'] = $gradeTextValue;
                                            $Global->savePost();
                                        }
                                    }

                                    $taskGrade = $tblGradeFromTask->getDisplayGrade();
                                }

                                /**
                                 * Average
                                 */
                                $average = Gradebook::useService()->calcStudentGrade(
                                    $tblPerson, $tblDivisionItem, $tblSubjectItem, $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null, null,
                                    $tblSubjectGroup ? $tblSubjectGroup : null
                                );
                                if (is_array($average)) {
                                    $average = 'Fehler';
                                } elseif (is_string($average) && strpos($average,
                                        '(')
                                ) {
                                    $average = substr($average, 0,
                                        strpos($average, '('));

                                    // Zensuren voreintragen, wenn noch keine vergeben ist
                                    if (($average || $average === (float)0) && $hasNoLeaveGrade && !$hasTaskGrade) {
                                        $hasPreviewGrades = true;
                                        $Global = $this->getGlobal();
                                        $Global->POST['Data']['Grades'][$tblSubjectItem->getId()]['Grade'] =
                                            str_replace('.', ',', round($average, 0));
                                        $Global->savePost();
                                    }
                                }
                            }

                            $selectComplete = (new SelectCompleter('Data[Grades][' . $tblSubjectItem->getId() . '][Grade]',
                                '', '', $selectList))
                                ->setTabIndex($tabIndex++);
                            if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                                $selectComplete->setDisabled();
                            }

                            // Zeugnistext
                            if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
                                $gradeText = new SelectBox('Data[Grades][' . $tblSubjectItem->getId() . '][GradeText]',
                                    '', array(TblGradeText::ATTR_NAME => $tblGradeTextList));

                                if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                                    $gradeText->setDisabled();
                                }
                            } else {
                                $gradeText = '';
                            }

                            if (!Generator::useService()->getCertificateSubjectBySubject($tblCertificate,
                                $tblSubjectItem)) {
                                $hasMissingSubjects = true;
                                $subjectName = new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSubjectItem->getDisplayName() . ' ' . new Ban());
                            } else {
                                $subjectName = $tblSubjectItem->getDisplayName();
                            }

                            $subjectData[$tblSubjectItem->getAcronym()] = array(
                                'SubjectName' => $subjectName,
                                'GradeList' => implode(' | ', $gradeList),
                                'Average' => $average,
                                'TaskGrade' => $taskGrade,
                                'Grade' => $selectComplete,
                                'GradeText' => $gradeText
                            );
                        }
                    }
                }
            }
        }

        if (!$isApproved && $tblType && $tblType->getName() == 'Mittelschule / Oberschule') {
            $canChangeCertificate = true;
        } else {
            $canChangeCertificate = false;
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Klasse',
                        $tblDivision
                            ? $tblDivision->getDisplayName()
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                        $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                        $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Zeugnisvorlage',
                        $tblCertificate
                            ? $tblCertificate->getName()
                            . ($tblCertificate->getDescription()
                                ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                            . ($canChangeCertificate
                                ? new Link('Bearbeiten', '/Education/Certificate/Prepare/Leave/Student', new Pencil(), array(
                                    'PersonId' => $tblPerson->getId(),
                                    'DivisionId' => $tblDivision->getId(),
                                    'ChangeCertificate' => true
                                ))
                                : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3)
            )),
            ($support
                ? new LayoutRow(new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO)))
                : null
            ),
            ($hasCertificateGrades && $hasMissingSubjects
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es sind nicht alle Fächer auf der Zeugnisvorlage eingestellt.', new Exclamation()
                )))
                : null
            ),
            ($hasCertificateGrades && $hasPreviewGrades
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                )))
                : null
            )
        ));

        if ($tblCertificate) {
            // DivisionTeacher
            $divisionTeacherList = array();
            if (($tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision))) {
                foreach ($tblPersonList as $tblPersonTeacher) {
                    $divisionTeacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName();
                }
            }

            if ($tblAppointedDateTask) {
                $date = $tblAppointedDateTask->getDate();
                $columns = array(
                    'SubjectName' => 'Fach',
                    'GradeList' => 'Noten',
                    'Average' => '&#216;',
                    'TaskGrade' => new ToolTip('SN', 'Stichtagsnote vom ' . ($date ? $date : '-')),
                    'Grade' => 'Zensur',
                    'GradeText' => 'oder Zeugnistext'
                );
            } else {
                $columns = array(
                    'SubjectName' => 'Fach',
                    'GradeList' => 'Noten',
                    'Average' => '&#216;',
                    'Grade' => 'Zensur',
                    'GradeText' => 'oder Zeugnistext'
                );
            }

            if (!empty($subjectData)) {
                ksort($subjectData);
                $subjectTable = new TableData(
                    $subjectData,
                    null,
                    $columns,
                    null
                );
            } else {
                $subjectTable = false;
            }

            $datePicker = (new DatePicker('Data[InformationList][CertificateDate]', '', 'Zeugnisdatum',
                new Calendar()))->setRequired();
            if ($tblCertificate->getCertificate() == 'EZSH\EzshGymAbg') {
                $arrangementTextArea = new TextArea('Data[InformationList][Arrangement]', '', 'Besonderes Engagement an den Zinzendorfschulen');
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $arrangementTextArea->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $arrangementTextArea,
                    $remarkTextArea
                );
            } elseif ($tblCertificate->getCertificate() == 'EZSH\EzshMsAbg') {
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            } elseif ($tblCertificate->getCertificate() == 'FoesAbgGeistigeEntwicklung') {
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            } else {
                if ($tblCertificate->getCertificate() == 'MsAbgGeistigeEntwicklung') {
                    $remarkTextArea = new TextArea('Data[InformationList][Support]', '', 'Inklusive Unterrichtung');
                } else {
                    $remarkTextArea = new TextArea('Data[InformationList][Remark]', '', 'Bemerkungen');
                }

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            }

            if ($tblCertificate->getCertificate() == 'GymAbgSekI'
                || $tblCertificate->getCertificate() == 'EZSH\EzshGymAbg'
                || $tblCertificate->getCertificate() == 'HOGA\GymAbgSekI'
            ) {
                $radio1 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 7 Absatz 7 Satz 2 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe 10 nach
                     Jahrgangsstufe 11 des Gymnasiums einen dem Realschulabschluss gleichgestellten mittleren Schulabschluss erworben.',
                    GymAbgSekI::COURSE_RS
                ));
                $radio2 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 7 Absatz 7 Satz 1 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe 9 nach
                     Klassenstufe 10 des Gymnasiums einen dem Hauptschulabschluss gleichgestellten Schulabschluss erworben.',
                    GymAbgSekI::COURSE_HS
                ));
                if ($isApproved) {
                    $radio1->setDisabled();
                    $radio2->setDisabled();
                }
                $otherInformationList[] = new Panel(
                    'Gleichgestellter Schulabschluss',
                    array($radio1, $radio2),
                    Panel::PANEL_TYPE_DEFAULT
                );
            } elseif ($tblCertificate->getCertificate() == 'MsAbg'
                || $tblCertificate->getCertificate() == 'EZSH\EzshMsAbg'
                || $tblCertificate->getCertificate() == 'HOGA\MsAbg'
            ) {
                $radio1 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 6 Absatz 1 Satz 7 des Sächsischen Schulgesetzes mit der Versetzung in die Klassenstufe 10
                     des Realschulbildungsganges einen dem Hauptschulabschluss gleichgestellten Abschluss erworben',
                    GymAbgSekI::COURSE_HS
                ));
                $radio2 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 27 Absatz 9 Satz 3 der Schulordnung Ober- und Abendoberschulen mit der Versetzung in die
                     Klassenstufe 10 des Realschulbildungsganges und der erfolgreichen Teilnahme an der Prüfung zum Erwerb des Hauptschulabschlusses
                     den qualifizierenden Hauptschulabschluss erworben.',
                    GymAbgSekI::COURSE_HSQ
                ));
                $radio3 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 63 Absatz 3 Nummer 3 der Schulordnung Ober- und Abendoberschulen einen dem Abschluss im Förderschwerpunkt Lernen gemäß 
                     § 34a Absatz 1 der Schulordnung Förderschulen gleichgestellten Abschluss erworben.',
                    GymAbgSekI::COURSE_LERNEN
                ));
                if ($isApproved) {
                    $radio1->setDisabled();
                    $radio2->setDisabled();
                    $radio3->setDisabled();
                }
                $otherInformationList[] = new Panel(
                    'Gleichgestellter Schulabschluss',
                    array($radio1, $radio2, $radio3),
                    Panel::PANEL_TYPE_DEFAULT
                );
            }

            $headmasterNameTextField = new TextField('Data[InformationList][HeadmasterName]', '',
                'Name des/der Schulleiters/in');
            $radioSex1 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Männlich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                    ? $tblCommonGender->getId() : 0));
            $radioSex2 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Weiblich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                    ? $tblCommonGender->getId() : 0));
            $teacherSelectBox = new SelectBox('Data[InformationList][DivisionTeacher]', 'Klassenlehrer(in):',
                $divisionTeacherList);
            if ($isApproved) {
                $headmasterNameTextField->setDisabled();
                $radioSex1->setDisabled();
                $radioSex2->setDisabled();
                $teacherSelectBox->setDisabled();
            }

            // Facharbeit
            if ($tblCertificate->getCertificate() == 'HOGA\FosAbg') {
                $frontend = (new \SPHERE\Application\Education\Certificate\Prepare\TechnicalSchool\Frontend());
//                $panelJob = $frontend->getPanelWithoutInput('Fachpraktischer Teil der Ausbildung', 'Job', $isApproved);
                $selectBoxJob = new SelectBox('Data[InformationList][Job_Grade_Text]', 'Fachpraktischer Teil der Ausbildung', array(
                        1 => "bestanden",
                        2 => "nicht bestanden"
                    ));
                if ($isApproved) {
                    $selectBoxJob->setDisabled();
                }
                $panelJob = new Panel(
                    'Fachpraktischer Teil der Ausbildung',
                    $selectBoxJob,
                    Panel::PANEL_TYPE_INFO
                    );
                $panelSkilledWork = $frontend->getPanel('Facharbeit', 'SkilledWork', 'Thema', $isApproved);

                $subjectAreaInput = (new TextField('Data[InformationList][SubjectArea]', '', 'Fachrichtung'));
                $educationDateFrom = new DatePicker('Data[InformationList][EducationDateFrom]', '', 'Ausbildung Datum vom');
                if ($isApproved) {
                    $subjectAreaInput->setDisabled();
                    $educationDateFrom->setDisabled();
                }
                $panelEducation = new Panel(
                    'Ausbildung',
                    array(
                        $educationDateFrom,
                        $subjectAreaInput
                    ),
                    Panel::PANEL_TYPE_INFO
                );

                $selectBoxExam = new SelectBox('Data[InformationList][Exam_Text]', 'Abschlussprüfung', array(
                    1 => 'Die Abschlussprüfung wurde erstmalig nicht bestanden. Sie kann wiederholt werden.',
                    2 => 'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.'
                ));
                if ($isApproved) {
                    $selectBoxExam->setDisabled();
                }
                $panelExam = new Panel(
                    'Abschlussprüfung',
                    $selectBoxExam,
                    Panel::PANEL_TYPE_INFO
                );
            } else {
                $panelJob = false;
                $panelSkilledWork = false;
                $panelEducation = false;
                $panelExam = false;
            }

            $form = new Form(new FormGroup(array(
                $subjectTable ? new FormRow(new FormColumn($subjectTable)) : null,
                $panelEducation ? new FormRow(new FormColumn($panelEducation)) : null,
                $panelJob ? new FormRow(new FormColumn($panelJob)) : null,
                $panelSkilledWork ? new FormRow(new FormColumn($panelSkilledWork)) : null,
                $panelExam ? new FormRow(new FormColumn($panelExam)) : null,
                new FormRow(new FormColumn(
                    new Panel(
                        'Sonstige Informationen',
                        $otherInformationList,
                        Panel::PANEL_TYPE_INFO
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Unterzeichner - Schulleiter',
                            array(
                                $headmasterNameTextField,
                                new Panel(
                                    new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                    array($radioSex1, $radioSex2),
                                    Panel::PANEL_TYPE_DEFAULT
                                )
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6),
                    $tblCertificate->getCertificate() != 'GymAbgSekII'
                        ? new FormColumn(
                        new Panel(
                            'Unterzeichner - Klassenlehrer',
                            $teacherSelectBox,
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                        : null
                )),
            )));
            if (!$isApproved) {
                $form->appendFormButton(new Primary('Speichern', new Save()));
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    Prepare::useService()->updateLeaveContent($form, $tblPerson, $tblDivision, $tblCertificate, $Data)
                )
            )));
        }

        return $layoutGroups;
    }

    /**
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblDivision|null $tblDivision
     * @param TblPerson|null $tblPerson
     * @param Stage $stage
     * @param TblType|null $tblType
     * @param TblCourse|null $tblCourse
     *
     * @return array
     */
    private function setLeaveContentForSekTwo(
        TblCertificate $tblCertificate = null,
        TblLeaveStudent $tblLeaveStudent = null,
        TblDivision $tblDivision = null,
        TblPerson $tblPerson = null,
        Stage $stage,
        TblType $tblType = null,
        TblCourse $tblCourse = null
    ) {

        $form = false;

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if ($tblCertificate) {
            if ($tblLeaveStudent) {
                $stage->addButton(new External(
                    'Zeugnis als Muster herunterladen',
                    '/Api/Education/Certificate/Generator/PreviewLeave',
                    new Download(),
                    array(
                        'LeaveStudentId' => $tblLeaveStudent->getId(),
                        'Name' => 'Zeugnismuster'
                    ),
                    'Zeugnis als Muster herunterladen'));

                $form = (new LeavePoints($tblLeaveStudent))->getForm();
            }
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Klasse',
                        $tblDivision
                            ? $tblDivision->getDisplayName()
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                        $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                        $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Zeugnisvorlage',
                        $tblCertificate
                            ? $tblCertificate->getName()
                            . ($tblCertificate->getDescription()
                                ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                ($support
                    ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                    : null
                ),
            )),
        ));

        if ($form && $tblLeaveStudent) {
            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Standard('Punkte bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Points',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                            )
                        ),
                        new Standard('Sonstige Informationen bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Information',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                            )
                        ),
                        '<br />',
                        '<br />'
                    )),
                )),
            ));
        }

        if ($tblCertificate) {
            /** @var Form $form */
            if ($form) {
                $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                    $form
                )));
            }

            $panelList[] = array();
            if (($leaveTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'LeaveTerm'))) {
                $panelList[] = new Panel(
                    'verlässt das Gymnasium',
                    $leaveTermInformation->getValue()
                );
            }
            if (($midTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'MidTerm'))) {
                $panelList[] = new Panel(
                    'Kurshalbjahr',
                    $midTermInformation->getValue()
                );
            }
            if (($dateInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $panelList[] = new Panel(
                    'Zeugnisdatum',
                    $dateInformation->getValue()
                );
            }
            if (($remarkInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'Remark'))) {
                $panelList[] = new Panel(
                    'Bemerkungen',
                    $remarkInformation->getValue()
                );
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Panel(
                    'Sonstige Informationen',
                    $panelList,
                    Panel::PANEL_TYPE_PRIMARY
                )
            )));
        }

        return $layoutGroups;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturPoints($Id = null, $Data = null)
    {

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Punkte'));

            $tblDivision = $tblLeaveStudent->getServiceTblDivision();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision ? $tblDivision->getId() : 0
                )
            ));

            if ($tblDivision
                && ($tblLevel = $tblDivision->getTblLevel())
            ) {
                $tblType = $tblLevel->getServiceTblType();
            } else {
                $tblType = false;
            }

            if (($tblStudent = $tblPerson->getStudent())){
                $tblCourse = $tblStudent->getCourse();
            } else {
                $tblCourse = false;
            }

            if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
            } else {
                $support = false;
            }

            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstName(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Klasse',
                            $tblDivision
                                ? $tblDivision->getDisplayName()
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                            $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schulart',
                            $tblType
                                ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                            $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Zeugnisvorlage',
                            $tblCertificate
                                ? $tblCertificate->getName()
                                . ($tblCertificate->getDescription()
                                    ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine Zeugnisvorlage verfügbar!'),
                            $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    ($support
                        ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                        : null
                    )
                )),
            ));

            $LeavePoints = new LeavePoints($tblLeaveStudent, BlockIView::EDIT_GRADES);
            $form = $LeavePoints->getForm();

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    Prepare::useService()->updateLeaveStudentAbiturPoints($form, $tblLeaveStudent, $Data)
                )
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturInformation($Id = null, $Data = null)
    {

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Sonstige Informationen'));

            $tblDivision = $tblLeaveStudent->getServiceTblDivision();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
            $isApproved = $tblLeaveStudent->isApproved();

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision ? $tblDivision->getId() : 0
                )
            ));

            if ($tblDivision
                && ($tblLevel = $tblDivision->getTblLevel())
            ) {
                $tblType = $tblLevel->getServiceTblType();
            } else {
                $tblType = false;
            }

            if (($tblStudent = $tblPerson->getStudent())){
                $tblCourse = $tblStudent->getCourse();
            } else {
                $tblCourse = false;
            }

            if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
            } else {
                $support = false;
            }

            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstName(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Klasse',
                            $tblDivision
                                ? $tblDivision->getDisplayName()
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                            $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schulart',
                            $tblType
                                ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                            $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Zeugnisvorlage',
                            $tblCertificate
                                ? $tblCertificate->getName()
                                . ($tblCertificate->getDescription()
                                    ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                . ' Keine Zeugnisvorlage verfügbar!'),
                            $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    ($support
                        ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                        : null
                    )
                )),
            ));

            if ($tblCertificate) {
                $leaveTerms = GymAbgSekII::getLeaveTerms();
                $midTerms = GymAbgSekII::getMidTerms();

                // Post
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    $global = $this->getGlobal();
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        if ($tblLeaveInformation->getField() == 'LeaveTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $leaveTerms);
                        } elseif ($tblLeaveInformation->getField() == 'MidTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $midTerms);
                        } else {
                            $value = $tblLeaveInformation->getValue();
                        }

                        $global->POST['Data'][$tblLeaveInformation->getField()] = $value;
                    }
                    $global->savePost();
                }

                $leaveTermSelectBox = (new SelectBox(
                    'Data[LeaveTerm]',
                    'verlässt das Gymnasium',
                    $leaveTerms
                ))->setRequired();
                $midTermSelectBox = (new SelectBox(
                    'Data[MidTerm]',
                    'Kurshalbjahr',
                    $midTerms
                ))->setRequired();
                $datePicker = (new DatePicker('Data[CertificateDate]', '', 'Zeugnisdatum',
                    new Calendar()))->setRequired();
                $remarkTextArea = new TextArea('Data[Remark]', '', 'Bemerkungen');
                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                    $leaveTermSelectBox->setDisabled();
                    $midTermSelectBox->setDisabled();
                }
                $otherInformationList = array(
                    $leaveTermSelectBox,
                    $midTermSelectBox,
                    $datePicker,
                    $remarkTextArea
                );

                $headmasterNameTextField = new TextField('Data[HeadmasterName]', '',
                    'Name des/der Schulleiters/in');
                $radioSex1 = (new RadioBox('Data[HeadmasterGender]', 'Männlich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                        ? $tblCommonGender->getId() : 0));
                $radioSex2 = (new RadioBox('Data[HeadmasterGender]', 'Weiblich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                        ? $tblCommonGender->getId() : 0));
                if ($isApproved) {
                    $headmasterNameTextField->setDisabled();
                    $radioSex1->setDisabled();
                    $radioSex2->setDisabled();
                }

                $form = new Form(new FormGroup(array(
                    new FormRow(new FormColumn(
                        new Panel(
                            'Sonstige Informationen',
                            $otherInformationList,
                            Panel::PANEL_TYPE_INFO
                        )
                    )),
                    new FormRow(array(
                        new FormColumn(
                            new Panel(
                                'Unterzeichner - Schulleiter',
                                array(
                                    $headmasterNameTextField,
                                    new Panel(
                                        new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                        array($radioSex1, $radioSex2),
                                        Panel::PANEL_TYPE_DEFAULT
                                    )
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    )),
                )));
            } else {
                $form = null;
            }

            if ($isApproved) {
                $content = $form;
            } else {
                $form->appendFormButton(new Primary('Speichern', new Save()));
                $content = new Well(
                    Prepare::useService()->updateAbiturLeaveInformation($form, $tblLeaveStudent, $Data)
                );
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                $content
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $Route
     * @param int $IsPrepared
     *
     * @return Stage|string
     */
    public function frontendSetIsPrepared($PrepareId = null, $GroupId = null, $Route = null, int $IsPrepared = 0)
    {
        $IsPrepared = (bool) $IsPrepared;
        $stage = new Stage('Zeugnisvorbeitung', $IsPrepared ? 'abgeschlossen' : 'abgeschlossen entfernen');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblPrepare) {
            Prepare::useService()->setIsPrepared($tblPrepare, $tblGroup ? $tblGroup : null, $IsPrepared);
            return $stage . new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . ' Zeugnisvorbereitung abgeschlossen wurde ' . ($IsPrepared ? ' gesetzt.' : ' entfernt.'))
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'Route' => $Route
                ));
        }

        return $stage;
    }
}
