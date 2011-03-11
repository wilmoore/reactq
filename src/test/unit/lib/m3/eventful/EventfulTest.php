<?php

/**
 * @namespace
 */
namespace m3\eventful;
      use m3\eventful\event\Subscription,
          PHPUnit_Framework_TestCase as TestCase;

class EventfulTest extends TestCase {

    public function setUp() {
        //var_dump(__CLASS__); exit;
        //$eventful->when('cash', 'accepted', function(){ echo 'nice doing business with you...'; });
    }

    /**
     * @test
     */
    public function Can_Instantiate_Eventful_Object() {
        $this->assertType(__NAMESPACE__.'\Eventful', new Eventful());
    }

    /**
     * @test
     */
    public function Can_Listen_For_Single_Event() {
        $eventful  = new Eventful();
        $eventName = 'cash:accepted';
        $callback  = function(){ echo 'nice doing business with you...'; };

        $eventful->listen($eventName, $callback);
        //$this->assertEquals('nice', $eventful->subscriptions('cash', 'accepted'));
    }

}
