<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer;

use SPHERE\Application\Document\Explorer\Storage\AbstractStorage;

/**
 * Class AbstractWriter
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer
 */
abstract class AbstractWriter extends AbstractStorage
{

    /**
     *
     */
    public function loadFile()
    {

        $this->setFileContent(file_get_contents($this->getRealPath()));
    }

    /**
     *
     */
    public function saveFile()
    {

        if (!$this->getRealPath()) {
            touch($this->getFileLocation());
        }
        file_put_contents($this->getRealPath(), $this->getFileContent(), LOCK_EX);
    }

    /**
     * @return bool
     */
    public function getFileExists()
    {

        if ($this->getRealPath()) {
            return true;
        }
        return false;
    }
}
