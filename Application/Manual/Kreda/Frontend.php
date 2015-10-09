<?php
namespace SPHERE\Application\Manual\Kreda;

use SPHERE\Common\Documentation\Content\Kreda;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\Kreda
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null|string $Chapter
     * @param null|string $Page
     * @param null|string $Search
     *
     * @return Stage
     */
    public function frontendKreda($Chapter = null, $Page = null, $Search = null)
    {

        $Stage = new Stage();

        $Stage->setContent(
            new Kreda($Chapter, $Page, $Search)
        );

        return $Stage;
    }
}
