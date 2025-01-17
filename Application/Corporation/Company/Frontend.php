<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Api\Corporation\Company\ApiCompanyContact;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Form
     */
    private function formContact()
    {
        $tblSalutationAll = Person::useService()->getSalutationAll();
        $tblRelationshipAll = Relationship::useService()->getTypeAllByGroup(
            Relationship::useService()->getGroupByIdentifier( 'COMPANY' )
        );

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Beziehung', array(
                            (new SelectBox('Person[' . ViewRelationshipToCompany::TBL_TYPE_ID . ']', 'Art des Ansprechpartners', array('Name' => $tblRelationshipAll),
                                new Conversation()))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Anrede', array(
                            (new SelectBox('Person[' . ViewPerson::TBL_SALUTATION_ID . ']', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Vorname', array(
                            (new TextField('Person[' . ViewPerson::TBL_PERSON_FIRST_NAME . ']', 'Rufname', 'Vorname'))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Nachname', array(
                            (new TextField('Person[' . ViewPerson::TBL_PERSON_LAST_NAME . ']', 'Nachname', 'Nachname'))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                ))
            ))
            , new Primary('Ansprechpartner prüfen')
        );
    }

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @param null $Group
     * @return Stage
     */
    public function frontendDestroyCompany($Id = null, $Confirm = false, $Group = null)
    {

        $Stage = new Stage('Institution', 'Löschen');
        if ($Id) {
            if ($Group) {
                $Stage->addButton(new Standard('Zurück', '/Corporation/Search/Group', new ChevronLeft(), array('Id' => $Group)));
            }

            $tblCompany = Company::useService()->getCompanyById($Id);
            if (!$tblCompany) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Die Institution konnte nicht gefunden werden.', new Ban()),
                            new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                        )))
                    )))
                );
            } else {
                $removable = true;
                if(($tblStudentTransfer = Student::useService()->getStudentTransferByCompany($tblCompany))){
                    $removable = false;
                }
                if (!$Confirm) {

                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            (!$removable ? new Danger('Institutaion kann nicht gelöscht werden, da Sie in der Schülerakte
                             verwendet wird ('.count($tblStudentTransfer).')'):'').
                            new Panel('Institution', new Bold($tblCompany->getName().( $tblCompany->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblCompany->getDescription()))) : '')),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question().' Diese Institution wirklich löschen?', array(
                                $tblCompany->getName(),
                                $tblCompany->getExtendedName(),
                                $tblCompany->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                ($removable
                                ?new Standard('Ja', '/Corporation/Company/Destroy', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true, 'Group' => $Group)
                                    )
                                :(new Standard('Ja', '#', new Ok()))->setDisabled()
                                )
                                . new Standard(
                                    'Nein', '/Corporation/Search/Group', new Disable(), array('Id' => $Group)
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Company::useService()->removeCompany($tblCompany)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Institution wurde gelöscht.')
                                    : new Danger(new Ban().' Die Institution konnte nicht gelöscht werden.')
                                ),
                                new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_SUCCESS, array('Id' => $Group))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Daten nicht abrufbar.', new Ban()),
                        new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param $CompanyId
     * @param $Search
     *
     * @return string
     */
    public function loadPersonSearch($CompanyId, $Search): string
    {
        if ($Search != '' && strlen($Search) > 2) {
            $Search = str_replace(',', '', $Search);
            $Search = str_replace('.', '', $Search);
            $resultList = array();
            $result = '';
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                foreach ($tblPersonList as $tblPerson) {
                    $resultList[] = array(
                        'Option' => (new RadioBox('Data[SelectedPerson]' , '&nbsp;', $tblPerson->getId()))
                            ->ajaxPipelineOnClick(ApiCompanyContact::pipelineLoadPerson($CompanyId)),
                        'LastName' => $tblPerson->getLastName(),
                        'FirstName' => $tblPerson->getFirstSecondName(),
                        'Address' => ($tblAddress = $tblPerson->fetchMainAddress())
                            ? $tblAddress->getGuiString()
                            : new Warning('Keine Adresse hinterlegt'),
                    );
                }

                $columnList = array(
                    'Option'   => '',
                    'LastName' => 'Nachname',
                    'FirstName' => 'Vorname',
                    'Address'    => 'Adresse'
                );

                $result = new TableData(
                    $resultList,
                    null,
                    $columnList,
                    array(
                        'order' => array(
                            array(1, 'asc')
                        ),
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                            array('orderable' => false, 'width' => '20px', 'targets' => 0),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false,
                        'destroy' => true
                    )
                );
            }

            if (empty($resultList)) {
                $result = new WarningMessage('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $result = new WarningMessage('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return new Title('Verfügbare Personen ' . new Small(new Muted('der Personen-Suche: ')) . new Bold($Search))
            . $result
            . ApiCompanyContact::pipelineLoadPerson($CompanyId, $Search);
    }

    /**
     * @param null $CompanyId
     * @param null $PersonId
     * @param null $Search
     * @param bool $isSetPost
     *
     * @return Form
     */
    public function getCompanyContactForm($CompanyId = null, $PersonId = null, $Search = null, bool $isSetPost = false): Form
    {
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $global = $this->getGlobal();
        if ($tblPerson) {
            if ($isSetPost) {
                $global->POST['Data']['SalutationId'] = ($tblSalutation = $tblPerson->getTblSalutation()) ? $tblSalutation->getId() : 0;
                $global->POST['Data']['Title'] = $tblPerson->getTitle();
            }
            $global->POST['Data']['FirstName'] = $tblPerson->getFirstSecondName();
            $global->POST['Data']['LastName'] = $tblPerson->getLastName();
        } elseif ($Search && $isSetPost) {
            $global->POST['Data']['LastName'] = ucfirst($Search);
        }
        $global->savePost();

        $inputSalutation = new SelectBox('Data[SalutationId]', 'Anrede', array('Salutation' => Person::useService()->getSalutationAll()));
        $inputTitle = new AutoCompleter('Data[Title]', 'Titel', 'Titel', Person::useService()->getTitleAll());
        $inputFirstName = new TextField('Data[FirstName]', '', 'Vorname');
        $inputLastName = new TextField('Data[LastName]', '', 'Nachname');

        if ($tblPerson) {
            $inputFirstName->setDisabled();
            $inputLastName->setDisabled();
        } else {
            $inputLastName->setRequired();
        }

        return (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                   new Panel(
                       $tblPerson ? 'Bestehende Person' : 'Neue Person anlegen',
                       array(
                           $inputSalutation,
                           $inputTitle,
                           $inputFirstName,
                           $inputLastName
                       )
                   )
                , 6),
                new FormColumn(
                    new Panel(
                        'Beziehung erstellen',
                        array(
                            (new SelectBox('Data[TypeId]', 'Beziehung',
                                array('{{ Name }}' => Relationship::useService()->getTypeAllByGroup(Relationship::useService()->getGroupByIdentifier('COMPANY')))))
                                ->setRequired(),
                            new TextField('Data[Remark]', '', 'Bemerkung')
                        )
                    )
                , 6),
            )),
            new FormRow(new FormColumn(
                (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                    'Ansprechpartner ' . ($tblPerson ? 'für eine bestehende Person anlegen' : 'mit einer neuen Person anlegen'),
                    ApiCompanyContact::getEndpoint(),
                    new Save())
                )
                ->ajaxPipelineOnClick(ApiCompanyContact::pipelineSaveRelationship($CompanyId, $PersonId))
            ))
        ))))->disableSubmitAction();
    }

    /**
     * @param $Id
     * @param $Group
     *
     * @return string
     */
    public function frontendContact($Id = null, $Group = null): string
    {
        $stage = new Stage('Institution', 'Ansprechpartner');
        $stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(), array(
            'Id' => $Id,
            'Group' => $Group
        )));

        $tblCompany = Company::useService()->getCompanyById($Id);

        if (!$tblCompany) {
            $stage->setContent(new WarningMessage('Die Institution ist nicht hinterlegt')
                . new Redirect('/Corporation', Redirect::TIMEOUT_ERROR));
            return $stage;
        }

        $tableContent = array();
        if (($tblToCompanyList = Relationship::useService()->getRelationshipToCompanyByCompany($tblCompany))) {
            foreach ($tblToCompanyList as $tblToCompany) {
                if (($tblPerson = $tblToCompany->getServiceTblPerson())) {
                    $tableContent[] = array(
                        'Type' => $tblToCompany->getTblType()->getName(),
                        'Remark' => $tblToCompany->getRemark(),
                        'Salutation' => $tblPerson->getSalutation(),
                        'FirstName' => $tblPerson->getFirstSecondName(),
                        'LastName' => $tblPerson->getLastName(),
                        'Option' => new Standard(new PersonIcon(), '/People/Person', null, array('Id' => $tblPerson->getId()))
                    );
                }
            }
        }

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                (new TextField('Data[Search]', '', ''))
                    ->ajaxPipelineOnKeyUp(ApiCompanyContact::pipelineSearchPerson($Id))
            ),
            new FormColumn(
                ApiCompanyContact::receiverBlock('', 'SearchContent')
            ),
            new FormColumn(
                ApiCompanyContact::receiverBlock('', 'NewPerson')
            )
        )))))->disableSubmitAction();

        $stage->setContent(
            new Title(new SuccessIcon() . ' Zugewiesene Ansprechpartner')
            . new Panel('Institution',
                array(
                    $tblCompany->getName(),
                    $tblCompany->getExtendedName(),
                    $tblCompany->getDescription(),
                ),
                Panel::PANEL_TYPE_SUCCESS,
                array(
                    empty($tableContent)
                        ? new Info( 'Keine Ansprechpartner zugewiesen' )
                        : new TableData(
                            $tableContent,
                            null,
                            array(
                                'Type' => 'Beziehung',
                                'Remark' => 'Bemerkung',
                                'Salutation' => 'Anrede',
                                'FirstName' => 'Vorname',
                                'LastName' => 'Nachname',
                                'Option' => '&nbsp;'
                            ),
                            false
                        )
                )
            )

            . new Title(new Search() . ' Mögliche Ansprechpartner suchen')
            . new Panel(
                new Search() . ' Personen-Suche',
                $form,
                Panel::PANEL_TYPE_INFO
            )

            . ApiCompanyContact::receiverBlock('', 'LoadPerson')
        );

        return $stage;
    }
}
