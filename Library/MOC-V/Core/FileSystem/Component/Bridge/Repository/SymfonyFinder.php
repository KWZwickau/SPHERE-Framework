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
    public function __construct(FileParameter $FileOption)
    {

        parent::__construct();
        AutoLoader::getNamespaceAutoLoader('Symfony\Component', __DIR__.'/../../../Vendor/');

        $this->FileOption = $FileOption;

        try {
            $this->Instance = new Finder();
            $this->Instance->useBestAdapter()->files()
                ->name(pathinfo($FileOption->getFile(), PATHINFO_BASENAME))
                ->in(pathinfo($FileOption->getFile(), PATHINFO_DIRNAME));
        } catch (\Exception $Exception) {
            // Nothing
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        if ($this->getRealPath()) {
            return (string)file_get_contents($this->getRealPath());
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        try {
            $Result = array();
            /** @var SplFileInfo $File */
            foreach ($this->Instance as $File) {
                array_push($Result, $File->getRealPath());
            }

            if (count($Result) > 1) {
                throw new \Exception(count($Result).' matches.');
            } elseif (count($Result) == 1) {
                return current($Result);
            } else {
                return '';
            }
        } catch (\Exception $Exception) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return $this->FileOption->getFile();
    }
}
