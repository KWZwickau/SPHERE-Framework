<?php
namespace SPHERE\Application;

use SPHERE\System\Database\Link\Identifier;

/**
 * Interface IServiceInterface
 *
 * @package SPHERE\Application
 */
interface IServiceInterface
{

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace);

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData);
}
