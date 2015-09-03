<?php //-->
/*
 * This file is part of the Utility package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

class Eden_System_Tests_System_FolderTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {

        $class = eden('system')->folder(__DIR__.'/../assets/foobar')->create(0777);
        $this->assertInstanceOf('Eden\\System\\Folder', $class);

        $this->assertTrue(file_exists(__DIR__.'/../assets/foobar'));
    }

    public function testGetFiles()
    {

        $files = eden('system')->folder(__DIR__.'/../assets')->getFiles();
        $this->assertEquals(4, count($files));

        $files = eden('system')->folder(__DIR__.'/../assets')->getFiles('/.*\.php$/');
        $this->assertEquals(2, count($files));

        $files = eden('system')->folder(__DIR__.'/../assets')->getFiles(null, true);
        $this->assertEquals(8, count($files));

        $files = eden('system')->folder(__DIR__.'/../assets')->getFiles('/.*\.php$/', true);
        $this->assertEquals(4, count($files));
    }

    public function testGetFolders()
    {

        $folders = eden('system')->folder(__DIR__.'/../assets')->getFolders();
        $this->assertEquals(2, count($folders));

        $folders = eden('system')->folder(__DIR__.'/../assets')->getFolders('/^foo/');
        $this->assertEquals(1, count($folders));

        $folders = eden('system')->folder(__DIR__.'/../assets')->getFolders(null, true);
        $this->assertEquals(2, count($folders));

        $folders = eden('system')->folder(__DIR__.'/../assets')->getFolders('/^test/', true);
        $this->assertEquals(1, count($folders));
    }

    public function testGetName()
    {

        $name = eden('system')->folder(__DIR__.'/../assets')->getName();
        $this->assertEquals('assets', $name);
    }

    public function testIsFolder()
    {

        $this->assertTrue(eden('system')->folder(__DIR__.'/../assets')->isFolder());
        $this->assertFalse(eden('system')->folder(__DIR__.'/../assets/stars.gif')->isFolder());
    }

    public function testRemove()
    {

        $class = eden('system')->folder(__DIR__.'/../assets/foobar')->remove();
        $this->assertInstanceOf('Eden\\System\\Folder', $class);

        $this->assertFalse(file_exists(__DIR__.'/../assets/foobar'));
    }

    public function testRemoveFiles()
    {

        $path = __DIR__.'/../assets/foobar';
        eden('system')->folder($path)->create(0777);
        eden('system')->file($path.'/file1.txt')->touch();
        eden('system')->file($path.'/2files.txt')->touch();
        eden('system')->file($path.'/file3.txt')->touch();

        eden('system')->folder($path)->removeFiles('/^file/');

        $this->assertTrue(file_exists($path.'/2files.txt'));
        $this->assertFalse(file_exists($path.'/file3.txt'));

        eden('system')->folder($path)->removeFiles();
        $this->assertFalse(file_exists($path.'/2files.txt'));
    }

    public function testRemoveFolders()
    {

        $path = __DIR__.'/../assets/foobar/subfolder';

        eden('system')->folder($path)->create(0777);

        eden('system')->folder(__DIR__.'/../assets/foobar')->removeFolders();

        $this->assertFalse(is_dir($path));

        eden('system')->folder(__DIR__.'/../assets/foobar')->remove();
    }

    public function testTruncate()
    {

        $path = __DIR__.'/../assets/foobar2';

        eden('system')->folder($path)->create(0777);
        eden('system')->folder($path.'/subfolder2')->create(0777);

        eden('system')->file($path.'/file1.txt')->touch();
        eden('system')->file($path.'/2files.txt')->touch();
        eden('system')->file($path.'/file3.txt')->touch();

        eden('system')->folder($path)->truncate();

        $this->assertFalse(is_dir($path.'/subfolder2'));
        $this->assertFalse(file_exists($path.'/2files.txt'));

        eden('system')->folder($path)->remove();
    }
}
