<?php
namespace SPHERE\System\Authenticator;

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
    function __construct( ITypeInterface $Type )
    {

        $this->Type = $Type;
        if ($this->Type->getConfiguration() !== null) {
            $Configuration = parse_ini_file( __DIR__.'/Configuration.ini', true );
            if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                $this->Type->setConfiguration( $Configuration[$this->Type->getConfiguration()] );
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
