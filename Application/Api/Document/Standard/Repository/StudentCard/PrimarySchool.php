<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 09:18
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class PrimarySchool
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class PrimarySchool extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerkartei - Grundschule';
    }

    /**
     * @return Frame
     */
    public function buildDocument()
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSliceArray($this->setGradeLayoutHeader())
            )
        );
    }
}