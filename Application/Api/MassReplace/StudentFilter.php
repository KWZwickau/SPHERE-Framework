<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

class StudentFilter extends Extension
{
    const STUDENT_FILTER = 'StudentFilter';

    /**
     * @param string $modalField
     *
     * @return Form|string
     */
    public function formStudentFilter($modalField)
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Bildung: Schuljahr '.new DangerText('*'),
                            array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1)))
                    ), 4),
//                    new FormColumn(array(
//                        new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Bildung: Schulart',
//                            array('Name' => Type::useService()->getTypeAll()))
//                    ), 3),
                    new FormColumn(array(
                        new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList))
                    ), 4),
                    new FormColumn(array(
                        new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Klasse: Gruppe',
                            'Klasse: Gruppe',
                            array('Name' => Division::useService()->getDivisionAll()))
                    ), 4),
                )),
                new FormRow(
                    new FormColumn(
                        (new \SPHERE\Common\Frontend\Link\Repository\Primary('Filter',
                            ApiMassReplace::getEndpoint(),
                            null,
                            $this->getGlobal()->POST))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field))
                    )
                ),
                new FormRow(
                    new FormColumn(
                        new DangerText('*'.new Small('Pflichtfeld'))
                    )
                )
            ))
//                , new Primary('Filtern'), '',
//                $this->getGlobal()->POST))->ajaxPipelineOnSubmit(ApiMassReplace::pipelineOpen($Field))
        ));
    }

    /**
     * @param $modalField
     * @param $Year
     * @param $Division
     *
     * @return Layout
     * // Content for OpenModal -> ApiMassReplace
     */
    public function getFrontendStudentFilter($modalField, $Year = null, $Division = null)
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = (new ApiMassReplace())->cloneField($Field, 'CloneField', 'Auswahl/Eingabe');

        $TableContent = $this->getStudentFilterResult($Year, $Division);

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(new Well(
                        ApiMassReplace::receiverFilter('Filter', $this->formStudentFilter($modalField))
                    )),
                    new LayoutColumn(new Well(
                        (new Form(
                            new FormGroup(
                                new FormRow(array(
                                    new FormColumn(
                                        new Panel('Weitere Personen:',
                                            (!empty($TableContent)
                                                ? new TableData($TableContent, null,
                                                    array(
                                                        'Check'         => 'Auswahl',
                                                        'Name'          => 'Name',
                                                        'StudentNumber' => 'Schülernummer',
                                                        'Level'         => 'Stufe',
                                                        'Division'      => 'Klasse',
                                                        'Course'        => 'Bildungsgang',
                                                    ), null)
                                                : new Warning('Keine Personen gefunden '.
                                                    new ToolTip(new Info(), 'Das Schuljahr ist ein Pflichtfeld'))),
                                            Panel::PANEL_TYPE_INFO
                                        )
                                    ),
                                    new FormColumn(
                                        $CloneField
                                    )
                                ))
                            )
                            , new Primary('Ändern'), '', $this->getGlobal()->POST))
//                            ->ajaxPipelineOnSubmit(ApiMassReplace::pipelineOpen($Field))
                            ->ajaxPipelineOnSubmit(ApiMassReplace::pipelineSave($Field))
                    ))
                ))
            )
        );
    }

    /**
     * @param null $Year
     * @param null $Division
     *
     * @return array $SearchResult
     */
    private function getStudentFilterResult($Year = null, $Division = null)
    {
        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = '';

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Year = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterType
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $Division = array_filter($Division);
            } else {
                $Division = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => array(),   // empty Person search
                2 => $Division,
                3 => $Year
            ));
        }

        $SearchResult = array();
        if ($Result != '') {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Course'] = '';
                $DataPerson['Check'] = '';
                if ($tblPerson) {
                    $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                        $tblPerson->getId()
                        , array($tblPerson->getId())))->setChecked();
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
//                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblStudentTransferType);
                        if ($tblStudentTransfer && $tblStudentTransfer->getServiceTblCourse()) {
                            $DataPerson['Course'] = $tblStudentTransfer->getServiceTblCourse()->getName();
                        }
                    }
                }
                $DataPerson['Division'] = '';
                $DataPerson['Level'] = '';

                $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                if ($tblDivision) {
                    $DataPerson['Division'] = $tblDivision->getName();
                    $tblLevel = $tblDivision->getTblLevel();
                    if ($tblLevel) {
                        $DataPerson['Level'] = $tblLevel->getName();
                    }
                }
//                /** @noinspection PhpUndefinedFieldInspection */
//                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
//                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
//                    /** @noinspection PhpUndefinedFieldInspection */
//                    $DataPerson['Address'] = $tblAddress->getGuiString();
//                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                if (!isset($DataPerson['ProspectYear'])) {
                    $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                }
                if (!isset($DataPerson['ProspectDivision'])) {
                    $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                        $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        return $SearchResult;
    }
}