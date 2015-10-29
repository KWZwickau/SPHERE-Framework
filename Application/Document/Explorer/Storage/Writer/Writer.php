<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer;

use SPHERE\Application\Document\Explorer\Storage\Writer\Type\Database;
use SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary;

/**
 * Class Writer
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer
 */
class Writer
{

    /**
     * @param string $Extension
     * @param string $Prefix
     *
     * @return Temporary
     */
    public function getTemporary($Extension = 'storage', $Prefix = 'SPHERE-Temporary')
    {

        return new Temporary($Prefix, $Extension);
    }

    /**
     * @param null|int $Id
     *
     * @return Database
     */
    public function getDatabase($Id = null)
    {

        return new Database($Id);
    }
}
