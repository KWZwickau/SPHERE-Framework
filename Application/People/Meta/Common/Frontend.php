<?php
namespace SPHERE\Application\People\Meta\Common;

use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Sheriff;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Common
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array())
    {

        $Stage = new Stage();

        $Stage->setDescription(
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta'] )) {
                /** @var TblCommon $tblCommon */
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Global->POST['Meta']['Remark'] = $tblCommon->getRemark();
                    /** @var TblCommonBirthDates $tblCommonBirthDates */
                    $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                    if ($tblCommonBirthDates) {
                        $Global->POST['Meta']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                        $Global->POST['Meta']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                        $Global->POST['Meta']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
                    }
                    /** @var TblCommonInformation $tblCommonInformation */
                    $tblCommonInformation = $tblCommon->getTblCommonInformation();
                    if ($tblCommonInformation) {
                        $Global->POST['Meta']['Information']['Nationality'] = $tblCommonInformation->getNationality();
                        $Global->POST['Meta']['Information']['Denomination'] = $tblCommonInformation->getDenomination();
                        $Global->POST['Meta']['Information']['IsAssistance'] = $tblCommonInformation->getIsAssistance();
                        $Global->POST['Meta']['Information']['AssistanceActivity'] = $tblCommonInformation->getAssistanceActivity();
                    }
                    $Global->savePost();
                }
            }
        }

        $tblCommonBirthDatesAll = Common::useService()->getCommonBirthDatesAll();
        $tblBirthplaceAll = array();
        if ($tblCommonBirthDatesAll) {
            array_walk($tblCommonBirthDatesAll,
                function (TblCommonBirthDates &$tblCommonBirthDates) use (&$tblBirthplaceAll) {

                    if ($tblCommonBirthDates->getBirthplace()) {
                        if (!in_array($tblCommonBirthDates->getBirthplace(), $tblBirthplaceAll)) {
                            array_push($tblBirthplaceAll, $tblCommonBirthDates->getBirthplace());
                        }
                    }
                });
        }

        $tblCommonInformationAll = Common::useService()->getCommonInformationAll();
        $tblNationalityAll = array();
        $tblDenominationAll = array();
        if ($tblCommonInformationAll) {
            array_walk($tblCommonInformationAll,
                function (TblCommonInformation &$tblCommonInformation) use (&$tblNationalityAll, &$tblDenominationAll) {

                    if ($tblCommonInformation->getNationality()) {
                        if (!in_array($tblCommonInformation->getNationality(), $tblNationalityAll)) {
                            array_push($tblNationalityAll, $tblCommonInformation->getNationality());
                        }
                    }
                    if ($tblCommonInformation->getDenomination()) {
                        if (!in_array($tblCommonInformation->getDenomination(), $tblDenominationAll)) {
                            array_push($tblDenominationAll, $tblCommonInformation->getDenomination());
                        }
                    }
                });
            $DefaultDenomination = array(
                'Altkatholisch',
                'Evangelisch',
                'Evangelisch-lutherisch',
                'Evangelisch-reformiert',
                'Französisch-reformiert',
                'Freireligiöse Landesgemeinde Baden',
                'Freireligiöse Landesgemeinde Pfalz',
                'Israelitische Religionsgemeinschaft Baden',
                'Römisch-katholisch',
                'Saarland: israelitisch'
            );
            array_walk($DefaultDenomination, function ($Denomination) use (&$tblDenominationAll) {

                if (!in_array($Denomination, $tblDenominationAll)) {
                    array_push($tblDenominationAll, $Denomination);
                }
            });
        }

        $Stage->setContent(
            Common::useService()->createMeta(
                (new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(array(
                                new Panel('Geburtsdaten', array(
                                    new DatePicker('Meta[BirthDates][Birthday]', 'Geburtstag', 'Geburtstag',
                                        new Calendar()),
                                    new AutoCompleter('Meta[BirthDates][Birthplace]', 'Geburtsort', 'Geburtsort',
                                        $tblBirthplaceAll,
                                        new MapMarker()),
                                    new SelectBox('Meta[BirthDates][Gender]', 'Geschlecht', array(
                                        TblCommonBirthDates::VALUE_GENDER_NULL   => '',
                                        TblCommonBirthDates::VALUE_GENDER_MALE   => 'Männlich',
                                        TblCommonBirthDates::VALUE_GENDER_FEMALE => 'Weiblich'
                                    ), new Child()),
                                ), Panel::PANEL_TYPE_INFO),
                            ), 3),
                            new FormColumn(array(
                                new Panel('Ausweisdaten / Informationen', array(
                                    new AutoCompleter('Meta[Information][Nationality]', 'Staatsangehörigkeit',
                                        'Staatsangehörigkeit',
                                        $tblNationalityAll, new Nameplate()
                                    ),
                                    new AutoCompleter('Meta[Information][Denomination]', 'Konfession',
                                        'Konfession',
                                        $tblDenominationAll, new TempleChurch()
                                    ),
                                ), Panel::PANEL_TYPE_INFO),
                            ), 3),
                            new FormColumn(array(
                                new Panel('Mitarbeit', array(
                                    new SelectBox('Meta[Information][IsAssistance]', 'Mitarbeitsbereitschaft', array(
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL => '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_YES  => 'Ja',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NO   => 'Nein'
                                    ), new Sheriff()
                                    ),
                                    new TextArea('Meta[Information][AssistanceActivity]',
                                        'Mitarbeitsbereitschaft - Tätigkeiten',
                                        'Mitarbeitsbereitschaft - Tätigkeiten', new Pencil()
                                    ),
                                ), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new FormColumn(array(
                                new Panel('Sonstiges', array(
                                    new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                                ), Panel::PANEL_TYPE_INFO)
                            ), 3),
                        )),
                    )),
                ), new Primary('Informationen speichern')
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta)
        );

        return $Stage;
    }
}
