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
 * @deprecated Muss noch überdacht werden! (Anzeige von Subjectdaten ohne Subject ist sinnlos!)
 * @Entity
 * @Table(name="viewStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentSubject extends AbstractView
{

    const TBL_STUDENT_SUBJECT_ID = 'TblStudentSubject_Id';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT = 'TblStudentSubject_tblStudent';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT_SUBJECT_TYPE = 'TblStudentSubject_tblStudentSubjectType';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT_SUBJECT_RANKING = 'TblStudentSubject_tblStudentSubjectRanking';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_SUBJECT = 'TblStudentSubject_serviceTblSubject';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_LEVEL_FROM = 'TblStudentSubject_serviceTblLevelFrom';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_LEVEL_TILL = 'TblStudentSubject_serviceTblLevelTill';

    const TBL_STUDENT_SUBJECT_RANKING_ID = 'TblStudentSubjectRanking_Id';
    const TBL_STUDENT_SUBJECT_RANKING_NAME = 'TblStudentSubjectRanking_Name';

    const TBL_STUDENT_SUBJECT_TYPE_ID = 'TblStudentSubjectType_Id';
    const TBL_STUDENT_SUBJECT_TYPE_NAME = 'TblStudentSubjectType_Name';

    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudentSubjectType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudentSubjectRanking;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblSubject;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblLevelFrom;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblLevelTill;

    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectRanking_Id;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentSubjectRanking_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectRanking_Name;

    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectType_Id;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentSubjectType_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectType_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schüler (Fächer)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_SUBJECT_RANKING_NAME, 'Fach: Nummerierung');
        $this->setNameDefinition(self::TBL_STUDENT_SUBJECT_TYPE_NAME, 'Fach: Kategorie');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        //ToDO
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudent(), ViewStudent::TBL_STUDENT_ID);
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudentAgreement(), ViewStudentAgreement::TBL_STUDENT_AGREEMENT_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudentDisorder(), ViewStudentDisorder::TBL_STUDENT_DISORDER_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudentFocus(), ViewStudentFocus::TBL_STUDENT_FOCUS_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudentLiberation(), ViewStudentLiberation::TBL_STUDENT_LIBERATION_TBL_STUDENT);
//        $this->addForeignView(self::TBL_STUDENT_SUBJECT_TBL_STUDENT, new ViewStudentTransfer(), ViewStudentTransfer::TBL_STUDENT_TRANSFER_TBL_STUDENT);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
