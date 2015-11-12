<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use SPHERE\Common\Frontend\Table\ITableInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class TableFoot
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableFoot extends Extension implements ITableInterface
{

    /** @var TableRow[] $TableRow */
    private $TableRow = array();

    /**
     * @param TableRow|TableRow[] $TableRow
     */
    public function __construct($TableRow)
    {

        if (!is_array($TableRow)) {
            $TableRow = array($TableRow);
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
