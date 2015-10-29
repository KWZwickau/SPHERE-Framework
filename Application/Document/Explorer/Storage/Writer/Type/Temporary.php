<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer\Type;

use SPHERE\Application\Document\Explorer\Storage\Writer\AbstractWriter;

/**
 * Class Temporary
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer\Type
 */
class Temporary extends AbstractWriter
{

    /**
     * @param string $Prefix
     * @param string $Extension
     */
    public function __construct($Prefix = 'SPHERE-Temporary', $Extension = 'storage')
    {

        $Location = sys_get_temp_dir().DIRECTORY_SEPARATOR.$Prefix.'-'.sha1(uniqid($Prefix, true)).'.'.$Extension;
        $this->setFileLocation($Location);
    }

    /**
     *
     */
    public function __destruct()
    {

        if ($this->getRealPath()) {
            unlink($this->getRealPath());
        }
    }
}
