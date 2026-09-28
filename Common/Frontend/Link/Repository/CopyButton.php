<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class CopyButton
 *
 * Button copies $Value to the clipboard (client-side only, no route / authorization check)
 * navigator.clipboard requires https/localhost, otherwise fallback to execCommand (e.g. local http)
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class CopyButton extends Extension implements ILinkInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Hash */
    protected $Hash = '';

    /**
     * CopyButton constructor.
     *
     * @param string              $Value   content copied to the clipboard
     * @param string              $Name
     * @param IIconInterface|null $Icon    default: ClipBoard
     * @param bool|string         $ToolTip
     * @param string              $Type    AbstractLink::TYPE_*
     */
    public function __construct(
        $Value,
        $Name = 'Kopieren',
        IIconInterface $Icon = null,
        $Type = AbstractLink::TYPE_DEFAULT
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/CopyButton.twig');

        $this->Template->setVariable('ElementIcon', $Icon);

        $this->Template->setVariable('ElementHash', $this->getHash());
        $this->Template->setVariable('ElementName', $this->Name);
        $this->Template->setVariable('ElementType', $Type);
        $this->Template->setVariable('ElementValue', (string)$Value);
    }

    /**
     * @return string
     */
    public function getHash()
    {
        if (empty( $this->Hash )) {
            $this->Hash = 'Copy-'.crc32( uniqid(__CLASS__, true) );
        }
        return $this->Hash;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

}
