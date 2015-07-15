<?php
namespace Satooshi\Bundle\CoverallsV1Bundle\Entity\Git;

/**
 * @covers Satooshi\Bundle\CoverallsV1Bundle\Entity\Git\Commit
 * @covers Satooshi\Bundle\CoverallsV1Bundle\Entity\Coveralls
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
class CommitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldNotHaveIdOnConstruction()
    {

        $this->assertNull( $this->object->getId() );
    }

    // getId()

    /**
     * @test
     */
    public function shouldNotHoveAuthorNameOnConstruction()
    {

        $this->assertNull( $this->object->getAuthorName() );
    }

    // getAuthorName()

    /**
     * @test
     */
    public function shouldNotHoveAuthorEmailOnConstruction()
    {

        $this->assertNull( $this->object->getAuthorEmail() );
    }

    // getAuthorEmail()

    /**
     * @test
     */
    public function shouldNotHoveCommitterNameOnConstruction()
    {

        $this->assertNull( $this->object->getCommitterName() );
    }

    // getCommitterName()

    /**
     * @test
     */
    public function shouldNotHoveCommitterEmailOnConstruction()
    {

        $this->assertNull( $this->object->getCommitterEmail() );
    }

    // getCommitterEmail()

    /**
     * @test
     */
    public function shouldNotHoveMessageOnConstruction()
    {

        $this->assertNull( $this->object->getMessage() );
    }

    // getMessage()

    /**
     * @test
     */
    public function shouldSetId()
    {

        $expected = 'id';

        $obj = $this->object->setId( $expected );

        $this->assertEquals( $expected, $this->object->getId() );
        $this->assertSame( $obj, $this->object );
    }


    // setId()

    /**
     * @test
     */
    public function shouldSetAuthorName()
    {

        $expected = 'author_name';

        $obj = $this->object->setAuthorName( $expected );

        $this->assertEquals( $expected, $this->object->getAuthorName() );
        $this->assertSame( $obj, $this->object );
    }

    // setAuthorName()

    /**
     * @test
     */
    public function shouldSetAuthorEmail()
    {

        $expected = 'author_email';

        $obj = $this->object->setAuthorEmail( $expected );

        $this->assertEquals( $expected, $this->object->getAuthorEmail() );
        $this->assertSame( $obj, $this->object );
    }

    // setAuthorEmail()

    /**
     * @test
     */
    public function shouldSetCommitterName()
    {

        $expected = 'committer_name';

        $obj = $this->object->setCommitterName( $expected );

        $this->assertEquals( $expected, $this->object->getCommitterName() );
        $this->assertSame( $obj, $this->object );
    }

    // setCommitterName()

    /**
     * @test
     */
    public function shouldSetCommitterEmail()
    {

        $expected = 'committer_email';

        $obj = $this->object->setCommitterEmail( $expected );

        $this->assertEquals( $expected, $this->object->getCommitterEmail() );
        $this->assertSame( $obj, $this->object );
    }

    // setCommitterEmail()

    /**
     * @test
     */
    public function shouldSetMessage()
    {

        $expected = 'message';

        $obj = $this->object->setMessage( $expected );

        $this->assertEquals( $expected, $this->object->getMessage() );
        $this->assertSame( $obj, $this->object );
    }

    // setMessage()

    /**
     * @test
     */
    public function shouldConvertToArray()
    {

        $expected = array(
            'id'              => null,
            'author_name'     => null,
            'author_email'    => null,
            'committer_name'  => null,
            'committer_email' => null,
            'message'         => null,
        );

        $this->assertSame( $expected, $this->object->toArray() );
        $this->assertSame( json_encode( $expected ), (string)$this->object );
    }

    // toArray()

    /**
     * @test
     */
    public function shouldConvertToFilledArray()
    {

        $id = 'id';
        $authorName = 'author_name';
        $authorEmail = 'author_email';
        $committerName = 'committer_name';
        $committerEmail = 'committer_email';
        $message = 'message';

        $this->object
            ->setId( $id )
            ->setAuthorName( $authorName )
            ->setAuthorEmail( $authorEmail )
            ->setCommitterName( $committerName )
            ->setCommitterEmail( $committerEmail )
            ->setMessage( $message );

        $expected = array(
            'id'              => $id,
            'author_name'     => $authorName,
            'author_email'    => $authorEmail,
            'committer_name'  => $committerName,
            'committer_email' => $committerEmail,
            'message'         => $message,
        );

        $this->assertSame( $expected, $this->object->toArray() );
        $this->assertSame( json_encode( $expected ), (string)$this->object );
    }

    protected function setUp()
    {

        $this->object = new Commit();
    }
}
