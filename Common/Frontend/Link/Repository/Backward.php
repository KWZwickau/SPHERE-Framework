<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Link\Repository\Backward\Session;
use SPHERE\Common\Frontend\Link\Repository\Backward\Step;

/**
 * Class Backward
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class Backward extends Standard
{

    private $BackStep = null;

    /**
     * Backward constructor.
     *
     * @param bool $IgnoreStep Disable History for this Step
     */
    final public function __construct($IgnoreStep = false)
    {

        $Session = new Session();
        $History = $Session->loadHistory();
        $Step = new Step($this->getRequest()->getUrl());

        $History->cleanStep($Step);
        if (!$IgnoreStep) {
            $History->addStep($Step);
        }
        $this->BackStep = $History->getBackStep();
        $Session->saveHistory($History);

        $this->getDebugger()->screenDump( $History );


        if ($this->BackStep) {
            parent::__construct('Zurück', $this->BackStep->getPath(), new ChevronLeft(),
                $this->BackStep->getData(), $this->BackStep->getRoute());
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {

        if ($this->BackStep) {
            return (string)parent::getContent();
        }
        return '';
    }
}
