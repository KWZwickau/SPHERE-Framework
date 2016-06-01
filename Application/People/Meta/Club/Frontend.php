<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2016
 * Time: 08:26
 */

namespace SPHERE\Application\People\Meta\Club;

use SPHERE\Application\People\Meta\Club\Service\Entity\TblClub;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array $Meta
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array(), $Group = null)
    {

        $Stage = new Stage();

        $Stage->setDescription(
            new Danger(
                new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta'])) {
                /** @var TblClub $tblClub */
                $tblClub = Club::useService()->getClubByPerson($tblPerson);
                if ($tblClub) {
                    $Global->POST['Meta']['Identifier'] = $tblClub->getIdentifier();
                    $Global->POST['Meta']['EntryDate'] = $tblClub->getEntryDate();
                    $Global->POST['Meta']['ExitDate'] = $tblClub->getExitDate();
                    $Global->POST['Meta']['Remark'] = $tblClub->getRemark();
                    $Global->savePost();
                }
            }
        }

        $Stage->setContent(
            Club::useService()->createMeta(
                (new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new Panel('Mitglied', array(
                                    new TextField(
                                        'Meta[Identifier]', 'Mitgliedsnummer', 'Mitgliedsnummer'
                                    ),
                                ), Panel::PANEL_TYPE_INFO
                                ), 4),
                            new FormColumn(
                                new Panel('Daten', array(
                                    new DatePicker(
                                        'Meta[EntryDate]', '', 'Eintrittsdatum', new Calendar()
                                    ),
                                    new DatePicker(
                                        'Meta[ExitDate]', '', 'Austrittsdatum', new Calendar()
                                    ),
                                ), Panel::PANEL_TYPE_INFO
                                ), 4),
                            new FormColumn(
                                new Panel('Sonstiges', array(
                                    new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                                ), Panel::PANEL_TYPE_INFO
                                ), 4),
                        )),
                    )),
                ), new Primary('Speichern', new Save())
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta, $Group)
        );

        return $Stage;
    }
}