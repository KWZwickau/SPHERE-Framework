<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\IBridgeInterface as File;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Video
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Video extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var string $Hash */
    private $Hash = '';
    /** @var array $Source */
    private $Source = array();

    /**
     * @param File      $Source
     * @param File|null $Splash
     */
    public function __construct(File $Source, File $Splash = null)
    {

        if (( $Location = $Source->getRealPath() )) {
            $FileInfo = new \finfo(FILEINFO_MIME_TYPE);
            $MimeType = $FileInfo->buffer(file_get_contents($Location));
        } else {
            $MimeType = '';
        }

        $this->Template = $this->getTemplate(__DIR__.'/Video.twig');
        $this->Template->setVariable('MimeType', $MimeType);
        $this->Template->setVariable('Source', $Source->getLocation());
        array_push($this->Source, $Source->getLocation());

        if ($Splash !== null && $Splash->getRealPath()) {
            $this->Template->setVariable('Splash', $Splash->getLocation());
        } else {
            $Splash = FileSystem::getFileLoader('Common/Style/Resource/Logo/kuw_logo2.png');
            $this->Template->setVariable('Splash', $Splash->getLocation());
        }
        $this->Template->setVariable('Splash', $Splash->getLocation());
    }

    /**
     * @return string
     */
    public function __toString()
    {

        $this->Template->setVariable('Hash', $this->getHash());
        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if (empty( $this->Hash )) {
            $CarouselItem = $this->Source;
            array_walk($CarouselItem, function (&$G) {

                if (is_object($G)) {
                    $G = serialize($G);
                }
            });
            $this->Hash = md5(json_encode($CarouselItem));
        }
        return $this->Hash;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }
}
