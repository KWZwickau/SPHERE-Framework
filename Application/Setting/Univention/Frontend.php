<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Api\Setting\Univention\ApiUnivention;
use SPHERE\Application\Api\Setting\Univention\ApiWorkGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Primary as PrimaryText;
use SPHERE\Common\Frontend\Text\Repository\TextBackground;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Univention
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendUnivention()
    {
        $Stage = new Stage('UCS', '');

        //ToDO Erklärung der Schnittstelle? + Vorraussetzungen

        return $Stage;
    }

    /**
     * @param bool $Upload
     *
     * @return Stage
     */
    public function frontendUnivAPI($Upload = '', $YearId = '')
    {
        set_time_limit(900);
        $Stage = new Stage('UCS', 'Schnittstelle API');

        // dynamsiche Rollenliste
        $roleList = (new UniventionRole())->getAllRoles();
        // Fehlerausgabe
        if($this->errorScan($Stage, $roleList)){
            return $Stage;
        }

        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
        // Fehlerausgabe
        if($this->errorScan($Stage, $schoolList)){
            return $Stage;
        }

        // early break if no answer
        if(!is_array($roleList) || !is_array($schoolList)){
            $Stage->setContent(new Warning('UCS liefert keine Informationen'));
            return $Stage;
        }
        $Acronym = Account::useService()->getMandantAcronym();
        // Mandant ist nicht in der Schulliste
        if( !array_key_exists($Acronym, $schoolList)){
//            if(!in_array($Acronym, $excludeList)){
                $Stage->setContent(new Warning('Ihr Schulträger ist noch nicht in UCS freigeschalten'));
                return $Stage;
//            }
        }


        $IsActiveAPI = false;
        if(($tblConsumer = Consumer::useService()->getConsumerBySession())
         && ($tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS))
        ){
            if($tblConsumerLogin->getIsActiveAPI()){
                $IsActiveAPI = true;
            }
        }

        $YearString = '&nbsp;Aktuelles SJ';
        if($YearId == ''){
            $YearString = new PrimaryText(new Bold($YearString));
        }
        $Stage->addButton(new Standard($YearString, '/Setting/Univention/Api', new GroupIcon(), array('YearId' => '')));
        if($nextYearList = Term::useService()->getYearAllFutureYears(1)){
            foreach($nextYearList as $nextYear){
                $YearString = '&nbsp;'.$nextYear->getDisplayName();
                if($YearId == $nextYear->getId()){
                    $YearString = new PrimaryText(new Bold($YearString));
                }
                $Stage->addButton(new Standard($YearString, '/Setting/Univention/Api', new GroupIcon(), array('YearId' => $nextYear->getId())));
            }
        }

        if($IsActiveAPI){
            $ButtonCreate = new Primary('Benutzer anlegen', '/Setting/Univention/Api', new Plus(), array('Upload' => 'Create', 'YearId' => $YearId));
            $ButtonUpdate = new Primary('Benutzer anpassen', '/Setting/Univention/Api', new Edit(), array('Upload' => 'Update', 'YearId' => $YearId));
            $ButtonDelete = new Danger('Benutzer löschen', '/Setting/Univention/Api', new Remove(), array('Upload' => 'Delete', 'YearId' => $YearId));
        } else {
            $ButtonCreate = (new Standard('Benutzer anlegen', '', new Plus()))->setDisabled();
            $ButtonUpdate = (new Standard('Benutzer anpassen', '', new Edit()))->setDisabled();
            $ButtonDelete = (new Standard('Benutzer löschen', '', new Remove()))->setDisabled();
        }

        $UserUniventionList = Univention::useService()->getApiUser();

        $UserSchulsoftwareList = array();
        // Vorraussetzung, es muss ein aktives Schuljahr geben.
        $tblYearList = Term::useService()->getYearByNow();
        if($tblYearList){
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($roleList, $schoolList, $YearId);
        }

        // Zählung
        $count['create'] = 0;
        $count['cantCreate'] = 0;
        $count['update'] = 0;
        $count['allUpdate'] = 0;
        $count['cantUpdate'] = 0;
        $count['delete'] = 0;
        // create: AccountActive welche nicht in der API vorhanden sind
        $createList = array();
        $cantCreateList = array();
        // update: Accounts welche Vorhanden sind, aber unterschiedliche Werte aufweisen
        $updateList = array();
        $cantUpdateList = array();
        // delete: Accounts, die in der API vorhaden sind, aber nicht in der Schulsoftware
        $deleteList = array();

        $tblCompareUpdate = array();
        $tblNoUpdateNeeded = array();

        // Vergleich
        if(!empty($UserSchulsoftwareList)){
            foreach($UserSchulsoftwareList as $AccountActive){
                // Nutzer in Univention nicht vorhanden (nach Id)
                if(!isset($UserUniventionList[$AccountActive['record_uid']])
                ){
                    if(($Error = $this->controlAccount($AccountActive))){
                        $cantCreateList[] = $Error;
                        $count['cantCreate']++;
//                        // lokaler Test einzelner Benutzer
//                    } elseif($AccountActive['name'] !== 'REF-BoRe18'
//                          && $AccountActive['name'] !== 'REF-FeWe05'
//                          && $AccountActive['name'] !== 'REF-Lehrer'
//                    ) {
//                        $cantCreateList[] = array(new Muted(new Bold($AccountActive['name']).' manuell deaktiviert'));
//                        $count['cantCreate']++;
                    } else {
                        $count['create']++;
                        $createList[] = $AccountActive;
                    }
                } else {

                    // Vergleich welche User geupdatet werden müssen
                    $isUpdate = false;
                    $CompareRow = array(
                        'User' => $AccountActive['name'],
                        'UCS' => array(
                            'firstname' => '',
                            'lastname' => '',
                            'email' => '',
                            'roles' => '',
                            'schools' => '',
                            'school_classes' => '',
                            'recoveryMail' => '',
                        ),
                        'SSW' => array(
                            'firstname' => '',
                            'lastname' => '',
                            'email' => '',
                            'roles' => '',
                            'schools' => '',
                            'school_classes' => '',
                            'recoveryMail' => '',
                        ),
                    );

                    $ExistUser = $UserUniventionList[$AccountActive['record_uid']];

                    // Fill TableContent
                    $CompareRow['UCS']['firstname'] = $ExistUser['firstname'];
                    $CompareRow['UCS']['lastname'] = $ExistUser['lastname'];
//                    $CompareRow['UCS']['email'] = $ExistUser['email'];
                    $Email = '';
                    if(isset($ExistUser['udm_properties']['e-mail'])){
                        $Email = current($ExistUser['udm_properties']['e-mail']);
                    }
                    $CompareRow['UCS']['email'] = $Email;
                    $recoveryMail = '';
                    if(isset($ExistUser['udm_properties']['PasswordRecoveryEmail'])){
                        $recoveryMail = $ExistUser['udm_properties']['PasswordRecoveryEmail'];
                    }
                    $CompareRow['UCS']['recoveryMail'] = $recoveryMail;
                    if(!empty($ExistUser['roles'])){
                        $RoleShort = array();
                        foreach($ExistUser['roles'] as $roleTemp){
                            if(strpos($roleTemp, 'student')) {
                                $RoleShort[] = 'Schüler';
                            } elseif(strpos($roleTemp, 'teacher')) {
                                $RoleShort[] = 'Lehrer';
                            } elseif(strpos($roleTemp, 'staff')) {
                                $RoleShort[] = 'Mitarbeiter';
                            }
                        }
                        $CompareRow['UCS']['roles'] = implode(', ', $RoleShort);
                    } else{
                        $CompareRow['UCS']['roles'] = '---';
                    }
                    $SchoolListUCS = array();
                    foreach($AccountActive['schools'] as $SchoolUCS){
                        $SchoolListUCS[] = substr($SchoolUCS, (strpos($SchoolUCS, 'schools/') + 8));
                    }
                    $CompareRow['UCS']['schools'] = implode(', ', $SchoolListUCS);
                    sort($ExistUser['school_classes']);
                    if(!empty($ExistUser['school_classes'])){
                        $ClassString = '';
                        foreach($ExistUser['school_classes'] as $ClassList) {
                            sort($ClassList);
                            if(!$ClassString){
                                $ClassString = implode(', ', $ClassList);
                            } else {
                                $ClassString .= ', '.implode($ClassList);
                            }
                        }
                        $CompareRow['UCS']['school_classes'] = $ClassString;
                    } else {
                        $CompareRow['UCS']['school_classes'] = '---';
                    }

                    $CompareRow['SSW']['firstname'] = $AccountActive['firstname'];
                    $CompareRow['SSW']['lastname'] = $AccountActive['lastname'];
                    $CompareRow['SSW']['email'] = $AccountActive['email'];
                    $CompareRow['SSW']['recoveryMail'] = $AccountActive['recoveryMail'];
                    if(!empty($AccountActive['roles'])){
                        $RoleShort = array();
                        foreach($AccountActive['roles'] as $roleTemp){
                            if(strpos($roleTemp, 'student')) {
                                $RoleShort[] = 'Schüler';
                            } elseif(strpos($roleTemp, 'teacher')) {
                                $RoleShort[] = 'Lehrer';
                            } elseif(strpos($roleTemp, 'staff')) {
                                $RoleShort[] = 'Mitarbeiter';
                            }
                        }
                        $CompareRow['SSW']['roles'] = implode(', ', $RoleShort);
                    } else{
                        $CompareRow['SSW']['roles'] = '---';
                    }
                    $SchoolListSSW = array();
                    foreach($AccountActive['schools'] as $SchoolSSW){
                        $SchoolListSSW[] = substr($SchoolSSW, (strpos($SchoolSSW, 'schools/') + 8));
                    }
                    $CompareRow['SSW']['schools'] = implode(', ', $SchoolListSSW);

                    // Liste Sortieren, aber aktuelle nicht verändern
                    $ActiveSchoolList = $AccountActive['school_classes'];
                    sort($ActiveSchoolList);
                    if(!empty($ActiveSchoolList)){
                        $ClassString = '';
                        foreach($ActiveSchoolList as $ClassList) {
                            sort($ClassList);
                            if(!$ClassString){
                                $ClassString = implode(', ', $ClassList);
                            } else {
                                $ClassString .= ', '.implode($ClassList);
                            }
                        }
                        $CompareRow['SSW']['school_classes'] = $ClassString;
                    } else {
                        $CompareRow['SSW']['school_classes'] = '---';
                    }

                    // entscheidung was ein Update erhält
                    if($ExistUser['firstname'] != $AccountActive['firstname']){
                        $isUpdate = true;
                        $CompareRow['SSW']['firstname'] = new TextBackground($CompareRow['SSW']['firstname']);
                    }
                    if($ExistUser['lastname'] != $AccountActive['lastname']){
                        $isUpdate = true;
                        $CompareRow['SSW']['lastname'] = new TextBackground($CompareRow['SSW']['lastname'], 'lightgreen');
                    }
                    if(strtolower($Email) != strtolower($AccountActive['email'])){
                        $isUpdate = true;
                        $CompareRow['SSW']['email'] = new TextBackground($CompareRow['SSW']['email']);
                    }
                    if(strtolower($recoveryMail) != strtolower($AccountActive['recoveryMail'])){
                        $isUpdate = true;
                        $CompareRow['SSW']['recoveryMail'] = new TextBackground($CompareRow['SSW']['recoveryMail']);
                    }

                    // Vergleich der Rollen in einem Array
                    if(!empty($ExistUser['roles']) && !empty($AccountActive['roles'])){
                        foreach($AccountActive['roles'] as $activeRole){
                            if(!in_array($activeRole, $ExistUser['roles'])){
                                $isUpdate = true;;
                                $CompareRow['SSW']['roles'] = new TextBackground($CompareRow['SSW']['roles']);
                            }
                        }
                    } elseif( empty($ExistUser['roles']) &&  !empty($AccountActive['roles'] )){
                        $isUpdate = true;
                        $CompareRow['SSW']['roles'] = new TextBackground($CompareRow['SSW']['roles']);
                    } elseif( !empty($ExistUser['roles']) &&  empty($AccountActive['roles'] )){
                        $isUpdate = true;
                        $CompareRow['SSW']['roles'] = new TextBackground($CompareRow['SSW']['roles']);
                    }
                    if($ExistUser['schools'] != $AccountActive['schools']){
                        $isUpdate = true;
                        $CompareRow['SSW']['schools'] = new TextBackground($CompareRow['SSW']['schools']);
                    }

                    // Vergleich der Klassen aus einem doppeltem Array
                    $SchoolExistCompareList = array();
                    foreach($ExistUser['school_classes'] as $ClassList){
                        foreach($ClassList as $Class){
                            $SchoolExistCompareList[] = $Class;
                        }
                    }
                    $SchoolActiveCompareList = array();
                    foreach($AccountActive['school_classes'] as $ClassList){
                        foreach($ClassList as $Class){
                            $SchoolActiveCompareList[] = $Class;
                        }
                    }
                    // fokus nur auf das erste Array, deswegen doppelter Check
                    if(array_diff($SchoolExistCompareList, $SchoolActiveCompareList)){
                        $isUpdate = true;
                        $CompareRow['SSW']['school_classes'] = new TextBackground($CompareRow['SSW']['school_classes']);
                    }
                    if(array_diff($SchoolActiveCompareList, $SchoolExistCompareList)){
                        $isUpdate = true;
                        $CompareRow['SSW']['school_classes'] = new TextBackground($CompareRow['SSW']['school_classes']);
                    }

                    if(($Error = $this->controlAccount($AccountActive))){
                        $cantUpdateList[] = $Error;
                        $count['cantUpdate']++;
                    } else {
                        if($isUpdate){

                            // Layout in TableContent
                            $firstWith = 4;
                            $secondWith = 8;
                            $CompareRow['UCS'] = new Small(
                                new Layout(new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Vorname:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['firstname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Nachname:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['lastname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Rolle:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['roles'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['email'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail Recovery:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['recoveryMail'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Schule:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['schools'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Klassen:'), $firstWith),
                                        new LayoutColumn($CompareRow['UCS']['school_classes'], $secondWith),
                                    )),
                                )))
                            );
                            $CompareRow['SSW'] = new Small(
                                new Layout(new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Vorname:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['firstname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Nachname:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['lastname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Rolle:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['roles'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['email'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail Recovery:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['recoveryMail'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Schule:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['schools'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Klassen:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['school_classes'], $secondWith),
                                    )),
                                )))
                            );
                            $CompareRow['SSWCopy'] = $CompareRow['SSW'];

                            array_push($tblCompareUpdate, $CompareRow);

                            $count['update']++;
//                            //toDO wieder entfernen
//                            if($AccountActive['name'] == 'REF-Lehrer1'){
                                $updateList[] = $AccountActive;
//                            }

                        } else {
                            $firstWith = 2;
                            $secondWith = 10;
                            $CompareRow['SSW'] = new Small(
                                new Layout(new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Vorname:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['firstname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Nachname:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['lastname'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Rolle:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['roles'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['email'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('E-Mail Recovery:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['recoveryMail'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Schule:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['schools'], $secondWith),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(new Bold('Klassen:'), $firstWith),
                                        new LayoutColumn($CompareRow['SSW']['school_classes'], $secondWith),
                                    )),
                                )))
                            );
                            array_push($tblNoUpdateNeeded, $CompareRow);
                        }
                    }
                    $count['allUpdate']++;
                }
                unset($UserUniventionList[$AccountActive['record_uid']]);
            }
            $count['delete'] = count($UserUniventionList);
            $deleteList = $UserUniventionList;
        }

        // Upload erst nach ausführlicher Bestätigung
        if($Upload == 'Create'){
            return $this->frontendApiAction($createList, $Upload, $YearId);
        } elseif($Upload == 'Update'){
            return $this->frontendApiAction($updateList, $Upload, $YearId);
        } elseif($Upload == 'Delete'){
            return $this->frontendApiAction($deleteList, $Upload, $YearId);
        }

        // Frontend Anzeige
        $ContentCreate = array();
