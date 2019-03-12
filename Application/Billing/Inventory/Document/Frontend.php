<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.03.2019
 * Time: 14:46
 */

namespace SPHERE\Application\Billing\Inventory\Document;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Billing\Inventory\Document
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendDocument()
    {

        $Stage = new Stage('Belegdruck', 'Übersicht');

//        $Stage->setContent(new Layout(
//            new LayoutGroup(array(
//                new LayoutRow(
//                    new LayoutColumn(
//                        ApiSetting::receiverPersonGroup($this->displayPersonGroup())
//                    )
//                ),
//                new LayoutRow(
//                    new LayoutColumn(
//                        ApiSetting::receiverSetting($this->displaySetting())
//                    )
//                )
//            ))
//        ));

        return $Stage;
    }
}