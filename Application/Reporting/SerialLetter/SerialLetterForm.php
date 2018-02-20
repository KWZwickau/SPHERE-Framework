<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
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
                )
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
                                new SelectBox('FilterGroup['.$tblFilterField->getField().']['.$tblFilterField->getFilterNumber().']',
                                    'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                                , 3),
                        ))
//                        , new TitleForm('Aktiver Filter '.$FilterCount)
                    );
                    $FilterCount++;
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id][]',
                        'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                    , 3),
            )));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id][0]',
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

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $GroupList[] = $tblGroup;
        }
        $LevelList = array();
        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if ($tblLevel->getName() !== '') {
                    $LevelList[] = $tblLevel;
                }
            }
        }

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
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $GroupList))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterYear[TblYear_Id]['.$FilterNumber.']', 'Bildung: Schuljahr',
                                        array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterStudent[TblLevel_Id]['.$FilterNumber.']', 'Klasse: Stufe',
                                        array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterStudent[TblDivision_Name]['.$FilterNumber.']', 'Klasse: Gruppe', '',
                                        array('Name' => Division::useService()->getDivisionAll()))
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
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterYear[TblYear_Id][]', 'Bildung: Schuljahr',
                            array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterStudent[TblLevel_Id][]', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterStudent[TblDivision_Name][]', 'Klasse: Gruppe', '',
                            array('Name' => Division::useService()->getDivisionAll()))
                        , 3),
                ))
            ));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterYear[TblYear_Id][0]', 'Bildung: Schuljahr',
                            array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterStudent[TblLevel_Id][0]', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterStudent[TblDivision_Name][0]', 'Klasse: Gruppe', '',
                            array('Name' => Division::useService()->getDivisionAll()))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                    $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
                }
                $Global->savePost();
            }
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Form
     */
    public function formFilterProspect(TblSerialLetter $tblSerialLetter = null)
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
        if ($tblGroup) {
//            $GroupList[] = '';
            $GroupList[] = $tblGroup;
        }

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
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $GroupList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear]['.$FilterNumber.']', 'Interessent: Schuljahr',
                                        '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision]['.$FilterNumber.']', 'Interessent: Stufe',
                                        '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA]['.$FilterNumber.']', 'Schulart:'
                                        , array('Name' => Type::useService()->getTypeAll()))
                                    , 3),
                            ))
                        ));
//                        /** @var TblFilterField $tblFilterField */
//                        foreach($FilterFieldList as $tblFilterField){
//                            if($tblFilterField->getField() === 'TblGroup_Id'){
//                                $Global->POST['FilterGroup'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            } elseif($tblFilterField->getField() === 'TblProspectReservation_ReservationYear'
//                            ||$tblFilterField->getField() === 'TblProspectReservation_ReservationDivision'
//                            ||$tblFilterField->getField() === 'TblProspectReservation_serviceTblTypeOptionA'){
//                                $Global->POST['FilterProspect'][$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
//                            }
//                        }
                    }
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear][]', 'Interessent: Schuljahr',
                            '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision][]', 'Interessent: Stufe',
                            '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA][]', 'Schulart:'
                            , array('Name' => Type::useService()->getTypeAll()))
                        , 3),
                ))
            ));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear][0]', 'Interessent: Schuljahr',
                            '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision][0]', 'Interessent: Stufe',
                            '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA][0]', 'Schulart:'
                            , array('Name' => Type::useService()->getTypeAll()))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
                $Global->savePost();
            }
        }

        return new Form(
            $FormGroup
        );
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
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $tblGroupList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterCompany[TblCompany_Name]['.$FilterNumber.']', 'Institution: Name', '',
                                        array('Name' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterCompany[TblCompany_ExtendedName]['.$FilterNumber.']', 'Institution: Zusatz', '',
                                        array('ExtendedName' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterRelationship[TblType_Id]['.$FilterNumber.']', 'Beziehung: Typ', array('Name' => $TypeList))
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
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_Name][]', 'Institution: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_ExtendedName][]', 'Institution: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterRelationship[TblType_Id][]', 'Beziehung: Typ', array('Name' => $TypeList))
                        , 3),
                ))
            ));
        } else {
            // First Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_Name][0]', 'Institution: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_ExtendedName][0]', 'Institution: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterRelationship[TblType_Id][0]', 'Beziehung: Typ', array('Name' => $TypeList))
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
}