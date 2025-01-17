<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\System\Extension\Extension;

class SerialLetterForm extends Extension
{

    /**
     * @return FormGroup
     */
    public function formSerialLetterStandardGroup()
    {

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                ),
                new FormColumn(
                    new HiddenField('IsPost')
                ),
            )),
        ));
    }

    /**
     * @return Form
     */
    public function formSerialLetter()
    {

        return new Form($this->formSerialLetterStandardGroup());
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     *
     * @return Form
     */
    public function formFilterPersonGroup(TblSerialLetter $tblSerialLetter = null)
    {
        $FormGroup = array();
        $FormGroup[] = (new SerialLetterForm())->formSerialLetterStandardGroup();

        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter können über das leeren der Gruppe entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterCount = 1;
                foreach ($tblFilterFieldList as $tblFilterField) {
                    // found Filter
                    $FormGroup[] = new FormGroup(new FormRow(array(
                            new FormColumn(
                                new SelectBox('Filter['.$tblFilterField->getField().']['.$tblFilterField->getFilterNumber().']',
                                    'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                                , 3),
                        ))
                    );
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('Filter[TblGroup_Id][]',
                        'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                    , 3),
            )));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('Filter[TblGroup_Id][0]',
                        'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                    , 3),
            )));
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     *
     * @return Form
     */
    public function formFilterStudent(TblSerialLetter $tblSerialLetter = null)
    {

        $FormGroup = array();
        $FormGroup[] = (new SerialLetterForm())->formSerialLetterStandardGroup();

        $MaxFieldNumber = 0;
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = $this->getStudentFilterContent($FilterNumber);
                       if($MaxFieldNumber <= $FilterNumber){
                           $MaxFieldNumber = $FilterNumber + 1;
                       }
                    }
                }
            }
        } else {
            // POST Student createSite
            $Global = $this->getGlobal();
            if(($tblYearList = Term::useService()->getYearByNow())){
                $Global->POST['Filter']['TblYear_Id'][0] = current($tblYearList)->getId();
                $Global->savePost();
            }
        }
        $FormGroup[] = $this->getStudentFilterContent($MaxFieldNumber);
        return new Form(
            $FormGroup
        );
    }

    /**
     * @param $FilterNumber
     *
     * @return FormGroup
     */
    private function getStudentFilterContent($FilterNumber)
    {

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[TblYear_Id]['.$FilterNumber.']', 'Bildung: Schuljahr',
                        array('{{Name}} {{Description}}' => Term::useService()->getYearAll())))->setRequired()
                    , 3),
                new FormColumn(
                    new SelectBox('Filter[TblSchoolType_Id]['.$FilterNumber.']', 'Schulart',
                        array('{{ Name }}' => School::useService()->getConsumerSchoolTypeAll()))
                    , 3),
                new FormColumn(
                    new SelectBox('Filter[Level]['.$FilterNumber.']', 'Stufe', DivisionCourse::useService()->getStudentEducationLevelListForSelectbox())
                    , 3),
                new FormColumn(
                    new AutoCompleter('Filter[TblDivisionCourse_Name]['.$FilterNumber.']', 'Klasse', '',
                        array('Name' => DivisionCourse::useService()->getDivisionCourseAll()))
                    , 3)
            ))
        ));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Form
     */
    public function formFilterProspect(TblSerialLetter $tblSerialLetter = null)
    {

        $FormGroup = array();
        $FormGroup[] = (new SerialLetterForm())->formSerialLetterStandardGroup();
        $MaxFieldNumber = 0;
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = $this->getProspectFilterContent($FilterNumber);
                        if($MaxFieldNumber <= $FilterNumber){
                            $MaxFieldNumber = $FilterNumber + 1;
                        }
                    }
                }
            }
