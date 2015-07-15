<?php
namespace MOC\V\Component\Document\Component\Parameter\Repository;

use MOC\V\Component\Document\Component\Exception\ComponentException;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\IParameterInterface;
use MOC\V\Component\Document\Component\Parameter\Parameter;

/**
 * Class PaperOrientationParameter
 *
 * @package MOC\V\Component\Document\Component\Parameter\Repository
 */
class PaperOrientationParameter extends Parameter implements IParameterInterface
{

    /** @var string $Orientation */
    private $Orientation = null;

    /**
     * @param string $Orientation
     */
    function __construct( $Orientation = 'PORTRAIT' )
    {

        $this->setOrientation( $Orientation );
    }

    /**
     * @return string
     */
    function __toString()
    {

        return $this->getOrientation();
    }

    /**
     * @return string
     */
    public function getOrientation()
    {

        return $this->Orientation;
    }

    /**
     * @param string $Orientation
     *
     * @return IBridgeInterface
     * @throws ComponentException
     */
    public function setOrientation( $Orientation )
    {

        switch (strtoupper( $Orientation )) {
            case 'LANDSCAPE':
            case 'PORTRAIT': {
                $this->Orientation = $Orientation;
                return $this;
            }
            default:
                throw new ComponentException( 'Orientation '.$Orientation.' not supported' );
        }

    }
}
