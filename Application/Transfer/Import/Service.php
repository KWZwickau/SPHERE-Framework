<?php
namespace SPHERE\Application\Transfer\Import;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Service
{
    /**
     * @var array
     */
    private $Location;

    /** @var PhpExcel $Document */
    private $Document;

    public function __construct($Location, $Document)
    {
        $this->Location = $Location;
        $this->Document = $Document;
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return array
     */
    public function splitStreet($columnName, $RunY)
    {
        $streetName = '';
        $streetNumber = '';
        $street = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($street != '') {
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }

        return array($streetName, $streetNumber);
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return string
     */
    public function formatZipCode($columnName, $RunY)
    {
        if ($this->Location[$columnName] !== null) {
            $code = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
            if ($code) {
                return str_pad(
                    $code,
                    5,
                    "0",
                    STR_PAD_LEFT
                );
            }
        }

        return '';
    }

    /**
     * @param $columnName
     * @param $RunY
     * @param TblStudent $tblStudent
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string $trueValue
     */
    public function setStudentAgreement(
        $columnName,
        $RunY,
        TblStudent $tblStudent,
        TblStudentAgreementCategory $tblStudentAgreementCategory,
        $trueValue = 'ja'
    ) {
        $agreement = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($agreement == $trueValue) {
            if (($tblStudentAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))) {
                foreach ($tblStudentAgreementTypeList as $tblStudentAgreementType) {
                    Student::useService()->insertStudentAgreement($tblStudent, $tblStudentAgreementType);
                }
            }
        }
    }

    /**
     * @param $columnName
     * @param $RunY
     * @param $error
     *
     * @return false|string
     */
    public function formatDateString($columnName, $RunY, &$error)
    {
        if ($this->Location[$columnName] !== null) {
            $date = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
            if ($date != '') {
                $len = strlen($date);
                switch ($len) {
                    case 5:
                        $result = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($date));
                        break;
                    case 6:
                        $result = substr($date, 0, 2) . '.' . substr($date, 2, 2) . '.' . substr($date, 4, 2);
                        break;
                    case 7:
                        $date = '0' . $date;
                    case 8:
                        $result = substr($date, 0, 2) . '.' . substr($date, 2, 2) . '.' . substr($date, 4, 4);
                        break;
                    default:
                        $error[] = 'Zeile: ' . ($RunY + 1) . $columnName . ':' . $date
                            . ' konnte nicht in ein Datum umgewandelt werden.';
                        $result = '';
                }

                return $result;
            }
        }

        return '';
    }
}