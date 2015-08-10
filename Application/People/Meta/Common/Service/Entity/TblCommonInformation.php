<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommonInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblCommonInformation extends Element
{

    const VALUE_IS_ASSISTANCE_NULL = 0;
    const VALUE_IS_ASSISTANCE_YES = 1;
    const VALUE_IS_ASSISTANCE_NO = 2;
}
