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
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE = 'TblStudent_SchoolAttendanceStartDate';
    const TBL_STUDENT_TBL_STUDENT_MEDICAL_RECORD = 'TblStudent_tblStudentMedicalRecord';
    const TBL_STUDENT_TBL_STUDENT_TRANSPORT = 'TblStudent_tblStudentTransport';
    const TBL_STUDENT_TBL_STUDENT_BILLING = 'TblStudent_tblStudentBilling';
    const TBL_STUDENT_TBL_STUDENT_LOCKER = 'TblStudent_tblStudentLocker';
    const TBL_STUDENT_TBL_STUDENT_BAPTISM = 'TblStudent_tblStudentBaptism';
    const TBL_STUDENT_TBL_STUDENT_INTEGRATION = 'TblStudent_tblStudentIntegration';
    const TBL_STUDENT_TBL_STUDENT_RELEASE = 'TblStudent_tblStudentRelease';

    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_SchoolAttendanceStartDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentMedicalRecord;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentTransport;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentBilling;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentLocker;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentBaptism;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_tblStudentIntegration;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Schüler: Schülernummer');
        $this->setNameDefinition(self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE, 'Schüler: Schulpflicht beginn');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentBaptism(), ViewStudentBaptism::TBL_STUDENT_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