//        $ContentUpdate = array();
        $ContentDelete = array();
        if(!empty($createList)){
            foreach($createList as $AccountArray) {
                $ContentCreate[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        if(!empty($updateList)){
            foreach($updateList as $AccountArray) {
                if(isset($AccountArray['UpdateLog'])){
                    $ContentUpdate[] = (new ToolTip($AccountArray['name'].' '.new InfoIcon(), htmlspecialchars(
                        implode('<br/>', $AccountArray['UpdateLog'])
                    )))->enableHtml();
                } else {
                    $ContentUpdate[] = $AccountArray['name'];
                }
            }
        }
        if(!empty($deleteList)){
            foreach($deleteList as $AccountArray) {
                $ContentDelete[] = $AccountArray['name'].' - '.$AccountArray['firstname'].' '.$AccountArray['lastname'];
            }
        }
        // Frontend Anzeige Error/Warnung
        $CantCreatePanelContent = array();
        $CantUpdatePanelContent = array();
        if(!empty($cantCreateList)){
            foreach($cantCreateList as $cantCreateAccount){
                $CantCreatePanelContent[] = implode('<br/>', $cantCreateAccount);
            }
        }
        if(!empty($cantUpdateList)){
            foreach($cantUpdateList as $cantUpdateAccount){
                $CantUpdatePanelContent[] = implode('<br/>', $cantUpdateAccount);
            }
        }

        $AccordionCreate = new Accordion();
        $AccordionCreate->addItem('Benutzer die nicht in UCS angelegt werden können ('.$count['cantCreate'].')',
            '<br/><br/>'.
            new Listing($CantCreatePanelContent)
        );
        $AccordionCreate->addItem('Benutzer für UCS anlegen ('.$count['create'].')',
            new Listing($ContentCreate)
        );

        $AccordionDelete = new Accordion();
        $AccordionDelete->addItem('Benutzer in UCS entfernen ('.$count['delete'].')',
            new Listing($ContentDelete)
        );

        $AccordionUpdate = new Accordion();
        $AccordionUpdate->addItem('Benutzer die nicht in UCS angepasst werden können ('.$count['cantUpdate'].')',
            '<br/><br/>'.
            new Listing($CantUpdatePanelContent)
        );
        $AccordionUpdate->addItem('Benutzer anpassen ('.$count['update'].')',   // ' von '.$count['allUpdate'].
            new TableData($tblCompareUpdate, null, array(
                'User' => 'Account',
                'UCS' => 'Daten aus UCS',
                'SSW' => 'Daten aus SSW',
                'SSWCopy' => 'Daten Ergebnis',
            ), array(
//                "paging" => false, // Deaktivieren Blättern
//                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                "searching" => false, // Deaktivieren Suchen
//                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '30%', 'targets' => array(1,2,3)),
                ),
                'fixedHeader' => false
            ))
            , true
        );

        $AccordionUntouched = new Accordion();
        $countOkAccount = $count['allUpdate'] - $count['update'] - $count['cantUpdate'];
        $AccordionUntouched->addItem('Benutzer unverändert ('.$countOkAccount.')',
            new TableData($tblNoUpdateNeeded, null, array(
                'User' => 'Account',
                'SSW' => 'Daten von der SSW sind in UCS aktuell',
            ), array(
//                "paging" => false, // Deaktivieren Blättern
//                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                "searching" => false, // Deaktivieren Suchen
//                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '10%', 'targets' => 0),
                    array('width' => '90%', 'targets' => array(1)),
                ),
                'fixedHeader' => false
            ))
            , false
        );


        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Übersicht',
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new SuccessText('('.$count['cantCreate'].') Benutzer, die nicht angelegt werden können').'<br/>'.
                                    new SuccessText('('.$count['create'].') Benutzer für UCS anlegen')
                                , 3),
                                new LayoutColumn(
                                    new DangerText('('.$count['delete'].') Benutzer in UCS entfernen')
                                , 3),
                                new LayoutColumn(
                                    new InfoText('('.$count['cantUpdate'].') Benutzer, die nicht angepasst werden können').'<br/>'.
                                    new InfoText('('.$count['update'].') Benutzer anpassen') // ' von '.$count['allUpdate'].
                                , 3),
                                new LayoutColumn(
                                    '('.$countOkAccount.') Benutzer unverändert'
                                , 3),
                            ))
                        ))
                    , Panel::PANEL_TYPE_INFO
                    )
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new SuccessText(new Plus().' Anlegen').new PullRight($ButtonCreate)))
                        .$AccordionCreate)
                , 6),
                new LayoutColumn(
                    new Well(new Title(new PullClear(new DangerText(new Remove().' Löschen').new PullRight($ButtonDelete)))
                        .$AccordionDelete)
                , 6)
            )),
            new LayoutRow(
                new LayoutColumn(
                    new Well(new Title(new PullClear(new InfoText(new Edit().' Anpassen').new PullRight($ButtonUpdate)))
                        .$AccordionUpdate)
                )
            ),
            new LayoutRow(
                new LayoutColumn(
                    new Well($AccordionUntouched)
                )
            ),
        ))));

        return $Stage;
    }

    /**
     * @param        $UserList
     * @param string $ApiType
     *
     * @return Stage
     */
    public function frontendApiAction($UserList, $ApiType = '', $YearId = '')
    {

        $Stage = new Stage('API', 'Transfermeldung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention/Api', new ChevronLeft(), array('YearId' => $YearId)));

        $CountMax = count($UserList);
        $TypeFrontend = '';
        if($CountMax > 0){

            // avoid max_input_vars
            $UserList = json_encode($UserList);
            if($ApiType == 'Create'){
                $TypeFrontend = 'Anlegen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }elseif($ApiType == 'Update'){
                $TypeFrontend = 'Bearbeiten von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }elseif($ApiType == 'Delete'){
                $TypeFrontend = 'Löschen von Nutzern';
                $PipelineServiceUser = ApiUnivention::pipelineServiceUser('0', $UserList, $ApiType, $CountMax);
            }

            // insert receiver into frontend
            $LayoutRowAPI = new LayoutRow(new LayoutColumn(ApiUnivention::receiverUser($PipelineServiceUser), 4));
            for($i = 1; $i <= $CountMax; $i++){
                $LayoutRowAPI->addColumn(new LayoutColumn(ApiUnivention::receiverUser('', $i), 4));
            }

            $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn(new Title($TypeFrontend)),
                new LayoutColumn(ApiUnivention::receiverLoad(ApiUnivention::pipelineLoad(0, $CountMax))),
                new LayoutColumn('<div style="height: 15px"> </div>'),
            )),
                $LayoutRowAPI
            ))));
        } else {
            $Stage->setContent(
                new Warning(new Center('Es sind keine Transaktionen verfügbar.'))
            );
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendWorkGroupAPI($isStart = false) //
    {

        $Stage = new Stage('API', 'Arbeitsgruppen-Abgleich');
        if(!$isStart){
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Warning('Diese Schnittstelle legt neue Stammgruppen aus der Schulsoftware als
                 Arbeitsgruppen im DLLP / UCS an und ordnet die entsprechenden Schüler
                  diesen Gruppen zu. Bitte beachten Sie, dass die entsprechenden Schüler zuvor
                   mittels der Schnittstelle "UCS über API" erst nach DLLP / UCS übertragen
                    werden müssen.'), 4),
                new LayoutColumn(new Primary('Datenabgleich der Arbeitsgruppen starten', '/Setting/Univention/WorkGroupApi', new Upload(), array('isStart' => true)))
            )))));
            return $Stage;
        }

        $Acronym = Account::useService()->getMandantAcronym();
        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
        // Fehlerausgabe
        if($this->errorScan($Stage, $schoolList)){
            return $Stage;
        }
        // early break if no answer
        if(!is_array($schoolList)){
            $Stage->setContent(new Warning('UCS liefert keine Informationen'));
            return $Stage;
        }
        // Mandant ist nicht in der Schulliste
        if( !array_key_exists($Acronym, $schoolList)){
            $Stage->setContent(new Warning('Ihr Schulträger ist noch nicht in UCS freigeschalten'));
            return $Stage;
        }
        $school = $schoolList[$Acronym];
        // Vorhandene Nutzer in Univention holen
        $UserUniventionList = Univention::useService()->getApiUser();
        $ApiUserNameList = array();
        if($UserUniventionList){
            foreach($UserUniventionList as $UserUnivention){
                $ApiUserNameList[] = $UserUnivention['name'];
            }
        }

        $ApiWorkGroupList = (new UniventionWorkGroup())->getWorkGroupListAll();
        $ApiGroupArray = array();
        if($ApiWorkGroupList){
            // Workgroup mit Nutzernamen
            foreach($ApiWorkGroupList as $ApiWorkGroup){
                $group = $ApiWorkGroup['name'];
                if(!empty($ApiWorkGroup['users'])){
                    foreach($ApiWorkGroup['users'] as &$User){
                        // Nutzernamen aus URL
                        $Position = strpos($User, $Acronym.'-');
                        $TempUser = str_split($User, $Position);
                        $User = $TempUser[1];
                    }
                }
                sort($ApiWorkGroup['users']);
                $ApiGroupArray[$group] = $ApiWorkGroup['users'];
            }
        }

        if(($tblYearList = Term::useService()->getYearByNow())){
            foreach($tblYearList as $tblYear){
                if(($tblDivisionCourseCoreGroupList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))){
                    foreach($tblDivisionCourseCoreGroupList as $tblDivisionCourseCoreGroup){
                        $CoreGroupName = $tblDivisionCourseCoreGroup->getName();
                        $tblPersonAccountList = array();
                        if(($tblPersonList = $tblDivisionCourseCoreGroup->getStudents())){
                            foreach($tblPersonList as $tblPerson){
                                // Nur Schüler mit einem Account
                                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                                    $tblAccount = current($tblAccountList);
                                    // Nutzer müssen in der API verfügbar sein
                                    if(in_array($tblAccount->getUsername(), $ApiUserNameList)){
                                        $tblPersonAccountList[] = $tblAccount->getUsername();
                                    }
                                }
                            }
                        }
                        if((array_key_exists($CoreGroupName, $ApiGroupArray))){
                            $ApiUserList = $ApiGroupArray[$CoreGroupName];
                            if(count($ApiUserList) != count($tblPersonAccountList)
                                || ($Diff = array_diff($ApiUserList, $tblPersonAccountList))){
                                // Gruppen SSW & Univention unterscheiden sich
                                $Type = 'update';
                            } else {
                                // sonst keine Änderungen
                                $Type = 'ok';
                            }
                        } else {
                            $Type = 'create';
                        }
                        $ContentArray[$CoreGroupName] = array(
                            'Group' => $CoreGroupName,
                            'UserList' => $tblPersonAccountList,
                            'Type' => $Type,
                            'School' => $school
                        );
                    }
                }
            }
        }

        if(!empty($ContentArray)){
            ksort($ContentArray);
        }

        $CountMax = count($ContentArray);
        if($CountMax > 0){

            // avoid max_input_vars
            $ContentJson = json_encode($ContentArray);
            $PipelineServiceWorkgroup = ApiWorkGroup::pipelineServiceWorkgroup('0', $ContentJson, $CountMax);

            // insert receiver into frontend
            $LayoutRowAPI = new LayoutRow(new LayoutColumn(ApiWorkGroup::receiverWorkgroup($PipelineServiceWorkgroup), 4));
            for($i = 1; $i <= $CountMax; $i++){
                $LayoutRowAPI->addColumn(new LayoutColumn(ApiWorkGroup::receiverWorkgroup('', $i), 4));
            }

            $Stage->setContent(new Layout(new LayoutGroup(array(new LayoutRow(array(
                //                new LayoutColumn(new Title($TypeFrontend)),
                new LayoutColumn(ApiWorkGroup::receiverLoad(ApiWorkGroup::pipelineLoad(0, $CountMax))),
                new LayoutColumn('<div style="height: 15px"> </div>'),
                )),
                $LayoutRowAPI
            ))));
        } else {
            $Stage->setContent(
                new Warning(new Center('Es sind keine Transaktionen verfügbar.'))
            );
        }

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param array $List
     *
     * @return bool
     */
    private function errorScan(Stage $Stage, $List = array())
    {

        if(isset($List['detail'])){
            $Stage->setContent(new Warning('Fehlerbericht der API
                <pre>'.print_r($List, true).'</pre>'
            ));
            return true;
        }
        return false;
    }

    /**
     * @param array $Account
     * correct Account values return false
     * incorrect Accounts return ErrorLog
     *
     * @return array|bool
     */
    public function controlAccount($Account = array())
    {

        $ErrorLog = array();
        // Handle Error Entity
        // welche Eigenschaften müssen vorhanden sein:
        if($Account['name'] == ''
            || $Account['firstname'] == ''
            || $Account['lastname'] == ''
            || $Account['email'] == ''
            || $Account['record_uid'] == ''
//            || $Account['recoveryMail'] == ''
            || empty($Account['school_classes'])
            || empty($Account['roles'])
            || empty($Account['schools'])) {

            $tblMember = false;
            $tblPerson = false;

            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
            $tblGroupTeacher = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
            // ausnahme für Lehrer/Mitarbeiter ohne Klasse
            if(($tblAccount = Account::useService()->getAccountById($Account['record_uid']))){
                if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                    $tblPerson = current($tblPersonList);
                    $tblMember = Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup);
                }
            }
            if($tblPerson){
                $PersonId = $tblPerson->getId();
                $PersonLink = (new Link(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'),
                    '/People/Person', new Person(), array('Id' => $PersonId)))->setExternal();
            } else {
                $PersonLink = new Muted(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'));
            }
            $ErrorLog[] = new Bold($Account['name']).' '.$PersonLink;

            // Umlautkontrolle, wenn ein Nutzername vorhanden ist
            if($Account['name'] !== '' && Univention::useService()->checkName($Account['name'])){
                $ErrorLog[] = 'Benutzername: '.new DangerText('enthält Umlaute oder Sonderzeichen');
            }

            // Schularten, welche keine E-Mail als Benutzernamen benötigen
            $SchoolTypeList = Univention::useService()->getSchoolTypeException();

            foreach($Account as $Key => $Value){
                $MouseOver = '';
                $KeyReplace = '';
                if(is_array($Value)){
                    switch ($Key){
                        case 'roles':
                            $KeyReplace = 'Rolle:';
                            // sich ausschließende Gruppen vergeben, auch eine Fehlermeldung (roles wird im service geleert)
                            if($tblMember
                            && (Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStaff)
                              || Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupTeacher)
                                )){
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'Person mit sich ausschließenden Personengruppen:<br />'
                                    .new DangerText('Schüler, Mitarbeiter/Lehrer')
                                )))->enableHtml();
                            } else {
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'Person in keiner der folgenen Personengruppen:<br />'
                                    .new DangerText('Schüler, Mitarbeiter/Lehrer')
                                )))->enableHtml();
                            }
                        break;
                        case 'schools':
                            $KeyReplace = 'Schule:';
                            $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
                                'Schüler ist keiner Klasse zugewiesen <br />'
                                .'oder Schule fehlt in UCS')))->enableHtml();
                        break;

                    }
                    // Sonderregelung Schüler ohne Klasse ist ein Fehler Lehrer/Mitarbeiter nicht
                    if($tblMember && $Key == 'school_classes'){
                        $KeyReplace = 'Klassen:';
                        $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                            new DangerText('Fehler:').'<br />'.
                            'Schüler ist keiner Klasse zugewiesen')))->enableHtml();
                    } elseif(!$tblMember && $Key == 'school_classes') {
                        continue;
                    }
                    if(empty($Value)){
                        $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden! ').$MouseOver;
                    }

                } else {
                    if($Value == ''){
                        switch ($Key){
                            case 'name':
                                $KeyReplace = 'Benutzername:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'</br>'.
                                    'Umlaute oder Sonderzeichen'
                                )))->enableHtml();
                            break;
                            case 'email':
                                $KeyReplace = 'E-Mail:';
                                $MouseOver = (new ToolTip(new InfoIcon(), htmlspecialchars(
//                                    new DangerText('Fehler:').'<br />'.
                                    'keine E-Mail als UCS Benutzername verwendet'
                                )))->enableHtml();
                            break;
                            // recovery Mail optional
