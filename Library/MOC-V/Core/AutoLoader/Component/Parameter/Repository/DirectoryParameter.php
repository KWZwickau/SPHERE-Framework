<?php
namespace MOC\V\Core\AutoLoader\Component\Parameter\Repository;

use MOC\V\Core\AutoLoader\Component\Exception\Repository\DirectoryNotFoundException;
use MOC\V\Core\AutoLoader\Component\Exception\Repository\EmptyDirectoryException;
use MOC\V\Core\AutoLoader\Component\IParameterInterface;
use MOC\V\Core\AutoLoader\Component\Parameter\Parameter;

/**
 * Class DirectoryParameter
 *
 * @package MOC\V\Core\AutoLoader\Component\Parameter\Repository
 */
class DirectoryParameter extends Parameter implements IParameterInterface
{

    /** @var string $Directory */
    private $Directory = null;

    /**
     * @param string $Directory
     */
    function __construct( $Directory )
    {

        $this->setDirectory( $Directory );
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
     * @throws EmptyDirectoryException
     * @throws DirectoryNotFoundException
     */
    public function setDirectory( $Directory )
    {

        if (empty( $Directory )) {
            throw new EmptyDirectoryException();
        }
        $Directory = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $Directory );
        if (is_dir( $Directory )) {
            $this->Directory = $Directory;
        } else {
            throw new DirectoryNotFoundException( $Directory );
        }
    }

}
