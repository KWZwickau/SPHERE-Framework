<?php
namespace SPHERE\System\Database\Type;

use SPHERE\System\Database\ITypeInterface;

/**
 * Class MariaDb
 *
 * @package SPHERE\System\Database\Type
 */
class MariaDb implements ITypeInterface
{

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return 'pdo_mysql';
    }
}
