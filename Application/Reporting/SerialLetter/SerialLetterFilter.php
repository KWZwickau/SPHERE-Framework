<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Group\Group as GroupCompany;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Extension\Extension;

class SerialLetterFilter extends Extension
{
    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $Filter
     *
     * @return array|false
     */
    public function getGroupFilterResultListBySerialLetter(TblSerialLetter $tblSerialLetter = null, array $Filter = array())
    {
        $FilterGroupList = array();
        $PostFilterList = array();
        if(!empty($Filter)){
            foreach($Filter as $FieldName => $Value){
                if($Value){
                    if (stristr($FieldName, 'TblGroup_')) {
                        $FilterGroupList[] = $Value;
                    }
                    if (stristr($FieldName, 'TblPerson_')) {
                        $PostFilterList[$FieldName] = $Value;
                    }
                }
            }
            if(!empty($PostFilterList) && empty($FilterGroupList)){
                // Person unter allen Personengruppen suchen, wenn keine Eingrenzung angegeben ist
                $FilterGroupList[] = 1;
            }
        } else {
            $tblFilterFieldList = ( $tblSerialLetter != null
                ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
                : false );
            if ($tblFilterFieldList) {
                /** @var TblFilterField $tblFilterField */
                foreach ($tblFilterFieldList as $tblFilterField) {
                    if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                        $FilterGroupList[] = $tblFilterField->getValue();
                    }
                }
            }
        }

