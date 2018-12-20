<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person as PersonApp;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPerson
 *
 * @package SPHERE\Application\Api\People
 */
class ApiPerson extends Extension implements IApiInterface
{
    use ApiTrait;

    const API_DISPATCHER = 'MethodName';

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function exportApi($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('FormCreatePerson');
        $Dispatcher->registerMethod('ServiceCreatePerson');
        $Dispatcher->registerMethod('TableSimilarPerson');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @param null|array $Receiver
     * @param null|array $Person
     * @return string
     */
    public function ServiceCreatePerson($Receiver = null, $Person = null)
    {
        return (string)PersonApp::useService()
            ->createPerson(
                $this->pipelineFormCreatePerson($Receiver),
                $Person
            );
    }

    /**
     * @param null|array $Receiver
     * @return Form
     */
    private function pipelineFormCreatePerson($Receiver)
    {
        $CreatePersonReceiver = new BlockReceiver();
        $CreatePersonReceiver->setIdentifier($Receiver['FormCreatePerson']);

        $CreatePersonPipeline = new Pipeline();

        $CreatePersonEmitter = new ServerEmitter($CreatePersonReceiver, ApiPerson::getRoute());
        $CreatePersonEmitter->setGetPayload(array(
            ApiPerson::API_DISPATCHER => 'ServiceCreatePerson'
        ));
        $CreatePersonEmitter->setPostPayload(array(
            'Receiver' => $Receiver
        ));
        $CreatePersonPipeline->addEmitter($CreatePersonEmitter);

        $Form = $this->formPerson($Receiver);
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->ajaxPipelineOnSubmit($CreatePersonPipeline);

        return $Form;
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    /**
     * @param null|array $Receiver
     * @return Form
     */
    private function formPerson($Receiver = null)
    {

        $tblGroupList = Group::useService()->getGroupAllSorted();
        if ($tblGroupList) {
            // Create CheckBoxes
            /** @noinspection PhpUnusedParameterInspection */
            $tabIndex = 7;
            array_walk($tblGroupList, function (TblGroup &$tblGroup) use (&$tabIndex) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = (new CheckBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        ) )->setTabIndex($tabIndex++);
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        $ValidatePersonReceiver = new BlockReceiver();
        $ValidatePersonReceiver->setIdentifier($Receiver['TableSimilarPerson']);
        $ValidatePersonPipeline = new Pipeline();
//        $ValidatePersonPipeline->setLoadingMessage('Suche ähnliche Personen');
        $ValidatePersonEmitter = new ServerEmitter($ValidatePersonReceiver, ApiPerson::getRoute());
        $ValidatePersonEmitter->setGetPayload(array(
            ApiPerson::API_DISPATCHER => 'TableSimilarPerson'
        ));
        $ValidatePersonEmitter->setPostPayload(array(
            'Receiver' => $Receiver
        ));
        $ValidatePersonPipeline->addEmitter($ValidatePersonEmitter);

        $tblSalutationAll = PersonApp::useService()->getSalutationAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anrede', array(
                            (new SelectBox('Person[Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()))->setTabIndex(1),
                            (new AutoCompleter('Person[Title]', 'Titel', 'Titel', array('Dipl.- Ing.'),
                                new Conversation()))->setTabIndex(4),
                        ), Panel::PANEL_TYPE_INFO), 2),
                    new FormColumn(
                        new Panel('Vorname', array(
                            (new TextField('Person[FirstName]', 'Vorname', 'Vorname'))->setRequired()
                                ->ajaxPipelineOnKeyUp($ValidatePersonPipeline)
//                                ->setAutoFocus()
                                ->setTabIndex(2),
                            (new TextField('Person[SecondName]', 'weitere Vornamen', 'Zweiter Vorname'))->setTabIndex(5),
                            (new TextField('Person[CallName]', 'Rufname', 'Rufname') )->setTabIndex(6),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Nachname', array(
                            (new TextField('Person[LastName]', 'Nachname', 'Nachname'))->setRequired()
                                ->ajaxPipelineOnKeyUp($ValidatePersonPipeline)
                                ->setTabIndex(3),
                            (new TextField('Person[BirthName]', 'Geburtsname', 'Geburtsname'))->setTabIndex(7),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
                ))
            ))
        );
    }

    /**
     * @param null|array $Receiver
     * @return string
     */
    public function FormCreatePerson($Receiver = null)
    {
        return (string)$this->pipelineFormCreatePerson($Receiver);
    }

