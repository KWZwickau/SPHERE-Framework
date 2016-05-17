<?php
namespace SPHERE\UnitTest\Suite\System\Cache;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;

/**
 * Class CacheHandlerTest
 *
 * @package SPHERE\UnitTest\Suite\System\Cache
 */
class CacheHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testFactory()
    {

        $Factory = new CacheFactory();
        $this->assertInstanceOf('SPHERE\System\Cache\CacheFactory', $Factory);
    }


    public function testSmartyHandler()
    {

        $Handler = new SmartyHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\SmartyHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Instance);
        $Status = $Instance->getStatus();
        $this->assertInstanceOf('SPHERE\System\Cache\CacheStatus', $Status);
        $this->assertInternalType('float', $Status->getAvailableSize());
        $this->assertInternalType('float', $Status->getFreeSize());
        $this->assertInternalType('int', $Status->getHitCount());
        $this->assertInternalType('int', $Status->getMissCount());
        $this->assertInternalType('float', $Status->getUsedSize());
        $this->assertInternalType('float', $Status->getWastedSize());
    }

    public function testTwigHandler()
    {

        $Handler = new TwigHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\TwigHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Instance);
        $Status = $Instance->getStatus();
        $this->assertInstanceOf('SPHERE\System\Cache\CacheStatus', $Status);
        $this->assertInternalType('float', $Status->getAvailableSize());
        $this->assertInternalType('float', $Status->getFreeSize());
        $this->assertInternalType('int', $Status->getHitCount());
        $this->assertInternalType('int', $Status->getMissCount());
        $this->assertInternalType('float', $Status->getUsedSize());
        $this->assertInternalType('float', $Status->getWastedSize());
    }

    public function testOpCacheHandler()
    {

        $Handler = new OpCacheHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Instance);
        $Status = $Instance->getStatus();
        $this->assertInstanceOf('SPHERE\System\Cache\CacheStatus', $Status);
        $this->assertInternalType('float', $Status->getAvailableSize());
        $this->assertInternalType('float', $Status->getFreeSize());
        $this->assertInternalType('int', $Status->getHitCount());
        $this->assertInternalType('int', $Status->getMissCount());
        $this->assertInternalType('float', $Status->getUsedSize());
        $this->assertInternalType('float', $Status->getWastedSize());
    }

    public function testMemoryHandler()
    {

        $Handler = new MemoryHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\MemoryHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Instance);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Instance);
        $Status = $Instance->getStatus();
        $this->assertInstanceOf('SPHERE\System\Cache\CacheStatus', $Status);
        $this->assertInternalType('float', $Status->getAvailableSize());
        $this->assertInternalType('float', $Status->getFreeSize());
        $this->assertInternalType('int', $Status->getHitCount());
        $this->assertInternalType('int', $Status->getMissCount());
        $this->assertInternalType('float', $Status->getUsedSize());
        $this->assertInternalType('float', $Status->getWastedSize());
    }
}
