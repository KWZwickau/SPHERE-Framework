<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Error;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiMassReplace
 *
 * @package SPHERE\Application\Api\People\Meta
 */
class ApiMassReplace extends Extension implements IApiInterface
{
    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openModal');
        $Dispatcher->registerMethod('showFilter');
        $Dispatcher->registerMethod('saveModal');
        $Dispatcher->registerMethod('closeModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param AbstractField $Field
     *
     * @return BlockReceiver
     */
    public static function receiverField(AbstractField $Field)
    {
        return (new BlockReceiver($Field))
            ->setIdentifier('Field-Target-'.crc32($Field->getName()));
    }

    /**
     * @param AbstractField $Field
     *
     * @return ModalReceiver
     */
    public static function receiverModal(AbstractField $Field)
    {
        /** @var SelectBox|TextField $Field */
        return (new ModalReceiver(new Bold('Massenänderung ').$Field->getLabel(), new Close()))
            ->setIdentifier('Field-Modal-'.crc32($Field->getName()));
    }

    /**
     * @param $Name
     * @param $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Name, $Content)
    {
        return (new BlockReceiver($Content))->setIdentifier($Name);
    }

    public static function pipelineOpen(AbstractField $Field)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field))
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineFilter(AbstractField $Field, $Name, $Content)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFilter($Name, $Content), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showFilter'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field))
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineSave(AbstractField $Field)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveModal'
        ));
        $Emitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineClose(AbstractField $Field, $CloneField)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverField($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'closeModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field)),
            'CloneField' => $CloneField
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal($Field)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param AbstractField $modalField
     * @param null          $Year
     * @param null          $Division
     *
     * @return Layout|string
     */
    public function openModal($modalField, $Year = null, $Division = null)
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = $this->cloneField($Field, 'CloneField', 'Auswahl/Eingabe');

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = '';

        if (isset($Year) && $Year && isset($Pile)) {
            // Preparation Filter
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Year = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterType
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $Division = array_filter($Division);
            } else {
                $Division = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => array(),   // empty Person search
                2 => $Division,
                3 => $Year
            ));
        }

        $SearchResult = array();
        if ($Result != '') {
            foreach ($Result as $Index => $Row) {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var TblPerson $tblPerson */
                $Person = $Row[1]->__toArray();
                if (isset($Person['Id'])) {
                    $tblPerson = Person::useService()->getPersonById($Person['Id']);
                    if ($tblPerson) {
                        $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                        if ($tblProspect) {
                            $tblProspectReservation = $tblProspect->getTblProspectReservation();
                            if ($tblProspectReservation) {
                                $DataPerson['ProspectYear'] = $tblProspectReservation->getReservationYear();
                                $DataPerson['ProspectDivision'] = $tblProspectReservation->getReservationDivision();
                            }
                        }
                    }
                }
//                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();
//                if ($tblDivisionStudent) {
//                    $tblDivision = $tblDivisionStudent->getTblDivision();
//                    if ($tblDivision) {
//                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
//                    } else {
//                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
//                    }
//                } else {
//                    $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
//                }
//
//                /** @noinspection PhpUndefinedFieldInspection */
//                $DataPerson['Exchange'] = (string)new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
//                    'Id'       => $Id,
//                    'PersonId' => $DataPerson['TblPerson_Id']
//                ));
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
//                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
//                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                }
//                /** @noinspection PhpUndefinedFieldInspection */
//                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
//                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
//                    /** @noinspection PhpUndefinedFieldInspection */
//                    $DataPerson['Address'] = $tblAddress->getGuiString();
//                }
//                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
//                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
//                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
//                }
//
                if (!isset($DataPerson['ProspectYear'])) {
                    $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                }
                if (!isset($DataPerson['ProspectDivision'])) {
                    $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                        $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        $Test = array();
        if (!empty($SearchResult)) {
            foreach ($SearchResult as $SearchResultRow) {
                $Test[] = $SearchResultRow['TblPerson_Id'].': '.$SearchResultRow['TblPerson_FirstName'].', '.
                    $SearchResultRow['TblPerson_LastName'];
            }
        }

        return
            $this->receiverFilter('Filter', $this->getStudentFilter($modalField)).
            new Code(print_r($Test, true)).
            (new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(array(
                        $CloneField
                    ))
                )
            )
            , new Primary('Ändern'), '', $this->getGlobal()->POST))
            ->ajaxPipelineOnSubmit(self::pipelineSave($Field));
    }

    /**
     * @param AbstractField $Field
     * @param string        $Name
     * @param null          $Label
     *
     * @return AbstractField|Error
     */
    private function cloneField(AbstractField $Field, $Name = 'CloneField', $Label = null)
    {
        /** @var AbstractField $Field */
        $Reflection = new \ReflectionObject($Field);
        $FieldParameterList = $Reflection->getConstructor()->getParameters();
        // Read Parent Constructor and create Args List
        $Constructor = array();
        /**
         * @var int                  $Position
         * @var \ReflectionParameter $Parameter
         */
        $ParameterList = array();
        foreach ($FieldParameterList as $Position => $Parameter) {
            if ($Reflection->hasMethod('get'.$Parameter->getName())) {
                $Constructor[$Position] = $Field->{'get'.$Parameter->getName()}();
            } elseif ($Parameter->isDefaultValueAvailable()) {
                $Constructor[$Position] = $Parameter->getDefaultValue();
            } else {
                if ($Parameter->allowsNull()) {
                    $Constructor[$Position] = null;
                } else {
                    $E = new \Exception($Reflection->getName()." Parameter-Definition missmatch. ");
                    return new Error($E->getCode(), $E->getMessage(), false);
                }
            }
            $ParameterList[$Position] = $Parameter->getName();
        }
        // Replace Field Name
        $Position = array_search('Name', $ParameterList);
        $Constructor[$Position] = $Name;
        // Replace Field Label
        if ($Label) {
            if (false !== ($Position = array_search('Label', $ParameterList))) {
                $Constructor[$Position] = $Label;
            }
        }
        // Create new Field
        /** @var AbstractField $NewField */
        $NewField = $Reflection->newInstanceArgs($Constructor);
        // Set Field Value to Parent
        if (preg_match(
            '!(^|&)'.preg_quote($Field->getName()).'=(.*?)(&|$)!is',
            urldecode(http_build_query($this->getGlobal()->REQUEST)),
            $Value
        )) {
            $NewField->setDefaultValue($Value[2], true);
        }
        return $NewField;
    }

    public function showFilter($modalField)
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        return $this->getStudentFilter($Field);
    }

    /**
     * @param string $ServiceClass
     * @param string $ServiceMethod
     * @return mixed
     */
    public function saveModal(
        $ServiceClass,
        $ServiceMethod
    ) {

        $Reflection = new \ReflectionClass( $ServiceClass );
        $MethodParameterList = $Reflection->getMethod( $ServiceMethod )->getParameters();

        // Read Parent Constructor and create Args List
        $Constructor = array();
        /**
         * @var int                  $Position
         * @var \ReflectionParameter $Parameter
         */
        foreach ($MethodParameterList as $Position => $Parameter) {
            if( array_key_exists( $Parameter->getName(), $this->getGlobal()->POST ) ) {
                $Constructor[$Position] = $this->getGlobal()->POST[$Parameter->getName()];
            } else {
                $Constructor[$Position] = null;
            }
        }

        $ServiceClass = $Reflection->newInstanceWithoutConstructor();
        return call_user_func_array( array( $ServiceClass, $ServiceMethod ), $Constructor );
    }

    /**
     * Create Clone and set new Value
     *
     * @param string $modalField
     * @param string $CloneField
     *
     * @return AbstractField
     */
    public function closeModal($modalField, $CloneField)
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        parse_str($Field->getName().'='.$CloneField, $NewValue);
        $Globals = $this->getGlobal();
        $Globals->POST = array_merge_recursive($Globals->POST, $NewValue);
        $Globals->savePost();
        $ReplaceField = $this->cloneField($Field, $Field->getName());
        return $ReplaceField;
    }

    public function getStudentFilter($modalField)
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
//        $tblYear = null;
//        $tblYearList = Term::useService()->getYearByNow();
//        if($tblYearList){
//            $tblYear = current($tblYearList);
//        }

//        if($CloneField != null){
//            parse_str($Field->getName().'='.$CloneField, $NewValue);
//            $Globals = $this->getGlobal();
//            $Globals->POST = array_merge_recursive($Globals->POST, $NewValue);
//            $Globals->savePost();
//        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Bildung: Schuljahr',
                            array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))),
                    ), 4),
                    new FormColumn(array(
                        new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => Division::useService()->getLevelAll()))
                    ), 4),
                    new FormColumn(array(
                        new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Klasse: Gruppe',
                            'Klasse: Gruppe',
                            array('Name' => Division::useService()->getDivisionAll()))
                    ), 4),
                )),
//                new FormRow(array(
//                    new FormColumn(array(
//                        new TextField('Filter['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
//                    ), 4),
//                    new FormColumn(array(
//                        new TextField('Filter['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
//                    ), 4)
//                ))
            ))
            , new Primary('Filtern'), '', $this->getGlobal()->POST))->ajaxPipelineOnSubmit(self::pipelineOpen($Field));
    }
}