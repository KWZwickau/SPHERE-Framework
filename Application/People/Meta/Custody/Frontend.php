<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Custody
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
                /** @var TblCustody $tblCustody */
                $tblCustody = Custody::useService()->getCustodyByPerson($tblPerson);
                if ($tblCustody) {
                    $Global->POST['Meta']['Remark'] = $tblCustody->getRemark();
                    $Global->POST['Meta']['Occupation'] = $tblCustody->getOccupation();
                    $Global->POST['Meta']['Employment'] = $tblCustody->getEmployment();
                    $Global->savePost();
                }
            }
        }

        $Stage->setContent(
            Custody::useService()->createMeta(
                (new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new Panel('Berufliches', array(
                                    new AutoCompleter('Meta[Occupation]', 'Beruf', 'Beruf',
                                        array(), new MapMarker()
                                    ),
                                    new AutoCompleter('Meta[Employment]', 'Arbeitsstelle', 'Arbeitsstelle',
                                        array(), new Nameplate()
                                    ),
                                ), Panel::PANEL_TYPE_INFO
                                ), 6),
                            new FormColumn(
                                new Panel('Sonstiges', array(
                                    new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                                ), Panel::PANEL_TYPE_INFO
                                ), 6),
                        )),
                    )),
                ), new Primary('Informationen speichern')
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta)
        );

        return $Stage;
    }
}
