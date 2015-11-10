<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\Contact\Address\Service;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Address
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Address extends Service
{

    public function createAddressToPersonFromImport(
        TblPerson $tblPerson,
        $StreetName,
        $StreetNumber,
        $CityCode,
        $CityName,
        $State = null,
        $CityDistrict = '',
        $PostOfficeBox = ''
    ) {

        return $this->insertAddressToPerson($tblPerson, $StreetName, $StreetNumber, $CityCode, $CityName, $CityDistrict,
            $PostOfficeBox, $State);
    }

}
