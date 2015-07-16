<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\System\Extension\Configuration;

class FormGroup extends Configuration implements IFormInterface
{

    /** @var FormRow[] $FormRow */
    private $FormRow = array();
    /** @var Title $FormTitle */
    private $FormTitle = null;

    /**
     * @param FormRow|FormRow[] $FormRow
     * @param Title         $FormTitle
     */
    public function __construct( $FormRow, Title $FormTitle = null )
    {

        if (!is_array( $FormRow )) {
            $FormRow = array( $FormRow );
        }
        $this->FormRow = $FormRow;
        $this->FormTitle = $FormTitle;
    }

    /**
     * @return Title
     */
    public function getFormTitle()
    {

        return $this->FormTitle;
    }

    /**
     * @return FormRow[]
     */
    public function getFormRow()
    {

        return $this->FormRow;
    }
}
