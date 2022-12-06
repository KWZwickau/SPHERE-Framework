<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool|false|int $Id
     *
     * @return Stage
     */
    public function frontendSearch($Id = false)
    {

        $Stage = new Stage('Suche', 'nach Gruppe');
        $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft()));
        Group::useFrontend()->addGroupSearchStageButton($Stage);

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {
            // result by Views
            $ContentArray = Individual::useService()->getPersonListByGroup($tblGroup);
            $filterWarning = false;
            $showDivision = false;

            $tableContent = array();
            if ($ContentArray){

                $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                // relationship array group by FromPerson
                $tblRelationshipList = Relationship::useService()->getPersonRelationshipArrayByType($tblRelationshipType);
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE);

                array_walk($ContentArray, function($contentRow) use (&$tableContent, $tblGroup, $tblRelationshipList,
                    $tblStudentTransferType, &$showDivision
                ){

//                    // Custody
                    $childrenList = array();
                    if ($tblGroup->getMetaTable() == 'CUSTODY') {
                        if(isset($tblRelationshipList[$contentRow['TblPerson_Id']])){
                            $CustodyChildList = $tblRelationshipList[$contentRow['TblPerson_Id']];
                            foreach($CustodyChildList as $childId) {
                                $tblPersonChild = Person::useService()->getPersonById($childId);
                                // Personen müssen noch im Tool vorhanden sein
                                if($tblPersonChild){
                                    $childrenList[$childId]
                                        = new Standard('', '/People/Person', new PersonIcon(),
                                            array(
                                                'Id' => $childId,
                                                'Group' => $tblGroup->getId()
                                            ),
                                            'zur Person wechseln'
                                        )
                                        . $tblPersonChild->getFirstSecondName(); //if necessary hole name
                                }
                            }
                        }
//                        // Old Logic
//                        $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
//                        $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($contentRow['TblPerson_Id']);
//
//                        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
//                            foreach ($tblRelationshipList as $tblToPerson) {
//                                if ($tblToPerson->getTblType()->getName() == "Sorgeberechtigt"
//                                    && ($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom())
//                                    && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())
//                                    && $tblPersonFrom->getId() == $tblPerson->getId()
//                                ) {
//                                    $childrenList[$tblPersonTo->getId()]
//                                        = new Standard('', '/People/Person', new PersonIcon(),
//                                            array(
//                                                'Id' => $tblPersonTo->getId(),
//                                                'Group' => $tblGroup->getId()
//                                            ),
//                                            'zur Person wechseln'
//                                        )
//                                        . ($tblPerson->getLastName() != $tblPersonTo->getLastName()
//                                            ? $tblPersonTo->getLastFirstName() : $tblPersonTo->getFirstSecondName());
//                                }
//                            }
//                        }
                    }

                    $displayDivisionList = '';
                    if ($tblGroup->getMetaTable() == 'STUDENT' || !$tblGroup->isLocked()) {
                        if(($tblPerson = Person::useService()->getPersonById($contentRow['TblPerson_Id']))){
                            $displayDivisionList = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                            if ($displayDivisionList) {
                                $showDivision = true;
                            }
                        }
                    }
                    $LeaveDate = '';
                    $DisplayDivision = '';
                    $DivisionYear = '';
                    if ($tblGroup->getMetaTable() == TblGroup::META_TABLE_ARCHIVE) {
                        if(($tblPerson = Person::useService()->getPersonById($contentRow['TblPerson_Id']))){
                            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                                if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                                    $LeaveDate = $tblStudentTransfer->getTransferDate();
                                }
                            }
                            // Klassenlisten des Ehemaligen Schülers
                            if(($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))){
                                $tblDivisionStudent = current($tblDivisionStudentList);
                                $FoundDivision = false;
                                // Sucher der letzten Klasse (nicht Klassenübergreifend: Level !ischecked)
                                while ($FoundDivision == false && $tblDivisionStudent){
                                    if(($tblDivision = $tblDivisionStudent->getTblDivision())
                                        && ($tblLevel = $tblDivision->getTblLevel())
                                        && !$tblLevel->getIsChecked()
                                    ){
                                        $DisplayDivision = $tblDivision->getDisplayName();
                                        if(($tblYear = $tblDivision->getServiceTblYear())){
                                            $DivisionYear = $tblYear->getDisplayName();
                                        }
                                        $FoundDivision = true;
                                    }
                                    $tblDivisionStudent = next($tblDivisionStudentList);
                                }
                            }
                        }
                    }

                    $item['FullName'] = $contentRow['TblPerson_LastFirstName'];
                    $item['Remark'] = $contentRow['TblCommon_Remark'];

                    $item['Address'] = (trim($contentRow['Address'])
                        ? $contentRow['Address']
                        : new Warning('Keine Adresse hinterlegt')
                    );
                    // Student
                    $item['Division'] = $displayDivisionList;
                    $item['Identifier'] = trim($contentRow['Identifier']);
                    // Custody
                    $item['Custody'] = (empty($childrenList) ? '' : (string)new Listing($childrenList));
                    // Prospect
                    $item['Year'] = $contentRow['Year'];
                    $item['Level'] = $contentRow['Level'];
                    $item['SchoolOption'] = $contentRow['SchoolOption'];
                    $item['School'] = $contentRow['School'];
                    // Archive
                    $item['LeaveDate'] = $LeaveDate;
                    $item['DivisionYear'] = $DivisionYear;
                    $item['DisplayDivision'] = $DisplayDivision;

                    $item['Option'] = new Standard('', '/People/Person', new Edit(),
                            array(
                            'Id'    => $contentRow['TblPerson_Id'],
                            'Group' => $tblGroup->getId())
                        , 'Bearbeiten')
                    .new Standard('',
                        '/People/Person/Destroy', new Remove(),
                        array('Id' => $contentRow['TblPerson_Id'],
                              'Group' => $tblGroup->getId())
                        , 'Person löschen');

                    // CSW fast reaction
                    if($item['Division'] == 'Achat'
                    || $item['Division'] == 'Bergkristall'
                    || $item['Division'] == 'Opal'
                    || $item['Division'] == 'Saphir'
                    || $item['Division'] == 'Topas'){
                        $item = array();
                    }

                    array_push($tableContent, $item);
                });
            }

            $tableContent = array_filter($tableContent);

            $Date = Term::useService()->getYearString();
            $YearNow = $Date;

            if($tblGroup->getMetaTable() == TblGroup::META_TABLE_CUSTODY){
                if(Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'ESZC')){
                    $ColumnArray = array(
                        'FullName' => 'Name',
                        'Address'  => 'Adresse',
                        'Custody'  => 'Sorgeberechtigt für',
                        'Remark'   => 'Bemerkung',
                        'Option'   => '',
                    );
                } else {
                    $ColumnArray = array(
                        'FullName' => 'Name',
                        'Address'  => 'Adresse',
                        'Custody'  => 'Sorgeberechtigt für',
                        'Option'   => '',
                    );
                }

            } elseif ($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT) {
                $ColumnArray = array(
                    'FullName'   => 'Name',
                    'Address'    => 'Adresse',
                    'Division'   => 'Klasse (SJ '.$YearNow.')',
                    'Identifier' => 'Schülernummer',
                    'Option'     => '',
                );
            } elseif ($tblGroup->getMetaTable() == TblGroup::META_TABLE_PROSPECT) {
                $ColumnArray = array(
                    'FullName'     => 'Name',
                    'Address'      => 'Adresse',
                    'Year'         => 'Schuljahr',
                    'Level'        => 'Klassenstufe',
                    'SchoolOption' => 'Schulart',
                    'School'       => 'Schule',
                    'Option'       => '',
                );
            } elseif ($tblGroup->getMetaTable() == TblGroup::META_TABLE_ARCHIVE) {
                $ColumnArray = array(
                    'FullName'        => 'Name',
                    'Address'         => 'Adresse',
                    'DisplayDivision' => 'Abgang mit Klasse',
                    'DivisionYear'    => 'Abgang Schuljahr',
                    'LeaveDate'       => 'Abgang Datum',
                    'Option'          => '',
                );
            } elseif ($showDivision) {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address'  => 'Adresse',
                    'Division'   => 'Klasse (SJ '.$YearNow.')',
                    'Option' => '',
                );
            } else {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address'  => 'Adresse',
                    'Option' => '',
                );
            }
            //Standard order & column definition
            $orderByColumn = array(0, 'asc');
            $columnDefs = array(
                array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                array('orderable' => false, 'width' => '60px', 'targets' => -1),
            );
            // Student column definition
            if($tblGroup->getMetaTable() == TblGroup::META_TABLE_CUSTODY){
                $columnDefs = array(
                    array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                    array('orderable' => false, 'targets' => -2),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
                );
            }
            // Student column definition
            if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
                $columnDefs = array(
                    array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                    array('type' => 'natural', 'targets' => array(2,3)),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
                );
            }
            // Archive order & column definition
            if($tblGroup->getMetaTable() == TblGroup::META_TABLE_ARCHIVE){
                $orderByColumn = array(array(4, 'desc'));
                $columnDefs = array(
                    array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 4),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
                );
            }
            if ($showDivision) {
                $columnDefs = array(
                    array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                    array('type' => 'natural', 'targets' => 2),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
                );
            }

            $Stage->setContent(
                ($filterWarning
                    ? $filterWarning
                    : '')
                . new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        new Panel(new PersonGroup() . ' Gruppe', array(
                            new Bold($tblGroup->getName()),
                            ($tblGroup->getDescription(true) ? new Small($tblGroup->getDescription(true)) : ''),
                            ($tblGroup->getRemark() ? new Danger(new Italic(nl2br($tblGroup->getRemark()))) : '')
                        ), Panel::PANEL_TYPE_INFO
                        )
                    )),
                        new LayoutRow(new LayoutColumn(array(
                            new Headline('Verfügbare Personen', 'in dieser Gruppe'),
                            new TableData($tableContent, null, $ColumnArray, array(
                                'columnDefs' => $columnDefs,
                                'order' => $orderByColumn
                            ))
                        )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }

    /**
     * @param Stage $Stage
     */
    public function addGroupSearchStageButton(Stage $Stage)
    {

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        if (!empty($tblGroupAll)) {
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName() . '&nbsp;&nbsp;' . new Label(Group::useService()->countMemberByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }
    }
}
