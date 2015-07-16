<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\System\Extension\Configuration;

/**
 * Class LayoutGroup
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutGroup extends Configuration implements ILayoutInterface
{

    /** @var LayoutRow[] $LayoutRow */
    private $LayoutRow = array();
    /** @var string $LayoutTitle */
    private $LayoutTitle = '';

    /**
     * @param LayoutRow|LayoutRow[] $LayoutRow
     * @param Title                 $LayoutTitle
     */
    public function __construct( $LayoutRow, Title $LayoutTitle = null )
    {

        if (!is_array( $LayoutRow )) {
            $LayoutRow = array( $LayoutRow );
        }
        $this->LayoutRow = $LayoutRow;
        $this->LayoutTitle = $LayoutTitle;
    }

    /**
     * @return string
     */
    public function getLayoutTitle()
    {

        return $this->LayoutTitle;
    }

    /**
     * @return LayoutRow[]
     */
    public function getLayoutRow()
    {

        return $this->LayoutRow;
    }
}
