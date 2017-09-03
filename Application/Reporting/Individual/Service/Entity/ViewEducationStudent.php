<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Icon\IIconInterface;
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

    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    const TBL_LEVEL_NAME = 'TblLevel_Name';
    const TBL_LEVEL_DESCRIPTION = 'TblLevel_Description';
    const TBL_LEVEL_IS_CHECKED = 'TblLevel_IsChecked';

    const TBL_DIVISION_NAME = 'TblDivision_Name';
    const TBL_DIVISION_DESCRIPTION = 'TblDivision_Description';

    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';

    const TBL_PERIOD_NAME = 'TblPeriod_Name';
    const TBL_PERIOD_DESCRIPTION = 'TblPeriod_Description';
    const TBL_PERIOD_FROM_DATE = 'TblPeriod_FromDate';
    const TBL_PERIOD_TO_DATE = 'TblPeriod_ToDate';

    const TBL_SUBJECT_ACRONYM = 'TblSubject_Acronym';
    const TBL_SUBJECT_NAME = 'TblSubject_Name';
    const TBL_SUBJECT_DESCRIPTION = 'TblSubject_Description';
    const TBL_SUBJECT_GROUP_NAME = 'TblSubjectGroup_Name';
    const TBL_SUBJECT_GROUP_DESCRIPTION = 'TblSubjectGroup_Description';
    const TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE = 'TblSubjectGroup_IsAdvancedCourse';

    /**
     * @return array
     */
    public static function getConstants()
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
        $this->setNameDefinition(self::TBL_LEVEL_NAME, 'Bildung: Klassenstufe');
        $this->setNameDefinition(self::TBL_LEVEL_DESCRIPTION, 'Bildung: Stufen Beschreibung');
        $this->setNameDefinition(self::TBL_LEVEL_IS_CHECKED, 'Bildung: Klasse ist Stufenübergreifend');
        $this->setNameDefinition(self::TBL_DIVISION_NAME, 'Bildung: Klassengruppe');
        $this->setNameDefinition(self::TBL_DIVISION_DESCRIPTION, 'Bildung: Klassen Beschreibung');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Bildung: Schulart');
        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Bildung: Schuljahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Bildung: Schuljahr Beschreibung');
        $this->setNameDefinition(self::TBL_PERIOD_NAME, 'Bildung: Name des Zeitraums');
        $this->setNameDefinition(self::TBL_PERIOD_DESCRIPTION, 'Bildung: Beschreibung des Zeitraums');
        $this->setNameDefinition(self::TBL_PERIOD_FROM_DATE, 'Bildung: Von Zeitraum');
        $this->setNameDefinition(self::TBL_PERIOD_TO_DATE, 'Bildung: Bis Zeitraum');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_NAME, 'Bildung: Fachgruppe');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_DESCRIPTION, 'Bildung: Fachgruppe Beschreibung');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE, 'Bildung: Fach ist Leistungskurs');
        $this->setNameDefinition(self::TBL_SUBJECT_ACRONYM, 'Bildung: Fachkürzel');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME, 'Bildung: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_DESCRIPTION, 'Bildung: Fach Beschreibung');

        //GroupDefinition
        $this->setGroupDefinition('Klasse', array(
            self::TBL_TYPE_NAME,
            self::TBL_LEVEL_NAME,
//            self::TBL_LEVEL_DESCRIPTION,
//            self::TBL_LEVEL_IS_CHECKED,
            self::TBL_DIVISION_NAME,
            self::TBL_DIVISION_DESCRIPTION,
        ));

        $this->setGroupDefinition('Zeitraum', array(
            self::TBL_YEAR_YEAR,
            self::TBL_YEAR_DESCRIPTION,
//            self::TBL_PERIOD_NAME,
//            self::TBL_PERIOD_DESCRIPTION,
//            self::TBL_PERIOD_FROM_DATE,
//            self::TBL_PERIOD_TO_DATE
        ));

        $this->setGroupDefinition('Fach', array(
            self::TBL_SUBJECT_ACRONYM,
            self::TBL_SUBJECT_NAME,
//            self::TBL_SUBJECT_DESCRIPTION,
            self::TBL_SUBJECT_GROUP_NAME,
//            self::TBL_SUBJECT_GROUP_DESCRIPTION,
//            self::TBL_SUBJECT_GROUP_IS_ADVANCED_COURSE
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

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::TBL_YEAR_YEAR:
                $Data = Term::useService()->getPropertyList( new TblYear(), TblYear::ATTR_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_LEVEL_IS_CHECKED:
                $Data = array( 0 => 'Nein', 1 => 'Ja' );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false );
                break;

            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
        }
        return $Field;
    }
}
