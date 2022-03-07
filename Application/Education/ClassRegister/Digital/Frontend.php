<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class Frontend extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/ClassRegister/Digital';

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
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
    public function frontendTeacherSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher',
            $IsAllYears, $IsGroup, $YearId, false, true, $yearFilterList);

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            $divisionList = array();

            // Klassenlehrer
            if (($tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                foreach ($tblDivisionList as $tblDivisionTeacher) {
                    if (($tblDivision = $tblDivisionTeacher->getTblDivision())
                        && ($tblYear = $tblDivision->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }

            // Fachlehrer
            if (($tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson))) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                        && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                        && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYearItem->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                    }
                }
            }

            if ($IsGroup) {
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    $isTudor = true;
                } else {
                    $isTudor = false;
                }

                if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                    foreach ($tblGroupAll as $tblGroup) {
                        // ist Tudor in Stammgruppe
                        $addGroup = false;
                        if ($isTudor && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $addGroup = true;
                        // oder ist Klassenlehrer oder Fachlehrer
                        } else {
                            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                                foreach ($tblPersonList as $tblPersonStudent) {
                                    if (($tblStudent = $tblPersonStudent->getStudent())
                                        && ($tblDivisionMain = $tblStudent->getCurrentMainDivision())
                                        && isset($divisionList[$tblDivisionMain->getId()])
                                    ) {
                                        $addGroup = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($addGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', self::BASE_ROUTE . '/Selected', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                        'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
                    $table = new TableData($divisionTable, null, array(
                        'Group' => 'Gruppe',
                        'Option' => ''
                    ), array(
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
                }
            } else {
                foreach ($divisionList as $item) {
                    $divisionTable[] = array(
                        'Year' => $item->getServiceTblYear() ? $item->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $item->getTypeName(),
                        'Division' => $item->getDisplayName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/Selected', new Select(),
                            array(
                                'DivisionId' => $item->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                            ),
                            'Auswählen'
                        )
                    );
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
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
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
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

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::HEADMASTER, self::BASE_ROUTE);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $IsGroup, $YearId, true, true, $yearFilterList);

        $divisionTable = array();
        if ($IsGroup) {
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/Selected', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
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
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblYear = $tblDivision->getServiceTblYear())) {
                        // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionTable[] = array(
                            'Year' => $tblYear->getDisplayName(),
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/Selected', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
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
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
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
}