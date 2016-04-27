<?php
namespace SPHERE\Application\Education\Certificate\Generator\Repository\Element;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;

class Ruler extends Element
{

    public function __construct()
    {

        parent::__construct();

        $this->setContent('<hr/>');
    }
}