//        } else {
//            // POST StandardGroup (first Visit)
//            $Global = $this->getGlobal();
//            if (!isset($Global->POST['Filter']['TblGroup_Id'][0])) {
//                $Global->POST['Filter']['TblGroup_Id'][0] = $tblGroup->getId();
//                $Global->savePost();
//            }
        }
        // new Filter
        $FormGroup[] = $this->getProspectFilterContent($MaxFieldNumber);

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param $FilterNumber
     *
     * @return FormGroup
     */
    private function getProspectFilterContent($FilterNumber)
    {

        return new FormGroup(array(new FormRow(array(
            new FormColumn(
                new AutoCompleter('Filter[TblProspectReservation_ReservationYear]['.$FilterNumber.']', 'Interessent: Schuljahr',
                    '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                , 3),
            new FormColumn(
                new AutoCompleter('Filter[TblProspectReservation_ReservationDivision]['.$FilterNumber.']', 'Interessent: Stufe',
                    '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                , 3),
            new FormColumn(
                new SelectBox('Filter[TblProspectReservation_serviceTblTypeOptionA]['.$FilterNumber.']', 'Schulart:'
                    , array('Name' => School::useService()->getConsumerSchoolTypeAll()))
                , 3)
        ))));
    }

    /**
     * @param null $tblSerialLetter
     *
     * @return Form
     */
    public function formFilterCompany($tblSerialLetter = null)
    {

        $tblGroupList = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupAll();
        $tblGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
        $tblCompanyGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
        $TypeList = Relationship::useService()->getTypeAllByGroup($tblCompanyGroup);

        $FormGroup = array();
        $FormGroup[] = (new SerialLetterForm())->formSerialLetterStandardGroup();
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new SelectBox('Filter[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $tblGroupList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('Filter[TblCompany_Name]['.$FilterNumber.']', 'Institution: Name', '',
                                        array('Name' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('Filter[TblCompany_ExtendedName]['.$FilterNumber.']', 'Institution: Zusatz', '',
                                        array('ExtendedName' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('Filter[TblType_Id]['.$FilterNumber.']', 'Beziehung: Typ', array('Name' => $TypeList))
                                    , 3),
                            ))
                        ));
                    }
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Filter[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('Filter[TblCompany_Name][]', 'Institution: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('Filter[TblCompany_ExtendedName][]', 'Institution: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('Filter[TblType_Id][]', 'Beziehung: Typ', array('Name' => $TypeList))
                        , 3),
                ))
            ));
        } else {
            // First Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Filter[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('Filter[TblCompany_Name][0]', 'Institution: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('Filter[TblCompany_ExtendedName][0]', 'Institution: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('Filter[TblType_Id][0]', 'Beziehung: Typ', array('Name' => $TypeList))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
            }
            $Global->savePost();
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $Filter
     * @param string          $TabActive
     *
     * @return string|Form
     */
    public function formFilterStaticPerson(TblSerialLetter $tblSerialLetter, array $Filter = array(), string $TabActive = 'PERSON'):string
    {

        $Form = '';
        if($TabActive == 'PERSON'){
            if(!$Filter){
                $_POST['Filter']['TblGroup_Id'] = 1;
            }
            $Form = new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new SelectBox('Filter[TblGroup_Id]', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
                        ), 4),
                        new FormColumn(array(
                            new TextField('Filter[TblPerson_FirstName]', 'Person: Vorname', 'Person: Vorname')
                        ), 4),
                        new FormColumn(array(
                            new TextField('Filter[TblPerson_LastName]', 'Person: Nachname', 'Person: Nachname')
                        ), 4)
                    ))
                )
                , new Primary('in Gruppen suchen'));
        } elseif($TabActive == 'DIVISION') {
            if(!$Filter){
                if(($tblYearList = Term::useService()->getYearByNow())){
                    $tblYear = current($tblYearList);
                    $_POST['Filter']['TblYear_Id'] = $tblYear->getId();
                }
            }
            $Form = new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            (new SelectBox('Filter[TblYear_Id]', 'Bildung: Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAll())))->setRequired(),
                        ), 4),
                        new FormColumn(
                            new SelectBox('Filter[Level]', 'Stufe', DivisionCourse::useService()->getStudentEducationLevelListForSelectbox())
                            , 4),
                        new FormColumn(
                            new AutoCompleter('Filter[TblDivisionCourse_Name]', 'Klasse', '',
                                array('Name' => DivisionCourse::useService()->getDivisionCourseAll()))
                            , 4)
                    )),
//                    new FormRow(array(
//                        new FormColumn(array(
//                            new TextField('Filter[FirstName]', 'Person: Vorname', 'Person: Vorname')
//                        ), 4),
//                        new FormColumn(array(
//                            new TextField('Filter[LastName]', 'Person: Nachname', 'Person: Nachname')
//                        ), 4)
//                    ))
                ))
                , new Primary('in Klassen suchen'));
        } elseif($TabActive == 'PROSPECT') {
            $ProspectReservationList = Prospect::useService()->getProspectReservationAll();
            $Form = new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            new AutoCompleter('Filter[TblProspectReservation_ReservationYear]', 'Interessent: Schuljahr', 'Interessent: Schuljahr',
                                array('ReservationYear' => $ProspectReservationList))
                            , 4),
                        new FormColumn(
                            new AutoCompleter('Filter[TblProspectReservation_ReservationDivision]', 'Interessent: Stufe', 'Interessent: Stufe',
                                array('ReservationDivision' => $ProspectReservationList))
                            , 4),
                        new FormColumn(
                            new SelectBox('Filter[TblProspectReservation_serviceTblTypeOptionA]', 'Schulart:'
                                , array('Name' => School::useService()->getConsumerSchoolTypeAll()))
                            , 4)
                    ))
                ))
                , new Primary('Interessenten suchen'), $this->getRequest()->getPathInfo(), array(
                'TabActive'   => 'PROSPECT',
                'Id'          => $tblSerialLetter->getId()
            ));
        }

        // Markieren der gefilterten Werte
        if($Form){
            foreach ($Filter as $Field => $Value) {
                if ($Value) {
                    $Form->setSuccess('Filter['.$Field.']', '', new Filter());
                }
            }
        }

        return $Form;
    }
}