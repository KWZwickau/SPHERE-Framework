<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use SPHERE\Common\Frontend\Table\ITableInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class TableHead
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableHead extends Extension implements ITableInterface
{

    /** @var TableRow[] $TableRow */
    private $TableRow = array();

    /**
     * @param null|TableRow|TableRow[] $TableRow
     */
    public function __construct($TableRow = null)
    {

        if (null !== $TableRow && !is_array($TableRow)) {
            $TableRow = array($TableRow);
        } elseif (null === $TableRow) {
            $TableRow = array();
        }
        $this->TableRow = $TableRow;
    }

    /**
     * @return TableRow[]
     */
    public function getTableRow()
    {

        return $this->TableRow;
    }
}
