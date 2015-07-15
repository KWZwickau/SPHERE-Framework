<?php
namespace Satooshi\Bundle\CoverallsV1Bundle\Config;

/**
 * @covers Satooshi\Bundle\CoverallsV1Bundle\Config\Configuration
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldNotHaveRepoTokenOnConstruction()
    {

        $this->assertFalse( $this->object->hasRepoToken() );
        $this->assertNull( $this->object->getRepoToken() );
    }

    // hasRepoToken()
    // getRepoToken()

    /**
     * @test
     */
    public function shouldNotHaveServiceNameOnConstruction()
    {

        $this->assertFalse( $this->object->hasServiceName() );
        $this->assertNull( $this->object->getServiceName() );
    }

    // hasServiceName()
    // getServiceName()

    /**
     * @test
     */
    public function shouldNotHaveSrcDirOnConstruction()
    {

        $this->assertNull( $this->object->getSrcDir() );
    }

    // getSrcDir()

    /**
     * @test
     */
    public function shouldHaveEmptyCloverXmlPathsOnConstruction()
    {

        $this->assertEmpty( $this->object->getCloverXmlPaths() );
    }

    // getCloverXmlPaths()

    /**
     * @test
     */
    public function shouldNotHaveJsonPathOnConstruction()
    {

        $this->assertNull( $this->object->getJsonPath() );
    }

    // getJsonPath()

    /**
     * @test
     */
    public function shouldBeDryRunOnConstruction()
    {

        $this->assertTrue( $this->object->isDryRun() );
    }

    // isDryRun()

    /**
     * @test
     */
    public function shouldNotBeExcludeNotStatementsOnConstruction()
    {

        $this->assertFalse( $this->object->isExcludeNoStatements() );
    }

    // isExcludeNoStatements()

    /**
     * @test
     */
    public function shouldNotBeVerboseOnConstruction()
    {

        $this->assertFalse( $this->object->isVerbose() );
    }

    // isVerbose

    /**
     * @test
     */
    public function shouldBeProdEnvOnConstruction()
    {

        $this->assertEquals( 'prod', $this->object->getEnv() );
    }

    // getEnv()

    /**
     * @test
     */
    public function shouldBeTestEnv()
    {

        $expected = 'test';

        $this->object->setEnv( $expected );

        $this->assertEquals( $expected, $this->object->getEnv() );
        $this->assertTrue( $this->object->isTestEnv() );
        $this->assertFalse( $this->object->isDevEnv() );
        $this->assertFalse( $this->object->isProdEnv() );
    }

    // isTestEnv()

    /**
     * @test
     */
    public function shouldBeDevEnv()
    {

        $expected = 'dev';

        $this->object->setEnv( $expected );

        $this->assertEquals( $expected, $this->object->getEnv() );
        $this->assertFalse( $this->object->isTestEnv() );
        $this->assertTrue( $this->object->isDevEnv() );
        $this->assertFalse( $this->object->isProdEnv() );
    }

    // isDevEnv()

    /**
     * @test
     */
    public function shouldBeProdEnv()
    {

        $expected = 'prod';

        $this->object->setEnv( $expected );

        $this->assertEquals( $expected, $this->object->getEnv() );
        $this->assertFalse( $this->object->isTestEnv() );
        $this->assertFalse( $this->object->isDevEnv() );
        $this->assertTrue( $this->object->isProdEnv() );
    }

    // isProdEnv()

    /**
     * @test
     */
    public function shouldSetRepoToken()
    {

        $expected = 'token';

        $same = $this->object->setRepoToken( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( $expected, $this->object->getRepoToken() );
    }



    // setRepoToken()

    /**
     * @test
     */
    public function shouldSetServiceName()
    {

        $expected = 'travis-ci';

        $same = $this->object->setServiceName( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( $expected, $this->object->getServiceName() );
    }

    // setServiceName()

    /**
     * @test
     */
    public function shouldSetSrcDir()
    {

        $expected = '/path/to/src';

        $same = $this->object->setSrcDir( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( $expected, $this->object->getSrcDir() );
    }

    // setSrcDir()

    /**
     * @test
     */
    public function shouldSetCloverXmlPaths()
    {

        $expected = array( '/path/to/clover.xml' );

        $same = $this->object->setCloverXmlPaths( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( $expected, $this->object->getCloverXmlPaths() );
    }

    // setCloverXmlPaths()

    /**
     * @test
     */
    public function shouldAddCloverXmlPath()
    {

        $expected = '/path/to/clover.xml';

        $same = $this->object->addCloverXmlPath( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( array( $expected ), $this->object->getCloverXmlPaths() );
    }

    // addCloverXmlPath()

    /**
     * @test
     */
    public function shouldSetJsonPath()
    {

        $expected = '/path/to/coveralls-upload.json';

        $same = $this->object->setJsonPath( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertSame( $expected, $this->object->getJsonPath() );
    }

    // setJsonPath()

    /**
     * @test
     */
    public function shouldSetDryRunFalse()
    {

        $expected = false;

        $same = $this->object->setDryRun( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertFalse( $this->object->isDryRun() );
    }

    // setDryRun()

    /**
     * @test
     */
    public function shouldSetDryRunTrue()
    {

        $expected = true;

        $same = $this->object->setDryRun( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isDryRun() );
    }

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsFalse()
    {

        $expected = false;

        $same = $this->object->setExcludeNoStatements( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertFalse( $this->object->isExcludeNoStatements() );
    }

    // setExcludeNoStatements()

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsTrue()
    {

        $expected = true;

        $same = $this->object->setExcludeNoStatements( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isExcludeNoStatements() );
    }

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsFalseUnlessFalse()
    {

        $expected = false;

        $same = $this->object->setExcludeNoStatementsUnlessFalse( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertFalse( $this->object->isExcludeNoStatements() );
    }

    // setExcludeNoStatementsUnlessFalse()

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsTrueUnlessFalse()
    {

        $expected = true;

        $same = $this->object->setExcludeNoStatementsUnlessFalse( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isExcludeNoStatements() );
    }

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsTrueIfFalsePassedAndIfTrueWasSet()
    {

        $expected = false;

        $same = $this->object->setExcludeNoStatements( true );
        $same = $this->object->setExcludeNoStatementsUnlessFalse( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isExcludeNoStatements() );
    }

    /**
     * @test
     */
    public function shouldSetExcludeNoStatementsTrueIfTruePassedAndIfTrueWasSet()
    {

        $expected = true;

        $same = $this->object->setExcludeNoStatements( true );
        $same = $this->object->setExcludeNoStatementsUnlessFalse( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isExcludeNoStatements() );
    }

    /**
     * @test
     */
    public function shouldSetVerboseFalse()
    {

        $expected = false;

        $same = $this->object->setVerbose( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertFalse( $this->object->isVerbose() );
    }

    // setVerbose()

    /**
     * @test
     */
    public function shouldSetVerboseTrue()
    {

        $expected = true;

        $same = $this->object->setVerbose( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertTrue( $this->object->isVerbose() );
    }

    /**
     * @test
     */
    public function shouldSetEnv()
    {

        $expected = 'myenv';

        $same = $this->object->setEnv( $expected );

        $this->assertSame( $same, $this->object );
        $this->assertEquals( $expected, $this->object->getEnv() );
    }

    // setEnv()

    protected function setUp()
    {

        $this->object = new Configuration();
    }
}
