<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
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
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends Extension implements IFrontendInterface
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
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDiplomaSelectDivision($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::DIPLOMA);

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Certificate/Prepare/Diploma',
            $IsAllYears, $YearId, $tblYear, true);

        $tblDivisionList = Division::useService()->getDivisionAll();

        // todo tudor
        $divisionTable = array();
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
                    && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                    && ($tblLevel->getName() == '09' || $tblLevel->getName() == '9' || $tblLevel->getName() == '10')
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
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
    public function frontendTeacherSelectDivision($IsAllYears = false, $YearId = null)
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

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Certificate/Prepare/Teacher',
            $IsAllYears, $YearId, $tblYear, false);

        $divisionTable = array();
        if ($tblPerson) {
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
                        'Division' => 'Klasse ' . $tblDivision->getDisplayName(),
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

            if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
            ) {
                if (($tblGroupAll = Group::useService()->getGroupAll())) {
                    foreach ($tblGroupAll as $tblGroup) {
                        if (!$tblGroup->isLocked() && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $divisionTable[] = array(
                                'Year' => '',
                                'Type' => '',
                                'Division' => 'Gruppe ' . $tblGroup->getName(),
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
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse/Gruppe',
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
    public function frontendHeadmasterSelectDivision($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::HEADMASTER);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Certificate/Prepare/Headmaster',
            $IsAllYears, $YearId, $tblYear);

        // todo tudor
        $divisionTable = array();
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
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
                            ))
                        ))
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
            'Zurück', '/Education/Certificate/Prepare/' . $Route, new ChevronLeft()
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
                                                        array('type' => 'de_date', 'targets' => 0)
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
                                                array('type' => 'de_date', 'targets' => 0)
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

        $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
        if ($tblPrepareAllByDivision) {
            foreach ($tblPrepareAllByDivision as $tblPrepareCertificate) {
                $tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate();
                $tblCertificateType = $tblGenerateCertificate ? $tblGenerateCertificate->getServiceTblCertificateType() : false;

                if ($tblCertificateType) {
                    if ($Route != 'Diploma') {
                        // Abschlusszeugnisse überspringen
                        if ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                            continue;
                        }
                    } else {
                        // alle außer Abschlusszeugnisse überspringen
                        if ($tblCertificateType->getIdentifier() != 'DIPLOMA') {
                            continue;
                        }
                    }
                }

                // Setzen der Zeugnisvorlagen
                Prepare::useService()->setTemplatesAllByPrepareCertificate($tblPrepareCertificate);

                $tableData[] = array(
                    'Date' => $tblPrepareCertificate->getDate(),
                    'Type' => $tblCertificateType ? $tblCertificateType->getName()
                        : '',
                    'Name' => $tblPrepareCertificate->getName(),
                    'Option' =>
                        (new Standard(
                            '', '/Education/Certificate/Prepare/Prepare'
                            . ($Route == 'Diploma' ? '/Diploma' : '')
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
                        ))
                );
            }
        }

        return $tableData;
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     * @param null $GradeTypeId
     * @param null $IsNotGradeType
     * @param null $Data
     * @param null $CertificateList
     *
     * @return Stage|string
     */
    public function frontendPrepareSetting(
        $PrepareId = null,
        $Route = 'Teacher',
        $GradeTypeId = null,
        $IsNotGradeType = null,
        $Data = null,
        $CertificateList = null
    ) {

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            // Kopfnoten festlegen
            if (!$IsNotGradeType
                && $tblPrepare->getServiceTblBehaviorTask()
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                    $tblDivision))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Kopfnoten festlegen');
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $hasPreviewGrades = false;
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
                if (!$tblCurrentGradeType && !empty($tblGradeTypeList)) {
                    $tblCurrentGradeType = current($tblGradeTypeList);
                    if (count($tblGradeTypeList) > 1) {
                        $tblNextGradeType = next($tblGradeTypeList);
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
                            'Route' => $Route,
                            'GradeTypeId' => $tblGradeType->getId()
                        )
                    );
                }

                $buttonList[] = new Standard('Sonstige Informationen',
                    '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true
                    )
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Course' => 'Bildungsgang',
                    'Grades' => 'Einzelnoten in ' . ($tblCurrentGradeType ? $tblCurrentGradeType->getName() : ''),
                    'Average' => '&#216;',
                    'Data' => 'Zensur'
                );

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
//                    $selectListWithTrend[-1] = '&nbsp;';
                    for ($i = 1; $i < 5; $i++) {
                        $selectListWithTrend[$i . '+'] = (string)($i . '+');
                        $selectListWithTrend[$i] = (string)$i;
                        $selectListWithTrend[$i . '-'] = (string)($i . '-');
                    }
                    $selectListWithTrend[5] = "5";

