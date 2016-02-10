<?php
namespace SPHERE\Application\Transfer\Gateway\Item;

class Address extends AbstractItem
{

    const FIELD_CITY_CODE = 'Address_City_Code';
    const FIELD_CITY_NAME = 'Address_City_Name';
    const FIELD_CITY_DISTRICT = 'Address_City_District';

    public function setPayload($CityCode,$CityName,$CityDistrict = '')
    {

        $this->setTemplate(__DIR__.'/Address.twig');

        $this->setData(self::FIELD_CITY_CODE, $CityCode);
        $this->setData(self::FIELD_CITY_NAME, $CityName);
        $this->setData(self::FIELD_CITY_DISTRICT, $CityDistrict);

        return $this;
    }
}
