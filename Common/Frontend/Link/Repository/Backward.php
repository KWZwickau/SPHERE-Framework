<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Link\Repository\Backward\Session;
use SPHERE\Common\Frontend\Link\Repository\Backward\Step;

/**
 * Class Backward
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class Backward extends Standard
{

    /**
     * Backward constructor.
     */
    final public function __construct()
    {


        $History = (new Session())->loadHistory();
        $History->addStep(new Step($this->getRequest()->getUrl()));

        $Step = $History->getStep();

        parent::__construct('Zurück (' . $History->getCount() . ')', $Step->getPath(), new ChevronLeft(),
            $Step->getData(), $Step->getRoute());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $History = (new Session())->loadHistory();

        if ($History->getCount() > 0) {
            return parent::getContent();
        }
        return '';
    }


}
