<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\Component\Exception\ComponentException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyDirectoryException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyFileException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\MissingDirectoryException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\MissingFileException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeDirectoryException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use MOC\V\Core\FileSystem\Exception\FileSystemException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testFileSystemException()
    {

        try {
            throw new FileSystemException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Exception\FileSystemException', $E );
        }

        try {
            throw new ComponentException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\ComponentException', $E );
        }

        try {
            throw new EmptyDirectoryException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyDirectoryException',
                $E );
        }

        try {
            throw new EmptyFileException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyFileException', $E );
        }

        try {
            throw new MissingDirectoryException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\MissingDirectoryException',
                $E );
        }

        try {
            throw new MissingFileException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\MissingFileException', $E );
        }

        try {
            throw new TypeDirectoryException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\TypeDirectoryException',
                $E );
        }

        try {
            throw new TypeFileException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E );
        }

    }

}
