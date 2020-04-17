<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Univention
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendUnivention()
    {

        $Stage = new Stage('Univention', 'Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting', new Upload()));
        $Stage->addButton(new Standard('Accounts übertragen', '', new Upload()));

        // dynamsiche Rollenliste
        $roleList = (new UniventionRole())->getAllRoles();
        Debugger::screenDump($roleList);

        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
        Debugger::screenDump($schoolList);

        $Acronym = false;

        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                $Acronym = $tblConsumer->getAcronym();
            }
        }

        $ErrorLog = array();

//        // Benutzer anlegen
//        $ErrorLog[] = (new UniventionUser())->createUser('MaxMustermann', 'Kukane', 'Klimpel', '7', array($roleList['student']),
//            array($schoolList['DEMOSCHOOL'], $schoolList['DEMOSCHOOL2']), $Acronym.'-7');
//        $ErrorLog[] = (new UniventionUser())->createUser('MustermannMax', 'Valentina', 'Allgaier', '8', array($roleList['staff'],$roleList['teacher']),
//            array($schoolList['DEMOSCHOOL']), $Acronym.'-8');

        // Benutzerliste suchen
        $UserList = (new UniventionUser())->getUserListByName('demo-', false);
        Debugger::screenDump($UserList);

//        // Benutzer entfernen
//        if($UserList){
//            foreach($UserList as $Name){
//                $ErrorLog[] = (new UniventionUser())->deleteUser($Name);
//            }
//        }

//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login');
//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login2');

        $ErrorLog = array_filter($ErrorLog);

        $Stage->setContent(new Listing($ErrorLog));


        return $Stage;
    }
}