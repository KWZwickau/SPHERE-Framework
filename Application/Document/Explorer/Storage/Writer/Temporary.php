<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer;

/**
 * Class Temporary
 * @package SPHERE\Application\Document\Explorer\Storage\Writer
 */
class Temporary extends AbstractWriter
{

    public function __construct()
    {
        $this->setFileLocation(tempnam(sys_get_temp_dir(), 'SPHERE-Temp-'));
    }

    public function __destruct()
    {
        unlink($this->getFileLocation());
    }
}
