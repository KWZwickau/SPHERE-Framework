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

    /**
     * @param TblPerson $tblPerson
     * @param string    $StreetName
     * @param string    $StreetNumber
     * @param string    $CityCode
     * @param string    $CityName
     * @param string    $CityDistrict
     * @param string    $PostOfficeBox
     *
     * @return Service\Entity\TblToPerson
     */
    public function createAddressToPersonFromImport(
        TblPerson $tblPerson,
        $StreetName,
        $StreetNumber,
        $CityCode,
        $CityName,
        $CityDistrict = '',
        $PostOfficeBox = ''
    ) {

        return $this->insertAddressToPerson($tblPerson, $StreetName, $StreetNumber, $CityCode, $CityName, $CityDistrict,
            $PostOfficeBox);
    }

}
