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
 * @Table(name="viewStudentFocus")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentFocus extends AbstractView
{

    const TBL_STUDENT_FOCUS_ID = 'TblStudentFocus_Id';
    const TBL_STUDENT_FOCUS_TBL_STUDENT = 'TblStudentFocus_tblStudent';
    const TBL_STUDENT_FOCUS_TBL_STUDENT_FOCUS_TYPE = 'TblStudentFocus_tblStudentFocusType';

    const TBL_STUDENT_FOCUS_TYPE_ID = 'TblStudentFocusType_Id';
    const TBL_STUDENT_FOCUS_TYPE_NAME = 'TblStudentFocusType_Name';
//    const TBL_STUDENT_FOCUS_TYPE_DESCRIPTION = 'TblStudentFocusType_Description';

    /**
     * @Column(type="string")
     */
    protected $TblStudentFocus_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocus_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocus_tblStudentFocusType;

    /**
     * @Column(type="string")
     */
    protected $TblStudentFocusType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocusType_Name;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentFocusType_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler (Förderbedarf: Schwerpunkte)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_FOCUS_TYPE_NAME, 'Förderbedarf: Schwerpunkte');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_FOCUS_TBL_STUDENT, new ViewStudent(), ViewStudent::TBL_STUDENT_ID);
//        $this->addForeignView(self::TBL_STUDENT_FOCUS_TBL_STUDENT, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_AGREEMENT_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_FOCUS_TBL_STUDENT, new ViewStudentDisorder(), ViewStudentDisorder::TBL_STUDENT_DISORDER_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_FOCUS_TBL_STUDENT, new ViewStudentLiberation(), ViewStudentLiberation::TBL_STUDENT_LIBERATION_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_FOCUS_TBL_STUDENT, new ViewStudentTransfer(), ViewStudentTransfer::TBL_STUDENT_TRANSFER_TBL_STUDENT);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
