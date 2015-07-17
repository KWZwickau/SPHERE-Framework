<?php
namespace SPHERE\Common\Frontend\Form;

use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\ITemplateInterface;

interface IFieldInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setSuccess( $Message, IIconInterface $Icon = null );

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setError( $Message, IIconInterface $Icon = null );
}
