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
    const TBL_STUDENT_TBL_STUDENT_MEDICAL_RECORD = 'TblStudent_tblStudentMedicalRecord';
    const TBL_STUDENT_TBL_STUDENT_TRANSPORT = 'TblStudent_tblStudentTransport';
    const TBL_STUDENT_TBL_STUDENT_BILLING = 'TblStudent_tblStudentBilling';
    const TBL_STUDENT_TBL_STUDENT_LOCKER = 'TblStudent_tblStudentLocker';
    const TBL_STUDENT_TBL_STUDENT_BAPTISM = 'TblStudent_tblStudentBaptism';
    const TBL_STUDENT_TBL_STUDENT_INTEGRATION = 'TblStudent_tblStudentIntegration';

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

        $this->setNameDefinition(self::TBL_STUDENT_DISORDER_TYPE_NAME, 'Förderbedarf: Teilleistungsstörungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_AGREEMENT_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_TBL_STUDENT_BAPTISM, new ViewStudentBaptism(), ViewStudentBaptism::TBL_STUDENT_BAPTISM_ID);
//        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentDisorder(), ViewStudentDisorder::TBL_STUDENT_DISORDER_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentFocus(), ViewStudentFocus::TBL_STUDENT_FOCUS_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_TBL_STUDENT_INTEGRATION, new ViewStudentIntegration(), ViewStudentIntegration::TBL_STUDENT_INTEGRATION_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentLiberation(), ViewStudentLiberation::TBL_STUDENT_LIBERATION_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_TBL_STUDENT_LOCKER, new ViewStudentLocker(), ViewStudentLocker::TBL_STUDENT_LOCKER_ID);
        $this->addForeignView(self::TBL_STUDENT_TBL_STUDENT_MEDICAL_RECORD, new ViewStudentMedicalRecord(), ViewStudentMedicalRecord::TBL_STUDENT_MEDICAL_RECORD_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentTransfer(), ViewStudentTransfer::TBL_STUDENT_TRANSFER_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_TBL_STUDENT_TRANSPORT, new ViewStudentTransport(), ViewStudentTransport::TBL_STUDENT_TRANSPORT_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
