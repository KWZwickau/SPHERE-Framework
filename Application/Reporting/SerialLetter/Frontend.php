<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Api\Reporting\SerialLetter\ApiSerialLetter;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialCompany;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\SerialLetter
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null       $SerialLetter
     * @param string     $TabActive
     * @param array|null $Filter
     *
     * @return Stage
     */
    public function frontendSerialLetter(
        $SerialLetter = null,
        $TabActive = 'STUDENT',
        $Filter = null
    ) {

//        ini_set('memory_limit', '1G');
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');
        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();

        // create Tabs
        $LayoutTabs[] = new LayoutTab('Dynamisch ('.TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT.')', 'STUDENT');
        $LayoutTabs[] = new LayoutTab('Dynamisch ('.TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT.')', 'PROSPECT');
        $LayoutTabs[] = new LayoutTab('Dynamisch ('.TblFilterCategory::IDENTIFIER_PERSON_GROUP.')', 'PERSONGROUP');
        //        $LayoutTabs[] = new LayoutTab('Dynamisch ('.TblFilterCategory::IDENTIFIER_COMPANY_GROUP.')', 'COMPANY');
        $LayoutTabs[] = new LayoutTab('Dynamisch ('.TblFilterCategory::IDENTIFIER_COMPANY.')', 'COMPANY');
        $LayoutTabs[] = new LayoutTab('Statisch', 'STATIC');
        if (!empty($LayoutTabs) && $TabActive === 'STUDENT') {
            /** @var LayoutTab[] $LayoutTabs */
            $LayoutTabs[0]->setActive();
        }

        $TableContent = array();
        if ($tblSerialLetterAll) {
            array_walk($tblSerialLetterAll, function (TblSerialLetter $tblSerialLetter) use (&$TableContent) {
                $tblFilterCategory = $tblSerialLetter->getFilterCategory();
                $Item['Name'] = $tblSerialLetter->getName();
                $Item['Description'] = $tblSerialLetter->getDescription();
                $Item['Category'] = ( $tblSerialLetter->getFilterCategory()
                    ? new Info('Serienbrief dynamisch ').new Bold($tblSerialLetter->getFilterCategory()->getName())
                    : new Info('Serienbrief statisch') );
                if($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP){
                    $Item['Option'] = ( new Standard('', '/Reporting/SerialLetter/Destroy', new Remove(),
                        array('Id' => $tblSerialLetter->getId()), 'Löschen') );
                } else {
                    $Item['Option'] =
                        ( $tblFilterCategory
                            ? ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                                array('Id' => $tblSerialLetter->getId(), 'doUpdatePersonListByFilter' => true), 'Bearbeiten') )
                            : ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                                array('Id' => $tblSerialLetter->getId()), 'Bearbeiten') )
                        )
                        .( new Standard('', '/Reporting/SerialLetter/Destroy', new Remove(),
                            array('Id' => $tblSerialLetter->getId()), 'Löschen') )
                        .
                        ( $tblFilterCategory
                            ? ''
                            : new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen')
                        )
                        .
                        ( $tblFilterCategory
                            ? ( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                                array('Id' => $tblSerialLetter->getId(), 'doUpdatePersonListByFilter' => true), 'Adressen auswählen') )
                            : ( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen') )
                        )
                        .( $tblFilterCategory
                            ? ( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                                array('Id' => $tblSerialLetter->getId(), 'doUpdatePersonListByFilter' => true),
                                'Adressliste für Serienbriefe anzeigen und herunterladen') )
                            : ( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                                array('Id' => $tblSerialLetter->getId()),
                                'Adressliste für Serienbriefe anzeigen und herunterladen') )
                        );
                }

                array_push($TableContent, $Item);
            });
        }

        $FormSerialLetter = (new SerialLetterForm())->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Timeout = null;
//        $TableSearch = array();
//        $IsFilter = false;


        switch ($TabActive) {
            case 'STATIC':
                $MetaTable = new Panel(new PlusSign().' Serienbrief anlegen '
                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetter, $SerialLetter))), Panel::PANEL_TYPE_INFO);
                break;
            case 'PERSONGROUP':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_PERSON_GROUP);

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterPersonGroup();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $Filter, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'STUDENT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT);
                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterStudent();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $Filter, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'PROSPECT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT);

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterProspect();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $Filter, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'COMPANY':

                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_COMPANY);

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterCompany();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $Filter, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            default:
                $MetaTable = new Panel(new PlusSign().' Serienbrief anlegen '
                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetter, $SerialLetter))), Panel::PANEL_TYPE_INFO);
        }
