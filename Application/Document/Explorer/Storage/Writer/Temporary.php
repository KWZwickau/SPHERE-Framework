<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer;

/**
 * Class Temporary
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer
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
        file_put_contents($Location, '');
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
