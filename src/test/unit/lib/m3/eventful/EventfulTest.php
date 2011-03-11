<?php

/**
 * @namespace
 */
namespace m3\eventful;
      use PHPUnit_Framework_TestCase as TestCase;

class EventfulTest extends TestCase {

    public function setUp() {
    }

    /**
     * @test
     */
    public function Can_Instantiate_Eventful_Object() {
        $this->assertType(__NAMESPACE__.'\Eventful', new Eventful());
    }

}
