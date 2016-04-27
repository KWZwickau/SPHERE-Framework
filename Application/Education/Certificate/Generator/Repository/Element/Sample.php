<?php
namespace SPHERE\Application\Education\Certificate\Generator\Repository\Element;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;

class Sample extends Element
{

    public function __construct()
    {

        parent::__construct();

        $this->setContent('MUSTER');
        $this->styleAlignCenter();
        $this->styleTextColor('darkred');
        $this->styleTextSize('24px');
    }
}
