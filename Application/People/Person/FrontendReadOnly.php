<?php

namespace SPHERE\Application\People\Person;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\Division\Filter\Service as FilterService;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\Common\Frontend\Link\Repository\Link;
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
     * @param bool|false|string $TabActive
     *
     * @param null|int $Id
     * @param null|array $Person
     * @param null|array $Meta
     * @param null|int $Group
     *
     * @return Stage
     */
    public function frontendPersonReadOnly($TabActive = '#', $Id = null, $Person = null, $Meta = null, $Group = null)
    {

        $stage = new Stage('Person', 'Datenblatt ' . ($Id ? 'bearbeiten' : 'anlegen'));
        $stage->addButton(
            new Standard('Zurück', '/People/Search/Group', new ChevronLeft(), array('Id' => $Group))
        );

        //  todo neue Person anlegen, wichtig nur mit ApiPersonEdit

        if ($Id != null && ($tblPerson = Person::useService()->getPersonById($Id))) {

            // todo Prüfung ob die Person bereits existiert bei neuen Personen

            $validationMessage = FilterService::getPersonMessageTable($tblPerson);

            $basicContent = ApiPersonReadOnly::receiverBlock(
                    new SuccessMessage('Die Grunddaten der Person werden geladen.'), 'BasicContent'
                ) . ApiPersonReadOnly::pipelineLoadBasicContent($Id);

            $personDataContent = $this->getPersonDataContent($Id);

            $stage->setContent(
                ($validationMessage ? $validationMessage : '')
                . $basicContent
                . $personDataContent
            );
        }

        return $stage;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getBasicContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId, true))) {
            $groups = array();
            if (($tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson))) {
                foreach ($tblGroupList as $tblGroup) {
                    $groups[] = $tblGroup->getName();
                }
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Anrede'),
                    self::getLayoutColumnValue($tblPerson->getSalutation()),
                    self::getLayoutColumnLabel('Vorname'),
                    self::getLayoutColumnValue($tblPerson->getFirstName()),
                    self::getLayoutColumnLabel('Nachname'),
                    self::getLayoutColumnValue($tblPerson->getLastName()),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Titel'),
                    self::getLayoutColumnValue($tblPerson->getTitle()),
                    self::getLayoutColumnLabel('Zweiter Vorname'),
                    self::getLayoutColumnValue($tblPerson->getSecondName()),
                    self::getLayoutColumnLabel('Geburtsname'),
                    self::getLayoutColumnValue($tblPerson->getBirthName()),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnLabel('Rufname'),
                    self::getLayoutColumnValue($tblPerson->getCallName()),
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnEmpty(),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Gruppen'),
                    self::getLayoutColumnValue(implode(', ', $groups), 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditBasicContent($PersonId));

            return TemplateReadOnly::getContent(
                'Grunddaten',
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new PersonParent()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getPersonDataContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblCommon = $tblPerson->getCommon())
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonInformation = $tblCommon->getTblCommonInformation())
            ) {
                $birthday = $tblCommonBirthDates->getBirthday();
                $birthplace = $tblCommonBirthDates->getBirthplace();
                $gender = ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                    ? $tblCommonGender->getName() : '';

                $nationality = $tblCommonInformation->getNationality();
                $denomination = $tblCommonInformation->getDenomination();
                $isAssistance = $tblCommonInformation->isAssistance();
                if ($isAssistance == TblCommonInformation::VALUE_IS_ASSISTANCE_YES) {
                    $isAssistance = 'Ja';
                } elseif ($isAssistance == TblCommonInformation::VALUE_IS_ASSISTANCE_NO) {
                    $isAssistance = 'Nein';
                } else {
                    $isAssistance = '';
                }
                $assistanceActivity = $tblCommonInformation->getAssistanceActivity();

                $remark = $tblCommon->getRemark();
            } else {
                $birthday = '';
                $birthplace = '';
                $gender = '';

                $nationality = '';
                $denomination = '';
                $isAssistance = '';
                $assistanceActivity = '';

                $remark = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geburtstag'),
                    self::getLayoutColumnValue($birthday),
                    self::getLayoutColumnLabel('Staatsangehörigkeit'),
                    self::getLayoutColumnValue($nationality),
                    self::getLayoutColumnLabel('Mitarbeitbereitschaft'),
                    self::getLayoutColumnValue($isAssistance),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geburtsort'),
                    self::getLayoutColumnValue($birthplace),
                    self::getLayoutColumnLabel('Konfession'),
                    self::getLayoutColumnValue($denomination),
                    self::getLayoutColumnLabel('Mitarbeitbereitschaft - Tätigkeiten'),
                    self::getLayoutColumnValue($assistanceActivity),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geschlecht'),
                    self::getLayoutColumnValue($gender),
                    self::getLayoutColumnEmpty(8),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Bemerkungen'),
                    self::getLayoutColumnValue($remark, 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', '#'));

            return TemplateReadOnly::getContent(
                'Personendaten',
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
            );
        }

        return '';
    }

    private static function getLayoutColumnLabel($label, $size = 2)
    {
        return new LayoutColumn(new Bold($label . ':'), $size);
    }

    private static function getLayoutColumnValue($value, $size = 2)
    {
        return new LayoutColumn($value ? $value : '&ndash;', $size);
    }

    private static function getLayoutColumnEmpty($size = 2)
    {
        return new LayoutColumn('&nbsp;', $size);
    }
}