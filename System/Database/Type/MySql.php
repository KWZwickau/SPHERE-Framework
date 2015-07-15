<?php
namespace SPHERE\System\Database\Type;

use SPHERE\System\Database\ITypeInterface;

/**
 * Class MySql
 *
 * @package SPHERE\System\Database\Type
 */
class MySql implements ITypeInterface
{

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return 'pdo_mysql';
    }
}
