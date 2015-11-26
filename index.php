<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Berlin');
session_start();
session_write_close();
set_time_limit(240);
ob_implicit_flush();
ini_set('memory_limit', '1024M');

/**
 * Setup: Loader
 */
require_once(__DIR__ . '/Library/MOC-V/Core/AutoLoader/AutoLoader.php');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__ . '/Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__ . '/', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__ . '/Library/Markdownify/2.1.6/src');
AutoLoader::getNamespaceAutoLoader('Faker', __DIR__ . '/System/Faker/Vendor', 'Faker');

$Main = new Main();

if (false) {
    $CacheConfig = (new ConfigFactory())->createReader(__DIR__ . '/System/Cache/Configuration.ini', new IniReader());
    (new CacheFactory())->createHandler(new APCuHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new MemcachedHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new MemoryHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new OpCacheHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new TwigHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new SmartyHandler(), $CacheConfig)->clearCache();
}

Debugger::$Enabled = false;

class FakePerson
{

    private $Faker = null;

    private $EntityPerson = null;

    public function __construct()
    {
        $this->Faker = \Faker\Factory::create('de_DE');
        $this->Gender = rand(0, 1);
    }

    public function createPerson()
    {
        $this->EntityPerson = Person::useService()->insertPerson(
            $this->getSalutation(), '', $this->getFirstName(), '', $this->getLastName(), array(), ''
        );

        $tblNoMemberAll = Group::useService()->getPersonAllHavingNoGroup();
        if (!empty($tblNoMemberAll)) {
            foreach ($tblNoMemberAll as $tblPerson) {
                Group::useService()->addGroupPerson(
                    Group::useService()->getGroupByMetaTable('COMMON'), $tblPerson
                );
            }
        }
    }

    public function getSalutation()
    {
        if (rand(0, 2) < 2) {
            switch ($this->Gender) {
                case 0:
                    return 1;
                    break;
                case 1:
                    return 2;
                    break;
                default:
                    return 3;
            }
        } else {
            return 3;
        }
    }

    public function getFirstName()
    {
        switch ($this->Gender) {
            case 0:
                return $this->Faker->firstNameMale;
                break;
            case 1:
                return $this->Faker->firstNameFemale;
                break;
            default:
                return $this->Faker->firstName;
        }
    }

    public function getLastName()
    {
        return $this->Faker->lastName;
    }

    public function createAddress()
    {
        Address::useService()->insertAddressToPerson(
            $this->EntityPerson, $this->getStreet(), $this->getStreetNumber(), $this->getCode(), $this->getCity(), '',
            '', $this->getState()
        );
    }

    public function getStreet()
    {

        return $this->Faker->streetName;
    }

    public function getStreetNumber()
    {

        return $this->Faker->buildingNumber;
    }

    public function getCode()
    {
        return $this->Faker->postcode;
    }

    public function getCity()
    {
        return $this->Faker->city;
    }

    public function getState()
    {
        if (($State = Address::useService()->getStateByName($this->Faker->state))) {
            return $State;
        }
        $State = Address::useService()->getStateAll();
        return $State[array_rand($State)];
    }
}


//for( $I=0; $I < 1000; $I++ ) {
//    $Person = new FakePerson();
//    $Person->createPerson();
//    $Person->createAddress();
//}

$Main->runPlatform();

