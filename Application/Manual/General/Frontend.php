<?php
namespace SPHERE\Application\Manual\General;

use SPHERE\Common\Documentation\Content\General as Book;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\General
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
    public function frontendGeneral($Chapter = null, $Page = null, $Search = null)
    {

        $Stage = new Stage();

        $Stage->setContent(
            new Book($Chapter, $Page, $Search)
        );

        return $Stage;
    }
}
