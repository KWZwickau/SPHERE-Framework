<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class MsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsHjRs extends Certificate
{

    /**
     * @param array $PageList
     * @return Frame
     * @internal param bool $IsSample
     *
     */
    public function buildCertificate($PageList = array())
    {

        if ($IsSample) {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() )
                        ->setContent('&nbsp;')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn(( new Element\Sample() )
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        } else {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() ), '75%')
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        }

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice($this->getSchoolName($personId))
                ->addSlice($this->getCertificateHead('Halbjahreszeugnis'))
                ->addSlice($this->getDivisionAndYear($personId, '20px', '1. Schulhalbjahr'))
                ->addSlice($this->getStudentName($personId))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('nahm am Unterricht der Schulart Mittelschule mit dem Ziel des Realschulabschlusses teil.')
                        ->styleTextSize('12px')
                    )
                    ->styleMarginTop('8px')
                )
                ->addSlice($this->getGradeLanes($personId))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
                ->addSlice($this->getOrientationStandard($personId))
                ->addSlice($this->getDescriptionHead($personId, true))
                ->addSlice($this->getDescriptionContent($personId, '85px', '15px'))
                ->addSlice($this->getDateLine($personId))
                ->addSlice($this->getSignPart($personId))
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('25px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            )
        );
    }
}
