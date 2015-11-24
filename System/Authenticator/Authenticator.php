<?php
namespace SPHERE\System\Authenticator;

use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\Type\Memory;

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
            $Cache = (new Cache(new Memory(), true))->getCache();
            if (false === ( $Configuration = $Cache->getValue($this->Type->getConfiguration()) )) {
                $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
                $Cache->setValue($this->Type->getConfiguration(), $Configuration);
            }
            if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                $this->Type->setConfiguration($Configuration[$this->Type->getConfiguration()]);
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
