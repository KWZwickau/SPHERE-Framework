<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tblAccount")
 */
class TblAccount extends \SPHERE\System\Database\Fitting\Entity
{

    const ATTR_USERNAME = 'Username';
    const ATTR_PASSWORD = 'Password';

    /**
     * @Column(type="string")
     */
    protected $Username;
    /**
     * @Column(type="string")
     */
    protected $Password;

    /**
     * @param string $Username
     */
    function __construct( $Username )
    {

        $this->Username = $Username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param string $Password
     */
    public function setPassword( $Password )
    {

        $this->Password = $Password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @param string $Username
     */
    public function setUsername( $Username )
    {

        $this->Username = $Username;
    }
}
