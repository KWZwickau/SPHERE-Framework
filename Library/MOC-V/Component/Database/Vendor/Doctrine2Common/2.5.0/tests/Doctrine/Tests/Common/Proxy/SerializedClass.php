<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class SerializedClass
{

    /**
     * @var mixed
     */
    public $baz = 'baz';
    /**
     * @var mixed
     */
    protected $bar = 'bar';
    /**
     * @var mixed
     */
    private $foo = 'foo';

    /**
     * @return mixed|string
     */
    public function getFoo()
    {

        return $this->foo;
    }

    /**
     * @param mixed $foo
     */
    public function setFoo($foo)
    {

        $this->foo = $foo;
    }

    /**
     * @return mixed|string
     */
    public function getBar()
    {

        return $this->bar;
    }

    /**
     * @param $bar
     */
    public function setBar($bar)
    {

        $this->bar = $bar;
    }

    /**
     * @return mixed|string
     */
    public function getBaz()
    {

        return $this->baz;
    }

    /**
     * @param $baz
     */
    public function setBaz($baz)
    {

        $this->baz = $baz;
    }
}
