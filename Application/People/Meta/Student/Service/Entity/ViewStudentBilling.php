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
 * @Table(name="viewStudentBilling")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentBilling extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE = 'TblStudent_SchoolAttendanceStartDate';
    const TBL_STUDENT_BILLING_SERVICE_TBL_SIBLING_RANK = 'TblStudentBilling_serviceTblSiblingRank';
    const TBL_SIBLING_RANK_ID = 'TblSiblingRank_Id';
    const TBL_SIBLING_RANK_NAME = 'TblSiblingRank_Name';

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
    protected $TblStudentBilling_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentBilling_serviceTblSiblingRank;
    /**
     * @Column(type="string")
     */
    protected $TblSiblingRank_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSiblingRank_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler (Geschwisterrang)';
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
        $this->setNameDefinition(self::TBL_SIBLING_RANK_NAME, 'Geschwister: Rang');

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
