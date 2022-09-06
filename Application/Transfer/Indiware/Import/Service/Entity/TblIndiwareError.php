<?php


namespace SPHERE\Application\Transfer\Indiware\Import\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareError")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareError extends Element
{

    const TYPE_LECTURE_SHIP = 'LectureShip';
//    const TYPE_STUDENT = 'Student';
    const TYPE_STUDENT_COURSE = 'StudentCourse';

    const ATTR_TYPE = 'Type';
    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_WARNING = 'Warning';
    const ATTR_COMPARE_STRING = 'CompareString';

    /**
     * @Column(type="string")
     */
    protected $Type;
    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="string")
     */
    protected $Warning;
    /**
     * @Column(type="string")
     */
    protected $CompareString;


    /**
     * @return string
     */
    public function getType()
    {

        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {

        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getWarning()
    {

        return $this->Warning;
    }

    /**
     * @param string $Warning
     */
    public function setWarning($Warning)
    {

        $this->Warning = $Warning;
    }

    /**
     * @return string
     */
    public function getCompareString()
    {

        return $this->CompareString;
    }

    /**
     * @param string $CompareString
     */
    public function setCompareString($CompareString)
    {

        $this->CompareString = $CompareString;
    }

    /**
     * @param string $Value1
     * @param string $Value2
     * @param string $Value3
     * @param string $Value4
     *
     * @return string
     */
    public static function fetchCompareString($Value1, $Value2, $Value3, $Value4 = '')
    {

        return $Value1.'_'.$Value2.'_'.$Value3.($Value4 ? '_'.$Value4: '');
    }
}