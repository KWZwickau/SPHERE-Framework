<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentTransfer extends AbstractView
{

    const TBL_STUDENT_TRANSFER_ID = 'TblStudentTransfer_Id';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_COMPANY = 'TblStudentTransfer_serviceTblCompany';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_TYPE = 'TblStudentTransfer_serviceTblType';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_COURSE = 'TblStudentTransfer_serviceTblCourse';
    const TBL_STUDENT_TRANSFER_TRANSFER_DATE = 'TblStudentTransfer_TransferDate';
    const TBL_STUDENT_TRANSFER_REMARK = 'TblStudentTransfer_Remark';
    const TBL_STUDENT_TRANSFER_TBL_STUDENT = 'TblStudentTransfer_tblStudent';
    const TBL_STUDENT_TRANSFER_TBL_STUDENT_TRANSFER_TYPE = 'TblStudentTransfer_tblStudentTransferType';
    const TBL_STUDENT_TRANSFER_TYPE_ID = 'TblStudentTransferType_Id';
    const TBL_STUDENT_TRANSFER_TYPE_NAME = 'TblStudentTransferType_Name';

    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblCourse;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_tblStudentTransferType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferType_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler (Schülertransfer)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_TRANSFER_DATE, 'Schülertransfer: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_REMARK, 'Schülertransfer: Bemerkungen');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_TYPE_NAME, 'Schülertransfer: Art');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_TRANSFER_TBL_STUDENT, new ViewStudent(), ViewStudent::TBL_STUDENT_ID);
//        $this->addForeignView(self::TBL_STUDENT_TRANSFER_TBL_STUDENT, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_AGREEMENT_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_TRANSFER_TBL_STUDENT, new ViewStudentDisorder(), ViewStudentDisorder::TBL_STUDENT_DISORDER_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_TRANSFER_TBL_STUDENT, new ViewStudentFocus(), ViewStudentFocus::TBL_STUDENT_FOCUS_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_TRANSFER_TBL_STUDENT, new ViewStudentLiberation(), ViewStudentLiberation::TBL_STUDENT_LIBERATION_TBL_STUDENT);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
