<?php
namespace MOC\V\Component\Template\Component\Bridge\Repository;

use MOC\V\Component\Template\Component\Bridge\Bridge;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\System\Extension\Repository\Debugger;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
//use Twig\Twig_SimpleFunction;
use Twig\TwigFilter;
use Umpirsky\Twig\Extension\PhpFunctionExtension;

/**
 * Class TwigTemplate
 *
 * @package MOC\V\Component\Template\Component\Bridge
 */
class TwigTemplate extends Bridge implements IBridgeInterface
{

    /** @var null|Environment Environment */
    private $Environment = null;
    /** @var null|\Twig_Template $Template */
    private $Template = null;
    /** @var null|FilesystemLoader */
    private $Loader = null;
    /** @var null|ArrayLoader[] */
    private $ArrayLoader = null;

    /**
     *
     */
    public function __construct()
    {

        require_once(__DIR__.'/../../../../../Php8Combined/vendor/autoload.php');
//         require_once( __DIR__.'/../../../Vendor/Twig/3.7.0/vendor/autoload.php' );
//        require_once( __DIR__.'/../../../Vendor/Twig/1.16.0/lib/Twig/Autoloader.php' );
//        \Twig_Autoloader::register();
//        $Test = new Twig_SimpleFunction('Test', true);

        AutoLoader::getNamespaceAutoLoader(
            'Umpirsky\Twig\Extension',
            __DIR__.'/../../../Vendor/TwigExtension/TwigPHPFunction/0.0.0/src'
        );
//        $this->ArrayLoader = new ArrayLoader(array(__DIR__.'/TwigTemplate'));
//        $this->Loader = new FilesystemLoader(__DIR__.'/TwigTemplate');
    }

    /**
     * @param string $String
     * @param bool   $Reload
     *
     * @return IBridgeInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function loadString($String, $Reload = false):IBridgeInterface
    {

        // toDO SR
//        Debugger::screenDump(Debugger::getCallingFunctionName(true));
//        Debugger::screenDump($String);
        $this->Loader = new FilesystemLoader($String);
//        $this->Loader = new FilesystemLoader('');


//        $this->Loader = new ArrayLoader(array(dirname($String)));
//        $this->Template = $this->Instance->loadTemplate($String);
        $this->createInstance($Reload);

        if($String == '/var/www/Common/Frontend/Ajax/Receiver'){
            $String = 'ModalReceiver.twig';
        }

        if($String == '/var/www/Common/Frontend/Form/Repository/Field'){
            $String = 'SelectBox.twig';
        }

        $this->Template = $this->Instance->load($String);
        return $this;
    }

    /**
     * @param bool|false $Reload
     *
     * @return Environment
     */
    public function createInstance($Reload = false)
    {

        $this->Environment = new Environment($this->Loader, ['Cache' => __DIR__.'/TwigTemplate']);

        $this->Instance = new Environment($this->Loader, [
            'auto_reload' => $Reload,
            'autoescape' => false,
            'cache' => realpath(__DIR__.'/TwigTemplate'),
        ]);

        $this->Instance->addFilter(new TwigFilter('utf8_encode', 'utf8_encode'));
        $this->Instance->addFilter(new TwigFilter('utf8_decode', 'utf8_decode'));
        $this->Instance->addExtension(new PhpFunctionExtension());
        $this->Instance->addExtension(new DebugExtension());

        return $this->Instance;
    }

    /**
     * @param FileParameter $Location
     * @param               $Reload
     *
     * @return $this|IBridgeInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function loadFile(FileParameter $Location, $Reload = false)
    {

        $this->Loader = new FilesystemLoader(dirname($Location->getFile()), basename($Location->getFile()));
//        $this->Loader = new ArrayLoader(array(basename($Location->getFile()) => $Location->getFile()));
//        $this->Template = $this->Instance->loadTemplate(basename($Location->getFile()));
        $this->createInstance($Reload);
        $this->Template = $this->Instance->load(basename($Location->getFile()));
        return $this;
    }

    /**
     * @return false|string
     * @throws \Exception
     */
    public function getContent()
    {

        return $this->Template->render($this->VariableList);
    }

}
