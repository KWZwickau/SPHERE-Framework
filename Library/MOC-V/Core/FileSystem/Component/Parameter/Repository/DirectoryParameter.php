<?php
namespace MOC\V\Core\FileSystem\Component\Parameter\Repository;

use MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyDirectoryException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeDirectoryException;
use MOC\V\Core\FileSystem\Component\IParameterInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Parameter;

/**
 * Class DirectoryParameter
 *
 * @package MOC\V\Core\FileSystem\Component\Parameter\Repository
 */
class DirectoryParameter extends Parameter implements IParameterInterface
{

    /** @var string $Directory */
    private $Directory = null;

    /**
     * @param string $Directory
     */
    public function __construct($Directory)
    {

        $this->setDirectory($Directory);
    }

    /**
     * @return string
     */
    public function getDirectory()
    {

        return $this->Directory;
    }

    /**
     * @param string $Directory
     *
     * @throws \MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyDirectoryException
     * @throws \MOC\V\Core\FileSystem\Component\Exception\Repository\TypeDirectoryException
     */
    public function setDirectory($Directory)
    {

        if (empty( $Directory )) {
            throw new EmptyDirectoryException();
        } else {
            if (is_dir($Directory)) {
                $this->Directory = $Directory;
            } else {
                throw new TypeDirectoryException($Directory);
            }
        }
    }

}