        $PersonIdList = array();
        if (!empty($FilterGroupList)) {
            foreach($FilterGroupList as $GroupId){
                if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
                    $PersonIdTemp = Group::useService()->fetchIdPersonAllByGroup($tblGroup);
                    if(!empty($PersonIdTemp)){
                        $PersonIdList = array_merge($PersonIdList, $PersonIdTemp);
                    }
                }
            }
        }
        $PersonIdList = array_unique($PersonIdList);

        $TableContent = array();
        if(!empty($PersonIdList)){
            $AddressTest = Address::useService()->fetchAddressAllByPersonIdList($PersonIdList);
            $PersonTest = Person::useService()->getPersonArrayByIdList($PersonIdList);
            foreach($PersonTest as $PersonId => $PersonRow){
                $Address = '';
                if(isset($AddressTest[$PersonId])){
                    $AddressRow = $AddressTest[$PersonId];
                    $Street = $AddressRow['StreetName'];
                    $StreetNumber = $AddressRow['StreetNumber'];
                    $Code = $AddressRow['Code'];
                    $City = $AddressRow['Name'];
                    $District = $AddressRow['District'];
                    $Address = $Code.' '.$City.($District ? ' '.$District: '').', '.$Street.' '.$StreetNumber;
                }
                $add = true;
                if(!empty($PostFilterList)){
                    foreach($PostFilterList as $FieldName => $Value){
                        if($Value){
                            if(($tblPerson = Person::useService()->getPersonById($PersonId))){
                                if($FieldName == 'TblPerson_FirstName'){
                                    if(strtolower($tblPerson->getFirstName()) != strtolower($Value))
                                        $add = false;
                                }
                                if($FieldName == 'TblPerson_LastName'){
                                    if(strtolower($tblPerson->getLastName()) != strtolower($Value))
                                        $add = false;
                                }
                            }
                        }
                    }
                }
                if($add){
                    $TableContent[] = array(
                        'PersonId'   => $PersonId,
                        'Salutation' => $PersonRow['Salutation'],
                        'Name'       => $PersonRow['LastName'].', '.$PersonRow['FirstName'].' '.$PersonRow['SecondName'].' ',
                        'Address'    => $Address,
                    );
                }
            }
        }
        return $TableContent;
    }

    public function getGroupTableByResult($Id, $TableResult = array())
    {

        $TableContent = array();
        if(empty($TableResult)){
           return $TableContent;
        }
        foreach($TableResult as $row){
            $item = array();
            $item['Exchange'] = (new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'PersonId' => $row['PersonId']
            )));
            $item['PersonId'] = $row['PersonId'];
            $item['Salutation'] = $row['Salutation'];
            $item['Name'] = $row['Name'];
            $item['Address'] = $row['Address'];
            $item['Division'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
            $item['StudentNumber'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
            if(($tblPerson = Person::useService()->getPersonById($row['PersonId']))){
                if(($tblYearList = Term::useService()->getYearByNow())) {
                    $VisitedDivision = '';
                    foreach($tblYearList as $tblYear){
                        if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                            if(($tblDivisionCourseD = $tblStudentEducation->getTblDivision())){
                                $VisitedDivision .= new Center(new Small(new Muted('Kl: '.$tblDivisionCourseD->getDisplayName())));
                            }
                            if(($tblDivisionCourseC = $tblStudentEducation->getTblCoreGroup())){
                                $VisitedDivision .= new Center(new Small(new Muted('St: '.$tblDivisionCourseC->getDisplayName())));
                            }
                        }
                    }
                    if($VisitedDivision) {
                        $item['Division'] = $VisitedDivision;
                    }
                }
                if(($tblStudent = $tblPerson->getStudent())
                && ($StudentNumber = $tblStudent->getIdentifierComplete())){
                    $item['StudentNumber'] = new Center($StudentNumber);
                }
            }
            $TableContent[] = $item;
        }
        return $TableContent;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $Filter
     *
     * @return array|false
     */
    public function getStudentFilterResultListBySerialLetter(TblSerialLetter $tblSerialLetter, array $Filter = array())
    {

        $TableResult = array();

        $FilterList = array();
        if(!empty($Filter)){
            // Filterung bei einfachem Filter
            foreach($Filter as $FilterName => $Value){
                $FilterList[0][$FilterName] = $Value;
            }
        } else {
            // Filterung aus DB
            $tblFilterFieldList = ( $tblSerialLetter != null
                ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
                : false );
            if ($tblFilterFieldList) {
                /** @var TblFilterField $tblFilterField */
                foreach($tblFilterFieldList as $tblFilterField) {
                    $FilterList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        if(!empty($FilterList)){
            foreach($FilterList as $FilterRow){
                $FilterResult = DivisionCourse::useService()->fetchIdPersonByFilter($FilterRow);
                $TableResult = array_merge($TableResult, $FilterResult);
            }
        }
        return ( !empty($TableResult) ? $TableResult : false );
    }

    /**
     * @param $Id
     * @param $TableResult
//     * @param $Filter
     *
     * @return array
     */
    public function getStudentTableByResult($Id, $TableResult) // , $Filter = array()
    {
        $TableContent = array();
        if(empty($TableResult)){
            return $TableContent;
        }
        foreach($TableResult as $row)
        {
            if(!($tblPerson = Person::useService()->getPersonById($row['PersonId']))){
                continue;
            }
            // Filter vorerst entfernt
//            if(isset($Filter['FirstName']) && $Filter['FirstName'] != ''){
//                if($tblPerson != $tblPerson->getFirstName()){
//                    continue;
//                }
//            }
//            if(isset($Filter['LastName']) && $Filter['LastName'] != ''){
//                if($tblPerson != $tblPerson->getLastName()){
//                    continue;
//                }
//            }

            $item = array();
            $item['Exchange'] = '';
            if($Id){
                $item['Exchange'] = (new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                    'Id'       => $Id,
                    'PersonId' => $row['PersonId']
                )));
            }
            $tblPerson = Person::useService()->getPersonById($row['PersonId']);
            $item['Salutation'] = $tblPerson->getSalutation();
            $item['Name'] = $tblPerson->getLastFirstName();
            $item['Address'] = '';
            if(($tblAddress = $tblPerson->fetchMainAddress())){
                $item['Address'] = $tblAddress->getGuiString();
            }
            $item['Year'] = '';
            if(($tblYear = Term::useService()->getYearById($row['YearId']))){
                $item['Year'] = new Center($tblYear->getDisplayName());
            }
            $item['Level'] = new Center($row['Level']);
            $item['Division'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
            $item['DivisionAndCore'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
            $item['StudentNumber'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
            if(($tblStudent = $tblPerson->getStudent())
            && ($StudentNumber = $tblStudent->getIdentifierComplete())){
                $item['StudentNumber'] = new Center($StudentNumber);
            }
            if(($tblYearList = Term::useService()->getYearByNow())) {
                $VisitedDivision = '';
                foreach($tblYearList as $tblYear){
                    if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                        if(($tblDivisionCourseD = $tblStudentEducation->getTblDivision())){
                            $item['Division'] = new Center($tblDivisionCourseD->getDisplayName());
                            $VisitedDivision .= new Center(new Small(new Muted('Kl: '.$tblDivisionCourseD->getDisplayName())));
                        }
                        if(($tblDivisionCourseC = $tblStudentEducation->getTblCoreGroup())){
                            $VisitedDivision .= new Center(new Small(new Muted('St: '.$tblDivisionCourseC->getDisplayName())));
                        }
                    }
                }
                if($VisitedDivision){
                    $item['DivisionAndCore'] = $VisitedDivision;
                }
            }
            $TableContent[] = $item;
        }
        if(!empty($TableContent)){
            foreach($TableContent as $Key => $row) {
                $Level[$Key] = strtoupper($row['Level']);
                $Name[$Key] = strtoupper($row['Name']);
            }
            array_multisort($Level, SORT_ASC, $Name, SORT_ASC, $TableContent);
        }

        return $TableContent;
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     *
     * @return array|bool
     */
    public function getProspectFilterResultListBySerialLetter(TblSerialLetter $tblSerialLetter = null, array $Filter = array())
    {

        $TableResult = array();
        $FilterList = array();
        if(!empty($Filter)){
            // Filterung bei einfachem Filter
            foreach($Filter as $FilterName => $Value){
                $FilterList[0][$FilterName] = $Value;
            }
        } else {
            // Filterung aus DB
            $tblFilterFieldList = ( $tblSerialLetter != null
                ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
                : false );
            if ($tblFilterFieldList) {
                /** @var TblFilterField $tblFilterField */
                foreach($tblFilterFieldList as $tblFilterField) {
                    $FilterList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        // Ergebnisse für jede Filternummer einzeln
        foreach($FilterList as $FilterRow){
            $FilterResult = Prospect::useService()->fetchIdPersonByFilter($FilterRow);
            $TableResult = array_merge($TableResult, $FilterResult);
        }

        return ( !empty($TableResult) ? $TableResult : false );
    }

    /**
     * @param $Result
     *
     * @return array
     */
    public function getProspectTableByResult($Id, $TableResult)
    {

        $TableContent = array();
        if(empty($TableResult)){
            return $TableContent;
        }
        foreach($TableResult as $row)
        {
            $item = array();
            $item['Exchange'] = '';
            if($Id){
                $item['Exchange'] = (new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                    'Id'       => $Id,
                    'PersonId' => $row['PersonId']
                )));
            }
            $tblPerson = Person::useService()->getPersonById($row['PersonId']);
            $item['Salutation'] = $tblPerson->getSalutation();
            $item['Name'] = $tblPerson->getLastFirstName();
            $item['Address'] = '';
            if(($tblAddress = $tblPerson->fetchMainAddress())){
                $item['Address'] = $tblAddress->getGuiString();
            }
            $item['ReservationDate'] = $item['InterviewDate'] = $item['TrialDate'] = '';
            if($row['ReservationDate']){
                $item['ReservationDate'] = $row['ReservationDate']->format('d.m.Y');
            }
            if($row['InterviewDate']){
                $item['InterviewDate'] = $row['InterviewDate']->format('d.m.Y');
            }
            if($row['TrialDate']){
                $item['TrialDate'] = $row['TrialDate']->format('d.m.Y');
            }

            $item['ReservationYear'] = new Center($row['ReservationYear']);
            $item['ReservationDivision'] = new Center($row['ReservationDivision']);
            $item['ReservationOptionA'] = $item['ReservationOptionB'] = '';
            if($row['ReservationOptionA']
                && ($tblSchoolType = Type::useService()->getTypeById($row['ReservationOptionA']))){
                $item['ReservationOptionA'] = $tblSchoolType->getName();
            }
            if($row['ReservationOptionB']
                && ($tblSchoolType = Type::useService()->getTypeById($row['ReservationOptionB']))){
                $item['ReservationOptionB'] = $tblSchoolType->getName();
            }
            $TableContent[] = $item;
        }
        if(!empty($TableContent)){
            foreach($TableContent as $Key => $row) {
                $Year[$Key] = $row['ReservationYear'];
                $Division[$Key] = strtoupper($row['ReservationDivision']);
                $Name[$Key] = strtoupper($row['Name']);
            }
            array_multisort($Year, SORT_ASC, $Division, SORT_ASC, $Name, SORT_ASC, $TableContent);
        }
        return $TableContent;
    }

    /**
 * @param TblSerialLetter|null $tblSerialLetter
 * @param array                $Result
 *
 * @return array|bool TblPerson[]
 */
    public function getPersonListByResult(TblSerialLetter $tblSerialLetter = null, $Result = array())
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
                    $DataList[$DataCompany['TblCompany_Id']][] = ($DataPerson['TblPerson_Id'] ? $DataPerson['TblPerson_Id'] : null);
                }
            }
        }
        return ( !empty($DataList) ? $DataList : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     *
     * @return array|bool
     */
    public function getCompanyFilterResultListBySerialLetter(TblSerialLetter $tblSerialLetter = null)
    {

        $TableContent = array();
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                $FilterList[$tblFilterField->getFilterNumber()][$tblFilterField->getField()] = $tblFilterField->getValue();
            }
            // Ergebnisse für jede Filternummer einzeln
            $CompanyPersonList = array();
            foreach($FilterList as $FilterRow){
                $CompanyResult = Company::useService()->fetchIpCompanyByFilter($FilterRow);
                if(($CompanyGroupResult = GroupCompany::useService()->fetchIdCompanyByFilter($FilterRow, $CompanyResult))){
                    foreach($CompanyGroupResult as $CompanyId){
                        if(($tblCompany = Company::useService()->getCompanyById($CompanyId))){
                            $isFilter = false;
                            $tblType = false;
                            foreach($FilterRow as $FilterName => $FilterValue){
                                if($FilterValue != null){
                                    if($FilterName == 'TblType_Id'){
                                        if(($tblType = Relationship::useService()->getTypeById($FilterValue))){
                                            $isFilter = true;
                                        }
                                    }
                                }
                            }
                            if($isFilter && $tblType){
                                $tblToCompanyList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany, $tblType);
                            } else {
                                $tblToCompanyList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                            }
                            if($tblToCompanyList){
                                foreach($tblToCompanyList as $tblToCompany){
                                    if($tblPerson = $tblToCompany->getServiceTblPerson()){
                                        $CompanyPersonList[$CompanyId.'_'.$tblPerson->getId()] = array('CompanyId' => $CompanyId, 'PersonId' => $tblPerson->getId());
                                    }
                                }
                                // ignore company's without TypeMatch
                            } elseif(!$tblType) {
                                $CompanyPersonList[$CompanyId.'_x'] = array('CompanyId' => $CompanyId, 'PersonId' => null);
                            }
                        }
                    }
                }
                $TableContent = array_merge($TableContent, $CompanyPersonList);
            }
        }

        return ( !empty($TableContent) ? $TableContent : false );
    }

    /**
     * @param $TableResult
     *
     * @return array
     */
    public function getCompanyTableByResult($TableResult)
    {

        $TableSearch = array();
        if (!empty($TableResult)) {
            foreach ($TableResult as $Index => $row) {
                $item = array();
                $tblCompany = Company::useService()->getCompanyById($row['CompanyId']);
                $tblPerson = Person::useService()->getPersonById($row['PersonId']);
                // sort empty name to the end (TableData)
                $item['Name'] = '<span hidden>zzz</span>';
                $item['Salutation'] = new Small(new Muted('-NA-'));
                $item['CompanyName'] = '';
                $item['CompanyExtendedName'] = '';
                $item['Type'] = '';
                $item['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if ($tblPerson) {
                    $item['Name'] = $tblPerson->getLastFirstName();
                    $item['Salutation'] = ($tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')));
                }
                if ($tblCompany) {
                    $item['CompanyName'] = $tblCompany->getName();
                    $item['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    $tblRelationshipList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            $tblPersonR = $tblRelationship->getServiceTblPerson();
                            if ($tblPersonR && $tblPerson &&  $tblPersonR->getId() === $tblPerson->getId()) {
                                if ($tblRelationship->getTblType()) {
                                    $item['Type'] = $tblRelationship->getTblType()->getName();
                                }
                            }
                        }
                    }
                    if (($tblAddress = Address::useService()->getAddressByCompany($tblCompany))) {
                        $item['Address'] = $tblAddress->getGuiString();
                    }
                }
                $TableSearch[] = $item;
            }
        }
        return $TableSearch;
    }
}