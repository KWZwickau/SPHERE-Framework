<?php
namespace SPHERE\Library\RandomGenerator;

/**
 * Class RandomName
 * @package SPHERE\Library\RandomGenerator
 */
class RandomName {

    const ATTR_RANDOM = '';
    const ATTR_MALE = 'm';
    const ATTR_FEMALE = 'w';

    private $first_name_man;
    private $first_name_woman;
    private $last_name;
    public function __construct() {
        $this->first_name_man = $this->getList('first-name-man');
        $this->first_name_woman = $this->getList('first-name-woman');
        $this->last_name = $this->getList('last-name');

    }

    /**
     * @param $type
     *
     * @return array
     */
    private function getList( $type ) {
        $json = file_get_contents($type . '.json', FILE_USE_INCLUDE_PATH );
        $data = json_decode( $json, true );
        return $data;
    }

    /**
     * @param string $Gender
     *
     * @return string
     */
    public function getFirstName($Gender = RandomName::ATTR_RANDOM)
    {

        // Genderless option
        if(!$Gender || ($Gender != 'w' && $Gender != 'm')){
            $choose = rand(1, 2);
            ($choose == 1
                ? $Gender = 'm'
                : $Gender = 'w');
        }

        if($Gender == 'm'){
            $randomIndex = array_rand($this->first_name_man);
            return $this->first_name_man[$randomIndex];
        } else {
        // fallback: return woman if strange things happen
//        if($Gender == 'w'){
            $randomIndex = array_rand( $this->first_name_woman );
            return $this->first_name_woman[$randomIndex];
        }
    }

    /**
     * @return String LastName
     */
    public function getLastName()
    {

        $random_lastName_index = array_rand( $this->last_name );
        return $first_name = $this->last_name[$random_lastName_index];
    }
}