//                            case 'recoveryMail':
//                                $KeyReplace = 'E-Mail recovery:';
//                                $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
////                                    new DangerText('Fehler:').'<br />'.
//                                    'keine Passwort vergessen E-Mail hinterlegt'
//                                )))->enableHtml();
//                            break;
                            case 'lastname':
                                $KeyReplace = 'Person:';
                                $MouseOver = new ToolTip(new InfoIcon(), 'keine Person am Account');
                            break;
                            case 'school_classes':
                                $KeyReplace = 'Klasse:';
                                $MouseOver = new ToolTip(new InfoIcon(), 'Person muss mindestens einer Klasse zugewiesen sein');
                            break;
                        }

                        if(empty($Value)){
                                // Mousover Problembeschreibung
                            switch($Key){
                                    // Stammgruppe ist optional
                                case 'groupArray':
                                    // recoveryMail ist optional
                                case 'recoveryMail':
                                    // Schulart ist optional (Lehrer etc.)
                                case 'school_type':

                                // no log
                                break;

                                    //Mail wird für Schularten aus der Einstellung nicht geprüft
                                    // Accounts ohne Schulart sind von der Ausnahme nicht betroffen
                                case 'email':
                                    if(!empty($SchoolTypeList) && in_array($Account['school_type'], $SchoolTypeList)){
                                        break;
                                    }
//                                    $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden! ').$MouseOver;
//                                    break;
                                default:
                                    $ErrorLog[] = ($KeyReplace ? : $Key).' '.new DangerText('nicht vorhanden! ').$MouseOver;
                            }
                        }
                    }
                }
            }
        } elseif(Univention::useService()->checkName($Account['name'])){

            $tblPerson = false;
            if(($tblAccount = Account::useService()->getAccountById($Account['record_uid']))){
                if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                    $tblPerson = current($tblPersonList);
                }
            }
            if($tblPerson){
                $PersonLink = (new Link(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'),
                    '/People/Person', new Person(), array('Id' => $tblPerson->getId())))->setExternal();
            } else {
                $PersonLink = new Muted(new Small('('.$Account['firstname'].' '.$Account['lastname'].')'));
            }

            $ErrorLog[] = new Bold($Account['name']).' '.$PersonLink;
            $ErrorLog[] = 'Benutzername: '.new DangerText('enthält Umlaute oder Sonderzeichen');
        }
        // Errorlog nur mit Namen wieder entfernen
        // Count 1 ist nur der Name ohne Fehlermeldung und ist im allgemeinen ein ungültiger "Fund"
        // tritt nur bei der Sonderregel "Lehrer/Mitarbeiter" ohne Klassen auf
        if(count($ErrorLog) == 1){
            $ErrorLog = array();
        }

        return (!empty($ErrorLog) ? $ErrorLog : false);
    }

    /**
     * @return Stage
     */
    public function frontendUnivCSV()
    {
        $Stage = new Stage('UCS', 'Schnittstelle CSV');
        $Stage->addButton(new Standard('CSV Mandant herunterladen', '/Api/Reporting/Univention/SchoolList/Download', new Download()));
        $Stage->addButton(new Standard('CSV User herunterladen', '/Api/Reporting/Univention/User/Download', new Download(), array(), 'Beinhaltet alle Schüler/Mitarbeiter/Lehrer Accounts'));
        // Schularten, welche keine E-Mail als Benutzernamen benötigen
        $SchoolTypeList = Univention::useService()->getSchoolTypeException();

        $ErrorLog = array();
        $countFirstStudent = 0;
        if(($AccountPrepareList = Univention::useService()->getExportAccount(true))){
            $i = 0;
            foreach($AccountPrepareList as $Data){
                $IsError = false;
                if(!$Data['name']){
                    // nur Schüler können vorkommen, die keinen Account haben, der Rest wird nur über vorhandenen Account gezogen
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars('Person als '.
                            new Bold('Schüler').' besitzt keinen Account')))->enableHtml().
                        new DangerText('Account fehlt ');
                    $IsError = true;
                } elseif(Univention::useService()->checkName($Data['name'])) {
                    // Umlaute & Sonderzeichen im Benutzernamen sind nicht erlaubt
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars('Benutzername beinhaltet '.
                            new Bold('Umlaute oder Sonderzeichen'))))->enableHtml().
                        new DangerText('Account '.$Data['name']);
                    $Data['account'] = new DangerText('Umlaute&nbsp;oder&nbsp;Sonderzeichen');
                    $IsError = true;
                }
                if(!$Data['schools']){
                    $Data['schools'] = (new ToolTip(new Exclamation(),
                            htmlspecialchars(new Minus().' Lehrer erhält alle Schulen aus Mandanteneinstellungen<br/>'
                                .new Minus().' Schüler benötigt aktuelle Klasse<br/>'
                                .new Minus().' Schüler benötigt aktuelle Schule in S-Akte'
                            )))->enableHtml().
                        new DangerText(' Keine Schule hinterlegt');
                    $IsError = true;
                } else {
//                    $Data['schools'] = new SuccessText(new SuccessIcon().' gefunden');
                    $Data['schools'] = false;
                }

                if(!$Data['school_classes'] && preg_match("/student/",$Data['roles'])){

                    $Data['school_classes'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().
                            ' Schüler benötigt eine aktuelle Klasse')))->enableHtml().
                        new DangerText(' Keine Klasse');
                    $IsError = true;
                } else {
//                    $Data['school_classes'] = new SuccessText(new SuccessIcon().' gefunden');
                    $Data['school_classes'] = false;
                }

                //Mail wird für Schularten aus der Einstellung nicht geprüft
                $isExcluded = in_array($Data['school_type'], $SchoolTypeList);

                if(!$Data['mail'] && !$isExcluded){
                    $Data['mail'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().'
                    Keine E-mail als '.new Bold('UCS Benutzername').' gepflegt')))->enableHtml().
                    new DangerText('kein UCS Benutzername');
                    $IsError = true;
                } else {
                    $Data['mail'] = false;
//                    $Data['mail'] = new SuccessText(new SuccessIcon().' gefunden');
                }
                if($IsError){
                    $i++;
                    if($countFirstStudent == 0
                        && isset($Data['Type'])
                        && $Data['Type'] == 'Student'){
                        $countFirstStudent = $i;
                    }

                    $ErrorLog[] = $Data;
                }
            }
        }

        $countWarning = 0;
        $LayoutRowList = array();
        if(!empty($ErrorLog)){
            $LayoutRowCount = 0;
            $LayoutRow = null;
            foreach ($ErrorLog as $Notification){
                $countWarning++;
                $PanelContent = array();
                $PanelContent[] = 'Person: '.$Notification['firstname'].' '. $Notification['lastname'];
                if($Notification['schools']){
                    $PanelContent[] = 'Schule: '.$Notification['schools'];
                }
                if($Notification['school_classes']){
                    $PanelContent[] = 'Klasse: '.$Notification['school_classes'];
                }
                if($Notification['mail']){
                    $PanelContent[] = 'E-mail: '.$Notification['mail'];
                }
                if(isset($Notification['account']) && $Notification['account']){
                    $PanelContent[] = 'Benutzername: '.$Notification['account'];
                }

                if ($LayoutRowCount % 6 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                    // Mitarbeiterzeile, wenn der erste Fehler kein Schüler ist
                    if($countWarning == 1 && $countFirstStudent != 1){
                        $LayoutRow->addColumn(new LayoutColumn(new Title('Mitarbeiter')));
                    }
                }

                // erster Fehler der auf einen Schüler zeigt -> Überschrift + Umbruch (Zählung von vorn)
                if($countWarning == ($countFirstStudent)){
                    $LayoutRow->addColumn(new LayoutColumn(new Title('Schüler')));
                    $LayoutRowCount = 0;
                }

                $LayoutRow->addColumn(new LayoutColumn(new Panel($Notification['name'], $PanelContent)
                    , 2));
                $LayoutRowCount++;
            }
        }

        $Stage->setcontent(new Layout(array(new LayoutGroup(
            new LayoutRow(
                new LayoutColumn(
                    new Title($countWarning.' Warnungen', 'insgesamt')
                )
            )
        ), new LayoutGroup(
            $LayoutRowList
        )
        )));

        return $Stage;
    }
}