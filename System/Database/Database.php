<?php
namespace SPHERE\System\Database;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use MOC\V\Component\Database\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\Repository\Flash;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\Warning;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Database\Fitting\Logger;
use SPHERE\System\Database\Fitting\Manager;
use SPHERE\System\Database\Link\Connection;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Database\Link\Register;
use SPHERE\System\Extension\Extension;

/**
 * Class Database
 *
 * @package SPHERE\System\Database
 */
class Database extends Extension
{

    /** @var null|bool $ConditionMemcached */
    private static $ConditionMemcached = null;
    /** @var null|bool $ConditionApc */
    private static $ConditionApc = null;
    /** @var Identifier $Identifier */
    private $Identifier = null;
    /** @var array $Configuration */
    private $Configuration = array();
    /** @var array $Protocol */
    private $Protocol = array();
    /** @var int $Timeout */
    private $Timeout = 1;

    /**
     * @param Identifier $Identifier
     *
     * @throws \Exception
     */
    function __construct(Identifier $Identifier)
    {

        $this->Identifier = $Identifier;
        $Register = new Register();
        if (!$Register->hasConnection($this->Identifier)) {
            $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
            if (isset( $Configuration[$this->Identifier->getConfiguration(true)] )) {
                $this->Configuration = $Configuration[$this->Identifier->getConfiguration(true)];
                $Driver = '\\SPHERE\\System\\Database\\Type\\'.$this->Configuration['Driver'];
                $Register->addConnection(
                    $this->Identifier,
                    new Connection(
                        $this->Identifier,
                        new $Driver,
                        $this->Configuration['Username'],
                        $this->Configuration['Password'],
                        empty( $this->Configuration['Database'] )
                            ? str_replace(':', '', $this->Identifier->getConfiguration(false))
                            : $this->Configuration['Database'],
                        $this->Configuration['Host'],
                        empty( $this->Configuration['Port'] )
                            ? null
                            : $this->Configuration['Port'],
                        $this->Timeout
                    )
                );
            } else {
                if (isset( $Configuration[$this->Identifier->getConfiguration(false)] )) {
                    $this->Configuration = $Configuration[$this->Identifier->getConfiguration(false)];
                    $Driver = '\\SPHERE\\System\\Database\\Type\\'.$this->Configuration['Driver'];
                    $Register->addConnection(
                        $this->Identifier,
                        new Connection(
                            $this->Identifier,
                            new $Driver,
                            $this->Configuration['Username'],
                            $this->Configuration['Password'],
                            empty( $this->Configuration['Database'] )
                                ? str_replace(':', '', $this->Identifier->getConfiguration(false))
                                : $this->Configuration['Database'],
                            $this->Configuration['Host'],
                            empty( $this->Configuration['Port'] )
                                ? null
                                : $this->Configuration['Port'],
                            $this->Timeout
                        )
                    );
                } else {
                    throw new \Exception(__CLASS__.' > Missing Configuration: ('.$this->Identifier->getConfiguration().')');
                }
            }
        }
    }

