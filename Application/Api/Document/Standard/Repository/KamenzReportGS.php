<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 22.06.2017
 * Time: 08:36
 */

namespace SPHERE\Application\Api\Document\Standard\Repository;


use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class KamenzReportGS
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReportGS extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Kamenz-Statistik GS';
    }



    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->styleTextBold()
                    ->styleMarginBottom('8px')
                    ->addElement((new Element())
                        ->setContent('1. Lehrpersonen nach Beschäftigungsumfang')
                    )
                )
            )
        );
    }
}