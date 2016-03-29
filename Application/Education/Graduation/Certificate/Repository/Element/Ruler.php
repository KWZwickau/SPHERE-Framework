<?php
namespace SPHERE\Application\Education\Graduation\Certificate\Repository\Element;

use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;

class Ruler extends Element
{

    public function __construct()
    {

        parent::__construct();

        $this->setContent('<hr/>');
    }
}
