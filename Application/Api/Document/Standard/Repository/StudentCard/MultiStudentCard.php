<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 21.03.2017
 * Time: 11:40
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;

/**
 * Class MultiStudentCard
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentCard
 */
class MultiStudentCard extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Schülerkartei';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        $buildDocument = new Document();
        foreach ($pageList as $page)
        {
            $buildDocument->addPage($page);
        }
        return (new Frame())->addDocument($buildDocument);
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return 0;
    }
}