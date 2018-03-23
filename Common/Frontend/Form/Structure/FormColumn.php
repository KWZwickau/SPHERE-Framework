<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\IStructureInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Extension\Extension;

/**
 * Class FormColumn
 *
 * @package SPHERE\Common\Frontend\Form\Structure
 */
class FormColumn extends Extension implements IStructureInterface
{

    /** @var IFrontendInterface|IFrontendInterface[] $Frontend */
    private $Frontend = array();
    /** @var int $Size */
    private $Size = 12;

    /**
     * @param IFrontendInterface|IFrontendInterface[] $Frontend
     * @param int $Size
     */
    public function __construct($Frontend, $Size = 12)
    {

        if (!is_array($Frontend)) {
            $Frontend = array($Frontend);
        }
        /** @var IFieldInterface $Object */
        foreach ((array)$Frontend as $Index => $Object) {
            if ((
                    $Object instanceof IFieldInterface
                    && null !== $Object->getName()
                ) || (
                    is_object($Object)
                    && method_exists($Object, 'getName')
                    && null !== $Object->getName()
                )
            ) {
                $Frontend[$Object->getName()] = $Object;
            } else {
                if (
                    $Object instanceof IFrontendInterface
                ) {
                    $Frontend[uniqid('IFrontendInterface', true)] = $Object;
                } else {
                    trigger_error(
                        'Element missing Field- or Frontend-Interface ' . new Code(
                            htmlspecialchars(print_r($Object,true))
                        )
                    );
                }
            }
            unset($Frontend[$Index]);
        }
        $this->Frontend = $Frontend;
        $this->Size = $Size;
    }

    /**
     * @return int
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @return IFrontendInterface[]
     */
    public function getFrontend()
    {

        return $this->Frontend;
    }
}
