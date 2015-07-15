<?php
namespace MOC\V\Core\FileSystem\Component\Bridge\Repository;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\Component\Bridge\Bridge;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class SymfonyFinder
 *
 * @package MOC\V\Core\FileSystem\Component\Bridge\Repository
 */
class SymfonyFinder extends Bridge implements IBridgeInterface
{

    /** @var Finder $Instance */
    private $Instance = null;
    /** @var FileParameter|null $FileOption */
    private $FileOption = null;

    /**
     * @param FileParameter $FileOption
     */
    function __construct( FileParameter $FileOption )
    {

        AutoLoader::getNamespaceAutoLoader( 'Symfony\Component', __DIR__.'/../../../Vendor/' );

        $this->FileOption = $FileOption;

        try {
            $this->Instance = new Finder();
            $this->Instance->useBestAdapter()->files()
                ->name( pathinfo( $FileOption->getFile(), PATHINFO_BASENAME ) )
                ->in( pathinfo( $FileOption->getFile(), PATHINFO_DIRNAME ) );
        } catch( \Exception $Exception ) {
            // Nothing
        }
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return $this->FileOption->getFile();
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        try {
            /** @var SplFileInfo $File */
            foreach ($this->Instance as $File) {
                return $File->getRealPath();
            }
        } catch( \Exception $Exception ) {
            return '';
        }
        return '';
    }
}