    /**
     * @param $EntityPath
     * @param $EntityNamespace
     *
     * @return Manager
     * @throws ORMException
     */
    public function getEntityManager($EntityPath, $EntityNamespace)
    {

        // Sanitize Namespace
        $EntityNamespace = trim(str_replace(array('/', '\\'), '\\', $EntityNamespace), '\\').'\\';
        $MetadataConfiguration = Setup::createAnnotationMetadataConfiguration(array($EntityPath));
        $MetadataConfiguration->setDefaultRepositoryClassName('\SPHERE\System\Database\Fitting\Repository');
        $MetadataConfiguration->addCustomHydrationMode( 'COLUMN_HYDRATOR', '\SPHERE\System\Database\Fitting\ColumnHydrator' );
        $ConnectionConfig = $this->getConnection()->getConnection()->getConfiguration();
        if (self::$ConditionMemcached || self::$ConditionMemcached = class_exists('\Memcached', false)) {
            /** @var Memcached $CacheDriver */
            $CacheDriver = (new Cache(new Memcached()))->getCache();
            $Cache = new MemcachedCache();
            $Cache->setMemcached($CacheDriver->getServer());
            $Cache->setNamespace($EntityPath);
            $ConnectionConfig->setResultCacheImpl($Cache);
            $MetadataConfiguration->setQueryCacheImpl($Cache);
            $MetadataConfiguration->setHydrationCacheImpl($Cache);
            if (self::$ConditionApc || self::$ConditionApc = function_exists('apc_fetch')) {
                $MetadataConfiguration->setMetadataCacheImpl(new ApcCache());
            } else {
                $MetadataConfiguration->setMetadataCacheImpl(new ArrayCache());
            }
        } else {
            if (self::$ConditionApc || self::$ConditionApc = function_exists('apc_fetch')) {
                $MetadataConfiguration->setMetadataCacheImpl(new ApcCache());
            } else {
                $MetadataConfiguration->setMetadataCacheImpl(new ArrayCache());
            }
            $MetadataConfiguration->setQueryCacheImpl(new ArrayCache());
            $MetadataConfiguration->setHydrationCacheImpl(new ArrayCache());
            $ConnectionConfig->setResultCacheImpl(new ArrayCache());
        }
        // $ConnectionConfig->setSQLLogger( new Logger() );
        return new Manager(
            EntityManager::create($this->getConnection()->getConnection(), $MetadataConfiguration), $EntityNamespace
        );
    }

    /**
     * @return IBridgeInterface|null
     * @throws \Exception
     */
    public function getConnection()
    {

        return (new Register())->getConnection($this->Identifier)->getConnection();
    }

    /**
     * @param $Statement
     *
     * @return int The number of affected rows
     */
    public function setStatement($Statement)
    {

        return $this->getConnection()->prepareStatement($Statement)->executeWrite();
    }

    /**
     * @param $Statement
     *
     * @return array
     */
    public function getStatement($Statement)
    {

        return $this->getConnection()->prepareStatement($Statement)->executeRead();
    }

    /**
     * @return AbstractPlatform
     * @throws \Exception
     */
    public function getPlatform()
    {

        return $this->getConnection()->getConnection()->getDatabasePlatform();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDatabase()
    {

        return $this->getConnection()->getConnection()->getDatabase();
    }

    /**
     * @param string $ViewName
     *
     * @return bool
     */
    public function hasView($ViewName)
    {

        return in_array($ViewName, $this->getSchemaManager()->listViews());
    }

    /**
     * @return AbstractSchemaManager
     */
    public function getSchemaManager()
    {

        return $this->getConnection()->getSchemaManager();
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {

        return $this->getSchemaManager()->createSchema();
    }

    /**
     * @param string $TableName
     * @param string $ColumnName
     *
     * @return bool
     */
    public function hasColumn($TableName, $ColumnName)
    {

        return in_array(strtolower($ColumnName),
            array_keys($this->getSchemaManager()->listTableColumns($TableName)));
    }

    /**
     * @param Table $Table
     * @param array $ColumnList
     *
     * @return bool
     */
    public function hasIndex(Table $Table, $ColumnList)
    {

        if ($Table->columnsAreIndexed($ColumnList)) {
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
    public function hasTable($TableName)
    {

        return in_array($TableName, $this->getSchemaManager()->listTableNames());
    }

    /**
     * @param string $Item
     */
    public function addProtocol($Item)
    {

        if (empty( $this->Protocol )) {
            $this->Protocol[] = '<samp>'.$Item.'</samp>';
        } else {
            $this->Protocol[] = '<div>'.new Transfer().'&nbsp;<samp>'.$Item.'</samp></div>';
        }
    }


    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function getProtocol($Simulate = false)
    {

        if (count($this->Protocol) == 1) {
            $Protocol = new Success(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Ok().'&nbsp'.implode('', $this->Protocol), 9),
                    new LayoutColumn(new Off().'&nbsp;Kein Update notwendig', 3)
                ))))
            );
        } else {
            $Protocol = new Info(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Flash().'&nbsp;'.implode('', $this->Protocol), 9),
                    new LayoutColumn(
                        ( $Simulate
                            ? new Warning().'&nbsp;Update notwendig'
                            : new Ok().'&nbsp;Update durchgeführt'
                        ), 3)
                ))))
            );
        }
        $this->Protocol = array();
        return $Protocol;
    }
}
