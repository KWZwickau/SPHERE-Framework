<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutRow
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutRow extends Extension implements ILayoutInterface
{

    /** @var LayoutColumn[] $LayoutColumn */
    private $LayoutColumn = array();

    /** @var bool|string $IsSortable */
    private $IsSortable = false;

    /**
     * @param LayoutColumn|LayoutColumn[] $LayoutColumn
     * @param bool                        $IsSortable
     */
    public function __construct($LayoutColumn, $IsSortable = false)
    {

        if (!is_array($LayoutColumn)) {
            $LayoutColumn = array($LayoutColumn);
        }
        $this->LayoutColumn = $LayoutColumn;
        $this->IsSortable = $IsSortable;
    }

    /**
     * @param LayoutColumn $LayoutColumn
     *
     * @return LayoutRow
     */
    public function addColumn(LayoutColumn $LayoutColumn)
    {

        array_push($this->LayoutColumn, $LayoutColumn);
        return $this;
    }

    /**
     * @return bool|string
     */
    public function isSortable()
    {

        return $this->IsSortable;
    }

    /**
     * @return LayoutColumn[]
     */
    public function getLayoutColumn()
    {

        return $this->LayoutColumn;
    }
}
