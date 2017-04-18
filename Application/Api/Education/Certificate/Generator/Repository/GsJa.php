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
 * Class GsJa
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GsJa extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param array $PageList
     * @return Frame
     * @internal param bool $IsSample
     *
     */
    public function buildCertificate($PageList = array())
    {

        if ($IsSample) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn(( new Element() ), '75%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
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
                ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
                ->addSlice($this->getDivisionAndYear($personId))
                ->addSlice($this->getStudentName($personId))
                ->addSlice($this->getGradeLanes($personId))
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Einschätzung:')
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Rating is not empty) %}
                                    {{ Content.Input.Rating }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('110px')
                        )
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanes($personId)
                    ->styleHeight('165px'))
                ->addSlice($this->getDescriptionHead($personId, true))
                ->addSlice($this->getDescriptionContent($personId, '130px', '5px'))
                ->addSlice($this->getTransfer($personId))
                ->addSlice($this->getDateLine($personId))
                ->addSlice($this->getSignPart($personId, true))
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('20px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
                (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            )
        );
    }
}
