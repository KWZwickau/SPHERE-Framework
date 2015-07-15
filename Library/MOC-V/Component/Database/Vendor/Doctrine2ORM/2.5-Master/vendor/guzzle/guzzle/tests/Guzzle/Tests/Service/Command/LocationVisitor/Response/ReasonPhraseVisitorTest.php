<?php

namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;

use Guzzle\Service\Command\LocationVisitor\Response\ReasonPhraseVisitor as Visitor;
use Guzzle\Service\Description\Parameter;

/**
 * @covers Guzzle\Service\Command\LocationVisitor\Response\ReasonPhraseVisitor
 */
class ReasonPhraseVisitorTest extends AbstractResponseVisitorTest
{

    public function testVisitsLocation()
    {

        $visitor = new Visitor();
        $param = new Parameter( array( 'location' => 'reasonPhrase', 'name' => 'phrase' ) );
        $visitor->visit( $this->command, $this->response, $param, $this->value );
        $this->assertEquals( 'OK', $this->value['phrase'] );
    }
}