//                    $selectListWithOutTrend[-1] = '&nbsp;';
                    for ($i = 1; $i < 5; $i++) {
                        $selectListWithOutTrend[$i] = (string)$i;
                    }
                    $selectListWithOutTrend[5] = "5";

                    $tabIndex = 1;
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName()
                        );

                        // Bildungsgang
                        $tblCourse = false;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            }
                        }
                        $studentTable[$tblPerson->getId()]['Course'] = $tblCourse ? $tblCourse->getName() : '';

                        if ($tblCurrentGradeType) {
                            $subjectGradeList = array();
                            $gradeList = array();
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

                            $gradeListString = '';
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
                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;

                            // calc average
                            $average = '';
                            if (!empty($gradeList)) {
                                $count = count($gradeList);
                                $average = $count > 0 ? round(array_sum($gradeList) / $count, 2) : '';
                                $studentTable[$tblPerson->getId()]['Average'] = $average;
                            } else {
                                $studentTable[$tblPerson->getId()]['Average'] = '';
                            }

                            // Post setzen
                            if ($Data === null
                                && ($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                                && ($tblTestType = $tblTask->getTblTestType())
                                && $tblCurrentGradeType
                            ) {
                                $Global = $this->getGlobal();
                                $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                    $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblCurrentGradeType
                                );
                                if ($tblPrepareGrade) {
                                    $gradeValue = $tblPrepareGrade->getGrade();
                                    $Global->POST['Data'][$tblPerson->getId()] = $gradeValue;
                                } elseif ($average) {
                                    // Noten aus dem Notendurchschnitt als Vorschlag eintragen
                                    $hasPreviewGrades = true;
                                    $Global->POST['Data'][$tblPerson->getId()] =
                                        str_replace('.', ',', round($average, 0));
                                }

                                $Global->savePost();
                            }

                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $tblPerson))
                                && $tblPrepareStudent->getServiceTblCertificate()
                            ) {
                                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                    && $tblCertificate->isInformation()
                                ) {
                                    $selectList = $selectListWithTrend;
                                } else {
                                    $selectList = $selectListWithOutTrend;
                                }

