<?php
namespace SPHERE\System\Database;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use MOC\V\Component\Database\Database;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Database\Fitting\Logger;
use SPHERE\System\Database\Fitting\Manager;
use SPHERE\System\Database\Link\Connection;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Database\Link\Register;

/**
 * Class Configuration
 *
 * @package SPHERE\System\Database
 */
class Configuration
{

    /** @var Identifier $Identifier */
    private $Identifier = null;
    /** @var array $Configuration */
    private $Configuration = array();

    /**
     * @param Identifier $Identifier
     *
     * @throws \Exception
     */
    function __construct( Identifier $Identifier )
    {

        $this->Identifier = $Identifier;
        $Register = new Register();
        if (!$Register->hasConnection( $this->Identifier )) {
            $Configuration = parse_ini_file( __DIR__.'/Configuration.ini', true );
            if (isset( $Configuration[$this->Identifier->getConfiguration()] )) {
                $this->Configuration = $Configuration[$this->Identifier->getConfiguration()];
                $Driver = '\\SPHERE\\System\\Database\\Type\\'.$this->Configuration['Driver'];
                $Register->addConnection(
                    $this->Identifier,
                    new Connection(
                        $this->Identifier,
                        new $Driver,
                        $this->Configuration['Username'],
                        $this->Configuration['Password'],
                        empty( $this->Configuration['Database'] )
                            ? str_replace( ':', '', $this->Identifier->getConfiguration() )
                            : $this->Configuration['Database'],
                        $this->Configuration['Host'],
                        empty( $this->Configuration['Port'] )
                            ? null
                            : $this->Configuration['Port']
                    )
                );
            } else {
                throw new \Exception( __CLASS__.' > Missing Configuration: ('.$this->Identifier->getConfiguration().')' );
            }
        }
    }

    /**
     * @param $EntityPath
     * @param $EntityNamespace
     *
     * @return EntityManager
     * @throws ORMException
     */
    public function getEntityManager( $EntityPath, $EntityNamespace )
    {

        $MetadataConfiguration = Setup::createAnnotationMetadataConfiguration( array( $EntityPath ) );
        $MetadataConfiguration->setDefaultRepositoryClassName( '\SPHERE\System\Database\Fitting\Repository' );
        $ConnectionConfig = $this->getConnection()->getBridgeInterface()->getConnection()->getConfiguration();
        if (class_exists( '\Memcached', false )) {
            $Cache = new MemcachedCache();
            /** @var Memcached $CacheConfiguration */
            $CacheConfiguration = ( new \SPHERE\System\Cache\Configuration( new Memcached() ) )->getCache();
            $Cache->setMemcached( $CacheConfiguration->getServer() );
            $Cache->setNamespace( $EntityPath );
            $ConnectionConfig->setResultCacheImpl( $Cache );
            $MetadataConfiguration->setQueryCacheImpl( $Cache );
            $MetadataConfiguration->setHydrationCacheImpl( $Cache );
            if (function_exists( 'apc_fetch' )) {
                $MetadataConfiguration->setMetadataCacheImpl( new ApcCache() );
            } else {
                $MetadataConfiguration->setMetadataCacheImpl( new ArrayCache() );
            }
        } else {
            if (function_exists( 'apc_fetch' )) {
                $MetadataConfiguration->setQueryCacheImpl( new ApcCache() );
                $MetadataConfiguration->setMetadataCacheImpl( new ApcCache() );
                $MetadataConfiguration->setHydrationCacheImpl( new ApcCache() );
                $ConnectionConfig->setResultCacheImpl( new ApcCache() );
            } else {
                $MetadataConfiguration->setQueryCacheImpl( new ArrayCache() );
                $MetadataConfiguration->setMetadataCacheImpl( new ArrayCache() );
                $MetadataConfiguration->setHydrationCacheImpl( new ArrayCache() );
                $ConnectionConfig->setResultCacheImpl( new ArrayCache() );
            }
        }
        $ConnectionConfig->setSQLLogger( new Logger() );

        return new Manager( EntityManager::create( $this->getConnection(), $MetadataConfiguration ), $EntityNamespace );
    }

    /**
     * @return Database|null
     * @throws \Exception
     */
    public function getConnection()
    {

        return ( new Register() )->getConnection( $this->Identifier )->getConnection();
    }

    /**
     * @param $Statement
     *
     * @return int The number of affected rows
     */
    public function setStatement( $Statement )
    {

        return $this->getConnection()->getBridgeInterface()->prepareStatement( $Statement )->executeWrite();
    }

    /**
     * @param $Statement
     *
     * @return array
     */
    public function getStatement( $Statement )
    {

        return $this->getConnection()->getBridgeInterface()->prepareStatement( $Statement )->executeRead();
    }

    /**
     * @throws \Exception
     */
    public function getPlatform()
    {

        ( new Register() )->getConnection( $this->Identifier )->getConnection()
            ->getBridgeInterface()->getConnection()->getDatabasePlatform();
    }

    /**
     * @throws \Exception
     */
    public function getDatabase()
    {

        ( new Register() )->getConnection( $this->Identifier )->getConnection()
            ->getBridgeInterface()->getConnection()->getDatabase();
    }

    /**
     * @param string $ViewName
     *
     * @return bool
     */
    final public function hasView( $ViewName )
    {

        return in_array( $ViewName, $this->getSchemaManager()->listViews() );
    }

    /**
     * @return AbstractSchemaManager
     */
    public function getSchemaManager()
    {

        return $this->getConnection()->getBridgeInterface()->getSchemaManager();
    }

    /**
     * @param string $TableName
     * @param string $ColumnName
     *
     * @return bool
     */
    final public function hasColumn( $TableName, $ColumnName )
    {

        return in_array( $ColumnName, $this->getSchemaManager()->listTableColumns( $TableName ) );
    }

    /**
     * @param Table $Table
     * @param array $ColumnList
     *
     * @return bool
     */
    final public function hasIndex( Table $Table, $ColumnList )
    {

        if ($Table->columnsAreIndexed( $ColumnList )) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $TableName
     *
     * @return bool
     */
    final public function hasTable( $TableName )
    {

        return in_array( $TableName, $this->getSchemaManager()->listTableNames() );
    }

}
