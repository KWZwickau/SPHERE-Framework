<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IStructureInterface;
use SPHERE\System\Extension\Extension;

class FormRow extends Extension implements IStructureInterface
{

    /** @var FormColumn[] $FormColumn */
    private $FormColumn = array();

    /**
     * @param FormColumn|FormColumn[] $FormColumn
     */
    public function __construct( $FormColumn )
    {

        if (!is_array( $FormColumn )) {
            $FormColumn = array( $FormColumn );
        }
        $this->FormColumn = $FormColumn;
    }

    /**
     * @return FormColumn[]
     */
    public function getFormColumn()
    {

        return $this->FormColumn;
    }
}
