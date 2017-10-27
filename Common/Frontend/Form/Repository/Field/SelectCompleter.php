<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractTextField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class SelectCompleter
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class SelectCompleter extends AbstractTextField implements IFieldInterface
{

    /**
     * @param string         $Name
     * @param string         $Label
     * @param string         $Placeholder
     * @param array          $Data array( value, value, .. )
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Label = '',
        $Placeholder = '',
        $Data = array(),
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/SelectCompleter.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (count($Data) == 1 && !is_numeric(key($Data))) {
            $Attribute = key($Data);
            $Convert = array();
            /** @var Element $Entity */
            foreach ((array)$Data[$Attribute] as $Entity) {
                if ($Entity) {
                    $Convert[$Entity->getId()] = $Entity->{'get'.$Attribute}();
                }
            }
            $Convert = array_unique($Convert);
            asort($Convert);
            $this->Template->setVariable('ElementData', $this->convertToJsonArray($Convert) );
            $this->Template->setVariable('DataLength', count($Convert));
        } else {
            $Data = array_unique($Data);
            asort($Data);
            $this->Template->setVariable('ElementData', $this->convertToJsonArray($Data));
            $this->Template->setVariable('DataLength', count($Data));
        }
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->setPostValue($this->Template, $Name, 'ElementValue');
    }

    /**
     * @param array $Data
     * @return string
     */
    private function convertToJsonArray ( $Data )
    {
        return json_encode(
            array_map('strval', array_values($Data))
        );
    }
}
