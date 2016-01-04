<?php
namespace SPHERE\System\Authenticator;

use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;

/**
 * Class Authenticator
 *
 * @package SPHERE\System\Authenticator
 */
class Authenticator
{

    /** @var ITypeInterface $Type */
    private $Type = null;

    /**
     * @param ITypeInterface $Type
     *
     * @throws \Exception
     */
    public function __construct(ITypeInterface $Type)
    {

        $this->Type = $Type;
        if ($this->Type->getConfiguration() !== null) {
            $Configuration = (new ConfigFactory())
                ->createReader(__DIR__.'/Configuration.ini', new IniReader())
                ->getConfig();
            if (null !== $Configuration->getContainer($this->Type->getConfiguration())) {
                $this->Type->setConfiguration($Configuration->getContainer($this->Type->getConfiguration())->getValue());
            }
        }
    }

    /**
     * @return ITypeInterface
     */
    public function getAuthenticator()
    {

        return $this->Type;
    }
}
