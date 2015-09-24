<?php
namespace SPHERE\UnitTest\Suite\System;

use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Authenticator\Type\Post;

/**
 * Class AuthenticatorTest
 *
 * @package SPHERE\UnitTest\Suite
 */
class AuthenticatorTest extends \PHPUnit_Framework_TestCase
{

    public function testTypeGet()
    {

        $Mock = new Get();
        $this->assertInstanceOf('SPHERE\System\Authenticator\ITypeInterface', $Mock);
        $this->assertInternalType('string', $Mock->getConfiguration());
        $this->assertEquals('Get', $Mock->getConfiguration());
    }

    public function testTypePost()
    {

        $Mock = new Post();
        $this->assertInstanceOf('SPHERE\System\Authenticator\ITypeInterface', $Mock);
        $this->assertInternalType('string', $Mock->getConfiguration());
        $this->assertEquals('Post', $Mock->getConfiguration());
    }

    public function testAuthenticator()
    {

        $Mock = new Authenticator(new Get());
        $this->assertInstanceOf('SPHERE\System\Authenticator\ITypeInterface', $Mock->getAuthenticator());

        $Mock = new Authenticator(new Post());
        $this->assertInstanceOf('SPHERE\System\Authenticator\ITypeInterface', $Mock->getAuthenticator());
    }
}
