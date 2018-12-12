<?php

namespace SPHERE\Application\People\Person;

use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\Division\Filter\Service as FilterService;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Stage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
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

        //  todo neue Person anlegen, wichtig nur mit ApiPersonEdit

        if ($Id != null && ($tblPerson = Person::useService()->getPersonById($Id))) {

            // todo Prüfung ob die Person bereits existiert bei neuen Personen

            // todo test Integration mit nur Readonly

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
            );
        }

        return $stage;
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