<?php

namespace SPHERE\Application\People\Person;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Filter\Service as FilterService;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class FrontendReadOnly
 *
 * @package SPHERE\Application\People\Person
 */
class FrontendReadOnly extends Extension implements IFrontendInterface
{

    /**
     *
     * @param null|int $Id
     * @param null|int $Group
     *
     * @return Stage
     */
    public function frontendPersonReadOnly($Id = null, $Group = null)
    {

        $stage = new Stage('Person', 'Datenblatt ' . ($Id ? 'bearbeiten' : 'anlegen'));
        $stage->addButton(
            new Standard('Zurück', '/People/Search/Group', new ChevronLeft(), array('Id' => $Group))
        );

        // Person bearbeiten
        if ($Id != null && ($tblPerson = Person::useService()->getPersonById($Id))) {

            $validationMessage = FilterService::getPersonMessageTable($tblPerson);

            $basicContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Grunddaten der Person werden geladen.'), 'BasicContent'
                ) . ApiPersonReadOnly::pipelineLoadBasicContent($Id);

            $commonContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Personendaten der Person werden geladen.'), 'CommonContent'
                ) . ApiPersonReadOnly::pipelineLoadCommonContent($Id);

            $prospectContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Interessent-Daten der Person werden geladen.'), 'ProspectContent'
                ) . ApiPersonReadOnly::pipelineLoadProspectTitle($Id);

            $teacherContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Lehrer-Daten der Person werden geladen.'), 'TeacherContent'
                ) . ApiPersonReadOnly::pipelineLoadTeacherTitle($Id);

            $custodyContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Sorgerecht-Daten der Person werden geladen.'), 'CustodyContent'
                ) . ApiPersonReadOnly::pipelineLoadCustodyTitle($Id);

            $clubContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Vereinsmitglied-Daten der Person werden geladen.'), 'ClubContent'
                ) . ApiPersonReadOnly::pipelineLoadClubTitle($Id);

            $integrationContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Integration-Daten der Person werden geladen.'), 'IntegrationContent'
                ) . ApiPersonReadOnly::pipelineLoadIntegrationTitle($Id);


            $stage->setContent(
                ($validationMessage ? $validationMessage : '')
                . $basicContent
                . $commonContent
                . $prospectContent
                . $teacherContent
                . $custodyContent
                . $clubContent
                . $integrationContent
                . self::getLayoutContact($tblPerson, $Group)
            );
        // neue Person anlegen
        } else {
            // todo Prüfung ob die Person bereits existiert bei neuen Personen

            if (Access::useService()->hasAuthorization('/Api/People/Person/ApiPersonEdit')) {
                $createPersonContent = ApiPersonEdit::receiverBlock(
                        new SuccessMessage('Das Formular wird geladen.'), 'PersonContent'
                    ) . ApiPersonEdit::pipelineCreatePersonContent();
            } else {
                $createPersonContent = new Danger('Sie haben nicht das Recht neue Personen anzulegen', new Exclamation());
            }

            $stage->setContent(
                $createPersonContent
            );
        }

        return $stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Group
     *
     * @return Layout
     */
    private static function getLayoutContact(TblPerson $tblPerson, $Group)
    {

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(new LayoutColumn(
                    Address::useFrontend()->frontendLayoutPerson($tblPerson, $Group)
                )),
            ), (new Title(new TagList().' Adressdaten',
                'der Person '.new Bold(new SuccessText($tblPerson->getFullName()))))
                ->addButton(
                    new Standard('Adresse hinzufügen', '/People/Person/Address/Create',
                        new ChevronDown(), array('Id' => $tblPerson->getId(), 'Group' => $Group)
                    )
                )
            ),
            new LayoutGroup(array(
                new LayoutRow(new LayoutColumn(
                    Phone::useFrontend()->frontendLayoutPerson($tblPerson , $Group)
                    . Mail::useFrontend()->frontendLayoutPerson($tblPerson, $Group)
                )),
            ), (new Title(new TagList().' Kontaktdaten',
                'der Person '.new Bold(new SuccessText($tblPerson->getFullName()))))
                ->addButton(
                    new Standard('Telefonnummer hinzufügen', '/People/Person/Phone/Create',
                        new ChevronDown(), array('Id' => $tblPerson->getId(), 'Group' => $Group)
                    )
                )
                ->addButton(
                    new Standard('E-Mail Adresse hinzufügen', '/People/Person/Mail/Create',
                        new ChevronDown(), array('Id' => $tblPerson->getId(), 'Group' => $Group)
                    )
                )
            ),
            new LayoutGroup(array(
                new LayoutRow(new LayoutColumn(array(
                    Relationship::useFrontend()->frontendLayoutPerson($tblPerson, $Group),
                    Relationship::useFrontend()->frontendLayoutCompany($tblPerson, $Group)
                ))),
            ), ( new Title(new TagList().' Beziehungen', new Bold(new SuccessText($tblPerson->getFullName())).' zu Personen und Institutionen') )
                ->addButton(
                    new Standard('Personenbeziehung hinzufügen', '/People/Person/Relationship/Create',
                        new ChevronDown(), array('Id' => $tblPerson->getId(), 'Group' => $Group)
                    )
                )
                ->addButton(
                    new Standard('Institutionenbeziehung hinzufügen', '/Corporation/Company/Relationship/Create',
                        new ChevronDown(), array('Id' => $tblPerson->getId(), 'Group' => $Group)
                    )
                )
            )
        ));
    }

    /**
     * @param string $label
     * @param int $size
     *
     * @return LayoutColumn
     */
    protected static function getLayoutColumnLabel($label, $size = 2)
    {
        return new LayoutColumn(new Bold($label . ':'), $size);
    }

    /**
     * @param string $value
     * @param int $size
     * @return LayoutColumn
     */
    protected static function getLayoutColumnValue($value, $size = 2)
    {
        return new LayoutColumn($value ? $value : '&ndash;', $size);
    }

    /**
     * @param int $size
     *
     * @return LayoutColumn
     */
    protected static function getLayoutColumnEmpty($size = 2)
    {
        return new LayoutColumn('&nbsp;', $size);
    }

    /**
     * @return Danger
     */
    protected static function getDataProtectionMessage()
    {

        return new Danger(
            new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
        );
    }
}