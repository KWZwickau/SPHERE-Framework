<?php
namespace SPHERE\Application\Education\Graduation\Certificate\Repository\Element;

use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;

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
