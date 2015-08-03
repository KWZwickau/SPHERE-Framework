<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPaymentType")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblPaymentType extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName( $Name )
    {

        $this->Name = $Name;
    }

}