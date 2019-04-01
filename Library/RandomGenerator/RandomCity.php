<?php
namespace SPHERE\Library\RandomGenerator;

/**
 * Class RandomCity
 * @package SPHERE\Library\RandomGenerator
 */
class RandomCity {

    private $city;

    public function __construct() {
        $this->city = $this->getList('city-name');

    }
    private function getList( $type ) {
        $json = file_get_contents($type . '.json', FILE_USE_INCLUDE_PATH );
        $data = json_decode( $json, true );
        return $data;
    }

    /**
     * @return string
     */
    public function getCityName()
    {

        $randomIndex = array_rand($this->city);
        return $this->city[$randomIndex];
    }
}