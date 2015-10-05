<?php
namespace SPHERE\Common\Documentation;

use SPHERE\Common\Documentation\Designer\Book;

/**
 * Class Documentation
 *
 * @package SPHERE\Common\Documentation
 */
class Documentation
{

    private $BookDevelopment = null;

    /**
     *
     */
    public function __construct()
    {

        $this->BookDevelopment = new Book('Styleguide & Cookbook');

        $Chapter = $this->BookDevelopment->createChapter('CT1', 'CD1');
        $Page = $Chapter->createPage();
        $Page->createHeadline('HT1', 'HD1');
    }

    /**
     * @return string
     */
    public function getDevelopment()
    {

        return (string)$this->BookDevelopment;
    }
}
