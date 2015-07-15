<?php
namespace Satooshi\Bundle\CoverallsV1Bundle\Entity;

use Satooshi\Bundle\CoverallsV1Bundle\Collector\CloverXmlCoverageCollector;
use Satooshi\Bundle\CoverallsV1Bundle\Entity\Git\Commit;
use Satooshi\Bundle\CoverallsV1Bundle\Entity\Git\Git;
use Satooshi\Bundle\CoverallsV1Bundle\Entity\Git\Remote;
use Satooshi\Bundle\CoverallsV1Bundle\Version;
use Satooshi\ProjectTestCase;

/**
 * @covers Satooshi\Bundle\CoverallsV1Bundle\Entity\JsonFile
 * @covers Satooshi\Bundle\CoverallsV1Bundle\Entity\Coveralls
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
class JsonFileTest extends ProjectTestCase
{

    /**
     * @test
     */
    public function shouldNotHaveSourceFileOnConstruction()
    {

        $path = 'test.php';

        $this->assertFalse( $this->object->hasSourceFile( $path ) );
        $this->assertNull( $this->object->getSourceFile( $path ) );
    }

    /**
     * @test
     */
    public function shouldCountZeroSourceFilesOnConstruction()
    {

        $this->assertFalse( $this->object->hasSourceFiles() );
        $this->assertEmpty( $this->object->getSourceFiles() );
    }

    /**
     * @test
     */
    public function shouldNotHaveServiceNameOnConstruction()
    {

        $this->assertNull( $this->object->getServiceName() );
    }

    /**
     * @test
     */
    public function shouldNotHaveRepoTokenOnConstruction()
    {

        $this->assertNull( $this->object->getRepoToken() );
    }

    /**
     * @test
     */
    public function shouldNotHaveServiceJobIdOnConstruction()
    {

        $this->assertNull( $this->object->getServiceJobId() );
    }

    /**
     * @test
     */
    public function shouldNotHaveServiceNumberOnConstruction()
    {

        $this->assertNull( $this->object->getServiceNumber() );
    }

    /**
     * @test
     */
    public function shouldNotHaveServiceEventTypeOnConstruction()
    {

        $this->assertNull( $this->object->getServiceEventType() );
    }

    /**
     * @test
     */
    public function shouldNotHaveServiceBuildUrlOnConstruction()
    {

        $this->assertNull( $this->object->getServiceBuildUrl() );
    }


    // hasSourceFile()
    // getSourceFile()

    /**
     * @test
     */
    public function shouldNotHaveServiceBranchOnConstruction()
    {

        $this->assertNull( $this->object->getServiceBranch() );
    }

    // hasSourceFiles()
    // getSourceFiles()

    /**
     * @test
     */
    public function shouldNotHaveServicePullRequestOnConstruction()
    {

        $this->assertNull( $this->object->getServicePullRequest() );
    }

    // getServiceName()

    /**
     * @test
     */
    public function shouldNotHaveGitOnConstruction()
    {

        $this->assertNull( $this->object->getGit() );
    }

    // getRepoToken()

    /**
     * @test
     */
    public function shouldNotHaveRunAtOnConstruction()
    {

        $this->assertNull( $this->object->getRunAt() );
    }

    // getServiceJobId()

    /**
     * @test
     */
    public function shouldHaveEmptyMetrics()
    {

        $metrics = $this->object->getMetrics();

        $this->assertEquals( 0, $metrics->getStatements() );
        $this->assertEquals( 0, $metrics->getCoveredStatements() );
        $this->assertEquals( 0, $metrics->getLineCoverage() );
    }

    // getServiceNumber()

    /**
     * @test
     */
    public function shouldSetServiceName()
    {

        $expected = 'travis-ci';

        $obj = $this->object->setServiceName( $expected );

        $this->assertEquals( $expected, $this->object->getServiceName() );
        $this->assertSame( $obj, $this->object );

        return $this->object;
    }

    // getServiceEventType()

    /**
     * @test
     */
    public function shouldSetRepoToken()
    {

        $expected = 'token';

        $obj = $this->object->setRepoToken( $expected );

        $this->assertEquals( $expected, $this->object->getRepoToken() );
        $this->assertSame( $obj, $this->object );

        return $this->object;
    }

    // getServiceBuildUrl()

    /**
     * @test
     */
    public function shouldSetServiceJobId()
    {

        $expected = 'job_id';

        $obj = $this->object->setServiceJobId( $expected );

        $this->assertEquals( $expected, $this->object->getServiceJobId() );
        $this->assertSame( $obj, $this->object );

        return $this->object;
    }

    // getServiceBranch()

    /**
     * @test
     */
    public function shouldSetGit()
    {

        $remotes = array( new Remote() );
        $head = new Commit();
        $git = new Git( 'master', $head, $remotes );

        $obj = $this->object->setGit( $git );

        $this->assertSame( $git, $this->object->getGit() );
        $this->assertSame( $obj, $this->object );

        return $this->object;
    }

    // getServicePullRequest()

    /**
     * @test
     */
    public function shouldSetRunAt()
    {

        $expected = '2013-04-04 11:22:33 +0900';

        $obj = $this->object->setRunAt( $expected );

        $this->assertEquals( $expected, $this->object->getRunAt() );
        $this->assertSame( $obj, $this->object );

        return $this->object;
    }

    // getGit()

    /**
     * @test
     */
    public function shouldAddSourceFile()
    {

        $sourceFile = $this->createSourceFile();

        $this->object->addSourceFile( $sourceFile );
        $this->object->sortSourceFiles();

        $path = $sourceFile->getPath();

        $this->assertTrue( $this->object->hasSourceFiles() );
        $this->assertSame( array( $path => $sourceFile ), $this->object->getSourceFiles() );
        $this->assertTrue( $this->object->hasSourceFile( $path ) );
        $this->assertSame( $sourceFile, $this->object->getSourceFile( $path ) );
    }

    // getRunAt()

    protected function createSourceFile()
    {

        $filename = 'test.php';
        $path = $this->srcDir.DIRECTORY_SEPARATOR.$filename;

        return new SourceFile( $path, $filename );
    }

    // getMetrics()

    /**
     * @test
     */
    public function shouldConvertToArray()
    {

        $expected = array(
            'source_files' => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $this->object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$this->object );
    }

    // setServiceName()

    /**
     * @test
     */
    public function shouldConvertToArrayWithSourceFiles()
    {

        $sourceFile = $this->createSourceFile();

        $this->object->addSourceFile( $sourceFile );

        $expected = array(
            'source_files' => array( $sourceFile->toArray() ),
            'environment'  => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $this->object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$this->object );
    }

    // setRepoToken()

    /**
     * @test
     * @depends shouldSetServiceName
     */
    public function shouldConvertToArrayWithServiceName( $object )
    {

        $item = 'travis-ci';

        $expected = array(
            'service_name' => $item,
            'source_files' => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$object );
    }

    // setServiceJobId()

    /**
     * @test
     * @depends shouldSetServiceJobId
     */
    public function shouldConvertToArrayWithServiceJobId( $object )
    {

        $item = 'job_id';

        $expected = array(
            'service_job_id' => $item,
            'source_files'   => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$object );
    }

    // setGit()

    /**
     * @test
     * @depends shouldSetRepoToken
     */
    public function shouldConvertToArrayWithRepoToken( $object )
    {

        $item = 'token';

        $expected = array(
            'repo_token'   => $item,
            'source_files' => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$object );
    }

    // setRunAt()

    /**
     * @test
     * @depends shouldSetGit
     */
    public function shouldConvertToArrayWithGit( $object )
    {

        $remotes = array( new Remote() );
        $head = new Commit();
        $git = new Git( 'master', $head, $remotes );

        $expected = array(
            'git'          => $git->toArray(),
            'source_files' => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertSame( $expected, $object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$object );
    }



    // addSourceFile()
    // sortSourceFiles()

    /**
     * @test
     * @depends shouldSetRunAt
     */
    public function shouldConvertToArrayWithRunAt( $object )
    {

        $item = '2013-04-04 11:22:33 +0900';

        $expected = array(
            'run_at'       => $item,
            'source_files' => array(),
            'environment' => array( 'packagist_version' => Version::VERSION ),
        );

        $this->assertEquals( $expected, $object->toArray() );
        $this->assertEquals( json_encode( $expected ), (string)$object );
    }

    // toArray()

    /**
     * @test
     */
    public function shouldFillJobsForServiceJobId()
    {

        $serviceName = 'travis-ci';
        $serviceJobId = '1.1';

        $env = array();
        $env['CI_NAME'] = $serviceName;
        $env['CI_JOB_ID'] = $serviceJobId;

        $object = $this->collectJsonFile();

        $same = $object->fillJobs( $env );

        $this->assertSame( $same, $object );
        $this->assertEquals( $serviceName, $object->getServiceName() );
        $this->assertEquals( $serviceJobId, $object->getServiceJobId() );
    }

    protected function collectJsonFile()
    {

        $xml = $this->createCloverXml();
        $collector = new CloverXmlCoverageCollector();

        return $collector->collect( $xml, $this->srcDir );
    }

    // service_name

    protected function createCloverXml()
    {

        $xml = $this->getCloverXml();

        return simplexml_load_string( $xml );
    }

    // service_job_id

    protected function getCloverXml()
    {

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="1365848893">
  <project timestamp="1365848893">
    <file name="%s/test.php">
      <class name="TestFile" namespace="global">
        <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="1" coveredstatements="0" elements="2" coveredelements="0"/>
      </class>
      <line num="5" type="method" name="__construct" crap="1" count="0"/>
      <line num="7" type="stmt" count="1"/>
    </file>
    <file name="%s/TestInterface.php">
      <class name="TestInterface" namespace="global">
        <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="0" coveredstatements="0" elements="1" coveredelements="0"/>
      </class>
      <line num="5" type="method" name="hello" crap="1" count="0"/>
    </file>
    <file name="%s/AbstractClass.php">
      <class name="AbstractClass" namespace="global">
        <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="0" coveredstatements="0" elements="1" coveredelements="0"/>
      </class>
      <line num="5" type="method" name="hello" crap="1" count="0"/>
    </file>
    <file name="dummy.php">
      <class name="TestFile" namespace="global">
        <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="1" coveredstatements="0" elements="2" coveredelements="0"/>
      </class>
      <line num="5" type="method" name="__construct" crap="1" count="0"/>
      <line num="7" type="stmt" count="0"/>
    </file>
    <package name="Hoge">
      <file name="%s/test2.php">
        <class name="TestFile" namespace="Hoge">
          <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="1" coveredstatements="0" elements="2" coveredelements="0"/>
        </class>
        <line num="6" type="method" name="__construct" crap="1" count="0"/>
        <line num="8" type="stmt" count="0"/>
      </file>
    </package>
  </project>
</coverage>
XML;
        return sprintf( $xml, $this->srcDir, $this->srcDir, $this->srcDir, $this->srcDir );
    }

    // repo_token

    /**
     * @test
     */
    public function shouldFillJobsForServiceNumber()
    {

        $repoToken = 'token';
        $serviceName = 'circleci';
        $serviceNumber = '123';

        $env = array();
        $env['COVERALLS_REPO_TOKEN'] = $repoToken;
        $env['CI_NAME'] = $serviceName;
        $env['CI_BUILD_NUMBER'] = $serviceNumber;

        $object = $this->collectJsonFile();

        $same = $object->fillJobs( $env );

        $this->assertSame( $same, $object );
        $this->assertEquals( $repoToken, $object->getRepoToken() );
        $this->assertEquals( $serviceName, $object->getServiceName() );
        $this->assertEquals( $serviceNumber, $object->getServiceNumber() );
    }

    // git

    /**
     * @test
     */
    public function shouldFillJobsForStandardizedEnvVars()
    {

        /*
         * CI_NAME=codeship
         * CI_BUILD_NUMBER=108821
         * CI_BUILD_URL=https://www.codeship.io/projects/2777/builds/108821
         * CI_BRANCH=master
         * CI_PULL_REQUEST=false
         */

        $repoToken = 'token';
        $serviceName = 'codeship';
        $serviceNumber = '108821';
        $serviceBuildUrl = 'https://www.codeship.io/projects/2777/builds/108821';
        $serviceBranch = 'master';
        $servicePullRequest = 'false';

        $env = array();
        $env['COVERALLS_REPO_TOKEN'] = $repoToken;
        $env['CI_NAME'] = $serviceName;
        $env['CI_BUILD_NUMBER'] = $serviceNumber;
        $env['CI_BUILD_URL'] = $serviceBuildUrl;
        $env['CI_BRANCH'] = $serviceBranch;
        $env['CI_PULL_REQUEST'] = $servicePullRequest;

        $object = $this->collectJsonFile();

        $same = $object->fillJobs( $env );

        $this->assertSame( $same, $object );
        $this->assertEquals( $repoToken, $object->getRepoToken() );
        $this->assertEquals( $serviceName, $object->getServiceName() );
        $this->assertEquals( $serviceNumber, $object->getServiceNumber() );
        $this->assertEquals( $serviceBuildUrl, $object->getServiceBuildUrl() );
        $this->assertEquals( $serviceBranch, $object->getServiceBranch() );
        $this->assertEquals( $servicePullRequest, $object->getServicePullRequest() );
    }

    // run_at

    /**
     * @test
     */
    public function shouldFillJobsForServiceEventType()
    {

        $repoToken = 'token';
        $serviceName = 'php-coveralls';
        $serviceEventType = 'manual';

        $env = array();
        $env['COVERALLS_REPO_TOKEN'] = $repoToken;
        $env['COVERALLS_RUN_LOCALLY'] = '1';
        $env['COVERALLS_EVENT_TYPE'] = $serviceEventType;
        $env['CI_NAME'] = $serviceName;

        $object = $this->collectJsonFile();

        $same = $object->fillJobs( $env );

        $this->assertSame( $same, $object );
        $this->assertEquals( $repoToken, $object->getRepoToken() );
        $this->assertEquals( $serviceName, $object->getServiceName() );
        $this->assertNull( $object->getServiceJobId() );
        $this->assertEquals( $serviceEventType, $object->getServiceEventType() );
    }

    // fillJobs()

    /**
     * @test
     */
    public function shouldFillJobsForUnsupportedJob()
    {

        $repoToken = 'token';

        $env = array();
        $env['COVERALLS_REPO_TOKEN'] = $repoToken;

        $object = $this->collectJsonFile();

        $same = $object->fillJobs( $env );

        $this->assertSame( $same, $object );
        $this->assertEquals( $repoToken, $object->getRepoToken() );
    }

    /**
     * @test
     * @expectedException Satooshi\Bundle\CoverallsV1Bundle\Entity\Exception\RequirementsNotSatisfiedException
     */
    public function throwRuntimeExceptionOnFillingJobsIfInvalidEnv()
    {

        $env = array();

        $object = $this->collectJsonFile();

        $object->fillJobs( $env );
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function throwRuntimeExceptionOnFillingJobsWithoutSourceFiles()
    {

        $env = array();
        $env['TRAVIS'] = true;
        $env['TRAVIS_JOB_ID'] = '1.1';

        $object = $this->collectJsonFileWithoutSourceFiles();

        $object->fillJobs( $env );
    }

    protected function collectJsonFileWithoutSourceFiles()
    {

        $xml = $this->createNoSourceCloverXml();
        $collector = new CloverXmlCoverageCollector();

        return $collector->collect( $xml, $this->srcDir );
    }

    protected function createNoSourceCloverXml()
    {

        $xml = $this->getNoSourceCloverXml();

        return simplexml_load_string( $xml );
    }

    protected function getNoSourceCloverXml()
    {

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="1365848893">
  <project timestamp="1365848893">
    <file name="dummy.php">
      <class name="TestFile" namespace="global">
        <metrics methods="1" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="1" coveredstatements="0" elements="2" coveredelements="0"/>
      </class>
      <line num="5" type="method" name="__construct" crap="1" count="0"/>
      <line num="7" type="stmt" count="0"/>
    </file>
  </project>
</coverage>
XML;
    }

    /**
     * @test
     */
    public function shouldReportLineCoverage()
    {

        $object = $this->collectJsonFile();

        $this->assertEquals( 50, $object->reportLineCoverage() );

        $metrics = $object->getMetrics();

        $this->assertEquals( 2, $metrics->getStatements() );
        $this->assertEquals( 1, $metrics->getCoveredStatements() );
        $this->assertEquals( 50, $metrics->getLineCoverage() );
    }

    // reportLineCoverage()

    /**
     * @test
     */
    public function shouldExcludeNoStatementsFiles()
    {

        $srcDir = $this->srcDir.DIRECTORY_SEPARATOR;

        $object = $this->collectJsonFile();

        // before excluding
        $sourceFiles = $object->getSourceFiles();
        $this->assertCount( 4, $sourceFiles );

        // filenames
        $paths = array_keys( $sourceFiles );
        $filenames = array_map( function ( $path ) use ( $srcDir ) {

            return str_replace( $srcDir, '', $path );
        }, $paths );

        $this->assertContains( 'test.php', $filenames );
        $this->assertContains( 'test2.php', $filenames );
        $this->assertContains( 'TestInterface.php', $filenames );
        $this->assertContains( 'AbstractClass.php', $filenames );

        // after excluding
        $object->excludeNoStatementsFiles();

        $sourceFiles = $object->getSourceFiles();
        $this->assertCount( 2, $sourceFiles );

        // filenames
        $paths = array_keys( $sourceFiles );
        $filenames = array_map( function ( $path ) use ( $srcDir ) {

            return str_replace( $srcDir, '', $path );
        }, $paths );

        $this->assertContains( 'test.php', $filenames );
        $this->assertContains( 'test2.php', $filenames );
        $this->assertNotContains( 'TestInterface.php', $filenames );
        $this->assertNotContains( 'AbstractClass.php', $filenames );
    }

    // excludeNoStatementsFiles()

    protected function setUp()
    {

        $this->projectDir = realpath( __DIR__.'/../../../..' );

        $this->setUpDir( $this->projectDir );

        $this->object = new JsonFile();
    }
}
