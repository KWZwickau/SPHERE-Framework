<?php

namespace SPHERE\Application\People\Search;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
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
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param $PseudoId
     *
     * @return Stage
     */
    public function frontendSearch($PseudoId = null)
    {
        $stage = new Stage('Suche', 'nach Personen oder Gruppen/Kursen');
        $search = '';
        $selectedId = '';
        $content = '';
        if ($PseudoId) {
            $type = $PseudoId[0];
            $id = substr($PseudoId, 1);

            if ($type == 'G' || $type == 'C') {
//                $stage->addButton(new Standard('Zurück', '/People/Dashboard', new ChevronLeft()));
                $selectedId = $PseudoId;
                $content = ApiPersonSearch::pipelineLoadGroupSelectBox($PseudoId);
            } elseif ($type == 'S') {
                $search = $id;
                $content = $this->loadPersonSearch($search);
            }
        }

        $stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    ApiPersonSearch::receiverBlock($this->getPanelSearchPerson($search), 'SearchTextInput')
                , 4),
                new LayoutColumn(
                    ApiPersonSearch::receiverBlock($this->getPanelSelectGroupOrDivisionCourse($selectedId), 'GroupSelectBox')
                , 4),
                new LayoutColumn($this->getPanelDashboard(), 4),
            ))))
            . ApiPersonSearch::receiverBlock($content, 'SearchContent')
        );

        return $stage;
    }

    /**
     * @param string $Search
     *
     * @return string
     */
    public function getPanelSearchPerson(string $Search): string
    {
        if ($Search) {
            $global = $this->getGlobal();
            $global->POST['Data']['Search'] = $Search;
            $global->savePost();
        }

        return new Panel(
            new Search() . ' Personen-Suche',
            (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Search]', '', ''))
                        ->ajaxPipelineOnKeyUp(ApiPersonSearch::pipelineSearchPerson())
                ),
            )))))->disableSubmitAction(),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param string $SelectId
     *
     * @return string
     */
    public function getPanelSelectGroupOrDivisionCourse(string $SelectId): string
    {
        if ($SelectId) {
            $global = $this->getGlobal();
            $global->POST['Data']['Id'] = $SelectId;
            $global->savePost();
        }

        $dataList = array();
        if (($tblGroupList = Group::useService()->getGroupAll())) {
            foreach ($tblGroupList as $tblGroup) {
                // alte Stammgruppen Personengruppen auslassen
                if ($tblGroup->isCoreGroup()) {
                    continue;
                }
                $dataList[] = new SelectBoxItem('G' . $tblGroup->getId(), $tblGroup->getName());
            }
        }
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByIsShownInPersonData($tblYear))) {
                    $tblDivisionCourseList = (new Extension())->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $dataList[] = new SelectBoxItem('C' . $tblDivisionCourse->getId(), $tblDivisionCourse->getDisplayName());
                    }
                }
            }
        }

        return new Panel(
            new Bold('oder') . ' Gruppen/Kurs-Auswahl',
            (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[Id]', '', array('{{ Name }}' => $dataList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiPersonSearch::pipelineSelectGroup())
                ),
            )))))->disableSubmitAction(),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @return string
     */
    public function getPanelDashboard(): string
    {
        return new Panel(
            new Bold('oder') . ' Übersicht',
            (new Standard('Dashboard anzeigen', ApiPersonSearch::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonSearch::pipelineLoadDashboard()),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param $Search
     *
     * @return string
     */
    public function loadPersonSearch($Search): string
    {
        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            $showDivision = false;
            $showCoreGroup = false;
            $showStudentIdentifier = false;
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                foreach ($tblPersonList as $tblPerson) {
                    $displayDivision = '';
                    $displayCoreGroup = '';
                    $displayStudentIdentifier = '';
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                        if (($tblDivision = $tblStudentEducation->getTblDivision())
                            && ($displayDivision = $tblDivision->getName())
                        ) {
                            $showDivision = true;
                        }
                        if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                            && ($displayCoreGroup = $tblCoreGroup->getName())
                        ) {
                            $showCoreGroup = true;
                        }
                    }
                    if (($tblStudent = $tblPerson->getStudent()) && $tblStudent->getIdentifierComplete()) {
                        $showStudentIdentifier = true;
                        $displayStudentIdentifier = $tblStudent->getIdentifierComplete();
                    }

                    $groupNameList = array();
                    if (($tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson))) {
                        foreach ($tblGroupList as $tblGroup) {
                            if ($tblGroup->isLocked()
                                && $tblGroup->getMetaTable() != 'COMMON' && $tblGroup->getMetaTable() != 'COMPANY_CONTACT'
                                && $tblGroup->getMetaTable() != 'DEBTOR' && $tblGroup->getMetaTable() != 'TUDOR'
                            ) {
                                $groupNameList[] = $tblGroup->getName();
                            }
                        }
                    }
                    sort($groupNameList);

                    $resultList[] = array(
                        'FullName' => $tblPerson->getLastFirstName(),
                        'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new Warning('Keine Adresse hinterlegt'),
                        'GroupList' => implode(', ', $groupNameList),
                        'Identifier' => $displayStudentIdentifier,
                        'Division' => $displayDivision,
                        'CoreGroup' => $displayCoreGroup,
                        'Option' =>
                            new Standard(
                                '',
                                '/People/Person',
                                new Edit(),
                                array('Id'    => $tblPerson->getId(), 'Group' => 'S' . $Search),
                                'Bearbeiten'
                            )
                            . new Standard(
                                '',
                                '/People/Person/Destroy',
                                new Remove(),
                                array('Id' => $tblPerson->getId(), 'Group' => 'S' . $Search),
                                'Person löschen'
                            )
                    );
                }

                $columnList = array(
                    'FullName'   => 'Name',
                    'Address'    => 'Adresse',
                    'GroupList'    => 'Gruppen',
                );
                if ($showStudentIdentifier) {
                    $columnList['Identifier'] = 'Schülernummer';
                }
                if ($showDivision) {
                    $columnList['Division'] = 'Klasse';
                }
                if ($showCoreGroup) {
                    $columnList['CoreGroup'] = 'Stammgruppe';
                }
                $columnList['Option'] = '';

                $result = new TableData(
                    $resultList,
                    null,
                    $columnList,
                    array(
                        'columnDefs' => array(
                            array('type' => \SPHERE\Application\Setting\Consumer\Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            }

            if (empty($resultList)) {
                $result = new WarningMessage('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $result = new WarningMessage('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return new Title('Verfügbare Personen ' . new Small(new Muted('der Personen-Suche: ')) . new Bold($Search))
            . $result;
    }

    /**
     * @param string $PseudoId
     *
     * @return string
     */
    public function loadGroup(string $PseudoId): string
    {
        if ($PseudoId) {
            $type = $PseudoId[0];
            $id = substr($PseudoId, 1);
            if ($type == 'G' && ($tblGroup = Group::useService()->getGroupById($id))) {
                return $this->getPersonGroupContent($tblGroup);
            } elseif ($type == 'C' && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($id))) {
                return $this->getDivisionCourseContent($tblDivisionCourse);
            }
        }

        return '';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getDivisionCourseContent(TblDivisionCourse $tblDivisionCourse): string
    {
        $showDivision = false;
        $showCoreGroup = false;
        $tableContent = array();
        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE) {
            $tblPersonList = array();
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListBySubjectDivisionCourse($tblDivisionCourse))) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblPersonTemp = $tblStudentSubject->getServiceTblPerson())) {
                        $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;
                    }
                }
            }
        } else {
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses();
        }

        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $item = array();
                $displayDivision = '';
                $displayCoreGroup = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if (($tblDivision = $tblStudentEducation->getTblDivision())
                        && ($displayDivision = $tblDivision->getName())
                    ) {
                        $showDivision = true;
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        && ($displayCoreGroup = $tblCoreGroup->getName())
                    ) {
                        $showCoreGroup = true;
                    }
                }

                $item['FullName'] = $tblPerson->getLastFirstName();
                $item['Address'] = (($tblAddress = $tblPerson->fetchMainAddress())
                    ? $tblAddress->getGuiString()
                    : new Warning('Keine Adresse hinterlegt')
                );
                $item['Identifier'] = ($tblStudent = $tblPerson->getStudent()) ? $tblStudent->getIdentifierComplete() : '';
                $item['Division'] = $displayDivision;
                $item['CoreGroup'] = $displayCoreGroup;
                $item['Option'] = new Standard(
                        '',
                        '/People/Person',
                        new Edit(),
                        array('Id'    => $tblPerson->getId(), 'Group' => 'C' . $tblDivisionCourse->getId()),
                        'Bearbeiten'
                    )
                    . new Standard(
                        '',
                        '/People/Person/Destroy',
                        new Remove(),
                        array('Id' => $tblPerson->getId(), 'Group' => 'C' . $tblDivisionCourse->getId())
                        , 'Person löschen'
                    );

                $tableContent[] = $item;
            }
        }

        $columnList = array(
            'FullName'   => 'Name',
            'Address'    => 'Adresse',
            'Identifier' => 'Schülernummer',
        );
        if ($showDivision) {
            $columnList['Division'] = 'Klasse';
        }
        if ($showCoreGroup) {
            $columnList['CoreGroup'] = 'Stammgruppe';
        }
        $columnList['Option'] = '';

        $columnDefs = array(
            array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
            array('type' => 'natural', 'targets' => array(3,4)),
            array('orderable' => false, 'width' => '60px', 'targets' => -1),
        );

        return new Title('Verfügbare Personen ' . new Small(new Muted('im Kurs: ')) . (new Bold($tblDivisionCourse->getDisplayName())))
            . new TableData($tableContent, null, $columnList, array('columnDefs' => $columnDefs, 'order' => array(0, 'asc')));
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return string
     */
    private function getPersonGroupContent(TblGroup $tblGroup): string
    {
        // result by Views
        $ContentArray = Individual::useService()->getPersonListByGroup($tblGroup);
        $showDivision = false;
        $showCoreGroup = false;

        $tableContent = array();
        if ($ContentArray){
            $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
            // relationship array group by FromPerson
            $tblRelationshipList = Relationship::useService()->getPersonRelationshipArrayByType($tblRelationshipType);
            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE);
            array_walk($ContentArray, function($contentRow) use (&$tableContent, $tblGroup, $tblRelationshipList,
                $tblStudentTransferType, &$showDivision, &$showCoreGroup
            ){
                // Custody
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
                                            'Group' => 'G' . $tblGroup->getId()
                                        ),
                                        'zur Person wechseln'
                                    )
                                    . $tblPersonChild->getFirstSecondName(); //if necessary hole name
                            }
                        }
                    }
                }

                $displayDivision = '';
                $displayCoreGroup = '';
                if ($tblGroup->getMetaTable() == 'STUDENT' || !$tblGroup->isLocked()) {
                    if(($tblPerson = Person::useService()->getPersonById($contentRow['TblPerson_Id']))
                        && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                    ){
                        if (($tblDivision = $tblStudentEducation->getTblDivision())
                            && ($displayDivision = $tblDivision->getName())
                        ) {
                            $showDivision = true;
                        }
                        if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                            && ($displayCoreGroup = $tblCoreGroup->getName())
                        ) {
                            $showCoreGroup = true;
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

                        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                            /** @var TblStudentEducation $tblStudentEducation */
                            $tblStudentEducation = current($this->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_DESC));
                            $DisplayDivision = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getName() : '';
                            $DisplayDivision .= ($tblCoreGroup= $tblStudentEducation->getTblCoreGroup()) ? ($DisplayDivision ? ', ' : '') . $tblCoreGroup->getName() : '';
                            $DivisionYear = ($tblYear = $tblStudentEducation->getServiceTblYear()) ? $tblYear->getDisplayName() : '';
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
                $item['Division'] = $displayDivision;
                $item['CoreGroup'] = $displayCoreGroup;
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
                            'Group' => 'G' . $tblGroup->getId())
                        , 'Bearbeiten')
                    .new Standard('',
                        '/People/Person/Destroy', new Remove(),
                        array('Id' => $contentRow['TblPerson_Id'],
                            'Group' => 'G' . $tblGroup->getId())
                        , 'Person löschen');

                array_push($tableContent, $item);
            });
        }

        $tableContent = array_filter($tableContent);

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
                'Identifier' => 'Schülernummer',
            );
            if ($showDivision) {
                $ColumnArray['Division'] = 'Klasse';
            }
            if ($showCoreGroup) {
                $ColumnArray['CoreGroup'] = 'Stammgruppe';
            }
            $ColumnArray['Option'] = '';
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
                'DisplayDivision' => 'Abgang mit Klasse/Stammgruppe',
                'DivisionYear'    => 'Abgang Schuljahr',
                'LeaveDate'       => 'Abgang Datum',
                'Option'          => '',
            );
        } elseif ($showDivision) {
            $ColumnArray = array(
                'FullName' => 'Name',
                'Address'  => 'Adresse',
            );
            if ($showDivision) {
                $ColumnArray['Division'] = 'Klasse';
            }
            if ($showCoreGroup) {
                $ColumnArray['CoreGroup'] = 'Stammgruppe';
            }
            $ColumnArray['Option'] = '';
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
                array('type' => 'natural', 'targets' => array(3,4)),
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
                array('type' => 'natural', 'targets' => 2,3),
                array('orderable' => false, 'width' => '60px', 'targets' => -1),
            );
        }

        return new Title('Verfügbare Personen ' . new Small(new Muted('in der Gruppe: ')) . (new Bold($tblGroup->getName())))
            . new TableData($tableContent, null, $ColumnArray, array('columnDefs' => $columnDefs, 'order' => $orderByColumn));
    }

    /**
     * @return string
     */
    public function loadDashboard(): string
    {
        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if (($tblGroupAll = Group::useService()->getGroupAllSorted())) {
            foreach ($tblGroupAll as $tblGroup) {
                // alte Personengruppen - Stammgruppen überspringen
                if ($tblGroup->isCoreGroup()) {
                    continue;
                }

                $content = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn($tblGroup->getName() . new Muted(new Small('<br/>' . $tblGroup->getDescription(true))), 6),
                    new LayoutColumn(new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Mitglieder')), 5),
                    new LayoutColumn(new PullRight(
//                        new Standard('', '/People', new GroupIcon(), array('PseudoId' => 'G' . $tblGroup->getId()))
                        (new Standard('', ApiPersonSearch::getEndpoint(), new GroupIcon()))
                            ->ajaxPipelineOnClick(array(
                                ApiPersonSearch::pipelineLoadSearchTextInput(''),
                                ApiPersonSearch::pipelineLoadGroupSelectBox('G' . $tblGroup->getId())
                            ))
                    ), 1)
                ))));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                    if ($tblGroup->getMetaTable() == 'STUDENT') {
                        $yearListForStudentCount = array();
                        if (($tblYearNowList = Term::useService()->getYearByNow())) {
                            foreach ($tblYearNowList as $tblYearNow) {
                                $yearListForStudentCount[$tblYearNow->getId()] = $tblYearNow;
                            }
                        }
                        $date = (new DateTime('now'))->add(new DateInterval('P2M'));
                        if (($tblYearFutureList = Term::useService()->getYearAllByDate($date))) {
                            foreach ($tblYearFutureList as $tblYearFuture) {
                                $yearListForStudentCount[$tblYearFuture->getId()] = $tblYearFuture;
                            }
                        }

                        $rows[] = new LayoutRow(new LayoutColumn('Schüler / Schuljahr'));
                        foreach ($yearListForStudentCount as $tblYearTemp) {
                            $rows[] = new LayoutRow(new LayoutColumn(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn(new Bold($tblYearTemp->getDisplayName()), 6),
                                        new LayoutColumn(new Muted(new Small(DivisionCourse::useService()->getCountStudentsByYear($tblYearTemp). '&nbsp;Mitglieder')), 5),
                                        new LayoutColumn(new PullRight(
                                            (new Standard('', ApiPersonSearch::getEndpoint(), new EyeOpen()))
                                                ->ajaxPipelineOnClick(ApiPersonSearch::pipelineOpenYearStudentCountModal($tblYearTemp->getId()))
                                        ), 1)
                                    )
                                )))
                            ));
                        }

                        $content = new Layout(new LayoutGroup($rows));
                        $tblGroupLockedList[] = $content;
                    }
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }

        /*
         * Kurse aus der Bildung
         */
        $dataCourseList = array();
        if (($tblYearNowList = Term::useService()->getYearByNow())) {
            foreach ($tblYearNowList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByIsShownInPersonData($tblYear))) {
                    $tblDivisionCourseList = (new Extension())->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                            $countStudentSubjectPeriod1 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 1);
                            $countStudentSubjectPeriod2 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 2);
                            $countContent = new Muted(new Small(
                                '1. HJ: ' . $countStudentSubjectPeriod1 . ' Mitglieder'
                                . '<br/>'
                                . ' 2. HJ: ' . $countStudentSubjectPeriod2 . ' Mitglieder'
                            ));
                        } else {
                            $countContent = new Muted(new Small($tblDivisionCourse->getCountStudents() . '&nbsp;Mitglieder'));
                        }

                        $dataCourseList[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($tblDivisionCourse->getName() . new Muted(new Small('<br/>' . $tblDivisionCourse->getDescription())), 6),
                            new LayoutColumn($countContent, 5),
                            new LayoutColumn(new PullRight(
//                                new Standard('', '/People', new GroupIcon(), array('PseudoId' => 'C' . $tblDivisionCourse->getId()))
                                (new Standard('', ApiPersonSearch::getEndpoint(), new GroupIcon()))
                                    ->ajaxPipelineOnClick(array(
                                        ApiPersonSearch::pipelineLoadSearchTextInput(''),
                                        ApiPersonSearch::pipelineLoadGroupSelectBox('C' . $tblDivisionCourse->getId())
                                    ))
                            ), 1)
                        ))));
                    }
                }
            }
        }

        return
            ApiPersonSearch::receiverModal()
            . new Title('Dashboard', 'Personen')
            . new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Personen in festen Gruppen', $tblGroupLockedList), 4
                ),
                !empty($tblGroupCustomList) ?
                    new LayoutColumn(
                        new Panel('Personen in individuellen Gruppen', $tblGroupCustomList), 4) : null,
                new LayoutColumn(
                    new Panel('Schüler in Kursen', $dataCourseList), 4
                )
            ))));
    }
}