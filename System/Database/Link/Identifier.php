<?php
namespace SPHERE\System\Database\Link;

/**
 * Class Identifier
 *
 * @package SPHERE\System\Database\Link
 */
class Identifier
{

    /** @var string $Identifier */
    private $Identifier = null;
    /** @var string $Configuration */
    private $Configuration = null;
    /** @var string $Cluster */
    private $Cluster = '';
    /** @var string $Application */
    private $Application = '';
    /** @var string $Module */
    private $Module = '';
    /** @var string|null $Service */
    private $Service = null;
    /** @var string|null $Consumer */
    private $Consumer = null;

    /**
     * @param string      $Cluster
     * @param string      $Application
     * @param string      $Module
     * @param string      $Service
     * @param string|null $Consumer
     */
    public function __construct( $Cluster, $Application, $Module = null, $Service = null, $Consumer = null )
    {

        $this->Cluster = $Cluster;
        $this->Application = $Application;
        $this->Module = $Module;
        $this->Service = $Service;
        $this->Consumer = $Consumer;
        $this->Configuration = $Cluster.':'.$Application.( $Module === null ? '' : ':'.$Module ).( $Service === null ? '' : ':'.$Service ).( $Consumer === null ? '' : ':'.$Consumer );
        $this->Identifier = sha1( $this->Configuration );
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return $this->Configuration;
    }

    /**
     * @return string
     */
    public function getCluster()
    {

        return $this->Cluster;
    }

    /**
     * @return string
     */
    public function getApplication()
    {

        return $this->Application;
    }

    /**
     * @return string
     */
    public function getModule()
    {

        return $this->Module;
    }

    /**
     * @return string
     */
    public function getService()
    {

        return $this->Service;
    }

    /**
     * @return null|string
     */
    public function getConsumer()
    {

        return $this->Consumer;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }
}
