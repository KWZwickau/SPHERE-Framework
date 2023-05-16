<?php

namespace SPHERE\Application\Reporting\DeclarationBasis;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\DeclarationBasis
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDeclarationBasis()
    {

        $form = $this->getForm();

        $Stage = new Stage('Stichtagsmeldung Integrationsschüler', 'Datum auswählen');
        $Stage->setContent(new Well(
            $form
        ));

        return $Stage;
    }

    /**
     * @param null $Date
     *
     * @return Form
     */
    public function getForm($Date = null)
    {
        if ($Date) {
            $global = $this->getGlobal();
            $global->POST['Date'] = $Date;
            $global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new DatePicker('Date', 'Stichtag', 'Stichtag', new Calendar()))->setRequired()
                    , 3)
            )),
        ))
        , new Primary('Herunterladen', new Download(), true), '\Api\Reporting\DeclarationBasis\Download');
    }
}