<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithAnnotationEnum
{

    /**
     * @AnnotationEnum(AnnotationEnum::ONE)
     */
    public $foo;
    /**
     * @AnnotationEnum("FOUR")
     */
    public $invalidProperty;

    /**
     * @AnnotationEnum("TWO")
     */
    public function bar()
    {
    }

    /**
     * @AnnotationEnum(5)
     */
    public function invalidMethod()
    {
    }
}
