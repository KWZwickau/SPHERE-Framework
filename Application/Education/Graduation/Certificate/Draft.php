<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

class Draft
{

    /** @var int $Person */
    private $Person = 0;
    /** @var int $Division */
    private $Division = 0;
    /** @var string $Certificate */
    private $Certificate = '';
    /** @var array $Data */
    private $Data = array();

    /**
     * Draft constructor.
     *
     * @param int    $Person
     * @param int    $Division
     * @param string $Certificate
     * @param array  $Data
     */
    public function __construct($Person = 0, $Division = 0, $Certificate = '', $Data = array())
    {

        $this->Person = (int)$Person;
        $this->Division = (int)$Division;
        $this->Certificate = (string)$Certificate;
        $this->Data = (array)$Data;
    }

    /**
     * @return int
     */
    public function getDivision()
    {

        return $this->Division;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {

        return $this->Certificate;
    }

    /**
     * @return array
     */
    public function getData()
    {

        return $this->Data;
    }

    /**
     * @param string $EncodedDraft
     *
     * @return Draft
     * @throws \Exception
     */
    public function decodeDraft($EncodedDraft)
    {

        $Draft = unserialize($EncodedDraft);
        if ($Draft instanceof Draft) {
            return $Draft;
        }
        throw new \Exception('Unable to decode Certificate-Draft');
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->encodeDraft();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function encodeDraft()
    {

        $Draft = serialize($this);
        if ($Draft === false) {
            throw new \Exception('Unable to encode Certificate-Draft');
        }
        return $Draft;
    }

    /**
     * @return int
     */
    public function getPerson()
    {

        return $this->Person;
    }
}
