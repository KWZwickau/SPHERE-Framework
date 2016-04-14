<?php
namespace SPHERE\Application\Api;

/**
 * Class Response
 *
 * @package SPHERE\Application\Api
 */
class Response
{

    /** @var array $Error */
    private $Error = array();
    /** @var array $Data */
    private $Data = array();

    /**
     * @return string
     */
    public function __toString()
    {

        $Message = json_encode(array(
            'Error' => $this->Error,
            'Data'  => $this->Data
        ));
        if ($Message === false) {
            return (string)$this->errorBadGateway();
        }
        return $Message;
    }

    /**
     * @return $this
     */
    private function errorBadGateway()
    {

        return (new Response())->addError('Bad Gateway', '', 502);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param int    $Code
     *
     * @return $this
     */
    public function addError($Name = '', $Description = '', $Code = -1)
    {

        array_push($this->Error, array('Name' => $Name, 'Description' => $Description, 'Code' => $Code));
        return $this;
    }

    /**
     * @param mixed $Payload
     *
     * @return $this
     */
    public function addData($Payload)
    {

        array_push($this->Data, $Payload);
        return $this;
    }
}