    /**
     * @param null|array $Receiver
     * @param null|array $Person
     * @return string
     */
    public function TableSimilarPerson($Receiver = null, $Person = null)
    {

        $InfoPersonReceiver = new BlockReceiver();
        $InfoPersonReceiver->setIdentifier($Receiver['InfoSimilarPerson']);
        $InfoPersonPipeline = new Pipeline();

        if ((!isset($Person['FirstName']) || empty($Person['FirstName']))
            || (!isset($Person['LastName']) || empty($Person['LastName']))
        ) {
            $InfoPersonEmitter = new ClientEmitter($InfoPersonReceiver, '');
            $InfoPersonPipeline->addEmitter($InfoPersonEmitter);
            // nothing for missing information
            return (string)$InfoPersonPipeline;
        } else {
            // dynamic search
            $Pile = new Pile();
            $Pile->addPile(PersonApp::useService(), new ViewPerson());
            // find Input fields in ViewPerson
            $Result = $Pile->searchPile(array(
                array(
                    ViewPerson::TBL_PERSON_FIRST_NAME => explode(' ', $Person['FirstName']),
                    ViewPerson::TBL_PERSON_LAST_NAME => explode(' ', $Person['LastName'])
                )
            ));

            if (!empty($Result)) { // show Person

                $TableList = array();
                /** @var ViewPerson[] $ViewPerson */
                foreach ($Result as $Index => $ViewPerson) {
                    $TableList[$Index] = current($ViewPerson)->__toArray();

                    $PersonId = $PersonName = '';
                    $Address = new Warning('Keine Adresse hinterlegt');
                    $BirthDay = new Warning('Kein Datum hinterlegt');
                    if (isset($TableList[$Index]['TblPerson_Id'])) {
                        $PersonId = $TableList[$Index]['TblPerson_Id'];
                        $tblPerson = PersonApp::useService()->getPersonById($PersonId);
                        if ($tblPerson) {
                            $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                            if ($tblCommon) {
                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                if ($tblCommonBirthDates) {
                                    if ($tblCommonBirthDates->getBirthday() != '') {
                                        $BirthDay = $tblCommonBirthDates->getBirthday();
                                    }
                                }
                            }

                            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                            if ($tblAddress) {
                                $Address = $tblAddress->getGuiString();
                            }
                        }
                    }
                    $TableList[$Index]['BirthDay'] = $BirthDay;
                    $TableList[$Index]['Address'] = $Address;
                    $TableList[$Index]['Option'] = new Standard('', '/People/Person', new PersonIcon(), array('Id' => $PersonId), 'Zur Person');
                }

                $Table = new TableData($TableList, new Title('Ähnliche Personen'), array(
                    ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                    ViewPerson::TBL_PERSON_TITLE => 'Titel',
                    ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                    ViewPerson::TBL_PERSON_SECOND_NAME => 'Zweiter Vorname',
                    ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                    ViewPerson::TBL_PERSON_BIRTH_NAME => 'Geburtsname',
                    'BirthDay' => 'Geburtstag',
                    'Address' => 'Adresse',
                    'Option' => '',
                ), array('order'      => array(
                    array(4, 'asc'),
                    array(2, 'asc')
                ),
                         'columnDefs' => array(
                             array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                             array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                         )));

                $InfoPersonEmitter = new ClientEmitter($InfoPersonReceiver, new Danger(new Bold(
                        count($TableList).' Personen mit ähnlichem Namen gefunden. Ist diese Person schon angelegt?')
                    . new Link('Zur Liste springen', null, null, array(), false, $Table->getHash())
                ));
                $InfoPersonPipeline->addEmitter($InfoPersonEmitter);
                return (string)$Table . $InfoPersonPipeline;
            }

//            $tblSalutation = PersonApp::useService()->getSalutationById($Person['Salutation']);
//            if( $tblSalutation ) {
//                $tblSalutation = $tblSalutation->getSalutation();
//            } else {
            $tblSalutation = '';
//            }

            $InfoPersonEmitter = new ClientEmitter($InfoPersonReceiver, new Success('Keine Personen zu ' . $tblSalutation . ' ' . $Person['FirstName'] . ' ' . $Person['LastName'] . ' gefunden'));
            $InfoPersonPipeline->addEmitter($InfoPersonEmitter);
            return (string)$InfoPersonPipeline;
        }
    }
}