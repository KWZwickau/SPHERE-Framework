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
 * @Table(name="viewStudentDisorder")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentDisorder extends AbstractView
{

    const TBL_STUDENT_DISORDER_ID = 'TblStudentDisorder_Id';
    const TBL_STUDENT_DISORDER_TBL_STUDENT = 'TblStudentDisorder_tblStudent';
    const TBL_STUDENT_DISORDER_TBL_STUDENT_DISORDER_TYPE = 'TblStudentDisorder_tblStudentDisorderType';

    const TBL_STUDENT_DISORDER_TYPE_ID = 'TblStudentDisorderType_Id';
    const TBL_STUDENT_DISORDER_TYPE_NAME = 'TblStudentDisorderType_Name';
//    const TBL_STUDENT_DISORDER_TYPE_DESCRIPTION = 'TblStudentDisorderType_Description';

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

        $this->addForeignView(self::TBL_STUDENT_DISORDER_TBL_STUDENT, new ViewStudent(), ViewStudent::TBL_STUDENT_ID);
//        $this->addForeignView(self::TBL_STUDENT_DISORDER_TBL_STUDENT, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_AGREEMENT_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_DISORDER_TBL_STUDENT, new ViewStudentFocus(), ViewStudentFocus::TBL_STUDENT_FOCUS_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_DISORDER_TBL_STUDENT, new ViewStudentLiberation(), ViewStudentLiberation::TBL_STUDENT_LIBERATION_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_DISORDER_TBL_STUDENT, new ViewStudentTransfer(), ViewStudentTransfer::TBL_STUDENT_TRANSFER_TBL_STUDENT);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
