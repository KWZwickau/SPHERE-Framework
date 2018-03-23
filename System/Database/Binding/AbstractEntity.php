<?php
namespace SPHERE\System\Database\Binding;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractEntity
 * @package SPHERE\System\Database\Binding
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class AbstractEntity extends Element
{

}
