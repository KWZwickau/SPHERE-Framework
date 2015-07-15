<?php
namespace MOC\V\Component\Document\Component\Parameter\Repository;

use MOC\V\Component\Document\Component\Exception\ComponentException;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\IParameterInterface;
use MOC\V\Component\Document\Component\Parameter\Parameter;

/**
 * Class PaperSizeParameter
 *
 * @package MOC\V\Component\Document\Component\Parameter\Repository
 */
class PaperSizeParameter extends Parameter implements IParameterInterface
{

    /** @var string $Size */
    private $Size = null;

    /**
     * @param string $Size
     */
    function __construct( $Size = 'A4' )
    {

        $this->setSize( $Size );
    }

    /**
     * @return string
     */
    function __toString()
    {

        return $this->getSize();
    }

    /**
     * @return string
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @param string $Size
     *
     * @return IBridgeInterface
     * @throws ComponentException
     */
    public function setSize( $Size )
    {

        switch ($Size) {
            case 'A4': {
                $this->Size = $Size;
                return $this;
            }
            default:
                throw new ComponentException( 'Size '.$Size.' not supported' );
        }

    }
}
