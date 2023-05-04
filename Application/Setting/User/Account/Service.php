<?php
namespace SPHERE\Application\Setting\User\Account;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Api\Contact\ApiContactAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\User\Account\Service\Data;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Application\Setting\User\Account\Service\Setup;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblUserAccount
     */
    public function getUserAccountById($Id)
    {

        return ( new Data($this->getBinding()) )->getUserAccountById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getUserAccountByPerson($tblPerson);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getUserAccountByAccount($tblAccount);
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return ( new Data($this->getBinding()) )->getUserAccountAll();
    }

    /**
     * @param string $Type
     *
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAllByType($Type)
    {

        return (new Data($this->getBinding()))->getUserAccountAllByType($Type);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByTime(DateTime $dateTime)
    {

        return (new Data($this->getBinding()))->getUserAccountByTime($dateTime);
    }

    /**
     * @param DateTime $groupByTime
     * @param DateTime $exportDate
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByLastExport(DateTime $groupByTime, DateTime $exportDate)
    {

        return (new Data($this->getBinding()))->getUserAccountByLastExport($groupByTime, $exportDate);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return false|array(TblUserAccount[])
     */
    public function getUserAccountByTimeGroupLimitList(DateTime $dateTime)
    {

        $tblUserAccountList = (new Data($this->getBinding()))->getUserAccountByTime($dateTime);
        $UserAccountArray = false;
        if($tblUserAccountList){
            foreach($tblUserAccountList as $tblUserAccount){
                $UserAccountArray[$tblUserAccount->getGroupByCount()][] = $tblUserAccount;
            }
        }
        return $UserAccountArray;

    }

    /**
     * @param DateTime $dateTime
     * @param int       $groupCount
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByTimeAndCount(DateTime $dateTime, $groupCount)
    {

        return (new Data($this->getBinding()))->getUserAccountByTimeAndCount($dateTime, $groupCount);
    }

    /**
     * @param false|array $tblUserAccountAll
     *
     * @return array|bool result[GroupByTime][]
     * result[GroupByTime][]
     */
    public function getGroupOfUserAccountList($tblUserAccountAll)
    {

        $result = array();
        if ($tblUserAccountAll && !empty($tblUserAccountAll)) {
            foreach ($tblUserAccountAll as $tblUserAccount) {
                $result[$tblUserAccount->getGroupByTime()][] = $tblUserAccount;
            }
        }
        return !empty($result) ? $result : false;
    }

    /**
     * @param $Data
     *
     * @return array|TblStudentEducation[]
     */
    public function getStudentFilterResult($Data):array
    {

        if(empty($Data) || !($tblYear = Term::useService()->getYearById($Data['Year']))){
            return array();
        }

        $tblSchoolType = null;
        if(isset($Data['SchoolType'])){
            if(!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))){
                $tblSchoolType = null;
            }
        }
        $level = null;
        if(isset($Data['Level'])){
            $level = $Data['Level'];
        }
        $tblDivisionCourse = null;
        if(isset($Data['DivisionCourse'])){
            if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Data['DivisionCourse']))){
                $tblDivisionCourse = null;
            }
        }
        if(!($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, $level, $tblDivisionCourse))){
            $tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, $level, null, $tblDivisionCourse);
        }
        if($tblStudentEducationList){
            return $tblStudentEducationList;
        }

        return array();
    }

    /**
     * @param array|TblStudentEducation[] $tblStudentEducationList
     * @param int                         $MaxResult
     *
     * @return array
     */
    public function getStudentTableContent(array $tblStudentEducationList, int $MaxResult):array
    {

        $SearchResult = array();
        if(empty($tblStudentEducationList)) {
            return $SearchResult;
        }
        foreach ($tblStudentEducationList as $tblStudentEducation) {
            $DataPerson['TblPerson_Id'] = '';
            $DataPerson['Name'] = '';
            $DataPerson['Address'] = '';
            $DataPerson['Option'] = '';
            $DataPerson['Check'] = '';
            $DataPerson['DivisionCourseD'] = '';
            $DataPerson['DivisionCourseC'] = '';
            $DataPerson['Level'] = $tblStudentEducation->getLevel();
            $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
            $DataPerson['ProspectYear'] = '';
            $DataPerson['ProspectDivision'] = '';
            if(($tblPerson = $tblStudentEducation->getServiceTblPerson())
                && !AccountAuthorization::useService()->getAccountAllByPerson($tblPerson)) {
                $DataPerson['TblPerson_Id'] = $tblPerson->getId();
                $DataPerson['Name'] = $tblPerson->getLastFirstName();
                $DataPerson['Address'] = $this->apiChangeMainAddressField($tblPerson);
                $DataPerson['Option'] = $this->apiChangeMainAddressButton($tblPerson);
                $DataPerson['Check'] = '<span hidden>'.$tblPerson->getLastFirstName().'</span>'
                    .(new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ', $tblPerson->getId(),
                        array($tblPerson->getId())))->setChecked();
                if(($tblCourse = $tblStudentEducation->getServiceTblCourse())) {
                    $DataPerson['Course'] = $tblCourse->getName();
                }
                if($tblDivisionCourseD = $tblStudentEducation->getTblDivision()) {
                    $DataPerson['DivisionCourseD'] = $tblDivisionCourseD->getDisplayName();
                }
                if($tblDivisionCourseC = $tblStudentEducation->getTblCoreGroup()) {
                    $DataPerson['DivisionCourseC'] = $tblDivisionCourseC->getDisplayName();
                }
            }
            if(isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();
            }
            if(!isset($DataPerson['ProspectYear'])) {
                $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
            }
            if(!isset($DataPerson['ProspectDivision'])) {
                $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
            }
            // nur Personen ohne Account ($DataPerson['Name'])
            if($tblPerson && $DataPerson['Name']) {
                $SearchResult[$tblPerson->getId()] = $DataPerson;
            }
        }
        // PHP 7.4 sort twoDimensional sorting
        usort($SearchResult, fn($a, $b) => $a['Name'] <=> $b['Name']);
        // return maximal possible Output
        return array_slice($SearchResult, 0, $MaxResult);
    }

    /**
     * @param array|TblStudentEducation[] $tblStudentEducationList
     * @param int   $MaxResult
     * @param null  $TypeId
     *
     * @return array
     */
    public function getCustodyTableContent(array $tblStudentEducationList, $MaxResult = 800, $TypeId = null)
    {

        $SearchResult = array();
        if (empty($tblStudentEducationList)) {
            return $SearchResult;
        }
        $DivisionList = array();
        foreach ($tblStudentEducationList as $tblStudentEducation) {
            if(($tblPersonStudent = $tblStudentEducation->getServiceTblPerson())) {
                if(($tblDivisionCourseD = $tblStudentEducation->getTblDivision())){
                    $DivisionList[$tblDivisionCourseD->getId()][] = $tblPersonStudent;
                } else {
                    $DivisionList['0'][] = $tblPersonStudent;
                }
            }
        }
        if($TypeId && ($tblType = Relationship::useService()->getTypeById($TypeId))){
            $tblTypeList[] = $tblType;
        } else {
            $tblTypeList = $this->getRelationshipList();
        }
        if(!empty($DivisionList)) {
            foreach ($DivisionList as $DivisionId => $tblPersonStudentList) {
                foreach($tblPersonStudentList as $tblPersonStudent){
                    foreach ($tblTypeList as $tblType) {
                        if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonStudent, $tblType))) {
                            foreach ($tblToPersonList as $tblToPerson) {
                                $tblPerson = $tblToPerson->getServiceTblPersonFrom();
                                // Person noch nicht gefunden
                                if (!array_key_exists($tblPerson->getId(), $SearchResult)){
                                    // nur Personen ohne Account
                                    if($tblPerson && $tblToPerson->getTblType() && $tblToPerson->getTblType()->getId() == $tblType->getId()
                                        && !AccountAuthorization::useService()->getAccountAllByPerson($tblPerson)) {
                                        $DataPerson['Name'] = $tblPerson->getLastFirstName();
                                        // Gibt Person und Schüler als Id zurück (12_13) dient für die Kassenfilterung auf Sorgeberechtigte
                                        $DataPerson['Check'] = '<span hidden>'.$tblPerson->getLastFirstName().'</span>'
                                            .(new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ', $tblPerson->getId().'_'.$DivisionId,
                                                array($tblPerson->getId())))->setChecked();
                                        $DataPerson['Type'] = $tblType->getName();
                                        $DataPerson['Address'] = $this->apiChangeMainAddressField($tblPerson);
                                        $DataPerson['Option'] = $this->apiChangeMainAddressButton($tblPerson);
                                        $SearchResult[$tblPerson->getId()] = $DataPerson;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // PHP 7.4 sort twoDimensional sorting
        usort($SearchResult, fn($a, $b) => $a['Name'] <=> $b['Name']);
        // return maximal possible Output
        return array_slice($SearchResult, 0, $MaxResult);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return BlockReceiver
     */
    public function apiChangeMainAddressField(TblPerson $tblPerson):BlockReceiver
    {

        return ApiContactAddress::receiverColumn($tblPerson->getId());
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string|Standard
     */
    public function apiChangeMainAddressButton(TblPerson $tblPerson):string
    {
        $Button = (new Standard('', ApiContactAddress::getEndpoint(), new Edit(), array(),
            'Bearbeiten der Hauptadresse'))
            ->ajaxPipelineOnClick(ApiContactAddress::pipelineOpen($tblPerson->getId()));

        return $Button;
    }

    /**
     * @return TblType[]
     */
    public function getRelationshipList()
    {

        $TypeList = array();
        $TypeNameList = array(
            TblType::IDENTIFIER_GUARDIAN,       // Sorgeberechtigt
            TblType::IDENTIFIER_AUTHORIZED,     // Bevollmächtigt
            TblType::IDENTIFIER_GUARDIAN_SHIP   // Vormund
        );
        foreach($TypeNameList as $TypeName){
            if(($tblType = Relationship::useService()->getTypeByName($TypeName))){
                $TypeList[] = $tblType;
            }
        }
        return $TypeList;
    }

    /**
     * @param IFormInterface $Form
     * @param TblUserAccount $tblUserAccount
     * @param array|null     $Data
     * @param string         $Path
     *
     * @return string|Form
     */
    public function generatePdfControl(IFormInterface $Form, TblUserAccount $tblUserAccount, $Data = null, $Path = '/Setting/User')
    {

        if($Data === null){
            return $Form;
        }

        $changePath = '/Setting/User/Account/Password/Generation';

        $SchoolPanel = new Panel('Schule', array(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Name', 4),
                    new LayoutColumn($Data['CompanyName'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Namenszusatz', 4),
                    new LayoutColumn($Data['CompanyExtendedName'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Ortsteil', 4),
                    new LayoutColumn($Data['CompanyDistrict'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Straße', 4),
                    new LayoutColumn($Data['CompanyStreet'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Ort', 4),
                    new LayoutColumn($Data['CompanyCity'], 8)
                ))
            ))),
        ));

        $ContactPersonPanel = new Panel('Kontakt', array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Telefon: ', 4),
                new LayoutColumn($Data['Phone'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Fax: ', 4),
                new LayoutColumn($Data['Fax'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('E-Mail: ', 4),
                new LayoutColumn($Data['Mail'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Internet: ', 4),
                new LayoutColumn($Data['Web'], 8),
            )))),
        ));
        $SignerPanel = new Panel('Ort/Datum', array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Ort: ', 4),
                new LayoutColumn(($Data['Place'] ? $Data['Place'] : ''), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Datum: ', 4),
                new LayoutColumn(($Data['Date'] ? $Data['Date'] : (new DateTime())->format('d.m.Y')), 8),
            )))),
        ));

        return
        new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $SchoolPanel
                    , 4),
                    new LayoutColumn(
                        $ContactPersonPanel
                    , 4),
                    new LayoutColumn(
                        $SignerPanel
                    , 4),
                )),
                new LayoutRow(
                    new LayoutColumn(array(
                        (new External('Passwort generieren & herunterladen', '\Api\Document\Standard\PasswordChange\Create'
                            , null, array('Data' => $Data), false, External::STYLE_BUTTON_PRIMARY))
                            ->setRedirect($Path, Redirect::TIMEOUT_SUCCESS),
                        new Standard('Nein', $changePath, new Disable(), array('Id' => $tblUserAccount->getId(), 'Path' => $Path))
                        )
                    )
                )
            ))
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsParent
     *
     * @return mixed
     */
    public function getCompanySchoolByPerson(TblPerson $tblPerson, $IsParent = false)
    {

        $tblCompany = false;
        if($IsParent){
            $tblRelationshipType = Relationship::useService()->getTypeByName( TblType::IDENTIFIER_GUARDIAN );
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))){
                foreach($tblRelationshipList as $tblRelationship){  //ToDO Mehrer Schüler auswahl nach "höherer Bildungsgang"
                    if(($tblPersonStudent = $tblRelationship->getServiceTblPersonTo())){
                        if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonStudent))) {
                            $tblCompany = $tblStudentEducation->getServiceTblCompany();
                        }
                    }
                }
            }
        } else {
            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))) {
                $tblCompany = $tblStudentEducation->getServiceTblCompany();
            }
        }
        return $tblCompany;
    }

    /**
     * @param string $Type
     *
     * @return bool|TblUserAccount[]
     */
    public function countUserAccountAllByType($Type)
    {

        return (new Data($this->getBinding()))->countUserAccountAllByType($Type);
    }

    /**
     * @param TblUserAccount[] $tblUserAccountList
     *
     * @return bool|DateTime
     */
    public function getLastExport($tblUserAccountList)
    {
        $BiggestDate = false;
        if(is_array($tblUserAccountList)){
            /** @var TblUserAccount $tblUserAccount */
            foreach($tblUserAccountList as $tblUserAccount){
                if(($Date = $tblUserAccount->getExportDate())) {
                    if(!$BiggestDate || $BiggestDate <= $Date){
                        $BiggestDate = $Date;
                    }
                }
            }
        }
        return $BiggestDate;
    }

    /**
     * @param array $tblUserAccountList
     *
     * @return array
     */
    public function getExcelData($tblUserAccountList = array())
    {

        $result = array();
        if (!empty($tblUserAccountList)) {

            // set flag IsExport
            $this->updateDownloadBulk($tblUserAccountList);

            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$result) {
                $tblPerson = $tblUserAccount->getServiceTblPerson();
                $tblAccount = $tblUserAccount->getServiceTblAccount();

                $item['Salutation'] = '';
                $item['Title'] = '';
                $item['FirstName'] = '';
                $item['SecondName'] = '';
                $item['LastName'] = '';
                $item['Gender'] = '';
                $item['AccountName'] = '';
                $item['Password'] = $tblUserAccount->getUserPassword();
                $item['StreetName'] = '';
                $item['StreetNumber'] = '';
                $item['CityCode'] = '';
                $item['CityName'] = '';
                $item['District'] = '';
                $item['State'] = '';
                $item['Nation'] = '';
                $item['Country'] = '';
                $item['MailPrivate'] = '';
                $item['MailBusiness'] = '';

                if($tblPerson){
                    $item['Salutation'] = $tblPerson->getSalutation();
                    $item['Title'] = $tblPerson->getTitle();
                    $item['FirstName'] = $tblPerson->getFirstName();
                    $item['SecondName'] = $tblPerson->getSecondName();
                    $item['LastName'] = $tblPerson->getLastName();
                    $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                    if ($tblCommon) {
                        $tblBirthDates = $tblCommon->getTblCommonBirthDates();
                        if ($tblBirthDates) {
                            $tblGender = $tblBirthDates->getTblCommonGender();
                            if ($tblGender) {
                                $item['Gender'] = substr($tblGender->getName(), 0, 1);
                            }
                        }
                    }
                    $tblAddress = $tblPerson->fetchMainAddress();
                    if ($tblAddress) {
                        $item['StreetName'] = $tblAddress->getStreetName();
                        $item['StreetNumber'] = $tblAddress->getStreetNumber();
                        $tblCity = $tblAddress->getTblCity();
                        if ($tblCity) {
                            $item['CityCode'] = $tblCity->getCode();
                            $item['CityName'] = $tblCity->getName();
                            $item['District'] = $tblCity->getDistrict();
                        }
                        $tblState = $tblAddress->getTblState();
                        if ($tblState) {
                            $item['State'] = $tblState->getName();
                        }
                        $item['Nation'] = $tblAddress->getNation();
                        $item['Country'] = $tblAddress->getCounty();
                    }

                    if (($tblMailToPersonList = Mail::useService()->getMailAllByPerson($tblPerson))) {
                        $mailPrivateList = array();
                        $mailBusinessList = array();
                        foreach ($tblMailToPersonList as $tblMailToPerson) {
                            if (($tblType = $tblMailToPerson->getTblType())) {
                                switch ($tblType->getName()) {
                                    case 'Privat': $mailPrivateList[] = $tblMailToPerson->getTblMail()->getAddress(); break;
                                    case 'Geschäftlich': $mailBusinessList[] = $tblMailToPerson->getTblMail()->getAddress();
                                }
                            }
                        }

                        $item['MailPrivate'] = implode(';', $mailPrivateList);
                        $item['MailBusiness'] = implode(';', $mailBusinessList);
                    }
                }
                if($tblAccount){
                    $item['AccountName'] = $tblAccount->getUsername();
                }

                array_push($result, $item);
            });
        }
        return $result;
    }

    /**
     * @param array $result
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($result = array())
    {

        if (!empty($result)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Titel");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "2. Vorn.");
            $export->setValue($export->getCell("4", "0"), "Name");
            $export->setValue($export->getCell("5", "0"), "Geschlecht");
            $export->setValue($export->getCell("6", "0"), "Account");
            $export->setValue($export->getCell("7", "0"), "Passwort");
            $export->setValue($export->getCell("8", "0"), "Straße");
            $export->setValue($export->getCell("9", "0"), "Str.Nr.");
            $export->setValue($export->getCell("10", "0"), "PLZ");
            $export->setValue($export->getCell("11", "0"), "Stadt");
            $export->setValue($export->getCell("12", "0"), "Ortsteil");
            $export->setValue($export->getCell("13", "0"), "Bundesland");
            $export->setValue($export->getCell("14", "0"), "Land");
            $export->setValue($export->getCell("15", "0"), "Email privat");
            $export->setValue($export->getCell("16", "0"), "Email geschäftlich");


            $export->setStyle($export->getCell(0, 0), $export->getCell(16, 0))
                ->setFontBold();

            $Row = 0;

            foreach ($result as $Data) {
                $Row++;

                $export->setValue($export->getCell("0", $Row), $Data['Salutation']);
                $export->setValue($export->getCell("1", $Row), $Data['Title']);
                $export->setValue($export->getCell("2", $Row), $Data['FirstName']);
                $export->setValue($export->getCell("3", $Row), $Data['SecondName']);
                $export->setValue($export->getCell("4", $Row), $Data['LastName']);
                $export->setValue($export->getCell("5", $Row), $Data['Gender']);
                $export->setValue($export->getCell("6", $Row), $Data['AccountName']);
                $export->setValue($export->getCell("7", $Row), $Data['Password']);
                $export->setValue($export->getCell("8", $Row), $Data['StreetName']);
                $export->setValue($export->getCell("9", $Row), $Data['StreetNumber']);
                $export->setValue($export->getCell("10", $Row), $Data['CityCode']);
                $export->setValue($export->getCell("11", $Row), $Data['CityName']);
                $export->setValue($export->getCell("12", $Row), $Data['District']);
                $export->setValue($export->getCell("13", $Row), $Data['Nation']);
                $export->setValue($export->getCell("14", $Row), $Data['Country']);
                $export->setValue($export->getCell("15", $Row), $Data['MailPrivate']);
                $export->setValue($export->getCell("16", $Row), $Data['MailBusiness']);
            }

            //Column width
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(9);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(9, 0), $export->getCell(9, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(11, 0), $export->getCell(11, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(12, 0), $export->getCell(12, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(13, 0), $export->getCell(13, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(14, 0), $export->getCell(14, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(15, 0), $export->getCell(14, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(16, 0), $export->getCell(14, $Row))->setColumnWidth(20);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param array          $PersonIdArray
     * @param string         $AccountType S = Student, C = Custody
     *
     * @return array
     * result['Time']
     * result['AddressMissCount']
     * result['AccountExistCount']
     * result['SuccessCount']
     */
    public function createAccount(/*IFormInterface $form, */
        $PersonIdArray = array(),
        $AccountType = 'S'
    )
    {

//        $IsMissingAddress = false;
        $TimeStamp = new DateTime('now');

        $successCount = 0;
        $accountExistCount = 0;
        $accountError = 0;
//        $accountWarning = 0;

        $GroupByCount = 1;
        $CountAccount = 0;

        $tblAccountSession = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getAccountBySession();
        $result = array();
        foreach ($PersonIdArray as $PersonId) {
            if ($CountAccount % 50 == 0
                && $CountAccount != 0) {
                $GroupByCount++;
            }
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if ($tblPerson) {
                // ignore Person with Account
                if (AccountGatekeeper::useService()->getAccountAllByPerson($tblPerson, true)) {
                    continue;
                }
                // Warning if Person without Main Address
                $tblAddress = $tblPerson->fetchMainAddress();
                if (!$tblAddress) {
                    $result['Address'][$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                        ': Hauptadresse fehlt';
//                    $accountWarning++;
//                    continue;
                }
                // ignore without Consumer
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer == '') {
                    continue;
                }
                $name = $this->generateUserName($tblPerson, $tblConsumer, $AccountType, $result);
                if(!$name){
                    $accountError++;
                    continue;
                }
                $password = $this->generatePassword(8, 0, 2, 1);
                if (($tblAccountList = AccountGatekeeper::useService()->getAccountAllByPerson($tblPerson, true))) {
                    $IsUserExist = false;
                    foreach ($tblAccountList as $tblAccount) {
                        // ignore System Accounts (Support)
                        if ($tblAccount->getServiceTblIdentification()->getName() == 'System') {
                            continue;
                        }
                        $IsUserExist = true;
                    }
                    if ($IsUserExist) {
                        $accountExistCount++;
                        continue;
                    }
                }

//                // nur bei Schüler-Accounts den AccountAlias aus Schüler-Mails setzen
//                if ($AccountType == 'S') {
                    if (($accountUserAlias = AccountGatekeeper::useService()->getAccountUserAliasFromMails($tblPerson))) {
                        $errorMessage = '';
                        if (!AccountGatekeeper::useService()->isUserAliasUnique($tblPerson, $accountUserAlias,
                            $errorMessage)
                        ) {
                            $accountUserAlias = false;
                            // Flag an der E-Mail Adresse entfernen
                            Mail::useService()->resetMailWithUserAlias($tblPerson);
                        }
                    }
                    $accountRecoveryMail = AccountGatekeeper::useService()->getAccountRecoveryMailFromMails($tblPerson);
//                } else {
//                    $accountUserAlias = false;
//                    $accountRecoveryMail = false;
//                }

                $tblAccount = AccountGatekeeper::useService()->insertAccount($name, $password, null, $tblConsumer,
                    false, false, $accountUserAlias ? $accountUserAlias : null,
                    $accountRecoveryMail ? $accountRecoveryMail : null);

                if ($tblAccount) {
                    $tblIdentification = AccountGatekeeper::useService()->getIdentificationByName('UserCredential');
                    AccountGatekeeper::useService()->addAccountAuthentication($tblAccount,
                        $tblIdentification);
                    $tblRole = Access::useService()->getRoleByName('Einstellungen: Benutzer (Schüler/Eltern)');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountGatekeeper::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    $tblRole = Access::useService()->getRoleByName('Schüler und Eltern Zugang');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountGatekeeper::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    AccountGatekeeper::useService()->addAccountPerson($tblAccount, $tblPerson);
                    $successCount++;

                    if ($AccountType == 'S') {
                        $Type = TblUserAccount::VALUE_TYPE_STUDENT;
                    } elseif ($AccountType == 'C') {
                        $Type = TblUserAccount::VALUE_TYPE_CUSTODY;
                    } else {
                        // default setting
                        $Type = TblUserAccount::VALUE_TYPE_STUDENT;
                    }
                    // add tblUserAccount
                    if($this->createUserAccount($tblAccount, $tblPerson, $TimeStamp, $password, $Type, $GroupByCount, $tblAccountSession)){
                        $CountAccount++;
                    }
                }
            }
        }

        $result['Time'] = $TimeStamp->format('d.m.Y H:i:s');
        $result['AccountExistCount'] = $accountExistCount;
        $result['SuccessCount'] = $successCount;
        $result['AccountError'] = $accountError;
        $result['AccountWarning'] = (isset($result['Address']) ? count($result['Address']) : 0);
        return $result;
//        return new Layout(
//            new LayoutGroup(
//                new LayoutRow(array(
//                    new LayoutColumn(
//                        ($IsMissingAddress
//                            ? new Warning($errorCount.' Personen ohne Hauptadresse ignoriert ('.$successCount.
//                                ' Benutzerzugänge zu Personen mit Gültiger Hauptadresse wurden erfolgreich angelegt).'
//                                . new Container('Weiter zum '.new Standard('Export', '/Setting/User/Account/Export', null,
//                                    array('Time' => $TimeStamp->format('d.m.Y H:i:s')))))
//                            : new Success($successCount.' Benutzer wurden erfolgreich angelegt.'
//                                . new Container('Weiter zum '.new Standard('Export', '/Setting/User/Account/Export', null,
//                                    array('Time' => $TimeStamp->format('d.m.Y H:i:s')))))
//                        )
//                    ),
////                    new LayoutColumn(
////                        ($AccountType == 'S'
////                            ? new Redirect('/Setting/User/Account/Student/Add',
////                                ($IsMissingAddress
////                                    ? Redirect::TIMEOUT_ERROR
////                                    : Redirect::TIMEOUT_SUCCESS
////                                ))
////                            : ($AccountType == 'C'
////                                ? new Redirect('/Setting/User/Account/Custody/Add',
////                                    ($IsMissingAddress
////                                        ? Redirect::TIMEOUT_ERROR
////                                        : Redirect::TIMEOUT_SUCCESS
////                                    ))
////                                : ''
////                            )
////                        )
////                    )
//                ))
//            )
//        );
    }

    /**
     * @param int $completeLength number all filled up with (abcdefghjkmnpqrstuvwxyz)
     * @param int $specialLength number of (!$%&=?*-:;.,+_)
     * @param int $numberLength number of (123456789)
     * @param int $capitalLetter number of (ABCDEFGHJKMNPQRSTUVWXYZ)
     *
     * @return string
     */
    public function generatePassword($completeLength = 8, $specialLength = 0, $numberLength = 2, $capitalLetter = 1)
    {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $secureCapitalChars = strtoupper($secureChars);
        $return = '';

        $count = $completeLength - $specialLength - $numberLength - $capitalLetter;
        if ($count > 0) {
            // get normal characters
            $temp = str_shuffle($secureChars);
            $return = substr($temp, 0, $count);
        }
        if ($capitalLetter > 0) {
            // get special characters
            $temp = str_shuffle($secureCapitalChars);
            $return .= substr($temp, 0, $capitalLetter);
        }
        if ($specialLength > 0) {
            // get special characters
            $temp = str_shuffle($specialChars);
            $return .= substr($temp, 0, $specialLength);
        }
        if ($numberLength > 0) {
            // get numbers
            $temp = str_shuffle($numberChars);
            $return .= substr($temp, 0, $numberLength);
        }
        // Random
        $return = str_shuffle($return);

        return $return;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblPerson  $tblPerson
     * @param DateTime  $TimeStamp
     * @param string     $userPassword
     * @param string     $Type STUDENT|CUSTODY
     * @param int        $GroupByCount
     * @param TblAccount $tblAccountSession
     *
     * @return false|TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        DateTime $TimeStamp,
        $userPassword,
        $Type,
        $GroupByCount,
        TblAccount $tblAccountSession
    ) {

        return ( new Data($this->getBinding()) )->createUserAccount(
            $tblAccount,
            $tblPerson,
            $TimeStamp,
            $userPassword,
            $Type,
            $GroupByCount,
            $tblAccountSession);
    }

    /**
     * @param TblPerson|null   $tblPerson
     * @param TblConsumer|null $tblConsumer
     * @param string           $AccountType
     * @param array            $result
     *
     * @return string|bool
     */
    public function generateUserName(TblPerson $tblPerson = null, TblConsumer $tblConsumer = null, $AccountType = 'S', &$result = array())
    {

        mb_internal_encoding("UTF-8");

        $FirstName = mb_substr($tblPerson->getFirstName(), 0, 2);
        $LastName = mb_substr($tblPerson->getLastName(), 0, 2);

        if($AccountType == 'S'){
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                    $randNumber = $tblCommonBirthDates->getBirthday('d');
                }
            }
            if(!isset($randNumber) || !$randNumber){
                $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                    ': Geburtsdatum fehlt';
                if(isset($result['Address'][$tblPerson->getId()])){
                    unset($result['Address'][$tblPerson->getId()]);
                }

                return false;
            }
        }
        // cut string with UTF8 encoding

        $UserName = $tblConsumer->getAcronym().'-'.$FirstName.$LastName;
        // replace Specialchar to normal Char like Ä -> A
        $UserName = $this->convertLetterToCorrectASCII($UserName);

        // Rand 32 - 99
        if($AccountType == 'C'){
            $randNumber = rand(32, 99);
        }

        // with leading 0 if number < 10
        $randNumber = str_pad($randNumber, 2, '0', STR_PAD_LEFT);

        $UserNamePrepare = $UserName.$randNumber;

        if($AccountType == 'C'){
            // find existing UserName?
            $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNamePrepare);
            if (!$tblAccount) {
                return $UserNamePrepare;
            } else {

                $NumberRange = range(32, 99);
                shuffle($NumberRange);
                foreach($NumberRange as $Rng){
//                    $Rng = str_pad($Rng, 2, '0', STR_PAD_LEFT);
                    $UserNameMod = $UserName.$Rng;
                    $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNameMod);
                    if (!$tblAccount) {
                        return  $UserNameMod;
                    }
                }
                // no free AccountName
                $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                    ': kein freier Benutzeraccount '.$UserName.'XX';
                return false;
            }
        } elseif($AccountType == 'S'){
            $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNamePrepare);
            if (!$tblAccount) {
                return $UserNamePrepare;
            } else {
                // second try
                $NumberRange = range(1, 9);
                shuffle($NumberRange);
                $FirstName2 = mb_substr($tblPerson->getFirstName(), 0, 3);
                $LastName2 = mb_substr($tblPerson->getLastName(), 0, 3);
                $UserName = $tblConsumer->getAcronym().'-'.$FirstName2.$LastName2;
                $UserName = $this->convertLetterToCorrectASCII($UserName);
                $UserNameMod = $UserName.$randNumber;
                // second try (without shuffleNumber)
                $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNameMod);
                if (!$tblAccount) {
                    return $UserNameMod;
                }

                // Last try add rng Number
                foreach($NumberRange as $Rng){
                    $UserNameMod = $UserName.$randNumber.$Rng;
                    $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNameMod);
                    if (!$tblAccount) {
                        return $UserNameMod;
                    }
                }
                // return ist nicht erfolgt -> keine Freie Nutzernamen.
                $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                    ': Benutzeraccount '.$UserName.end($NumberRange).' existiert bereits';
                return false;
            }
        }


        return $UserName;
    }

    /**
     * @param $String
     *
     * @return string
     */
    public function convertLetterToCorrectASCII($String)
    {

        $UpdateArray[] = array('A' => 'Á');
        $UpdateArray[] = array('A' => 'Â');
        $UpdateArray[] = array('A' => 'Ã');
        $UpdateArray[] = array('A' => 'Ä');
        $UpdateArray[] = array('A' => 'Å');
        $UpdateArray[] = array('Ae' => 'Æ');
        $UpdateArray[] = array('C' => 'Ç');
        $UpdateArray[] = array('C' => 'Č');
        $UpdateArray[] = array('D' => 'Ð');
        $UpdateArray[] = array('E' => 'È');
        $UpdateArray[] = array('E' => 'É');
        $UpdateArray[] = array('E' => 'Ê');
        $UpdateArray[] = array('E' => 'Ë');
        $UpdateArray[] = array('E' => 'Ĕ');
        $UpdateArray[] = array('E' => 'Ě');
        $UpdateArray[] = array('G' => 'Ģ');
        $UpdateArray[] = array('G' => 'Ğ');
        $UpdateArray[] = array('I' => 'Ì');
        $UpdateArray[] = array('I' => 'Í');
        $UpdateArray[] = array('I' => 'Î');
        $UpdateArray[] = array('I' => 'Ï');
        $UpdateArray[] = array('N' => 'Ñ');
        $UpdateArray[] = array('O' => 'Ò');
        $UpdateArray[] = array('O' => 'Ó');
        $UpdateArray[] = array('O' => 'Ô');
        $UpdateArray[] = array('O' => 'Õ');
        $UpdateArray[] = array('O' => 'Ō');
        $UpdateArray[] = array('O' => 'Ö');
        $UpdateArray[] = array('O' => 'Ø');
        $UpdateArray[] = array('P' => 'Þ');
        $UpdateArray[] = array('P' => 'þ');
        $UpdateArray[] = array('S' => 'Ŝ');
        $UpdateArray[] = array('S' => 'Ş');
        $UpdateArray[] = array('U' => 'Ù');
        $UpdateArray[] = array('U' => 'Ú');
        $UpdateArray[] = array('U' => 'Û');
        $UpdateArray[] = array('U' => 'Ü');
        $UpdateArray[] = array('U' => 'Ū');
        $UpdateArray[] = array('Y' => 'Ý');
        $UpdateArray[] = array('a' => 'à');
        $UpdateArray[] = array('a' => 'á');
        $UpdateArray[] = array('a' => 'â');
        $UpdateArray[] = array('a' => 'ã');
        $UpdateArray[] = array('a' => 'ä');
        $UpdateArray[] = array('a' => 'å');
        $UpdateArray[] = array('ae' => 'æ');
        $UpdateArray[] = array('c' => 'ç');
        $UpdateArray[] = array('c' => 'č');
        $UpdateArray[] = array('d' => 'ð');
        $UpdateArray[] = array('e' => 'è');
        $UpdateArray[] = array('e' => 'é');
        $UpdateArray[] = array('e' => 'ê');
        $UpdateArray[] = array('e' => 'ë');
        $UpdateArray[] = array('e' => 'ĕ');
        $UpdateArray[] = array('e' => 'ě');
        $UpdateArray[] = array('g' => 'ğ');
        $UpdateArray[] = array('g' => 'ģ');
        $UpdateArray[] = array('i' => 'ì');
        $UpdateArray[] = array('i' => 'í');
        $UpdateArray[] = array('i' => 'î');
        $UpdateArray[] = array('i' => 'ï');
        $UpdateArray[] = array('n' => 'ñ');
        $UpdateArray[] = array('o' => 'ò');
        $UpdateArray[] = array('o' => 'ó');
        $UpdateArray[] = array('o' => 'ô');
        $UpdateArray[] = array('o' => 'õ');
        $UpdateArray[] = array('o' => 'ō');
        $UpdateArray[] = array('o' => 'ö');
        $UpdateArray[] = array('o' => 'ø');
        $UpdateArray[] = array('p' => 'Þ');
        $UpdateArray[] = array('p' => 'þ');
        $UpdateArray[] = array('s' => 'ß');
        $UpdateArray[] = array('ŝ' => 'ß');
        $UpdateArray[] = array('ş' => 'ß');
        $UpdateArray[] = array('u' => 'ù');
        $UpdateArray[] = array('u' => 'ú');
        $UpdateArray[] = array('u' => 'û');
        $UpdateArray[] = array('u' => 'ü');
        $UpdateArray[] = array('u' => 'ū');
        $UpdateArray[] = array('x' => '×');
        $UpdateArray[] = array('y' => 'ý');
        $UpdateArray[] = array('y' => 'ÿ');

        foreach($UpdateArray as $LetterCompare) {
            foreach($LetterCompare as $replace => $search) {
                $String = str_replace($search, $replace, $String);
            }
        }

        return $String;
    }

    /**
     * @param TblUserAccount[] $tblUserAccountList
     *
     * @return bool
     */
    public function updateDownloadBulk($tblUserAccountList)
    {

        $ExportDate = new DateTime();
        $UserName = '';
        $tblAccount = AccountGatekeeper::useService()->getAccountBySession();
        if ($tblAccount) {
            $UserName = $tblAccount->getUsername();
        }
        return (new Data($this->getBinding()))->updateDownloadBulk($tblUserAccountList, $ExportDate, $UserName);
    }

    /**
     * @param TblUserAccount $tblUserAccount
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        return ( new Data($this->getBinding()) )->removeUserAccount($tblUserAccount);
    }

    /**
     * @param DateTime $GroupByTime
     *
     * @return bool
     */
    public function clearPassword(DateTime $GroupByTime)
    {

        $tblUserAccountList = $this->getUserAccountByTime($GroupByTime);
        if ($tblUserAccountList) {
            return (new Data($this->getBinding()))->updateUserAccountClearPassword($tblUserAccountList);
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Password
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function changePassword(TblUserAccount $tblUserAccount, $Password)
    {

        return (new Data($this->getBinding()))->updateUserAccountChangePassword($tblUserAccount, $Password);
    }

    public function changeUpdateDate(TblUserAccount $tblUserAccount, $Type)
    {

        $UserName = '';
        if(($tblAccount = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getAccountBySession())){
            $UserName = $tblAccount->getUsername();
        }

        return (new Data($this->getBinding()))->changeUpdateDate($tblUserAccount, $UserName, $Type);
    }

    /**
     * @param array $PersonIdList
     *
     * @return array
     */
    public function sortPersonIdListByDivisionAndName($PersonIdList = array())
    {
        if(empty($PersonIdList)){
            return array();
        }
        $tblClassList = array();
        foreach($PersonIdList as $PersonId){
            if(($tblPerson = Person::useService()->getPersonById($PersonId))){
                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblDivisionCourse = $tblStudentEducation->getTblDivision())) {
                    $tblClassList[$tblDivisionCourse->getDisplayName()][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                } else {
                    $tblClassList['000'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                }
            }
        }
        if(empty($tblClassList)){
            return array();
        }

        ksort($tblClassList, SORT_NUMERIC);
        foreach($tblClassList as &$tblPersonList){
            asort($tblPersonList);
        }
        $PersonIdList = array();
        if(!empty($tblClassList)){
            foreach($tblClassList as $tmpTblPersonList){
                if(!empty($tmpTblPersonList)){
                    foreach($tmpTblPersonList as $PersonId => $Person){
                        $PersonIdList[] = $PersonId;
                    }
                }
            }
        }
        return $PersonIdList;
    }

    /**
     * @param array $PersonIdList
     *
     * @return array
     */
    public function sortGuardianPersonIdListByDivisionAndName($PersonIdList = array())
    {
        if(empty($PersonIdList)){
            return array();
        }
        $tblClassList = array();
        foreach($PersonIdList as $PersonId){
            $IdList = explode('_', $PersonId);
            $PersonId = $IdList[0]; // PersonId Custody (+ Vormund + Bevollmächtigt)
            $DivisionId = $IdList[1]; // DivisionId vom Student (von welchem die Filterung kahm)
            if(($tblPerson = Person::useService()->getPersonById($PersonId))){
                if($DivisionId != '0' && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))){
                    $tblClassList[$tblDivisionCourse->getDisplayName()][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                } else{
                    $tblClassList['000'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                }
            }
        }
        if(empty($tblClassList)){
            return array();
        }

        ksort($tblClassList, SORT_NUMERIC);
        foreach($tblClassList as &$tblPersonList){
            asort($tblPersonList);
        }
        $PersonIdList = array();
        if(!empty($tblClassList)){
            foreach($tblClassList as $tmpTblPersonList){
                if(!empty($tmpTblPersonList)){
                    foreach($tmpTblPersonList as $PersonId => $Person){
                        $PersonIdList[] = $PersonId;
                    }
                }
            }
        }
        return $PersonIdList;
    }
}