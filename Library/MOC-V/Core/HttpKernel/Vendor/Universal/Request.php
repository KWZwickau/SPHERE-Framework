<?php
namespace MOC\V\Core\HttpKernel\Vendor\Universal;

use MOC\V\Core\AutoLoader\AutoLoader;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class Request
 *
 * @package MOC\V\Core\HttpKernel\Vendor\Universal
 */
class Request
{

    /** @var null|SymfonyRequest $SymfonyRequest */
    private $SymfonyRequest = null;

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('Symfony\Component', __DIR__.'/../');

        $this->SymfonyRequest = SymfonyRequest::createFromGlobals();
    }

    /**
     * @return null|SymfonyRequest
     */
    public function getSymfonyRequest()
    {

        return $this->SymfonyRequest;
    }

}
