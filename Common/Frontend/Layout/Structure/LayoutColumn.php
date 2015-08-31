<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutColumn
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutColumn extends Extension implements ILayoutInterface
{

    /** @var string|IFrontendInterface|IFrontendInterface[] $Frontend */
    private $Frontend = array();
    /** @var int $Size */
    private $Size = 12;

    /**
     * @param string|IFrontendInterface|IFrontendInterface[] $Frontend
     * @param int                                            $Size
     */
    public function __construct($Frontend, $Size = 12)
    {

        if (!is_array($Frontend)) {
            $Frontend = array($Frontend);
        }
        $this->Frontend = $Frontend;
        $this->Size = $Size;
    }

    /**
     * @return int
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @return IFrontendInterface[]
     */
    public function getFrontend()
    {

        return $this->Frontend;
    }
}
