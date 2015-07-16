<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Extension\Configuration;

class FormRow extends Configuration implements IFormInterface
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
