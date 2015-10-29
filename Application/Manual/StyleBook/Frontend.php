<?php
namespace SPHERE\Application\Manual\StyleBook;

use SPHERE\Common\Documentation\Content\StyleBook as Book;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\StyleBook
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
    public function frontendStyleBook($Chapter = null, $Page = null, $Search = null)
    {

        $Stage = new Stage();

        $Stage->setContent(
            new Book($Chapter, $Page, $Search)
        );

        return $Stage;
    }
}
