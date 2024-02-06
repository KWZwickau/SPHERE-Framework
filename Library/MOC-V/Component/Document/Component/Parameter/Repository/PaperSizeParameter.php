<?php
namespace MOC\V\Component\Document\Component\Parameter\Repository;

use MOC\V\Component\Document\Component\Exception\ComponentException;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\IParameterInterface;
use MOC\V\Component\Document\Component\Parameter\Parameter;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use SPHERE\System\Extension\Repository\Debugger;

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
    public function __construct($Size = 'A4')
    {

        $this->setSize($Size);
    }

    /**
     * @return string
     */
    public function __toString()
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
     * @return int
     */
    public function getSizeConstant()
    {

        $PageSetupConstants = PageSetup::getConstants();
        if(isset($PageSetupConstants['PAPERSIZE_'.$this->Size])){
            return $PageSetupConstants['PAPERSIZE_'.$this->Size];
        }
        // default
        return $PageSetupConstants['PAPERSIZE_A4'];
    }

    /**
     * @param $Size
     *
     * @return $this
     * @throws ComponentException
     */
    public function setSize($Size)
    {

        switch ($Size) {
            case 'A3':
            case 'A4':
            case 'A5':
                $this->Size = $Size;
                return $this;
            default:
                throw new ComponentException('Size '.$Size.' not supported');
        }

    }
}
