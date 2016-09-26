<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudentDisorder")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentDisorder extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';
//    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
//    const TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE = 'TblStudent_SchoolAttendanceStartDate';
    const TBL_STUDENT_DISORDER_ID = 'TblStudentDisorder_Id';
    const TBL_STUDENT_DISORDER_TBL_STUDENT = 'TblStudentDisorder_tblStudent';
    const TBL_STUDENT_DISORDER_TBL_STUDENT_DISORDER_TYPE = 'TblStudentDisorder_tblStudentDisorderType';
    const TBL_STUDENT_DISORDER_TYPE_ID = 'TblStudentDisorderType_Id';
    const TBL_STUDENT_DISORDER_TYPE_NAME = 'TblStudentDisorderType_Name';
//    const TBL_STUDENT_DISORDER_TYPE_DESCRIPTION = 'TblStudentDisorderType_Description';

    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_serviceTblPerson;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudent_Identifier;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudent_SchoolAttendanceStartDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorder_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorder_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorder_tblStudentDisorderType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorderType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorderType_Name;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentDisorderType_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler (Förderbedarf: Teilleistungsstörung)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Schüler: Schülernummer');
//        $this->setNameDefinition(self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE, 'Schüler: Schulpflicht beginn');
        $this->setNameDefinition(self::TBL_STUDENT_DISORDER_TYPE_NAME, 'Teilleistungsstörung: Name');
//        $this->setNameDefinition(self::TBL_STUDENT_DISORDER_TYPE_DESCRIPTION, 'Teilleistungsstörung: Beschreibung');

    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
//        $this->addForeignView(self::TBL_STUDENT_AGREEMENT_TBL_STUDENT, new ViewStudent(), ViewStudent::TBL_STUDENT_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
