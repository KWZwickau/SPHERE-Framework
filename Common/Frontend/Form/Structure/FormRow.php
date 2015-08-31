<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IStructureInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class FormRow
 *
 * @package SPHERE\Common\Frontend\Form\Structure
 */
class FormRow extends Extension implements IStructureInterface
{

    /** @var FormColumn[] $FormColumn */
    private $FormColumn = array();

    /** @var bool|string $IsSortable */
    private $IsSortable = false;

    /**
     * @param FormColumn|FormColumn[] $FormColumn
     * @param bool                    $IsSortable
     */
    public function __construct($FormColumn, $IsSortable = false)
    {

        if (!is_array($FormColumn)) {
            $FormColumn = array($FormColumn);
        }
        $this->FormColumn = $FormColumn;
        $this->IsSortable = $IsSortable;
    }

    /**
     * @param FormColumn $FormColumn
     *
     * @return FormRow
     */
    public function addColumn(FormColumn $FormColumn)
    {

        array_push($this->FormColumn, $FormColumn);
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
     * @return FormColumn[]
     */
    public function getFormColumn()
    {

        return $this->FormColumn;
    }
}
