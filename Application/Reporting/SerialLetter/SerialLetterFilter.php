<?php
namespace SPHERE\Application\Reporting\SerialLetter;


use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;

class SerialLetterFilter
{

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroupList
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getGroupFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroupList = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroupList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        $ResultList = array();

        //Filter Group
        if (isset($FilterGroupList) && !empty($FilterGroupList)
        ) {
            foreach ($FilterGroupList as $FilterNumber => $FilterGroup) {
                // Database Join with foreign Key
                $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
                $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                    null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
                );
                $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                    ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
                );

                if ($FilterGroup) {
                    // Preparation FilterGroup
                    array_walk($FilterGroup, function (&$Input) {

                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterGroup = array_filter($FilterGroup);
                } else {
                    $FilterGroup = array();
                }
                // Preparation FilterPerson
                $FilterPerson = array();

                $Result = $Pile->searchPile(array(
                    0 => $FilterGroup,
                    1 => $FilterPerson
                ));
                // get Timeout status
                $IsTimeout = $Pile->isTimeout();

                // get all Results
                $ResultList = array_merge($Result, $ResultList);
            }
        }

        return ( !empty($ResultList) ? $ResultList : false );
    }

    /**
     * @param $Result
     *
     * @return array
     */
    public function getGroupTableByResult($Result)
    {
        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewPerson[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroupList
     * @param array                $FilterStudentList
     * @param array                $FilterYearList
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getStudentFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroupList = array(),
        $FilterStudentList = array(),
        $FilterYearList = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroupList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblLevel_')) {
                    $FilterStudentList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblDivision_')) {
                    $FilterStudentList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblYear_')) {
                    $FilterYearList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        $ResultList = array();

        //Filter Group
        if (isset($FilterGroupList) && !empty($FilterGroupList)) {
            foreach ($FilterGroupList as $FilterNumber => $FilterGroup) {
                // Database Join with foreign Key
                $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
                $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                    null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
                );
                $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                    ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
                );
                $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
                    ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
                );
                $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
                    ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
                );

                if ($FilterGroup) {
                    // Preparation FilterGroup
                    array_walk($FilterGroup, function (&$Input) {

                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterGroup = array_filter($FilterGroup);
                } else {
                    $FilterGroup = array();
                }
                // Preparation FilterPerson
                $FilterPerson = array();

                // Preparation $FilterStudent
                if (isset($FilterStudentList[$FilterNumber])) {
                    array_walk($FilterStudentList[$FilterNumber], function (&$Input) {
                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterStudentList[$FilterNumber] = array_filter($FilterStudentList[$FilterNumber]);
                } else {
                    $FilterStudentList[$FilterNumber] = array();
                }
                // Preparation $FilterYear
                if (isset($FilterYearList[$FilterNumber])) {
                    array_walk($FilterYearList[$FilterNumber], function (&$Input) {
                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterYearList[$FilterNumber] = array_filter($FilterYearList[$FilterNumber]);
                } else {
                    $FilterYearList[$FilterNumber] = array();
                }

                $Result = $Pile->searchPile(array(
                    0 => $FilterGroup,
                    1 => $FilterPerson,
                    2 => $FilterStudentList[$FilterNumber],
                    3 => $FilterYearList[$FilterNumber]
                ));
                // get Timeout status
                $IsTimeout = $Pile->isTimeout();

                $ResultList = array_merge($Result, $ResultList);
            }
        }

        return ( !empty($ResultList) ? $ResultList : false );
    }

    /**
     * @param $Result
     *
     * @return array
     */
    public function getStudentTableByResult($Result)
    {
        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewDivisionStudent[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();

                $DataPerson['DivisionYear'] = new Small(new Muted('Gefiltertes Jahr:')).new Container('-NA-');
                $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                /** @var TblDivisionStudent $tblDivisionStudent */
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                            $DataPerson['DivisionYear'] = new Small(new Muted('Gefiltertes Jahr:')).new Container($tblYear->getName());
                        }
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
                    }
                }

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroupList
     * @param array                $FilterProspectList
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getProspectFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroupList = array(),
        $FilterProspectList = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroupList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblProspectReservation_')) {
                    $FilterProspectList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }

        $ResultList = array();

        //Filter Group
        if (isset($FilterGroupList) && !empty($FilterGroupList)
        ) {
            foreach ($FilterGroupList as $FilterNumber => $FilterGroup) {
                for ($i = 0; $i <= 1; $i++) {
                    if ($i == 1 && isset($FilterProspectList[$FilterNumber]['TblProspectReservation_serviceTblTypeOptionA'])) {
                        // change OptionA to Option B
                        $FilterProspectList[$FilterNumber]['TblProspectReservation_serviceTblTypeOptionB'] =
                            $FilterProspectList[$FilterNumber]['TblProspectReservation_serviceTblTypeOptionA'];
                        unset($FilterProspectList[$FilterNumber]['TblProspectReservation_serviceTblTypeOptionA']);
                    }
                    // Database Join with foreign Key
                    $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
                    $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                        null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
                    );
                    $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                        ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
                    );
                    $Pile->addPile(( new ViewPeopleMetaProspect() )->getViewService(), new ViewPeopleMetaProspect(),
                        ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON, ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON
                    );

                    if ($FilterGroup) {
                        // Preparation FilterGroup
                        array_walk($FilterGroup, function (&$Input) {

                            if (!is_array($Input)) {
                                if (!empty($Input)) {
                                    $Input = explode(' ', $Input);
                                    $Input = array_filter($Input);
                                } else {
                                    $Input = false;
                                }
                            }
                        });
                        $FilterGroup = array_filter($FilterGroup);
                    } else {
                        $FilterGroup = array();
                    }
                    // Preparation FilterPerson
                    $FilterPerson = array();

                    // Preparation FilterProspect
                    if (isset($FilterProspectList[$FilterNumber])) {
                        array_walk($FilterProspectList[$FilterNumber], function (&$Input) {
                            if (!is_array($Input)) {
                                if (!empty($Input)) {
                                    $Input = explode(' ', $Input);
                                    $Input = array_filter($Input);
                                } else {
                                    $Input = false;
                                }
                            }
                        });
                        $FilterProspectList[$FilterNumber] = array_filter($FilterProspectList[$FilterNumber]);
                    } else {
                        $FilterProspectList[$FilterNumber] = array();
                    }
                    // Filter first time
                    $Result = $Pile->searchPile(array(
                        0 => $FilterGroup,
                        1 => $FilterPerson,
                        2 => $FilterProspectList[$FilterNumber]
                    ));
                    // get Timeout status
                    $IsTimeout = $Pile->isTimeout();

                    $ResultList = array_merge($Result, $ResultList);
                }
            }
        }

        return ( !empty($ResultList) ? $ResultList : false );
    }

    /**
     * @param $Result
     *
     * @return array
     */
    public function getProspectTableByResult($Result)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewPerson[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));
                $DataPerson['ReservationDate'] = '';
                $DataPerson['InterviewDate'] = '';
                $DataPerson['TrialDate'] = '';
                $DataPerson['ReservationYear'] = '';
                $DataPerson['ReservationDivision'] = '';
                $DataPerson['ReservationOptionA'] = '';
                $DataPerson['ReservationOptionB'] = '';

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);

                    $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                    if ($tblProspect) {
                        $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                        if ($tblProspectAppointment) {
                            $DataPerson['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $DataPerson['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $DataPerson['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        $tblProspectReservation = $tblProspect->getTblProspectReservation();
                        if ($tblProspectReservation) {
                            $DataPerson['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $DataPerson['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                $DataPerson['ReservationOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                            }
                            if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                $DataPerson['ReservationOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                            }
                        }
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
 * @param TblSerialLetter|null $tblSerialLetter
 * @param                      $Result
 *
 * @return array|bool TblPerson[]
 */
    public function getPersonListByResult(TblSerialLetter $tblSerialLetter = null, $Result)
    {
        $tblCategory = false;
        if ($tblSerialLetter !== null) {
            $tblCategory = $tblSerialLetter->getFilterCategory();
        }

        $PersonList = array();
        $PersonIdList = array();
        if ($Result && !empty($Result)) {
            if (!$tblCategory
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT
            ) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[1]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            } elseif ($tblCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[3]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            }

            if (!empty($PersonIdList)) {
                foreach ($PersonIdList as $PersonId) {
                    $PersonList[] = Person::useService()->getPersonById($PersonId);
                }
            }
        }
        return ( !empty($PersonList) ? $PersonList : false );
    }

    /**
     * @param $Result
     *
     * @return array|bool
     * @throws \Exception
     */
    public function getCompanyListByResult($Result)
    {

        $DataList = array();
        $PersonList = array();
        $CompanyList = array();
        if ($Result && !empty($Result)) {
            /** @var AbstractView[]|ViewCompany[]|ViewPerson[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataCompany = $Row[1]->__toArray();
                $DataPerson = $Row[3]->__toArray();
                $IsSave = false;
                if (!array_key_exists($DataCompany['TblCompany_Id'], $CompanyList)) {
                    $CompanyList[$DataCompany['TblCompany_Id']] = $DataCompany['TblCompany_Id'];
                    $IsSave = true;
                }
                if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonList)) {
                    $PersonList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    $IsSave = true;
                }
                if($IsSave){
                    $DataList[$DataCompany['TblCompany_Id']][] = ($DataPerson['TblPerson_Id'] ? $DataPerson['TblPerson_Id'] : '');
                }
            }
        }
        return ( !empty($DataList) ? $DataList : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param                      $Result
     *
     * @return array|bool TblPerson[]
     */
    public function getCompanyPersonListByResult(TblSerialLetter $tblSerialLetter = null, $Result)
    {
        $tblCategory = false;
        if ($tblSerialLetter !== null) {
            $tblCategory = $tblSerialLetter->getFilterCategory();
        }

        $PersonList = array();
        $PersonIdList = array();
        if ($Result && !empty($Result)) {
            if (!$tblCategory
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT
            ) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[1]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            } elseif ($tblCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[3]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            }

            if (!empty($PersonIdList)) {
                foreach ($PersonIdList as $PersonId) {
                    $PersonList[] = Person::useService()->getPersonById($PersonId);
                }
            }
        }
        return ( !empty($PersonList) ? $PersonList : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroupList
     * @param array                $FilterCompanyList
     * @param array                $FilterRelationshipList
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getCompanyFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroupList = array(),
        $FilterCompanyList = array(),
        $FilterRelationshipList = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroupList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblCompany_')) {
                    $FilterCompanyList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblType_')) {
                    $FilterRelationshipList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }

        $ResultList = array();

        //Filter Group
        if (isset($FilterGroupList) && !empty($FilterGroupList)
        ) {
            foreach ($FilterGroupList as $FilterNumber => $FilterGroup) {
                // Database Join with foreign Key
                $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
                $Pile->addPile(( new ViewCompanyGroupMember() )->getViewService(), new ViewCompanyGroupMember(),
                    null, ViewCompanyGroupMember::TBL_MEMBER_SERVICE_TBL_COMPANY
                );
                $Pile->addPile(( new ViewCompany() )->getViewService(), new ViewCompany(),
                    ViewCompany::TBL_COMPANY_ID, ViewCompany::TBL_COMPANY_ID
                );
                $Pile->addPile(( new ViewRelationshipToCompany() )->getViewService(), new ViewRelationshipToCompany(),
                    ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_PERSON
                );
                $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                    ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
                );

                if ($FilterGroup) {
                    // Preparation FilterGroup
                    array_walk($FilterGroup, function (&$Input) {

                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterGroup = array_filter($FilterGroup);
                } else {
                    $FilterGroup = array();
                }
                // Preparation FilterCompany
                if (isset($FilterCompanyList[$FilterNumber])) {
                    array_walk($FilterCompanyList[$FilterNumber], function (&$Input) {
                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterCompanyList[$FilterNumber] = array_filter($FilterCompanyList[$FilterNumber]);
                } else {
                    $FilterCompanyList[$FilterNumber] = array();
                }
                // Preparation FilterRelationship
                if (isset($FilterRelationshipList[$FilterNumber])) {
                    array_walk($FilterRelationshipList[$FilterNumber], function (&$Input) {
                        if (!is_array($Input)) {
                            if (!empty($Input)) {
                                $Input = explode(' ', $Input);
                                $Input = array_filter($Input);
                            } else {
                                $Input = false;
                            }
                        }
                    });
                    $FilterRelationshipList[$FilterNumber] = array_filter($FilterRelationshipList[$FilterNumber]);
                } else {
                    $FilterRelationshipList[$FilterNumber] = array();
                }
                // Preparation FilterPerson
                $FilterPerson = array();

                $Result = $Pile->searchPile(array(
                    0 => $FilterGroup,
                    1 => $FilterCompanyList[$FilterNumber],
                    2 => $FilterRelationshipList[$FilterNumber],
                    3 => $FilterPerson
                ));
                // get Timeout status
                $IsTimeout = $Pile->isTimeout();

                $ResultList = array_merge($Result, $ResultList);
            }
        }

        return ( !empty($ResultList) ? $ResultList : false );
    }

    /**
     * @param $Result
     *
     * @return array
     */
    public function getCompanyTableByResult($Result)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewCompany[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataCompany = $Row[1]->__toArray();
                $DataPerson = $Row[3]->__toArray();

                $tblCompany = Company::useService()->getCompanyById($DataCompany['TblCompany_Id']);
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByCompany($tblCompany);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                $DataPerson['CompanyName'] = '';
                $DataPerson['CompanyExtendedName'] = '';
                $DataPerson['Type'] = '';
                if ($tblCompany) {
                    $DataPerson['CompanyName'] = $tblCompany->getName();
                    $DataPerson['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    $tblRelationshipList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                    if ($tblRelationshipList) {
                        /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany $tblRelationship */
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getServiceTblPerson()->getId() === $tblPerson->getId()) {
                                if ($tblRelationship->getTblType()) {
                                    $DataPerson['Type'] = $tblRelationship->getTblType()->getName();
                                }
                            }
                        }
                    }
                }


                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }
}