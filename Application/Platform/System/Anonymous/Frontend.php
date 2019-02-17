<?php
namespace SPHERE\Application\Platform\System\Anonymous;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Platform\System\Anonymous
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAnonymous()
    {

        $Stage = new Stage('Daten Anonymisieren');
        $Stage->addButton(new Standard('Personen Anonymisieren', __NAMESPACE__.'/UpdatePerson'));
        $Stage->addButton(new Standard('Adressen Anonymisieren', __NAMESPACE__.'/UpdateAddress'));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendUpdatePerson()
    {

        $Stage = new Stage('Daten Anonymisieren');
        $Stage->setContent(Anonymous::useService()->UpdatePerson());

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendUpdateAddress()
    {

        $Stage = new Stage('Daten Anonymisieren');
        $Stage->setContent(Anonymous::useService()->UpdateAddress());

        return $Stage;
    }
}
