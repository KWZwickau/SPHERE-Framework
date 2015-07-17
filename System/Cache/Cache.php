<?php
namespace SPHERE\System\Cache;

/**
 * Class Cache
 *
 * @package SPHERE\System\Cache
 */
class Cache
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
    public function getCache()
    {

        return $this->Type;
    }
}
