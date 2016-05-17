<?php
namespace SPHERE\UnitTest\Suite\System\Cache;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\CookieHandler;
use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Cache\Handler\DefaultHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
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

    public function testDefaultHandler()
    {

        $Handler = new DefaultHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\DefaultHandler', $Instance);
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

    public function testMemcachedHandler()
    {

        $Handler = new MemcachedHandler();
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

    public function testDataCacheHandler()
    {

        $Handler = new DataCacheHandler('UnitTest');
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

    public function testCookieHandler()
    {

        $Handler = new CookieHandler();
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\AbstractHandler', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\HandlerInterface', $Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\CacheInterface', $Handler);
        $Instance = (new CacheFactory())->createHandler($Handler);
        $this->assertInstanceOf('SPHERE\System\Cache\Handler\CookieHandler', $Instance);
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

    public function testAPCuHandler()
    {

        $Handler = new APCuHandler();
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
}
