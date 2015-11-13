<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use SPHERE\Common\Frontend\Table\ITableInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class TableRow
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableRow extends Extension implements ITableInterface
{

    /** @var TableColumn[] $TableColumn */
    private $TableColumn = array();

    /**
     * @param TableColumn|TableColumn[] $TableColumn
     */
    public function __construct($TableColumn)
    {

        if (!is_array($TableColumn)) {
            $TableColumn = array($TableColumn);
        }
        $this->TableColumn = $TableColumn;
    }

    /**
     * @return TableColumn[]
     */
    public function getTableColumn()
    {

        return $this->TableColumn;
    }
}
