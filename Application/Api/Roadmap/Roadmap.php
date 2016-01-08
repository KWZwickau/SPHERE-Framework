<?php
namespace SPHERE\Application\Api\Roadmap;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Template\Template;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Roadmap
 *
 * @package SPHERE\Application\Api\Roadmap
 */
class Roadmap implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadRoadmapAsPdf'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    public function downloadRoadmapAsPdf()
    {

        $Roadmap = (new \SPHERE\Common\Roadmap\Roadmap())->getRoadmapObject();

        $Template = Template::getTwigTemplateString(
            $this->removeHtmlComments(
                '<html><head><style>'
                .$this->removeUnsupportedCss(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Roadmap.css'))
                .'</style></head><body>'
                .'<div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-offset-1 col-lg-10 col-xs-12">'
                .$Roadmap->getStage(false)
                .'</div>
                        </div>
                    </div>'
                .'</body></html>'
            )
        );
        $Location = sys_get_temp_dir().DIRECTORY_SEPARATOR.'Roadmap-'.md5(uniqid('Roadmap', true)).'.pdf';

        /** @var DomPdf $Document */
        $Document = Document::getDocument($Location);
        $Document->setContent($Template);
        $Document->saveFile(new FileParameter($Location));

        print FileSystem::getDownload($Location, 'Roadmap.pdf');
    }

    /**
     * Remove HTML comments
     *
     * @param string $Content
     *
     * @return mixed
     */
    private function removeHtmlComments($Content)
    {

        return preg_replace('/<!--(.|\s)*?-->/', '', $Content);
    }

    /**
     * Remove unsupported CSS
     *
     * @param string $Content
     *
     * @return mixed
     */
    private function removeUnsupportedCss($Content)
    {

        return preg_replace(array(
            '![^\{\}]*?:[^\{\}]*?:[^\{\}]*?\{[^\{\}]*?\}!i',
            '![^\{\}]*?~[^\{\}]*?\{[^\{\}]*?\}!i',
            '![^\{\}]*?\+[^\{\}]*?\{[^\{\}]*?\}!i'
        ), '', $Content);
    }
}
