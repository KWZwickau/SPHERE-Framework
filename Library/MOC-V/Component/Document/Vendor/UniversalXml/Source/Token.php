<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class Token
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
class Token extends TokenPattern
{

    private $Name = '';
    private $AttributeList = array();
    private $Position = 0;

    /**
     * @param array $Content
     */
    function __construct($Content)
    {

        $this->Position = $Content[1];
        $this->Name = preg_replace('!\s.*?$!is', '', $Content[0]);
        preg_match_all('![\w:]+="[^"]*?"!is', $Content[0], $Matches);
        $Token = $Matches[0];

        $Attribute = array();
        while (null !== ( $AttributeString = array_pop($Token) )) {
            if ($AttributeString != '/') {
                preg_match('!(.*?)="(.*?)(?=")!is', $AttributeString, $Attribute);
                if (count($Attribute) == 3) {
                    $this->AttributeList[$Attribute[1]] = $Attribute[2];
                }
            }
        }
        $this->determineType($Content[0]);
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {

        return $this->Position;
    }

    /**
     * @return array
     */
    public function getAttributeList()
    {

        return $this->AttributeList;
    }
}
