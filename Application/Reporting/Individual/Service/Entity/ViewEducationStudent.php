<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewEducationStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewEducationStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_LEVEL_NAME = 'TblLevel_Name';
    const TBL_LEVEL_DESCRIPTION = 'TblLevel_Description';
    const TBL_LEVEL_IS_CHECKED = 'TblLevel_IsChecked';
    const TBL_DIVISION_NAME = 'TblDivision_Name';
    const TBL_DIVISION_DESCRIPTION = 'TblDivision_Description';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';
    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';
    const TBL_PERIOD_NAME = 'TblPeriod_Name';
    const TBL_PERIOD_DESCRIPTION = 'TblPeriod_Description';
    const TBL_PERIOD_FROM_DATE = 'TblPeriod_FromDate';
    const TBL_PERIOD_TO_DATE = 'TblPeriod_ToDate';
    const TBL_SUBJECT_GROUP_NAME = 'TblSubjectGroup_Name';
    const TBL_SUBJECT_GROUP_DESCRIPTION = 'TblSubjectGroup_Description';
    const TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE = 'TblSubjectGroup_IsAdvancedCourse';
    const TBL_SUBJECT_ACRONYM = 'TblSubject_Acronym';
    const TBL_SUBJECT_NAME = 'TblSubject_Name';
    const TBL_SUBJECT_DESCRIPTION = 'TblSubject_Description';

    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Name;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Description;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_IsChecked;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Name;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Description;
    /**
     * @Column(type="string")
     */
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_Name;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_Description;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_FromDate;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_ToDate;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectGroup_Description;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectGroup_IsAdvancedCourse;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Acronym;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Description;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_LEVEL_NAME, 'Stufe');
        $this->setNameDefinition(self::TBL_LEVEL_DESCRIPTION, 'Stufen Beschreibung');
        $this->setNameDefinition(self::TBL_LEVEL_IS_CHECKED, 'Stufenübergreifende Klasse');
        $this->setNameDefinition(self::TBL_DIVISION_NAME, 'Klasse');
        $this->setNameDefinition(self::TBL_DIVISION_DESCRIPTION, 'Klassen beschreibung');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Schulart');
        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Schuljahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Schuljahr Beschreibung');
        $this->setNameDefinition(self::TBL_PERIOD_NAME, 'Name des Zeitraums');
        $this->setNameDefinition(self::TBL_PERIOD_DESCRIPTION, 'Beschreibung des Zeitraums');
        $this->setNameDefinition(self::TBL_PERIOD_FROM_DATE, 'Von Zeitraum');
        $this->setNameDefinition(self::TBL_PERIOD_TO_DATE, 'Bis Zeitraum');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_NAME, 'Fachgruppe');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_DESCRIPTION, 'Fachgruppe Beschreibung');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE, 'Leistungskurs');
        $this->setNameDefinition(self::TBL_SUBJECT_ACRONYM, 'Acronym');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME, 'Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_DESCRIPTION, 'Fach Beschreibung');

        //GroupDefinition
        $this->setGroupDefinition('Zeitraum', array(
            self::TBL_YEAR_YEAR,
            self::TBL_YEAR_DESCRIPTION,
            self::TBL_PERIOD_NAME,
            self::TBL_PERIOD_DESCRIPTION,
            self::TBL_PERIOD_FROM_DATE,
            self::TBL_PERIOD_TO_DATE
        ));

        $this->setGroupDefinition('Klasse', array(
            self::TBL_LEVEL_NAME,
            self::TBL_LEVEL_DESCRIPTION,
            self::TBL_LEVEL_IS_CHECKED,
            self::TBL_DIVISION_NAME,
            self::TBL_DIVISION_DESCRIPTION,
            self::TBL_TYPE_NAME
        ));

        $this->setGroupDefinition('Fach', array(
            self::TBL_SUBJECT_NAME,
            self::TBL_SUBJECT_ACRONYM,
            self::TBL_SUBJECT_DESCRIPTION,
            self::TBL_SUBJECT_GROUP_NAME,
            self::TBL_SUBJECT_GROUP_DESCRIPTION,
            self::TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
//        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }
}