//                                $selectBox = (new SelectBox('Data[' . $tblPerson->getId() . ']', '', $selectList));
//                                $selectBox->setTabIndex($tabIndex++);
//                                $selectBox->configureLibrary( SelectBox::LIBRARY_SELECT2 );
                                $selectComplete = (new SelectCompleter('Data[' . $tblPerson->getId() . ']', '', '', $selectList))
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
                        new FormRow(
                            new FormColumn(
                                $tableData
                            )
                        ),
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
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
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
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

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
                                'Route' => $Route,
                                'GradeTypeId' => $tblGradeType->getId()
                            )
                        );
                    }
                }

                $buttonList[] = new Standard(new Info(new Bold('Sonstige Informationen')),
                    '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true
                    )
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Course' => 'Bildungsgang',
                    'ExcusedDays' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                    'UnexcusedDays' => 'U-FZ' // 'unent&shy;schuld&shy;igte FZ'
                );

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName()
                        );

                        // Bildungsgang
                        $tblCourse = false;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            }
                        }
                        $studentTable[$tblPerson->getId()]['Course'] = $tblCourse ? $tblCourse->getName() : '';

                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);

                        /*
                         * Fehlzeiten
                         */
                        // Post setzen von Fehlzeiten und Fehlzeiten aus dem Klassenbuch voreintragen
                        if ($Data === null) {
                            $Global = $this->getGlobal();
                            if ($Global) {
                                $Global->POST['Data'][$tblPerson->getId()]['ExcusedDays'] =
                                    $tblPrepareStudent && $tblPrepareStudent->getExcusedDays() !== null
                                        ? $tblPrepareStudent->getExcusedDays()
                                        : Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                        $tblDivision, new \DateTime($tblPrepare->getDate()));
                                $Global->POST['Data'][$tblPerson->getId()]['UnexcusedDays'] =
                                    $tblPrepareStudent && $tblPrepareStudent->getUnexcusedDays() !== null
                                        ? $tblPrepareStudent->getUnexcusedDays()
                                        : Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                        $tblDivision, new \DateTime($tblPrepare->getDate()));
                            }
                            $Global->savePost();
                        }

                        if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                            $studentTable[$tblPerson->getId()]['ExcusedDays'] =
                                (new NumberField('Data[' . $tblPerson->getId() . '][ExcusedDays]', '',
                                    ''))->setDisabled();
                            $studentTable[$tblPerson->getId()]['UnexcusedDays'] =
                                (new NumberField('Data[' . $tblPerson->getId() . '][UnexcusedDays]', '',
                                    ''))->setDisabled();
                        } elseif ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
                            $studentTable[$tblPerson->getId()]['ExcusedDays'] =
                                new NumberField('Data[' . $tblPerson->getId() . '][ExcusedDays]', '', '');
                            $studentTable[$tblPerson->getId()]['UnexcusedDays'] =
                                new NumberField('Data[' . $tblPerson->getId() . '][UnexcusedDays]', '', '');
                        } else {
                            // keine Zeugnisvorlage ausgewählt
                            $studentTable[$tblPerson->getId()]['ExcusedDays'] = '';
                            $studentTable[$tblPerson->getId()]['UnexcusedDays'] = '';
                        }
                        /*
                         * Sonstige Informationen der Zeugnisvorlage
                         */
                        $this->getTemplateInformation($tblPrepare, $tblPerson, $studentTable, $columnTable, $Data,
                            $CertificateList);

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

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => array(
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

//                $form = new Form(
//                    new FormGroup(
//                        new FormRow(array(
//                            new FormColumn(
//                                new NumberField('Data[ExcusedDays]', '', 'Entschuldigte Fehltage'), 6
//                            ),
//                            new FormColumn(
//                                new NumberField('Data[UnexcusedDays]', '', 'Unentschuldigte Fehltage'), 6
//                            )
//                        ))
//                    )
//                );

                $form = new Form(
                    new FormGroup(array(
                        new FormRow(
                            new FormColumn(
                                $tableData
                            )
                        ),
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
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn($buttonList),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Prepare::useService()->updatePrepareInformationList($form, $tblPrepare, $Route,
                                        $Data, $CertificateList)
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
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param array $studentTable
     * @param array $columnTable
     * @param array|null $Data
     * @param array|null $CertificateList
     */
    private function getTemplateInformation(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable,
        &$Data,
        &$CertificateList
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

                    $tblDivision = $tblPrepareCertificate->getServiceTblDivision();
                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblDivision ? $tblDivision : null);

                    // create Certificate with Placeholders
                    $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
                    $Certificate->createCertificate($Data, $pageList);

                    $CertificateList[$tblPerson->getId()] = $Certificate;

                    $FormField = Generator::useService()->getFormField();
                    $FormLabel = Generator::useService()->getFormLabel();

                    if ($Data === null) {
                        $Global = $this->getGlobal();
                        $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepareCertificate,
                            $tblPerson);
                        $hasTransfer = false;
                        $isTeamSet = false;
                        $hasRemarkText = false;
                        if ($tblPrepareInformationAll) {
                            foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                if ($tblPrepareInformation->getField() == 'Team' || $tblPrepareInformation->getField() == 'TeamExtra') {
                                    $isTeamSet = true;
                                }

                                if ($tblPrepareInformation->getField() == 'Remark') {
                                    $hasRemarkText = true;
                                }

                                if ($tblPrepareInformation->getField() == 'SchoolType'
                                    && method_exists($Certificate, 'selectValuesSchoolType')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesSchoolType());
                                } elseif ($tblPrepareInformation->getField() == 'Type'
                                    && method_exists($Certificate, 'selectValuesType')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesType());
                                } elseif ($tblPrepareInformation->getField() == 'Transfer'
                                    && method_exists($Certificate, 'selectValuesTransfer')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesTransfer());
                                    $hasTransfer = true;
                                } else {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()]
                                        = $tblPrepareInformation->getValue();
                                }
                            }
                        }

                        // Coswig Versetzungsvermerk in die Bemerkung vorsetzten
                        if (!$hasRemarkText
                            && ($tblConsumer = Consumer::useService()->getConsumerBySession())
                            && $tblConsumer->getAcronym() == 'EVSC'
                            && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                            && $tblCertificateType->getIdentifier() == 'YEAR'
                        ) {
                            $nextLevel = 'x';
                            if (($tblLevel = $tblDivision->getTblLevel())
                                && is_numeric($tblLevel->getName())
                            ) {
                                $nextLevel = floatval($tblLevel->getName()) + 1;
                            }
                            $Global->POST['Data'][$tblPerson->getId()]['Remark'] =
                                $tblPerson->getFirstSecondName() . ' wird versetzt in Klasse ' . $nextLevel . '.';
                        }

                        // Arbeitsgemeinschaften aus der Schülerakte laden
                        if (!$isTeamSet) {
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
                                if (!empty($tempList)) {
                                    $Global->POST['Data'][$tblPerson->getId()]['Team'] = implode(', ', $tempList);
                                    $Global->POST['Data'][$tblPerson->getId()]['TeamExtra'] = implode(', ', $tempList);
                                }
                            }
                        }

                        // Vorsetzen auf Versetzungsvermerk: wird versetzt
                        if (!$hasTransfer) {
                            $Global->POST['Data'][$tblPerson->getId()]['Transfer'] = 1;
                        }

                        $Global->savePost();
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
                                $hasTeamExtra
                            ) {

                                $PlaceholderList = explode('.', $Placeholder);
                                $Identifier = array_slice($PlaceholderList, 1);
                                if (isset($Identifier[0])) {
                                    unset($Identifier[0]);
                                }


                                $FieldName = $PlaceholderList[0] . '[' . implode('][', $Identifier) . ']';

                                $dataFieldName = str_replace('Content[Input]', 'Data[' . $tblPerson->getId() . ']',
                                    $FieldName);

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
                                                } elseif ($PlaceholderName == 'Content.Input.Transfer'
                                                    && method_exists($Certificate, 'selectValuesTransfer')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesTransfer();
                                                }
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new SelectBox($dataFieldName, '',
                                                        $selectBoxData))->setDisabled();
                                                } else {
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new SelectBox($dataFieldName, '', $selectBoxData));
                                                }
                                            } else {
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    /** @var TextArea $Field */
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new $Field($dataFieldName, '', ''))->setDisabled();
                                                } else {
                                                    // Arbeitsgemeinschaften beim Bemerkungsfeld
                                                    if (!$hasTeamExtra && $key == 'Remark') {
                                                        if (!isset($columnTable['Team'])) {
                                                            $columnTable['Team'] = 'Arbeitsgemeinschaften';
                                                        }
                                                        $studentTable[$tblPerson->getId()]['Team']
                                                            = (new TextField('Data[' . $tblPerson->getId() . '][Team]',
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
                                                        $studentTable[$tblPerson->getId()][$key]
                                                            = (new $Field($dataFieldName, '', ''));
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

                                        if (!isset($columnTable[$key])) {
                                            $columnTable[$key] = $Label;
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

        if ($isDiploma) {
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungs&shy;gang',
                'SubjectGrades' => 'Fachnoten',
                'CheckSubjects' => 'Prüfung Fächer/Zeugnis'
            );
        } else {
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungs&shy;gang',
                'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                'SubjectGrades' => 'Fachnoten',
                'CheckSubjects' => 'Prüfung Fächer/Zeugnis',
                'BehaviorGrades' => 'Kopfnoten',
            );
        }

        $countBehavior = 0;
        if (($tblPrepareCertificate = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblGradeTypeList = array();
            if ($tblPrepareCertificate->getServiceTblBehaviorTask()) {
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepareCertificate->getServiceTblBehaviorTask(),
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

        if ($tblPrepareList) {
            $studentTable = array();
            foreach ($tblPrepareList as $tblPrepare) {
                $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepare);
                $checkSubjectList = Prepare::useService()->checkCertificateSubjectsForStudents($tblPrepare);
                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $isMuted = $isCourseMainDiploma;
                            $course = '';
                            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
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
                            if ($isDiploma) {
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
                                            && $tblGradeItem->getGrade() !== null && $tblGradeItem->getGrade() !== ''
                                        ) {
                                            $countSubjectGrades++;
                                        }
                                    }
                                }
                            }

                            if ($tblPrepare->getServiceTblBehaviorTask()) {
                                $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                                    $tblPrepare, $tblPerson, $tblPrepare->getServiceTblBehaviorTask()->getTblTestType()
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

                            if ($tblPrepare->getServiceTblBehaviorTask()) {
                                $behaviorGradesText = $countBehaviorGrades . ' von ' . $countBehavior; // . ' Zensuren&nbsp;';
                            } else {
                                $behaviorGradesText = 'Kein Kopfnoten ausgewählt';
                            }

                            $excusedDays = null;
                            $unexcusedDays = null;
                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                            if ($tblPrepareStudent) {
                                $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                                $excusedDays = $tblPrepareStudent->getExcusedDays();
                                $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
                            } else {
                                $tblCertificate = false;
                            }

                            if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
                                if ($excusedDays === null) {
                                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                        $tblDivision,
                                        new \DateTime($tblPrepare->getDate()));
                                }
                                if ($unexcusedDays === null) {
                                    $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                        $tblDivision,
                                        new \DateTime($tblPrepare->getDate()));
                                }
                            } else {
                                $excusedDays = '';
                                $unexcusedDays = '';
                            }

                            $number = count($studentTable) + 1;
                            $name = $tblPerson->getLastFirstName();
                            $subjectGradesDisplayText = ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate())
                                ? ($countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                                    ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $subjectGradesText)
                                    : new Success(new Enable() . ' ' . $subjectGradesText))
                                : '';
                            $behaviorGradesDisplayText = ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate())
                                ? ($countBehaviorGrades < $countBehavior || !$tblPrepare->getServiceTblBehaviorTask()
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

                            $studentTable[$tblPerson->getId()] = array(
                                'Number' => $isDiploma && $isMuted ? new Muted($number) : $number,
                                'Name' => $isDiploma && $isMuted ? new Muted($name) : $name,
                                'Course' => $isDiploma && $isMuted ? new Muted($course) : $course,
                                'ExcusedAbsence' => $excusedDays . ' ',
                                'UnexcusedAbsence' => $unexcusedDays . ' ',
                                'SubjectGrades' => $isDiploma && $isMuted ? '' : $subjectGradesDisplayText,
                                'BehaviorGrades' => $behaviorGradesDisplayText,
                                'CheckSubjects' => $checkSubjectsString,
                                'Option' =>
                                    $isDiploma && $isMuted ? '' : ($tblCertificate
                                        ? (new Standard(
                                            '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                            array(
                                                'PrepareId' => $tblPrepare->getId(),
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
                                        . (($tblCertificate->getCertificate() == 'MsAbsRs'
                                            && $tblPrepareStudent
                                            && !$tblPrepareStudent->isApproved())
                                            ? new Standard(
                                                '', '/Education/Certificate/Prepare/DroppedSubjects',
                                                new CommodityItem(),
                                                array(
                                                    'PrepareId' => $tblPrepare->getId(),
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
                                && $tblCertificate && $tblCertificate->getCertificate() == 'MsAbsRs'
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

            $columnTable['Option'] = '';

            // Todo Group
            $buttonSigner = new Standard(
                'Unterzeichner auswählen',
                '/Education/Certificate/Prepare/Signer',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route
                ),
                'Unterzeichner auswählen'
            );

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
            );

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
                            ), 6),
                            $tblPrepare->getServiceTblGenerateCertificate()
                            && $tblPrepare->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
                                ? new LayoutColumn(array(
                                new Panel(
                                    'Unterzeichner',
                                    array(
                                        $tblPrepare->getServiceTblPersonSigner()
                                            ? $tblPrepare->getServiceTblPersonSigner()->getFullName()
                                            : new Exclamation() . ' Kein Unterzeichner ausgewählt',
                                        $buttonSigner
                                    ),
                                    $tblPrepare->getServiceTblPersonSigner()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6)
                                : null,
                            // todo Group
                            new LayoutColumn(array(
                                $tblPrepare->getServiceTblAppointedDateTask()
                                    ? new Standard(
                                    'Fachnoten ansehen',
                                    '/Education/Certificate/Prepare/Prepare/Preview/SubjectGrades',
                                    null,
                                    array(
                                        'PrepareId' => $PrepareId,
                                        'Route' => $Route
                                    )
                                ) : null,
                                // todo Group
                                new External(
                                    'Alle Zeugnisse als Muster herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Musterzeugnis'
                                    ),
                                    false
                                )
                            ))
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
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
                    $FormLabel = Generator::useService()->getFormLabel();

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
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendPrepareShowSubjectGrades($PrepareId = null, $Route = null)
    {

        // Todo Group
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
        ) {

            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                    new ChevronLeft(),
                    array('PrepareId' => $PrepareId, 'Route' => $Route))
            );

            $studentList = array();
            $tableHeaderList = array();
            // Alle Klassen ermitteln in denen der Schüler im Schuljahr Unterricht hat
            $divisionList = array();
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            $divisionPersonList = array();
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {
                    if (($tblYear = $tblDivision->getServiceTblYear())
                        && ($tblPersonDivisionList = Student::useService()->getDivisionListByPersonAndYear($tblPerson, $tblYear))
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
                if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivisionItem))) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblSubject = $tblTest->getServiceTblSubject();
                        if ($tblSubject && $tblTest->getServiceTblDivision()) {
                            $tableHeaderList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();

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
                                        if ($tblPerson) {
                                            if ($Route == 'Diploma') {
                                                $studentList = $this->setDiplomaGrade($tblPrepare, $tblPerson,
                                                    $tblSubject, $studentList);
                                            } else {
                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                        ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                    $tblPrepare
                                                );
                                            }
                                        }
                                    }

                                    // nicht vorhandene Schüler in der Gruppe auf leer setzten
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPersonItem) {
                                            if (!isset($studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()])) {
                                                $studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()] = '';
                                            }
                                        }
                                    }
                                }
                            } else {
                                if ($tblDivisionStudentAll) {
                                    foreach ($tblDivisionStudentAll as $tblPerson) {
                                        // nur Schüler der ausgewählten Klasse
                                        if (isset($divisionPersonList[$tblPerson->getId()])) {
                                            if ($Route == 'Diploma') {
                                                $studentList = $this->setDiplomaGrade($tblPrepare, $tblPerson,
                                                    $tblSubject, $studentList);
                                            } else {
                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                    $tblTest, $tblSubject, $tblPerson, $studentList, null, $tblPrepare);
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
                                            'Klasse ' . $tblDivision->getDisplayName()
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
        TblPrepareCertificate $tblPrepare = null
    ) {
        $studentList[$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson,
            $tblDivision,
            $tblSubject,
            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
            null,
            false,
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
     * @param null $PersonId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendShowCertificate(
        $PrepareId = null,
        $PersonId = null,
        $Route = null
    ) {
        $Stage = new Stage('Zeugnisvorschau', 'Anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $ContentLayout = array();

            $tblCertificate = false;
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'
                        . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        $tblDivision = $tblPrepare->getServiceTblDivision();
                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Template */
                        $Template = new $CertificateClass($tblDivision ? $tblDivision : null);

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

                        $ContentLayout = $bridge->getContent();
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
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
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
                            new LayoutColumn(array(
                                $ContentLayout
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
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSigner($PrepareId = null, $Route = null, $Data = null)
    {

        $Stage = new Stage('Unterzeichner', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : 0;
                $Global->savePost();
            }

            $tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Unterzeichner (Klassenlehrer)',
                                array('{{ FullName }}' => $tblPersonList)
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
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                $tblPersonList
                                    ? new Well(Prepare::useService()->updatePrepareSetSigner($form,
                                    $tblPrepare, $Data, $Route))
                                    : new Warning('Für diese Klasse sind keine Klassenlehrer vorhanden.')
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
     * @param null $PersonId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDroppedSubjects($PrepareId = null, $PersonId = null, $Route = null, $Data = null)
    {
        $Stage = new Stage('Abgewählte Fächer', 'Verwalten');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
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
                                array('Id' => $tblPrepareAdditionalGrade->getId(), 'Route' => $Route), 'Löschen'))
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
                                        'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Education/Prepare/Reorder',
                                            'Data' => array(
                                                'PrepareId' => $tblPrepare->getId(),
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
                        new SelectBox('Data[Subject]', 'Fach', array('Name' => $availableSubjectList)), 6
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
     * @param bool|false $Confirm
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendDestroyDroppedSubjects(
        $Id = null,
        $Confirm = false,
        $Route = null
    ) {

        $Stage = new Stage('Abgewähltes Fach', 'Löschen');

        $tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeById($Id);
        $tblPrepare = $tblPrepareAdditionalGrade->getTblPrepareCertificate();
        $tblPerson = $tblPrepareAdditionalGrade->getServiceTblPerson();

        $parameters = array(
            'PrepareId' => $tblPrepare ? $tblPrepare->getId() : 0,
            'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
            'Route' => $Route
        );

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
                                    'Klasse ' . (($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        array('Id' => $Id, 'Confirm' => true, 'Route' => $Route)
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
        }
    }

    /**
     * @param null $PrepareId
     * @param null $SubjectId
     * @param null $Route
     * @param null $IsNotSubject
     * @param null $IsFinalGrade
     * @param null $Data
     * @param null $CertificateList
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaSetting(
        $PrepareId = null,
        $SubjectId = null,
        $Route = null,
        $IsNotSubject = null,
        $IsFinalGrade = null,
        $Data = null,
        $CertificateList = null
    ) {

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {

            // Fachnoten mit Prüfungsnoten festlegen
            if (!$IsNotSubject
                && $tblPrepare->getServiceTblAppointedDateTask()
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                    $tblDivision))
            ) {

                return $this->setExamsSetting($tblPrepare, $tblDivision, $tblTestList, $SubjectId, $Route,
                    $IsFinalGrade, $Data, $IsNotSubject);

                // Sonstige Informationen
            } elseif (($tblDivision = $tblPrepare->getServiceTblDivision())
                && (($IsNotSubject
                        || (!$IsNotSubject && !$tblPrepare->getServiceTblBehaviorTask()))
                    || (!$IsNotSubject && $tblPrepare->getServiceTblBehaviorTask()
                        && !Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                            $tblDivision)))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

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
                    $IsNotSubject
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Course' => 'Bildungsgang',
                );

                $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepare);
                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
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
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => $isMuted ? new Muted(count($studentTable) + 1) : count($studentTable) + 1,
                            'Name' => $isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName()
                        );
                        $courseName = $tblCourse ? $tblCourse->getName() : '';
                        $studentTable[$tblPerson->getId()]['Course'] = $isMuted ? new Muted($courseName) : $courseName;

                        /*
                         * Sonstige Informationen der Zeugnisvorlage
                         */
                        if (!$isMuted) {
                            $this->getTemplateInformation($tblPrepare, $tblPerson, $studentTable, $columnTable, $Data,
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

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => array(
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
                        new FormRow(
                            new FormColumn(
                                $tableData
                            )
                        ),
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
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
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
                                    Prepare::useService()->updatePrepareInformationList($form, $tblPrepare, $Route,
                                        $Data, $CertificateList)
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
     * @param $tblTestList
     * @param $SubjectId
     * @param $Route
     * @param $IsFinalGrade
     * @param $Data
     *
     * @param $IsNotSubject
     * @return Stage
     */
    private function setExamsSetting(
        TblPrepareCertificate $tblPrepare,
        TblDivision $tblDivision,
        $tblTestList,
        $SubjectId,
        $Route,
        $IsFinalGrade,
        $Data,
        $IsNotSubject
    ) {
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
            array(
                'DivisionId' => $tblDivision->getId(),
                'Route' => $Route
            )
        ));

        $tblCurrentSubject = false;
        $tblNextSubject = false;
        $tblSubjectList = array();

        $buttonList = $this->createExamsButtonList(
            $tblPrepare, $tblCurrentSubject, $tblNextSubject, $tblTestList, $SubjectId, $Route, $tblSubjectList,
            $IsNotSubject
        );

        $studentTable = array();
        if (Prepare::useService()->isCourseMainDiploma($tblPrepare)) {
            // Klasse 9 Hauptschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
                'J' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('J'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'J',
                'LS' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ls',
                'LM' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Lm',
            );
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'Jn (Jahresnote)';
                $tableTitle = 'Jahresnote';
                if ($tblNextSubject) {
                    $textSaveButton = 'Speichern und weiter zum nächsten Fach';
                } else {
                    $textSaveButton = 'Speichern und weiter zu den sonstigen Informationen';
                }
            } else {
                $tableTitle = 'Leistungsnachweisnoten';
                $textSaveButton = 'Speichern und weiter zur Jahresnote';
            }
        } else {
            // Klasse 10 Realschule
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
                'JN' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Jn',
                'PS' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PS'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Ps',
                'PM' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PM'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pm',
                'PZ' => ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PZ'))
                    ? $tblPrepareAdditionalGradeType->getName() : 'Pz',
            );
            if ($IsFinalGrade) {
                $columnTable['Average'] = '&#216;';
                $columnTable['EN'] = 'En (Endnote)';
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

        list($studentTable, $hasPreviewGrades) = $this->createExamsContent($tblPrepare, $tblDivision, $tblTestList,
            $IsFinalGrade, $studentTable, $tblCurrentSubject, $tblSubjectList);

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
                new FormRow(
                    new FormColumn(
                        $tableData
                    )
                ),
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
                                'Klasse',
                                $tblDivision->getDisplayName(),
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
                            Prepare::useService()->updatePrepareExamGrades(
                                $form,
                                $tblPrepare,
                                $tblCurrentSubject,
                                $tblNextSubject ? $tblNextSubject : null,
                                $IsFinalGrade ? $IsFinalGrade : null,
                                $Route,
                                $Data
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
        $IsNotSubject
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
            $textLinkButton = ' - Leistungsnachweisnoten/Jahresnote';
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
                'Route' => $Route,
                'IsNotSubject' => true
            )
        );

        return $buttonList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblDivision $tblDivision
     * @param $tblTestList
     * @param $IsFinalGrade
     * @param $studentTable
     * @param $tblCurrentSubject
     * @param $tblSubjectList
     *
     * @return array
     */
    private function createExamsContent(
        TblPrepareCertificate $tblPrepare,
        TblDivision $tblDivision,
        $tblTestList,
        $IsFinalGrade,
        $studentTable,
        $tblCurrentSubject,
        $tblSubjectList
    ) {

        $hasPreviewGrades = false;
        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepare);
        if ($tblStudentList) {
            $tabIndex = 1;
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $hasSubject = false;

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
                        }
                    }
                }

                $studentTable[$tblPerson->getId()] = array(
                    'Number' => $isMuted ? new Muted(count($studentTable) + 1) : count($studentTable) + 1,
                    'Name' => $isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName()
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
                                        $tblDivision, $tblSubject, $tblSubjectGroup
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
                    if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                        && ($tblTestType = $tblTask->getTblTestType())
                        && $tblCurrentSubject
                    ) {
                        if (isset($tblSubjectList[$tblCurrentSubject->getId()])) {
                            $Global = $this->getGlobal();
                            $gradeList = array();

                            foreach ($tblSubjectList[$tblCurrentSubject->getId()] as $testId => $value) {
                                if ($isCourseMainDiploma) {
                                    if (!$isMuted && ($tblTestTemp = Evaluation::useService()->getTestById($testId))) {
                                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                            $tblDivision,
                                            $tblCurrentSubject,
                                            $tblTestTemp->getServiceTblSubjectGroup() ? $tblTestTemp->getServiceTblSubjectGroup() : null
                                        );
                                        $average = Gradebook::useService()->calcStudentGrade(
                                            $tblPerson,
                                            $tblDivision,
                                            $tblCurrentSubject,
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreRule ? $tblScoreRule : null,
                                            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
                                            $tblTestTemp->getServiceTblSubjectGroup() ? $tblTestTemp->getServiceTblSubjectGroup() : null,
                                            false,
                                            $tblTask->getDate() ? $tblTask->getDate() : false
                                        );

                                        if ($average) {
                                            if (!is_array($average) && ($pos = strpos($average, '('))){
                                                $average = substr($average, 0, $pos);
                                            }
                                            $Global->POST['Data'][$tblPerson->getId()]['J'] = str_replace('.', ',',
                                                $average);
                                            $gradeList['J'] = $average;
                                        }
                                    }
                                } else {
                                    if (($tblTestTemp = Evaluation::useService()->getTestById($testId))) {
                                        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent(
                                            $tblTestTemp, $tblPerson
                                        );
                                        if ($tblGrade) {
                                            $gradeValue = $tblGrade->getDisplayGrade();
                                            $Global->POST['Data'][$tblPerson->getId()]['JN'] = $gradeValue;
                                            if ($gradeValue && is_numeric($gradeValue)) {
                                                $gradeList['JN'] = $gradeValue;
                                            }
                                        }
                                    }
                                }
                            }

                            if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                                $tblPrepare,
                                $tblPerson
                            ))
                            ) {
                                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                    if ($tblPrepareAdditionalGrade->getServiceTblSubject()
                                        && $tblCurrentSubject->getId() == $tblPrepareAdditionalGrade->getServiceTblSubject()->getId()
                                        && ($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                                        && $tblPrepareAdditionalGradeType->getIdentifier() != 'PRIOR_YEAR_GRADE'
                                    ) {
                                        $Global->POST['Data'][$tblPerson->getId()][$tblPrepareAdditionalGradeType->getIdentifier()]
                                            = $tblPrepareAdditionalGrade->getGrade();
                                        if ($tblPrepareAdditionalGrade->getGrade()) {
                                            $gradeList[$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                                        }
                                    }
                                }
                            }

                            // calc average --> finalGrade
                            if ($IsFinalGrade) {
                                if ($isCourseMainDiploma) {
                                    if (!$isMuted) {
                                        $calcValue = '';
                                        if (isset($gradeList['J'])) {
                                            $calc = false;
                                            if (isset($gradeList['LS'])) {
                                                $calc = (2 * $gradeList['J'] + $gradeList['LS']) / 3;
                                            } elseif (isset($gradeList['LM'])) {
                                                $calc = (2 * $gradeList['J'] + $gradeList['LM']) / 3;
                                            }
                                            if ($calc) {
                                                $calcValue = round($calc, 2);
                                            } else {
                                                $calcValue = $gradeList['J'];
                                            }
                                        }

                                        $studentTable[$tblPerson->getId()]['Average'] = str_replace('.', ',',
                                            $calcValue);

                                        if (!Prepare::useService()->getPrepareAdditionalGradeBy(
                                                $tblPrepare,
                                                $tblPerson,
                                                $tblCurrentSubject,
                                                Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                            )
                                            && $calcValue
                                        ) {
                                            $hasPreviewGrades = true;
                                            $Global->POST['Data'][$tblPerson->getId()]['EN'] = round($calcValue, 0);
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
                                                $calc = ($gradeList['JN'] + $gradeList['PS']) / 2;
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
                                            $tblPrepare,
                                            $tblPerson,
                                            $tblCurrentSubject,
                                            Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN')
                                        )
                                        && $calcValue
                                    ) {
                                        $hasPreviewGrades = true;
                                        $Global->POST['Data'][$tblPerson->getId()]['EN'] = round($calcValue, 0);
                                    }
                                }
                            }

                            $Global->savePost();
                        }
                    }

                    if ($isCourseMainDiploma) {
                        // Klasse 9 Hauptschule
                        if (!$isMuted && $hasSubject) {
                            $isApproved = ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                    $tblPerson))
                                && $tblPrepareStudent->isApproved();
                            if ($IsFinalGrade
                                || $isApproved
                            ) {
                                $studentTable[$tblPerson->getId()]['J'] =
                                    (new TextField('Data[' . $tblPerson->getId() . '][J]'))->setDisabled();
                                $studentTable[$tblPerson->getId()]['LS'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][LS]'))->setDisabled();
                                $studentTable[$tblPerson->getId()]['LM'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][LM]'))->setDisabled();
                            } else {
                                $studentTable[$tblPerson->getId()]['J'] =
                                    (new TextField('Data[' . $tblPerson->getId() . '][J]'))->setTabIndex($tabIndex++)->setDisabled();
                                $studentTable[$tblPerson->getId()]['LS'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][LS]'))->setTabIndex($tabIndex++);
                                $studentTable[$tblPerson->getId()]['LM'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][LM]'))->setTabIndex($tabIndex++);
                            }

                            if ($IsFinalGrade) {
                                if ($isApproved) {
                                    $studentTable[$tblPerson->getId()]['EN'] =
                                        (new NumberField('Data[' . $tblPerson->getId() . '][EN]'))->setDisabled();
                                } else {
                                    $studentTable[$tblPerson->getId()]['EN'] =
                                        (new NumberField('Data[' . $tblPerson->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                }
                            }
                        } else {
                            $studentTable[$tblPerson->getId()]['J']
                                = $studentTable[$tblPerson->getId()]['LS']
                                = $studentTable[$tblPerson->getId()]['LM']
                                = $studentTable[$tblPerson->getId()]['EN'] = '';
                        }
                    } else {
                        // Klasse 10 Realschule
                        if ($hasSubject) {
                            $isApproved = ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                    $tblPerson))
                                && $tblPrepareStudent->isApproved();
                            if ($IsFinalGrade
                                || $isApproved
                            ) {
                                $studentTable[$tblPerson->getId()]['JN'] =
                                    (new TextField('Data[' . $tblPerson->getId() . '][JN]'))->setDisabled();
                                $studentTable[$tblPerson->getId()]['PS'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PS]'))->setDisabled();
                                $studentTable[$tblPerson->getId()]['PM'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PM]'))->setDisabled();
                                $studentTable[$tblPerson->getId()]['PZ'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PZ]'))->setDisabled();
                            } else {
                                $studentTable[$tblPerson->getId()]['JN'] =
                                    (new TextField('Data[' . $tblPerson->getId() . '][JN]'))->setTabIndex($tabIndex++)->setDisabled();
                                $studentTable[$tblPerson->getId()]['PS'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PS]'))->setTabIndex($tabIndex++);
                                $studentTable[$tblPerson->getId()]['PM'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PM]'))->setTabIndex($tabIndex++);
                                $studentTable[$tblPerson->getId()]['PZ'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . '][PZ]'))->setTabIndex($tabIndex++);
                            }

                            if ($IsFinalGrade) {
                                if ($isApproved) {
                                    $studentTable[$tblPerson->getId()]['EN'] =
                                        (new NumberField('Data[' . $tblPerson->getId() . '][EN]'))->setDisabled();
                                } else {
                                    $studentTable[$tblPerson->getId()]['EN'] =
                                        (new NumberField('Data[' . $tblPerson->getId() . '][EN]'))->setTabIndex($tabIndex++);
                                }
                            }
                        } else {
                            $studentTable[$tblPerson->getId()]['JN']
                                = $studentTable[$tblPerson->getId()]['PS']
                                = $studentTable[$tblPerson->getId()]['PM']
                                = $studentTable[$tblPerson->getId()]['PZ']
                                = $studentTable[$tblPerson->getId()]['EN'] = '';
                        }
                    }
                }
            }
        }
        return array($studentTable, $hasPreviewGrades);
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

        $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();

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
        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
        ) {
            if ($tblLevel->getName() == '10') {
                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('MsAbsRs');
            } elseif ($tblLevel->getName() == '9' || $tblLevel->getName() == '09') {
                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('MsAbsHsQ');
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
}
