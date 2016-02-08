<?php
namespace MOC\V\Component\Documentation;

use MOC\V\Component\Documentation\Component\Bridge\Repository\ApiGen;
use MOC\V\Component\Documentation\Component\IBridgeInterface;
use MOC\V\Component\Documentation\Component\IVendorInterface;
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;

/**
 * Class Documentation
 *
 * @package MOC\V\Component\Documentation
 */
class Documentation implements IVendorInterface
{

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    public function __construct(IVendorInterface $VendorInterface)
    {

        $this->setVendorInterface($VendorInterface);
    }

    /**
     * @param string                $Project
     * @param string                $Title
     * @param DirectoryParameter    $Source
     * @param DirectoryParameter    $Destination
     * @param null|ExcludeParameter $Exclude
     *
     * @return IBridgeInterface
     */
    public static function getDocumentation(
        $Project,
        $Title,
        DirectoryParameter $Source,
        DirectoryParameter $Destination,
        ExcludeParameter $Exclude = null
    ) {

        return self::getApiGenDocumentation($Project, $Title, $Source, $Destination, $Exclude);
    }

    /**
     * @param string                $Project
     * @param string                $Title
     * @param DirectoryParameter    $Source
     * @param DirectoryParameter    $Destination
     * @param null|ExcludeParameter $Exclude
     *
     * @return IBridgeInterface
     */
    public static function getApiGenDocumentation(
        $Project,
        $Title,
        DirectoryParameter $Source,
        DirectoryParameter $Destination,
        ExcludeParameter $Exclude = null
    ) {

        return new ApiGen($Project, $Title, $Source, $Destination, $Exclude);
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface(IVendorInterface $VendorInterface)
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
