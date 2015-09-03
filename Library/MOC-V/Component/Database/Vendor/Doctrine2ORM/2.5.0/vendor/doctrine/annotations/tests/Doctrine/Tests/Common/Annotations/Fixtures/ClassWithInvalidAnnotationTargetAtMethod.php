<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithInvalidAnnotationTargetAtMethod
{

    /**
     * @AnnotationTargetClass("functionName")
     */
    public function functionName($param)
    {

    }
}
