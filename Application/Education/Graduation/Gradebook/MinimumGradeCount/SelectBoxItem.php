<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2017
 * Time: 08:46
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount;

class SelectBoxItem
{
    const PERIOD_FULL_YEAR = 10;
    const PERIOD_FIRST_PERIOD = 1;
    const PERIOD_SECOND_PERIOD = 2;

    const HIGHLIGHTED_ALL = 1;
    const HIGHLIGHTED_IS_HIGHLIGHTED = 2;
    const HIGHLIGHTED_IS_NOT_HIGHLIGHTED = 3;

    public function __construct($Id, $Name)
    {

        $this->Name = $Name;
        $this->Id = $Id;
    }

    protected $Name;
    protected $Id;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * @param mixed $Id
     */
    public function setId($Id)
    {
        $this->Id = $Id;
    }
}
