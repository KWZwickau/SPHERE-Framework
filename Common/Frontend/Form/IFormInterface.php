<?php
namespace SPHERE\Common\Frontend\Form;

use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Interface IFormInterface
 *
 * @package SPHERE\Common\Frontend\Form
 */
interface IFormInterface extends IStructureInterface
{

    /**
     * @param string              $Name
     * @param string              $Message
     * @param IIconInterface|null $Icon
     */
    public function setSuccess($Name, $Message = '', IIconInterface $Icon = null);

    /**
     * @param string              $Name
     * @param string              $Message
     * @param IIconInterface|null $Icon
     */
    public function setError($Name, $Message, IIconInterface $Icon = null);

    /**
     * @param FormGroup $GridGroup
     */
    public function appendGridGroup(FormGroup $GridGroup);

    /**
     * @param FormGroup $GridGroup
     */
    public function prependGridGroup(FormGroup $GridGroup);

    /**
     * @return string
     */
    public function __toString();
}
