<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Configuration;

/**
 * Class FormColumn
 *
 * @package KREDA\Sphere\Client\Frontend\Form\Structure
 */
class FormColumn extends Configuration implements IFormInterface
{

    /** @var IFrontendInterface|IFrontendInterface[] $Frontend */
    private $Frontend = array();
    /** @var int $Size */
    private $Size = 12;

    /**
     * @param IFrontendInterface|IFrontendInterface[] $Frontend
     * @param int                                     $Size
     */
    public function __construct( $Frontend, $Size = 12 )
    {

        if (!is_array( $Frontend )) {
            $Frontend = array( $Frontend );
        }
        /** @var AbstractInput $Object */
        foreach ((array)$Frontend as $Index => $Object) {
            if (null !== $Object->getName()) {
                $Frontend[$Object->getName()] = $Object;
                unset( $Frontend[$Index] );
            }
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
