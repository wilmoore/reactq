<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueue;
      use PHPUnit_Framework_TestCase as TestCase,
          ReactQueueFixture\Domain\Entity\User,
          ReactQueueFixture\Offer\Event\EventHandler as OfferEventHandler,
          Zend\Stdlib\CallbackHandler;

/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class ReactQueueTest extends TestCase {

    /**
     * Re-usable ReactQueue Fixture
     *
     * @var ReactQueue
     */
    private $reactQueue;

    /**
     * Retrieves valid selectors
     *
     * @return array
     */
    public function provider_valid_selectors() {
        return array(
            array('offer.accepted'),        // basic string + dot
            array('offer.first_acceptance'),// basic string + dot + underscore
            array('offer.first-acceptance'),// basic string + dot + dash
            array('offer:accepted'),        // basic string + colon
            array('my.offer3:accepted'),    // basic string + dot + colon + numbers
            array('^=offer'),               // starts-with
            array('$=accepted'),            // ends-with
            array('*=post'),                // contains string
            array('~=post'),                // contains word
            array('!=post'),                // basic string (negated, i.e. does not equal)
        );
    }

    /**
     * Retrieves invalid selectors
     *
     * @return array
     */
    public function provider_invalid_selectors() {
        return array(
            array(null),                // null
            array(''),                  // empty string
            array('offer accepted'),    // string with spaces
            array('offer/accepted'),    // string with forward-slash
            array('offer\accepted'),    // string with back-slash
            array('offer.Œccepted'), // unicode string
            array('@offer.accepted'),   // contains @
            array('offer!accepted'),    // contains !
            array('offer#accepted'),    // contains #
        );
    }

    /**
     * Retrieves invalid event names
     *
     * @return  array
     */
    public function provider_invalid_event_names() {
        return array(
            array('^=offer'),               // starts-with
            array('$=accepted'),            // ends-with
            array('*=post'),                // contains string
            array('~=post'),                // contains word
            array('!=post'),                // basic string (negated, i.e. does not equal)
        );
    }

    /**
     * Do the following before each test...
     *
     * @return  void
     */
    public function setUp() {
        $this->reactQueue = new ReactQueue();
    }

    /**
     * @test
     */
    public function Can_Instantiate_Eventful_Object() {
        $this->assertType(__NAMESPACE__.'\ReactQueue', new ReactQueue());
    }

    /**
     * @test
     * @param           $selector
     * @dataProvider    provider_valid_selectors
     * @return          void
     */
    public function Valid_Selectors_Validate($selector) {
        $this->assertTrue(
            $this->reactQueue->isValidSelector($selector)
        );
    }

    /**
     * @test
     * @param           $selector
     * @dataProvider    provider_invalid_selectors
     * @return          void
     */
    public function InValid_Selectors_Do_Not_Validate($selector) {
        $this->assertFalse(
            $this->reactQueue->isValidSelector($selector)
        );
    }

    /**
     * @test
     */
    public function Verify_Applied_Function() {
        $on     = new ReactQueue();
        $handle = $on('offer.accept')->apply(function(){return;});

        $this->assertTrue($on->isApplied($handle));
    }

    /**
     * @test
     */
    public function Verify_Event_Handler_Returns_Any_Response() {
        $on       = new ReactQueue();
        $handle   = $on('offer.accept')->apply(array(new OfferEventHandler(), 'accept'));
        $response = $on->trigger('offer.accept', new User(), array('dollars' => 24));

        $this->assertType('Zend\EventManager\ResponseCollection', $response);
    }

    /**
     * @test
     */
    public function Verify_Event_Handler_Returns_Correct_Response() {
        $on       = new ReactQueue();
        $handle   = $on('offer.accept')->apply(array(new OfferEventHandler(), 'accept'));
        $response = $on->trigger('offer.accept', new User(), array('dollars' => 24));

        $this->assertEquals('jane, I have accepted your offer of $24', $response[0]);
    }

    /**
     * @test
     */
    public function Verify_Event_Handler_Modifies_Given_Instance() {
        $user       = new User();
        $user->name = 'John';

        $on         = new ReactQueue();
        $handle     = $on('offer.accept')->apply(array(new OfferEventHandler(), 'accept'));
        $response   = $on->trigger('offer.accept', $user, array('dollars' => 24));

        $this->assertEquals('John, I have accepted your offer of $24', $response[0]);
    }

    /**
     * @test
     * @expectedException   ReactQueue\Exception\InvalidCallbackException
     */
    public function Applying_Bad_Callback_Throws_Exception() {
        $on     = new ReactQueue();
        $handle = $on('e')->apply('obviously-not-a-valid-callback');
    }

    /**
     * @test
     * @param           $selector
     * @dataProvider    provider_invalid_selectors
     * @return          void
     */
    public function InValid_Event_Names_Should_Not_Validate($selector) {
        $this->assertFalse(
            $this->reactQueue->isValidSelector($selector)
        );
    }

    /**
     * Verifies that a single handler can be attached to multiple events in a single call by utilizing
     * a selector pattern.
     *
     * @return  void
     */
    public function Verify_Event_Selector_Pattern_Triggers_Multiple_Event() {
        $on               = new ReactQueue();
        $eventHandler     = array(new OfferEventHandler(), 'log');
        $handlerReference = $on('^=offer')->apply($eventHandler);

        $acceptResponse   = $on->trigger('offer.accept',  new User(), array('dollars' => 24));
        $declineResponse  = $on->trigger('offer.decline', new User(), array('dollars' => 24));

        $this->assertType('Zend\EventManager\ResponseCollection', $acceptResponse);
        $this->assertType('Zend\EventManager\ResponseCollection', $acceptResponse);
    }

}
