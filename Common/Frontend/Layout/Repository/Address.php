<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Address
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Address extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param TblAddress $tblAddress
     */
    public function __construct(TblAddress $tblAddress)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Address.twig');
        $this->Template->setVariable('Address', $tblAddress);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }
}
