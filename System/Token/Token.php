<?php
namespace SPHERE\System\Token;

/**
 * Class Token
 *
 * @package SPHERE\System\Token
 */
class Token
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
    public function getToken()
    {

        return $this->Type;
    }
}
