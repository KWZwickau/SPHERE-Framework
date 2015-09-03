<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

$var = 1;
function () use ($var)
{
}

;

class NamespaceWithClosureDeclaration
{

}
