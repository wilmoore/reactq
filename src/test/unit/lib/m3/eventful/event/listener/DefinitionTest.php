<?php
/**
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace m3\eventful\event\listener;
      use PHPUnit_Framework_TestCase as TestCase;

class ListenerFixture { public function foo(){return 'bar';} }

/**
 * This defines an event listener's metadata (event name, callback)
 *
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */
class DefinitionTest extends TestCase {

    public function setUp() {}

    /**
     * @test
     */
    public function Can_Instantiate_Definition_Object() {
        $fixture = new ListenerFixture();
        $this->assertType(__NAMESPACE__.'\Definition', new Definition('money:accept', array($fixture, 'fun')));
    }

    /**
     * @test
     */
    public function Can_Instantiate_Definition_And_Retrieve_Callback() {
        $fixture  = new ListenerFixture();
        $listener = new Definition('money:accept', array($fixture, 'fun'));

        $this->assertEquals(__NAMESPACE__.'\ListenerFixture::fun', (string) $listener);
    }

}