//        if (!empty($LayoutTabs) && $TabActive === 'STATIC') {
//            $LayoutTabs[0]->setActive();
//        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Category'    => 'Kategorie',
                                'Option'      => '',
                            ), array(
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '180px', 'targets' => 3)
                                )
                            ))
                        ))
                    ))
                ), new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new LayoutTabs($LayoutTabs),
                            $MetaTable
                        ))
                    ))
                ), new Title(new PlusSign().' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param int    $Id
     * @param bool   $doUpdatePersonListByFilter
     * @param array  $SerialLetter
     * @param array  $Filter
     *
     * @return Stage|string
     */
    public function frontendSerialLetterEdit(
        int $Id,
        bool $doUpdatePersonListByFilter = false,
        array $SerialLetter = array(),
        array $Filter = array()
    ) {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        if (!( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation()).new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
        if ($tblFilterCategory) {
            if ($doUpdatePersonListByFilter) {
                if($tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY){
                    SerialLetter::useService()->updateSerialCompany($tblSerialLetter);
                } else {
                    SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
                }
            }
        }
            if ($tblFilterCategory) {
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                    $TabActive = 'PERSONGROUP';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                    $TabActive = 'STUDENT';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                    $TabActive = 'PROSPECT';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY) {
                    $TabActive = 'COMPANY';
                }
            }

        $Stage->addButton(new Standard(new Bold(new Info('Bearbeiten')), '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblSerialLetter->getFilterCategory()) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Adressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        $FormSerialLetter = (new SerialLetterForm())->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Global = $this->getGlobal();
        // Post FilterField wenn keiner aus dem Post kommt
        if (empty($Filter)) {
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $Filter[$tblFilterField->getField()][$tblFilterField->getFilterNumber()] = $tblFilterField->getValue();
                }
            }
            $Global->POST['Filter'] = $Filter;
            $Global->savePost();
        }

        // Post SerialLetterName
        if ($SerialLetter == null) {
            $Global->POST['SerialLetter']['Name'] = $tblSerialLetter->getName();
            $Global->POST['SerialLetter']['Description'] = $tblSerialLetter->getDescription();
            $Global->savePost();
        }
        $TableContent = array();
        // hide table while POST request
        $IsPost = false;
        if(isset($Global->POST['IsPost'])) {
            $IsPost = true;
        }
        $CategoryName = '';
        if($tblFilterCategory){
            $CategoryName = $tblFilterCategory->getName();
        }
        switch ($CategoryName) {
            case TblFilterCategory::IDENTIFIER_PERSON_GROUP:
                if(!$IsPost) {
                    $TableContent = (new SerialLetterFilter())->getGroupFilterResultListBySerialLetter($tblSerialLetter);
                }
                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterPersonGroup($tblSerialLetter);
                $FormSerialLetterDynamic->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $Filter)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    ).
                    (!$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation' => 'Anrede',
                                                  'Name'       => 'Name',
                                                  'Address'    => 'Adresse'
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false,
                                                          'width'     => '3%', 'targets' => 0)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT:
                if(!$IsPost) {
                    $TableResult = (new SerialLetterFilter())->getStudentFilterResultListBySerialLetter($tblSerialLetter);
                    if($TableResult) {
                        $TableContent = (new SerialLetterFilter())->getStudentTableByResult(null, $TableResult);
                    }
                }

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterStudent($tblSerialLetter);
                $FormSerialLetterDynamic->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $Filter)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .(!$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'    => 'Anrede',
                                                  'Name'          => 'Name',
                                                  'Address'       => 'Adresse',
                                                  'Year'          => 'Jahr',
                                                  'Level'         => 'Stufe',
                                                  'Division'      => 'Klasse',
                                                  'StudentNumber' => 'Schüler-Nr.'
                                            ),
                                            array(
                                                'order'      => array(array(3, 'asc'), array(4, 'asc'), array(5, 'asc'), array(1, 'asc')),
                                                'paging' => false,
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0),
//                                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),  // doesn't work
                                                    array('type' => 'natural', 'targets' => 4),
                                                    array('type' => 'natural', 'targets' => 5),
                                                    array('type' => 'natural', 'targets' => 6),
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT:
                if(!$IsPost) {
                    $TableResult = (new SerialLetterFilter())->getProspectFilterResultListBySerialLetter($tblSerialLetter);
                    if ($TableResult) {
                        $TableContent = (new SerialLetterFilter())->getProspectTableByResult(null, $TableResult);
                    }
                }

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterProspect($tblSerialLetter);
                $FormSerialLetterDynamic->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $Filter)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .(!$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'          => 'Anrede',
                                                  'Name'                => 'Name',
                                                  'Address'             => 'Adresse',
                                                  'ReservationDate'     => 'Eingangsdatum',
                                                  'InterviewDate'       => 'Aufnahmegespräch',
                                                  'TrialDate'           => 'Schnuppertag',
                                                  'ReservationYear'     => 'Anmeldung für Jahr',
                                                  'ReservationDivision' => 'Anmeldung für Stufe',
                                                  'ReservationOptionA'  => 'Schulart: Option A',
                                                  'ReservationOptionB'  => 'Schulart: Option B',
                                            ),
                                            array(
                                                'order'      => array(array(6, 'asc'), array(7, 'asc'), array(1, 'asc')),
                                                'paging' => false,
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0),
//                                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(1)), // doesn't work
                                                    array('type' => 'de_date', 'targets' => array(3, 4, 5)),
                                                    array('type' => 'natural', 'targets' => array(6, 7))
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case TblFilterCategory::IDENTIFIER_COMPANY:
                if(!$IsPost) {
                    $TableResult = (new SerialLetterFilter())->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
                    if ($TableResult) {
                        $TableContent = (new SerialLetterFilter())->getCompanyTableByResult($TableResult);
                    }
                }

                $FormSerialLetterDynamic = (new SerialLetterForm())->formFilterCompany($tblSerialLetter);
                $FormSerialLetterDynamic->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $Filter)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .(!$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'          => 'Anrede',
                                                  'Name'                => 'Name',
                                                  'Address'             => 'Adresse',
                                                  'CompanyName'         => 'Name der Institution',
                                                  'CompanyExtendedName' => 'Zusatz',
                                                  'Type'                => 'Typ'
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            default:
                $MetaTable = new Panel(new Edit().' Serienbrief'
                    , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetter, $tblSerialLetter
                        , $SerialLetter))), Panel::PANEL_TYPE_INFO);
        }

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        $MetaTable
                    )
                ),
            )))
        );

        return $Stage;
    }

    /**
     * @param null|int $Id
     * @param array Filter
     * @param string   $TabActive
     *
     * @return Stage|string
     */
    public function frontendSerialLetterPersonSelected($Id = null, $Filter = array(), $TabActive = 'PERSON')
    {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ( $Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard(new Bold(new Info('Personen Auswahl')),
                '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Adressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        // ToDO Service zur Filtlerung Gruppen / Klassen / Interessenten
        $TableContentRight = array();
        switch ($TabActive) {
            case 'DIVISION':
                $TableResult = (new SerialLetterFilter())->getStudentFilterResultListBySerialLetter($tblSerialLetter, $Filter);
                break;
            case 'PROSPECT':
                //ToDO Funktion schreiben
                $TableResult = (new SerialLetterFilter())->getProspectFilterResultListBySerialLetter($tblSerialLetter, $Filter);
                break;
            default:
                // case 'PERSON':
                $TableResult = (new SerialLetterFilter())->getGroupFilterResultListBySerialLetter($tblSerialLetter, $Filter);
                break;
        }
        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);

        if (!empty($tblPersonList) && !empty($TableResult)) {

            $tblPersonIdList = array();
            // Id Liste vorhandener Personen
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tblPersonIdList) {
                if(!in_array($tblPerson->getId(), $tblPersonIdList)) {
                    array_push($tblPersonIdList, $tblPerson->getId());
                }
            });
            // vorhandene Personen aus Suche entfernen
            array_walk($TableResult, function (&$item) use ($tblPersonIdList) {
                if(in_array($item['PersonId'], $tblPersonIdList)) {
                    $item = false;
                }
            });
            $TableResult = array_filter($TableResult);
        }
        // ToDO TableContent aus dem Result erzeugen
        switch ($TabActive) {
            case 'DIVISION':
                $TableContentRight = (new SerialLetterFilter())->getStudentTableByResult($Id, $TableResult);
                break;
            case 'PROSPECT':
                // ToDO
                $TableContentRight = (new SerialLetterFilter())->getProspectTableByResult($Id, $TableResult);
                break;
            default:
                // case 'PERSON':
                $TableContentRight = (new SerialLetterFilter())->getGroupTableByResult($Id, $TableResult);
                break;
        }


        $TableContentLeft = array();
        if(!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use ($Id, &$TableContentLeft) {
                $item = array();
                $item['Exchange'] = (new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id'       => $Id,
                    'PersonId' => $tblPerson->getId()
                )));
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Address'] = new WarningMessage('Keine Adresse hinterlegt!');
                $item['StudentNumber'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
                $item['ReservationYear'] = '';
                $item['ReservationDivision'] = '';
                $item['DivisionAndCore'] = new Center(new Small(new Small(new Small(new Muted('-NA-')))));
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if($tblAddress) {
                    $item['Address'] = $tblAddress->getGuiString();
                }
                if($tblStudent) {
                    if(($StudentNumber = $tblStudent->getIdentifierComplete())){
                        $item['StudentNumber'] = new Center($StudentNumber);
                    }
                }
                // only Prospect all Data -> "where no Student exist" disabled
                if(($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson)) ) {// && !$tblStudent
                    if(($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                        $item['ReservationYear'] = new Center($tblProspectReservation->getReservationYear());
                        $item['ReservationDivision'] = new Center($tblProspectReservation->getReservationDivision());
                    }
                }

                $VisitedDivision = '';
                if(($tblYear = Term::useService()->getYearByNow())){
                    $tblYear = current($tblYear);
                }
                if($tblYear && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
                    $tblDivisionCourseD = $tblStudentEducation->getTblDivision();
                    if($tblDivisionCourseD){
                        $VisitedDivision .= new Center(new Small(new Muted(new Container('Kl. '.$tblDivisionCourseD->getDisplayName()))));
                    }
                    $tblDivisionCourseC = $tblStudentEducation->getTblCoreGroup();
                    if($tblDivisionCourseC){
                        $VisitedDivision .= new Center(new Small(new Muted(new Container('St. '.new Small($tblDivisionCourseC->getDisplayName())))));
                    }
                }
                if($VisitedDivision){
                    $item['DivisionAndCore'] = $VisitedDivision;
                }
                $TableContentLeft[] = $item;
            });
        }

        $Form = (new SerialLetterForm())->formFilterStaticPerson($tblSerialLetter, $Filter, $TabActive);

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        // create Tabs
        $LayoutTabs[] = new LayoutTab('Personen in Gruppen suchen', 'PERSON', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Schüler in Klassen suchen', 'DIVISION', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Interessenten suchen', 'PROSPECT', array('Id' => $tblSerialLetter->getId()));
        if (!empty($LayoutTabs) && $TabActive === 'PERSON') {
            /** @var LayoutTab[] $LayoutTabs */
            $LayoutTabs[0]->setActive();
        }

        $PanelSearchHead = '';
        switch ($TabActive) {
            case 'DIVISION':
                $PanelSearchHead = new Search().' Schüler-Suche nach '.new Bold('Schuljahr / Klasse / Schüler');
                break;
            case 'PROSPECT':
                $PanelSearchHead = new Search().' Interresenten-Suche nach '.new Bold('Jahr / Stufe');
                break;
            default:
                // case 'PERSON':
                $PanelSearchHead = new Search().' Personen-Suche nach '.new Bold('Personengruppe');
                break;
        }
        $MetaTable = new Panel($PanelSearchHead , array($Form), Panel::PANEL_TYPE_INFO);

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Title(new PersonIcon().' Personen', 'Zugewiesen'),
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                    ))))
                ), 6),
                new LayoutColumn(array(
                    new Title(new PersonIcon().' Personen', 'Verfügbar'),
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new LayoutTabs($LayoutTabs),
                            $MetaTable
                        ))),
                        new LayoutRow(new LayoutColumn(
                            (!$Filter
                                ? new WarningMessage('Benutzen Sie bitte den Filter')
                                : ( empty($TableContentRight)
                                    ? new WarningMessage('Keine Ergebnisse bei aktueller Filterung '.new SuccessText(new Filter()))
                                    : ''
                                )
                            )
                        ))
                    )))
                ), 6)
            ))))
            .( $TabActive === 'PROSPECT'
                ?
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($TableContentLeft, null,
                                    array('Exchange'            => '',
                                        'Name'                => 'Name',
                                        'Address'             => 'Adresse',
                                        'ReservationYear'     => 'Intr.-Jahr',
                                        'ReservationDivision' => 'Intr.-Stufe'
                                    ),
                                    array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 4),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                            'Handler' => array(
                                                'From' => 'glyphicon-minus-sign',
                                                'To'   => 'glyphicon-plus-sign',
                                                'All'  => 'TableRemoveAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableCurrent',
                                                'To'   => 'TableAvailable',
                                            )
                                        )
                                    )
                                ),
                                new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                            ), 6),
                            new LayoutColumn(array(
                                new TableData($TableContentRight, null,
                                    array('Exchange'            => ' ',
                                          'Name'                => 'Name',
                                          'Address'             => 'Adresse',
                                          'ReservationYear'     => 'Intr.-Jahr',
                                          'ReservationDivision' => 'Intr.-Stufe'
                                    ),
                                    array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 4),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                            'Handler' => array(
                                                'From' => 'glyphicon-plus-sign',
                                                'To'   => 'glyphicon-minus-sign',
                                                'All'  => 'TableAddAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableAvailable',
                                                'To'   => 'TableCurrent',
                                            ),
                                        )
                                    )
                                //                                )
                                ),
                                ( !$Filter
                                    ? ''
                                    : new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                                )
                            ), 6)
                        ))
                    )
                )
                : new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(array(
                        new TableData($TableContentLeft, null,
                            array('Exchange'      => '',
                                  'Name'          => 'Name',
                                  'Address'       => 'Adresse',
                                  'DivisionAndCore' => 'Klasse',
                                  'StudentNumber' => 'Schüler-Nr.'
                            ),
                            array(
                                'order'                => array(array(1, 'asc')),
                                'columnDefs'           => array(
                                    array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                    array('type' => 'natural', 'targets' => 4),
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                ),
                                'ExtensionRowExchange' => array(
                                    'Enabled' => true,
                                    'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                    'Handler' => array(
                                        'From' => 'glyphicon-minus-sign',
                                        'To'   => 'glyphicon-plus-sign',
                                        'All'  => 'TableRemoveAll'
                                    ),
                                    'Connect' => array(
                                        'From' => 'TableCurrent',
                                        'To'   => 'TableAvailable',
                                    )
                                )
                            )
                        ),
                        new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                    ), 6),
                    new LayoutColumn(array(
                        new TableData($TableContentRight, null,
                            array('Exchange'      => ' ',
                                  'Name'          => 'Name',
                                  'Address'       => 'Adresse',
                                  'Division'      => 'Klasse',
                                  'StudentNumber' => 'Schüler-Nr.'
                            ),
                            array(
                                'order'                => array(array(1, 'asc')),
                                'columnDefs'           => array(
                                    array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                    array('type' => 'natural', 'targets' => 4),
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                ),
                                'ExtensionRowExchange' => array(
                                    'Enabled' => true,
                                    'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                    'Handler' => array(
                                        'From' => 'glyphicon-plus-sign',
                                        'To'   => 'glyphicon-minus-sign',
                                        'All'  => 'TableAddAll'
                                    ),
                                    'Connect' => array(
                                        'From' => 'TableAvailable',
                                        'To'   => 'TableCurrent',
                                    ),
                                )
                            )
//                                )
                        ),
                        ( !$Filter
                            ? ''
                            : new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                        )
                    ), 6)
                ))))
            )
        );

        return $Stage;
    }

    /**
     * @param null|int $Id
     * @param bool     $doUpdatePersonListByFilter
     *
     * @return Stage|string
     */
    public function frontendPersonAddress(
        $Id = null,
        $doUpdatePersonListByFilter = false
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }

        $isCompany = false;
        if ($tblSerialLetter && $tblSerialLetter->getFilterCategory()
            && $tblSerialLetter->getFilterCategory()->getName() === TblFilterCategory::IDENTIFIER_COMPANY
        ) {
            $isCompany = true;
        }

        if($isCompany){
            // update SerialCompany
            if ($doUpdatePersonListByFilter) {
                SerialLetter::useService()->updateSerialCompany($tblSerialLetter);
            }
            $tblCompanyList = SerialLetter::useService()->getSerialCompanyBySerialLetter($tblSerialLetter);
//            if (!$tblCompanyList) {
//                return $Stage.new Danger('Es sind keine Institutionen dem Serienbrief zugeordnet', new Exclamation());
//            }
            return $this->frontendCompanyAddressContent($Id);
        } else {
            $tblFilterCategory = $tblSerialLetter->getFilterCategory();
            // update SerialPerson
            if($tblFilterCategory){
                if ($doUpdatePersonListByFilter) {
                    SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
                }
            }
//            $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
//            if (!$tblPersonList) {
//                return $Stage.new Danger('Es sind keine Personen dem Serienbrief zugeordnet', new Exclamation());
//            }
            return $this->frontendPersonAddressContent($Id);
        }
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    private function frontendCompanyAddressContent($Id)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage->setContent(new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation()));
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard(new Bold(new Info('Adressen Auswahl')), '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Adressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        $TableContent = array();
        $tblSerialCompanyList = SerialLetter::useService()->getSerialCompanyBySerialLetter($tblSerialLetter);
        if(!$tblSerialCompanyList){
            return $Stage->setContent(new WarningMessage('Filterung ohne Ergebnis'));
        }
        array_walk($tblSerialCompanyList, function (TblSerialCompany $tblSerialCompany) use (&$TableContent, $tblSerialLetter, $tblFilterCategory) {
            $StatusDisplay = ($tblSerialCompany->getisIgnore()
                ? new Center(new DangerText(new ToolTip(new Disable(), 'Wird nicht Exportiert')))
                : new Center(new SuccessText(new ToolTip(new SuccessIcon(), 'Wird Exportiert'))));
            $Item['IsIgnore'] = ApiSerialLetter::receiverCompanyStatus($StatusDisplay, $tblSerialCompany->getId().'Display');
            // default value Company
            $Item['CompanyName'] = '';
            $Item['CompanyRelationship'] = '';
            $Item['Address'] = '';
            // default value Person
            $Item['Name'] = '';
            $Item['Salutation'] = '';

            $tblCompany = $tblSerialCompany->getServiceTblCompany();
            $tblAddress = false;
            if($tblCompany){
                $Item['CompanyName'] = $tblCompany->getDisplayName();
                if(($tblAddress = Address::useService()->getAddressByCompany($tblCompany))){
                    $Item['Address'] = $tblAddress->getGuiTwoRowString(false);
                }
            }

            // show button if Address is OK
            $Content = '';
            if($tblAddress){
                if($tblSerialCompany->getisIgnore()){
                    $Content = (new Standard('', ApiSerialLetter::getEndpoint(), new SuccessIcon(), array(), 'Exportieren'))
                        ->ajaxPipelineOnClick(ApiSerialLetter::pipelineCompanyChangeStatus($tblSerialCompany->getId()));
                } else {
                    $Content = (new Standard('', ApiSerialLetter::getEndpoint(), new Disable(), array(), 'Nicht Exportieren'))
                        ->ajaxPipelineOnClick(ApiSerialLetter::pipelineCompanyChangeStatus($tblSerialCompany->getId()));
                }
            }

            $Item['Option'] = $Receiver = ApiSerialLetter::receiverCompanyStatus($Content, $tblSerialCompany->getId());

            $tblPerson = $tblSerialCompany->getServiceTblPerson();
            if($tblPerson){
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Name'] = $tblPerson->getLastFirstName();
//                $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
//                    array('Id'       => $tblSerialLetter->getId(),
//                          'PersonId' => $tblPerson->getId(),
//                          'Route'    => '/Reporting/SerialLetter/Address'));
            }

            if($tblPerson && $tblCompany){
                $tblCompanyRelationshipList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
                if($tblCompanyRelationshipList){
                    foreach($tblCompanyRelationshipList as $tblCompanyRelationship){
                        if($tblCompanyRelationship->getServiceTblCompany()
                            && $tblCompanyRelationship->getServiceTblCompany()->getId() == $tblCompany->getId()){
                            $Item['CompanyRelationship'] = $tblCompanyRelationship->getTblType()->getName();
                        }
                    }
                }
            }

            if (empty($Item['Address'])) {
                $Item['Address'] = '<div class="alert alert-warning" style="Margin-Bottom:2px">Keine Adressen hinterlegt!</div>';
//                $Item['Address'] = new WarningMessage('Keine Adressen hinterlegt!');
                $Item['IsIgnore'] = new Center(new DangerText(new ToolTip(new Disable(), 'Wird nicht Exportiert (Keine Adresse)')));
            }

            array_push($TableContent, $Item);
        });

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCompanyCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())));
        $PanelFooter = new PullRight(new Label('Enthält '.$SerialLetterCount
            .' Anschreiben', Label::LABEL_TYPE_INFO));

        $TableShow =
            new TableData($TableContent, null, array(
                    'CompanyName'         => 'Institution',
                    'Address'             => 'Serienbrief Adresse',
                    'CompanyRelationship' => 'Beziehungstyp',
                    'Salutation'          => 'Anrede',
                    'Name'                => 'Name',
                    'IsIgnore'            => 'Status',
                    'Option'              => '')
                , array(
                    'columnDefs' => array(
                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        array('orderable' => false, 'width' => '1%', 'targets' => -2),
                        array('width' => '15%', 'targets' => 0),
                        array('width' => '10%', 'targets' => 1)
                    )
                ));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Title(new Setup().' Adressen', 'Zuweisung'),
                                            new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                                        ), 6),
                                    ))
                                )
                            )
                        ), 12),
                        new LayoutColumn(
                            $TableShow
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    private function frontendPersonAddressContent($Id)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage->setContent(new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation()));
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard(new Bold(new Info('Adressen Auswahl')), '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Adressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $TableContent = array();
        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblSerialLetter, $tblFilterCategory) {
            $Item['Name'] = $tblPerson->getLastFirstName();
            $Item['StudentNumber'] = new Small(new Muted('-NA-'));
            $Item['CompanyRelationship'] = '';
            $Item['Address'] = array();
            $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                array('Id'       => $tblSerialLetter->getId(),
                      'PersonId' => $tblPerson->getId(),
                      'Route'    => '/Reporting/SerialLetter/Address'));
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $Item['StudentNumber'] = $tblStudent->getIdentifierComplete();
            }
            if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                $tblRelationshipCompanyList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
                if ($tblRelationshipCompanyList) {
                    foreach ($tblRelationshipCompanyList as $tblRelationshipCompany) {
                        if ($tblRelationshipCompany->getServiceTblCompany()) {
                            if ($Item['CompanyRelationship'] === '') {
                                $Item['CompanyRelationship'] = $tblRelationshipCompany->getTblType()->getName();
                            } else {
                                $Item['CompanyRelationship'] .= ', '.$tblRelationshipCompany->getTblType()->getName();
                            }
                        }
                    }
                }
            }

            $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressPersonList) {
                $Data = array();
                $WarningList = array();
                if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                    /** @var TblAddressPerson $tblAddressPerson */
                    foreach ($tblAddressPersonList as $tblAddressPerson) {
                        $tblToCompany = $tblAddressPerson->getServiceTblToPerson($tblSerialLetter->getFilterCategory());
                        /** @var TblToCompany $tblToCompany */
                        if ($tblToCompany) {
                            $tblAddress = $tblToCompany->getTblAddress();

                            if (!isset($Data[$tblAddress->getId()]['Person'])) {
                                $Data[$tblAddress->getId()]['Person'] =
                                    $tblPerson->getLastName().' '.$tblPerson->getFirstName();
                                if ($tblPerson->getSalutation() === '') {
                                    $WarningList[] = $tblPerson->getLastName().' '.
                                        $tblPerson->getFirstName();
                                }
                            } else {
                                $Data[$tblAddress->getId()]['Person'] =
                                    $Data[$tblAddress->getId()]['Person'].', '.
                                    $tblPerson->getLastName().' '.$tblPerson->getFirstName();
                                if ($tblPerson->getSalutation() === '') {
                                    $WarningList[] = $tblPerson->getLastName().' '.
                                        $tblPerson->getFirstName();
                                }
                            }
                            if (!isset($Data[$tblAddress->getId()]['District'])) {
                                if (( $tblCity = $tblAddress->getTblCity() )) {
                                    $Data[$tblAddress->getId()]['District'] = $tblAddress->getTblCity()->getDistrict();
                                }
                            }
                            if (!isset($Data[$tblAddress->getId()]['Street'])) {
                                $Data[$tblAddress->getId()]['Street'] =
                                    $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                            }
                            if (!isset($Data[$tblAddress->getId()]['City'])) {
                                if (( $tblCity = $tblAddress->getTblCity() )) {
                                    $Data[$tblAddress->getId()]['City'] = $tblCity->getCode().' '.$tblCity->getName();
                                }
                            }
                        }
                    }
                } else {
                    /** @var TblAddressPerson $tblAddressPerson */
                    foreach ($tblAddressPersonList as $tblAddressPerson) {
                        if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                            if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                    if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                        if (!isset($Data[$tblAddress->getId()]['Person'])) {
                                            $Data[$tblAddress->getId()]['Person'] =
                                                $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                            if ($PersonToAddress->getSalutation() === '') {
                                                $WarningList[] = $PersonToAddress->getLastName().' '.
                                                    $PersonToAddress->getFirstName();
                                            }
                                        } else {
                                            $Data[$tblAddress->getId()]['Person'] =
                                                $Data[$tblAddress->getId()]['Person'].', '.
                                                $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                            if ($PersonToAddress->getSalutation() === '') {
                                                $WarningList[] = $PersonToAddress->getLastName().' '.
                                                    $PersonToAddress->getFirstName();
                                            }
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['District'])) {
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $Data[$tblAddress->getId()]['District'] = $tblAddress->getTblCity()->getDistrict();
                                            }
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['Street'])) {
                                            $Data[$tblAddress->getId()]['Street'] =
                                                $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['City'])) {
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $Data[$tblAddress->getId()]['City'] = $tblCity->getCode().' '.$tblCity->getName();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($WarningList)) {
                    $WarningList = array_unique($WarningList);
                    foreach ($WarningList as $Warning) {
                        $Item['Address'][] = new LayoutColumn(
//                            '<div class="alert alert-warning" style="Margin-Bottom:2px">'.new Exclamation().' Fehlende Anrede ('.$Warning.')</div>'
                            new WarningMessage(new Exclamation().' Fehlende Anrede ('.$Warning.')')
                            , 4);
                    }
                }

                $AddressList = array();
                if (!empty($Data)) {
                    foreach ($Data as $AddressPanel) {
                        $AddressList[] = new LayoutColumn(
                            new Panel('', $AddressPanel)
                            , 4
                        );
                    }
                    $Item['Address'][] = new LayoutColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(
                                    $AddressList
                                )
                            )
                        )
                    );
                }


                $Item['Address'] = array_filter($Item['Address']);

                $Item['Address'] = (string)new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            $Item['Address']
                        )
                    )
                );
            }

            if (empty($Item['Address'])) {
//                $Item['Address'] = '<div class="alert alert-warning" style="Margin-Bottom:2px">Keine Adressen ausgewählt!</div>';
                $Item['Address'] = new WarningMessage('Keine Adressen ausgewählt!');
            }

            array_push($TableContent, $Item);
        });

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $Buttons = array();
//        $Buttons[] = new Standard('Löschen', '/Reporting/SerialLetter/Address/Remove', new Remove(), array('Id' => $tblSerialLetter->getId()));
        // Company use other automatic
        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $Buttons[] = new Standard('Institutionen mit Personen anschreiben', '/Reporting/SerialLetter/Address/Company', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
        } elseif ($tblFilterCategory || !$tblFilterCategory) {
            $Buttons[] = new Standard('Personen direkt anschreiben', '/Reporting/SerialLetter/Address/Person', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
            $Buttons[] = new Standard('Sorgeberechtigte anschreiben', '/Reporting/SerialLetter/Address/Guardian', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
        }

        $PanelButtonContent = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(implode('<br/><br/>',$Buttons), 5),
            new LayoutColumn(new WarningMessage('Die automatische Adresszuordnung erfolgt nur bei Personen, bei denen noch keine Serienbrief-Adresse zugewiesen wurde.'), 7)
        ))));

        $TableShow =
            new TableData($TableContent, null, array(
                    'Name'          => 'Name',
                    'StudentNumber' => 'Schüler-Nr.',
                    'Address'       => 'Serienbrief Adresse',
                    'Option'        => '')
                , array(
                    'order'      => array(array(2, 'asc')
                    , array(0, 'asc')
                    ),
                    'columnDefs' => array(
                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        array('width' => '15%', 'targets' => 0),
                        array('width' => '10%', 'targets' => 1),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                    )
                ));
        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
            $TableShow =
                new TableData($TableContent, null, array(
                        'Name'    => 'Name',
                        'Address' => 'Serienbrief Adresse',
                        'Option'  => '')
                    , array(
                        'order'      => array(array(1, 'asc')
                        , array(0, 'asc')
                        ),
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('width' => '15%', 'targets' => 0),
                        )
                    ));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Title(new Setup().' Adressen', 'Zuweisung'),
                                            new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                                        ), 6),
                                        new LayoutColumn(array(
                                                new Title('Adressauswahl', 'Automatik'),
                                                new Panel('Adressen von untenstehenden Personen', $PanelButtonContent
                                                    , Panel::PANEL_TYPE_INFO))
                                            , 6)
                                    ))
                                )
                            )
                        ), 12),
                        new LayoutColumn(
                            $TableShow
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSetAddressByPerson($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', '');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage->setContent(new WarningMessage('Kein Serienbrief ausgewählt'))
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonSelf($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSetAddressByPersonGuardian($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', 'aus Sorgeberechtigten');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage->setContent(new WarningMessage('Kein Serienbrief ausgewählt'))
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonGuardian($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendAddressRemove($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id))
            );
            $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
            if (!$tblSerialLetter) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
                    if(!empty($tblPersonList)){
                        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
                        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                            'Anzahl Anschreiben: '.$SerialLetterCount,);
                        $PanelFooter = new PullRight(new Label('Enthält '.count($tblPersonList)
                                .' Person(en)', Label::LABEL_TYPE_INFO)
                        );
                    } else {
                        $PanelContent = 'Keine Personen im Serienbrief';
                        $PanelFooter = '';
                    }

                    $Stage->setContent(
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                ), 6),
                                new LayoutColumn(
                                    new Panel(new Question().' Alle Zuweisungen für Adressen des Serienbriefs wirklich löschen?', array(
                                        $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                                    ),
                                        Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Reporting/SerialLetter/Address/Remove', new Ok(),
                                            array('Id' => $Id, 'Confirm' => true)
                                        )
                                        .new Standard(
                                            'Nein', '/Reporting/SerialLetter/Address', new Disable(),
                                            array('Id' => $Id)
                                        )
                                    )
                                )
                            ))

                        ))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->destroyAddressPersonAllBySerialLetter($tblSerialLetter)
                                    ? new Success(new SuccessIcon().' Die Zuweisungen für Adressen des Serienbriefs wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null   $Id
     * @param null   $PersonId
     * @param string $Route
     * @param null   $Check
     *
     * @return Stage|string
     */
    public function frontendPersonAddressEdit($Id = null, $PersonId = null, $Route = '/Reporting/SerialLetter/Address', $Check = null)
    {

        $Stage = new Stage('Adresse(n)', 'Auswählen');
        $tblSerialLetter = ( $Id === null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft(), array('Id' => $Id)));
        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Adressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage.new WarningMessage('Person nicht gefunden', new Exclamation());
        }

        // Set Global Post
        $Global = $this->getGlobal();
        if ($Check === null) {
            $tblAddressAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressAllByPerson) {
                $FilterCategory = ( $tblFilterCategory ? $tblFilterCategory : null );
                foreach ($tblAddressAllByPerson as $tblAddressPerson) {
                    if ($tblAddressPerson->getServiceTblPerson()
                        && $tblAddressPerson->getServiceTblPersonToAddress()
                        && $tblAddressPerson->getServiceTblToPerson($FilterCategory)
                    ) {
                        $Global->POST['Check']
                        [$tblAddressPerson->getServiceTblPerson()->getId()]
                        [$tblAddressPerson->getServiceTblToPerson($FilterCategory)->getId()]
                        ['Address'] = 1;
                    }
                }
            }
        }
        $Global->savePost();

        $dataList = array();
        $columnList = array(
            'Salutation'   => 'Anrede',
            'Person'       => 'Person',
            'Relationship' => 'Beziehung',
            'AddressType'  => 'Adresstyp',
            'Address'      => 'Adressen'
        );
        if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $columnList = array(
                'Salutation'   => 'Anrede',
                'Person'       => 'Person',
                'Relationship' => 'Beziehung',
                'Company'      => 'Institution',
                'AddressType'  => 'Adresstyp',
                'Address'      => 'Adressen'
            );
        }

        $personCount = 0;

        if ($tblSerialLetter->getFilterCategory()
            && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblSerialLetter->getFilterCategory()->getName()
        ) {
            $tblAddressToPersonList = array();
            $tblCompanyList = array();
            $tblCompanyRelationshipList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
            if ($tblCompanyRelationshipList) {
                /** @var TblToCompany $tblCompanyRelationship */
                foreach ($tblCompanyRelationshipList as $tblCompanyRelationship) {
                    if ($tblCompanyRelationship->getServiceTblCompany()) {
                        $tblCompanyList[] = $tblCompanyRelationship->getServiceTblCompany();
                    }
                }
            }
            if (!empty($tblCompanyList)) {
                /** @var TblCompany $tblCompany */
                foreach ($tblCompanyList as $tblCompany) {
                    if ($tblCompany->fetchMainAddress()) {
                        $tblAddressToPersonList = array_merge($tblAddressToPersonList, Address::useService()->getAddressAllByCompany($tblCompany));
                    }
                }
            }
        } else {
            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
        }
        if ($tblAddressToPersonList) {
            foreach ($tblAddressToPersonList as $tblToPerson) {
                if (!$tblToPerson instanceof TblToCompany) {
                    $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                    $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();
                    $subDataList[] = array(
                        'Salutation'   => $tblPerson->getSalutation(),
                        'Person'       => $tblToPerson->getServiceTblPerson() ? new Bold($tblToPerson->getServiceTblPerson()->getFullName()) : '',
                        'Relationship' => '',
                        'AddressType'  => $tblToPerson->getTblType()->getName(),
                        'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson->getId().'][Address]',
                            '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                    );
                }
            }
        }

        if ($tblSerialLetter->getFilterCategory()
            && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblSerialLetter->getFilterCategory()->getName()
        ) {
            $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
        } else {
            $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        }
        $PersonToPersonId = array();
        if ($tblRelationshipAll) {
            /** @var TblToPerson|TblToCompany $tblRelationship */
            foreach ($tblRelationshipAll as $tblRelationship) {
                $tblGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
                if ($tblGroup->getId() == $tblRelationship->getTblType()->getTblGroup()->getId()) {
                    $tblType = $tblRelationship->getTblType();
                    if ($tblType) {
                        if ($tblAddressToPersonList) {
                            /** @var TblToCompany $tblToCompany */
                            foreach ($tblAddressToPersonList as $tblToCompany) {
                                if ($tblToCompany->getTblAddress()) {
                                    if ($tblToCompany->getServiceTblCompany() && $tblRelationship->getServiceTblCompany()) {
                                        if ($tblToCompany->getServiceTblCompany()->getId() === $tblRelationship->getServiceTblCompany()->getId()) {
                                            $RelationShip = $tblType->getName();
                                            $subDataList[] = array(
                                                'Salutation'   => $tblPerson->getSalutation(),
                                                'Person'       => $tblPerson->getFullName(),
                                                'Relationship' => $RelationShip,
                                                'Company'      => ( $tblToCompany->getServiceTblCompany()
                                                    ? $tblToCompany->getServiceTblCompany()->getName().' '
                                                    .$tblToCompany->getServiceTblCompany()->getExtendedName()
                                                    : '' ),
                                                'AddressType'  => $tblToCompany->getTblType()->getName(),
                                                'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToCompany->getId().'][Address]',
                                                    '&nbsp; '.$tblToCompany->getTblAddress()->getGuiString(), 1)
                                            );
                                        }
                                    }
                                } else {
                                    if ($tblToCompany->getServiceTblCompany() && $tblRelationship->getServiceTblCompany()) {
                                        if ($tblToCompany->getServiceTblCompany()->getId() === $tblRelationship->getServiceTblCompany()->getId()) {
                                            $RelationShip = $tblType->getName();
                                            /** @var TblToPerson $tblRelationship */
                                            $subDataList[] = array(
                                                'Salutation'   => $tblPerson->getSalutation(),
                                                'Person'       => $tblPerson->getFullName(),
                                                'Relationship' => $RelationShip,
                                                'Company'      => ( $tblToCompany->getServiceTblCompany()
                                                    ? $tblToCompany->getServiceTblCompany()->getName().' '
                                                    .$tblToCompany->getServiceTblCompany()->getExtendedName()
                                                    : '' ),
                                                'AddressType'  => $tblToCompany->getTblType()->getName(),
                                                'Address'      => new Warning(
                                                    new Exclamation().' Keine Adresse hinterlegt')
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $tblType = $tblRelationship->getTblType();
                    if ($tblType && $tblType->getName() !== 'Arzt') {
                        if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                            if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                                $direction = $tblRelationship->getServiceTblPersonFrom()->getLastFirstName().' ist '.$tblType->getName()
                                    .' für '.new Bold($tblPerson->getLastFirstName());
                            } else {
                                $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                                $direction = new Bold($tblPerson->getLastFirstName()).' ist '.$tblType->getName()
                                    .' für '.$tblRelationship->getServiceTblPersonTo()->getLastFirstName();
                            }
                            if ($tblAddressToPersonList) {
                                foreach ($tblAddressToPersonList as $tblToPerson) {
                                    $PersonIdAddressIdNow = ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getId() : '' ).'.'.
                                        ( $tblToPerson->getTblAddress() ? $tblToPerson->getTblAddress()->getId() : '' );
                                    // ignore duplicated Person by Relationship
                                    if (!array_key_exists($PersonIdAddressIdNow, $PersonToPersonId)) {
                                        $subDataList[] = array(
                                            'Salutation'   => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getSalutation() : '' ),
                                            'Person'       => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '' ),
                                            'Relationship' => $direction,
                                            'AddressType'  => $tblToPerson->getTblType()->getName(),
                                            'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                                                '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1)
                                        );
                                        $PersonToPersonId[$PersonIdAddressIdNow] = $PersonIdAddressIdNow;
                                    }
                                }
                            } else {
                                /** @var TblToPerson $tblRelationship */
                                if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                    $subDataList[] = array(
                                        'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                        'Person'       => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getFullName() : '' ),
                                        'Relationship' => $direction,
                                        'AddressType'  => '',
                                        'Address'      => new Warning(
                                            new Exclamation().' Keine Adresse hinterlegt')
                                    );
                                } else {
                                    $subDataList[] = array(
                                        'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                        'Person'       => ( $tblRelationship->getServiceTblPersonTo() ? $tblRelationship->getServiceTblPersonTo()->getFullName() : '' ),
                                        'Relationship' => $direction,
                                        'AddressType'  => '',
                                        'Address'      => new Warning(
                                            new Exclamation().' Keine Adresse hinterlegt')
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }


        if (isset($subDataList)) {
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(array(
                            new TableData($subDataList, null, $columnList,
                                array(
                                    'order' => array(array(2, 'asc'), array(1, 'asc')),
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                    ),
//                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
//                                    ),
                                )),
                            new HiddenField('SubmitCheck'),
                            new Primary('Speichern', new Save())
                        ))
                    )
                )
            );
        }

        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.(empty($tblPersonList) ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
        $PanelPerson = new Panel(
            new Bold($tblPerson->getFullName()),
            'Verwendete Adresse(n): '.( $tblAddressPersonList === false ? 0 : count($tblAddressPersonList) ),
            Panel::PANEL_TYPE_SUCCESS);

        if (isset($Form)) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Title(new Listing().' Adressen', 'Auswahl')
                            , new Layout(
                                    new LayoutGroup(
                                        new LayoutRow(array(
                                            new LayoutColumn(
                                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                                )
                                                , 6),
                                            new LayoutColumn(
                                                $PanelPerson
                                                , 6)
                                        ))
                                    )
                                ),
                                new Well(SerialLetter::useService()->setPersonAddressSelection(
                                    $Form, $tblSerialLetter, $tblPerson, $Check, $Route
                                ))
                            ))
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Title(new Listing().'  Adressen', 'Auswahl')
                                , 12),
                            new LayoutColumn(
                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                )
                                , 6),
                            new LayoutColumn(
                                $PanelPerson
                                , 6),
                            new LayoutColumn(
                                new WarningMessage('Die Person '.$tblPerson->getFullName().' sowie die in Beziehung gesetzten Personen besitzen keine Adresse. Zur Person '
                                    .new Standard('', '/People/Person', new PersonIcon(), array('Id' => $tblPerson->getId())))
                                , 12)
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param int  $Id
     * @param bool $doUpdatePersonListByFilter
     *
     * @return Stage|string
     */
    public function frontendSerialLetterExport(int $Id, bool $doUpdatePersonListByFilter = false)
    {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {
            $tblFilterCategory = $tblSerialLetter->getFilterCategory();
            $isCompany = false;
            // update SerialPerson
            if ($tblFilterCategory) {
                if($tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY){
                    $isCompany = true;
                }
                if ($doUpdatePersonListByFilter) {
                    if($tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY){
                        SerialLetter::useService()->updateSerialCompany($tblSerialLetter);
                    } else {
                        SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
                    }
                }
            }

            if($isCompany){
                return $this->frontendCompanyExport($tblSerialLetter);
            }

            $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
                array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
            if (!$tblFilterCategory) {
                $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                    array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
            }
            $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
            $Stage->addButton(new Standard(new Bold(new Info('Adressliste')), '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Adressliste für Serienbriefe anzeigen und herunterladen'));

            $dataList = array();
            $columnList = array(
                'Number'          => 'Nr.',
                'Person'          => 'Person',
                'StudentNumber'   => 'Schüler-Nr.',
                'Division'        => 'Aktuelle Klasse(n)',
                'SchoolCourse'    => 'Aktueller Bildungsgang',
                'Salutation'      => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address'         => 'Adresse',
                'Option'          => ''
            );
            $Interactive = array(
                'order'      => array(array(0, 'asc')),
                'columnDefs' => array(
                    array(
//                        'orderable' => false,
                        'width' => '3%', 'targets' => 0),
                    array('type' => 'natural', 'targets' => 2),
                    array('type' => 'natural', 'targets' => 4),
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 5),
                )
            );
            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                $columnList = array(
                    'Number'              => 'Nr.',
                    'Person'              => 'Person',
                    'ReservationDivision' => 'Stufe',
                    'ReservationYear'     => 'Jahr',
                    'ReservationOptionA'  => 'Schulart 1',
                    'ReservationOptionB'  => 'Schulart 2',
                    'Salutation'          => 'Anrede',
                    'PersonToAddress'     => 'Adressat',
                    'Address'             => 'Adresse',
                    'Option'              => ''
                );
                $Interactive = array(
                    'order'      => array(array(0, 'asc')),
                    'columnDefs' => array(
                        array(
//                            'orderable' => false,
                            'width' => '3%', 'targets' => 0),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 7),
                    )
                );
            }
            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                $columnList = array(
                    'Number'     => 'Nr.',
                    'Person'     => 'Person',
                    'Salutation' => 'Anrede',
//                    'PersonToAddress' => 'Adressat',
                    'Company'    => 'Institution',
                    'Address'    => 'Adresse',
                    'Option'     => ''
                );
                $Interactive = array(
                    'order'      => array(array(0, 'asc')),
                    'columnDefs' => array(
                        array(
//                            'orderable' => false,
                            'width' => '3%', 'targets' => 0),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                    )
                );
            }

            $countAddresses = 0;
            $count = 0;
            $tblPersonList = false;
            $tbSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            if ($tbSerialPersonList) {
                /** @var TblSerialPerson $tbSerialPerson */
                foreach ($tbSerialPersonList as $tbSerialPerson) {
                    if ($tbSerialPerson->getServiceTblPerson()) {
                        $tblPersonList[] = $tbSerialPerson->getServiceTblPerson();
                    }
                }
            }

            if ($tblPersonList) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    if (!$tblFilterCategory) {
                        $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                            $tblPerson);
                        if ($tblAddressPersonAllByPerson) {
                            /** @var TblAddressPerson $tblAddressPerson */
                            foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {
                                // clean data if missing address
                                if (!$tblAddressPerson->getServiceTblToPerson()) {
                                    SerialLetter::useService()->destroySerialAddressPerson($tblAddressPerson);
                                }
                            }
                        }
                    }

                    // get fresh list
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        $AddressList = array();
                        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
                        array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson) use (&$AddressList, $tblPerson, $Id, $tblSerialLetter, $tblFilterCategory) {
                            if ($tblFilterCategory && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblFilterCategory->getName()) {
                                if (( $tblToCompany = $tblAddressPerson->getServiceTblToPerson($tblFilterCategory) )) {
                                    $AddressList[$tblAddressPerson->getId()]['Person'] =
                                        $tblPerson->getLastFirstName();
                                    $AddressList[$tblAddressPerson->getId()]['PersonToAddress'] =
                                        $tblPerson->getLastFirstName();
                                    $AddressList[$tblAddressPerson->getId()]['Address'] =
                                        ( ($tblAddressCompany = $tblToCompany->getTblAddress())
                                            ? $tblAddressCompany->getGuiString()
                                            : ''
                                        )
                                    ;
                                    $AddressList[$tblAddressPerson->getId()]['Company'] =
                                        ( $tblToCompany->getServiceTblCompany()->getExtendedName() !== ''
                                            ? $tblToCompany->getServiceTblCompany()->getName().'<br/>'.
                                            $tblToCompany->getServiceTblCompany()->getExtendedName()
                                            : $tblToCompany->getServiceTblCompany()->getName() );

                                    $AddressList[$tblAddressPerson->getId()]['Salutation'] =
                                        $tblPerson->getSalutation() !== ''
                                            ? $tblPerson->getSalutation()
                                            : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                    $AddressList[$tblAddressPerson->getId()]['Option'] =
                                        new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                            array('Id'       => $Id,
                                                  'PersonId' => $tblPerson->getId(),
                                                  'Route'    => '/Reporting/SerialLetter/Export'));
                                }
                            } else {
                                if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                                    if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                        if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                            if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                                if (!isset($AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'])) {
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                        $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                        $PersonToAddress->getSalutation();
                                                    if ($PersonToAddress->getSalutation() === '') {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                            new Warning(new Exclamation().' Fehlt!');
                                                    }
                                                } else {
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'].', '.
                                                        $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                    if ($AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !==
                                                        new Exclamation().'Fehlt!'
                                                    ) {
                                                        if ($PersonToAddress->getSalutation() === '') {
                                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                                new Warning(new Exclamation().' Fehlt!');
                                                        } else {
                                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                                ( $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                                    ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'].', '.
                                                                    $PersonToAddress->getSalutation()
                                                                    : $PersonToAddress->getSalutation() );
                                                        }
                                                    }
                                                }
                                            }

                                            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                                                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                                if ($tblProspect) {
                                                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationYear'] = $tblProspectReservation->getReservationYear();
                                                    if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                                                    }
                                                    if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                                                    }
                                                }
                                            } else {
                                                $StudentNumber = new Small(new Muted('-NA-'));
                                                $SchoolCourse = '';
                                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                                if ($tblStudent) {
                                                    if ($tblStudent->getIdentifierComplete() != '') {
                                                        $StudentNumber = $tblStudent->getIdentifierComplete();
                                                    }
                                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                                                    if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                                                        if($tblStudentTransfer->getServiceTblCourse()){
                                                            $SchoolCourse = $tblStudentTransfer->getServiceTblCourse()->getName();
                                                        }
                                                    }
                                                }

                                                $Division = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                                                if ($Division === '') {
                                                    $Division = new Small(new Muted('-NA-'));
                                                }
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $StudentNumber;
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['Division'] = $Division;
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['SchoolCourse'] = $SchoolCourse;

                                            }

                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Person'] =
                                                ( $tblAddressPerson->getServiceTblPerson()
                                                    ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                                    : new Warning(new Exclamation().' Person nicht gefunden.') );
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToAddress'] =
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'];
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Address'] =
                                                ( $tblAddressPerson->getServiceTblToPerson()
                                                    ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                                    : new Warning(new Exclamation().' Adresse nicht gefunden.') );
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                                isset($AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'])
                                                && $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                    ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList']
                                                    : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Option'] =
                                                new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                                    array('Id'       => $Id,
                                                          'PersonId' => $tblPerson->getId(),
                                                          'Route'    => '/Reporting/SerialLetter/Export'));
                                        }
                                    }
                                }
                            }
                        });
                        if ($AddressList) {
                            foreach ($AddressList as $Address) {
                                $countAddresses++;
                                $dataList[] = array(
                                    'Number'              => ++$count,
                                    'Person'              => ( isset($Address['Person']) ? $Address['Person'] : '' ),
                                    'StudentNumber'       => ( isset($Address['StudentNumber']) ? $Address['StudentNumber'] : '' ),
                                    'Division'            => ( isset($Address['Division']) ? $Address['Division'] : '' ),
                                    'SchoolCourse'        => ( isset($Address['SchoolCourse']) ? $Address['SchoolCourse'] : '' ),
                                    'PersonToAddress'     => ( isset($Address['PersonToAddress']) ? $Address['PersonToAddress'] : '' ),
                                    'Address'             => ( isset($Address['Address']) ? $Address['Address'] : '' ),
                                    'Company'             => ( isset($Address['Company']) ? $Address['Company'] : '' ),
                                    'ReservationDivision' => ( isset($Address['ReservationDivision']) ? $Address['ReservationDivision'] : '' ),
                                    'ReservationYear'     => ( isset($Address['ReservationYear']) ? $Address['ReservationYear'] : '' ),
                                    'ReservationOptionA'  => ( isset($Address['ReservationOptionA']) ? $Address['ReservationOptionA'] : '' ),
                                    'ReservationOptionB'  => ( isset($Address['ReservationOptionB']) ? $Address['ReservationOptionB'] : '' ),
                                    'Salutation'          => ( isset($Address['Salutation']) ? $Address['Salutation'] : '' ),
                                    'Option'              => ( isset($Address['Option']) ? $Address['Option'] : '' )
                                );
                            }
                        }
                    } else {
                        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                            if ($tblProspect) {
                                $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                $ReservationDivision = $tblProspectReservation->getReservationDivision();
                                $ReservationYear = $tblProspectReservation->getReservationYear();
                                if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                    $ReservationOptionA = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                                }
                                if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                    $ReservationOptionB = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                                }
                            }
                        } else {
                            $StudentNumber = new Small(new Muted('-NA-'));
                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent) {
                                if ($tblStudent->getIdentifierComplete() != '') {
                                    $StudentNumber = $tblStudent->getIdentifierComplete();
                                }
                                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                                if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                                    if($tblStudentTransfer->getServiceTblCourse()){
                                        $SchoolCourse = $tblStudentTransfer->getServiceTblCourse()->getName();
                                    }
                                }
                            }
                            $DivisionString = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                            if ($DivisionString === '') {
                                $DivisionString = new Small(new Muted('-NA-'));
                            }
                            $Division = $DivisionString;
                        }

                        $dataList[] = array(
                            'Number'              => ++$count,
                            'Person'              => $tblPerson->getLastFirstName(),
                            'StudentNumber'       => ( isset($StudentNumber) ? $StudentNumber : '' ),
                            'Division'            => ( isset($Division) ? $Division : '' ),
                            'SchoolCourse'        => ( isset($SchoolCourse) ? $SchoolCourse : '' ),
                            'PersonToAddress'     => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                            'Address'             => '',
                            'Salutation'          => '',
                            'Company'             => '',
                            'ReservationDivision' => ( isset($ReservationDivision) ? $ReservationDivision : '' ),
                            'ReservationYear'     => ( isset($ReservationYear) ? $ReservationYear : '' ),
                            'ReservationOptionA'  => ( isset($ReservationOptionA) ? $ReservationOptionA : '' ),
                            'ReservationOptionB'  => ( isset($ReservationOptionB) ? $ReservationOptionB : '' ),
                            'Option'              => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                array('Id'       => $Id,
                                      'PersonId' => $tblPerson->getId(),
                                      'Route'    => '/Reporting/SerialLetter/Export'))
                        );
                    }
                }
            }

            if ($countAddresses > 0) {
                $Stage->addButton(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                        '/Api/Reporting/SerialLetter/Download', new Download(),
                        array('Id' => $tblSerialLetter->getId()))
                );
            }

            $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);

            $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                'Anzahl Anschreiben: '.$SerialLetterCount,);
            $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                    .' Person(en)', Label::LABEL_TYPE_INFO)
            );

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            $countAddresses == 0
                                ? new LayoutColumn(
                                new WarningMessage('Keine Adressen ausgewählt.',
                                    new Exclamation())
                            )
                                : null,
                            new LayoutColumn(
                                new Title(new EyeOpen().' Adressen', 'Übersicht')
                            ),
                            new LayoutColumn(
                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                6),
                            new LayoutColumn(
                                new TableData(
                                    $dataList, null, $columnList, $Interactive
                                )
                            )
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }

    public function frontendCompanyExport(TblSerialLetter $tblSerialLetter)
    {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Institutionen mit Adressen uns Ansprechpartner herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard(new Bold(new Info('Adressliste')), '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Adressliste für Serienbriefe anzeigen und herunterladen'));

        $TableContent = array();
        $columnList = array(
            'Number'     => 'Nr.',
            'Company'    => 'Institution',
            'Address'    => 'Adresse',
            'Salutation' => 'Anrede',
            'Title'      => 'Titel',
            'FirstName'  => 'Vorname',
            'LastName'   => 'Name'
        );
        $Interactive = array(
            'order'      => array(array(0, 'asc')),
            "columnDefs" => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 5),
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 6),
            ),
        );
        $tblSerialCompanyList = SerialLetter::useService()->getSerialCompanyBySerialLetter($tblSerialLetter, false);
        if($tblSerialCompanyList){
            // sort company by name
            $Company = array();
            foreach ($tblSerialCompanyList as $key => $row) {
                $Company[$key] = ($row->getServiceTblCompany() ? $row->getServiceTblCompany()->getName() : '');
            }
            array_multisort($Company, SORT_ASC, $tblSerialCompanyList);
        }

        if($tblSerialCompanyList){
            $count = 1;
            array_walk($tblSerialCompanyList, function(TblSerialCompany $tblSerialCompany) use (&$TableContent, &$count, $tblSerialLetter, &$Interactive) {
                $Item['Number'] = $count++;
                $Item['Salutation'] = '';
                $Item['Title'] = '';
                $Item['FirstName'] = '';
                $Item['LastName'] = '';
                if(($tblPerson = $tblSerialCompany->getServiceTblPerson())){

                    //ToDO Punkt entfernen (nur Vorname)
                    //ToDO Mehr Spalten (Titel, Vorname, Nachname)
                    $Item['Salutation'] = $tblPerson->getSalutation();
                    $Item['Title'] = $tblPerson->getTitle();
                    $Item['FirstName'] = str_replace('.', '', $tblPerson->getFirstName());
                    $Item['LastName'] = $tblPerson->getLastName();
                }

                $Item['Company'] = '';
                $Item['Address'] = '';
                if(($tblCompany = $tblSerialCompany->getServiceTblCompany())){
                    $Item['Company'] = $tblCompany->getDisplayName();

                    if(($tblAddress = Address::useService()->getAddressByCompany($tblCompany))){
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                array_push($TableContent, $Item);
            });
        }


        if (true) {
            $Stage->addButton(
                new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                    '/Api/Reporting/SerialLetter/Company/Download', new Download(),
                    array('Id' => $tblSerialLetter->getId()))
            );
        }

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCompanyCount($tblSerialLetter);

        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())));
        $PanelFooter = new PullRight(new Label('Enthält '.$SerialLetterCount
                .' Anschreiben', Label::LABEL_TYPE_INFO)
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Title(new EyeOpen().' Adressen', 'Übersicht')
                        ),
                        new LayoutColumn(
                            new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                            6),
                        new LayoutColumn(
                            new TableData(
                                $TableContent, null, $columnList, $Interactive
                            )
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendSerialLetterDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft())
            );
            $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
            if (!$tblSerialLetter) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Adressliste für Serienbriefe', new Bold($tblSerialLetter->getName()).
                                ( $tblSerialLetter->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblSerialLetter->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question().' Diese Adressliste für Serienbriefe wirklich löschen?', array(
                                $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/SerialLetter/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                .new Standard(
                                    'Nein', '/Reporting/SerialLetter', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->destroySerialLetter($tblSerialLetter)
                                    ? new Success(new SuccessIcon().' Die Adressliste für Serienbriefe wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
