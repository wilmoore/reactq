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
     * Index 0 = selector (event name or pattern)
     * Index 1 = whether string should be treated as a valid event selector pattern
     * Index 2 = corresponding selector type (if string is a selector pattern)
     * Index 3 = corresponding regular expression (if string is a selector pattern)
     *
     * @return array
     */
    public function provider_valid_selectors() {
        return array(
            array('offer.accepted',         false,  null,   null),              // basic string + dot
            array('offer.first_acceptance', false,  null,   null),              // basic string + dot + underscore
            array('offer.first-acceptance', false,  null,   null),              // basic string + dot + dash
            array('offer:accepted',         false,  null,   null),              // basic string + colon
            array('my.offer3:accepted',     false,  null,   null),              // basic string + dot + colon + numbers
            array('^=offer',                true,   '^=',   '/^offer/'),        // starts-with
            array('$=accepted',             true,   '$=',   '/accepted$/'),     // ends-with
            array('*=post',                 true,   '*=',   '/.*post.*/'),      // contains string
            array('~=post',                 true,   '~=',   '/.*\bpost\b.*/'),  // contains word
            array('!=post',                 true,   '!=',   '/^(?!post)$/'),    // basic string (negated, i.e. does not equal)
        );
    }

    /**
     * Retrieves invalid selectors
     *
     * @return array
     */
    public function provider_invalid_selectors() {
        return array(
            array(null),                    // null
            array(''),                      // empty string
            array('offer accepted'),        // string with spaces
            array('offer/accepted'),        // string with forward-slash
            array('offer\accepted'),        // string with back-slash
            array('offer.åccepted'),        // unicode string
            array('@offer.accepted'),       // contains @
            array('offer!accepted'),        // contains !
            array('offer#accepted'),        // contains #
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
     * Asserts that ReactQueue can be instantiated.
     *
     * @test
     */
    public function Can_Instantiate_ReactQueue_Object() {
        $this->assertType(__NAMESPACE__.'\ReactQueue', new ReactQueue());
    }

    /**
     * Asserts that provided valid selectors evaluate as valid.
     *
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
     * Asserts that provided invalid selectors evaluate as invalid.
     *
     * @test
     * @param           $selector
     * @dataProvider    provider_invalid_selectors
     * @return          void
     */
    public function InValid_Selectors_Do_Not_Validate($selector) {
        $answer = $this->reactQueue->isValidSelector($selector);
        $this->assertFalse($answer);
    }

    /**
     * Asserts that a handler reference can be used to check that a particular listener/callback has been set.
     *
     * @test
     */
    public function Verify_That_A_Handler_Can_Queried_For_After_Being_Set() {
        $react  = new ReactQueue();
        $handle = $react->on('offer.accept')->call(function(){return;});

        $this->assertTrue($react->hasHandler($handle));
    }

    /**
     * Verify that handlers, when triggered, return a response collection object.
     *
     * @test
     */
    public function Verify_Event_Handler_Returns_Any_Response() {
        $on       = new ReactQueue();
        $handle   = $on('offer.accept')->call(array(new OfferEventHandler(), 'accept'));
        $response = $on->trigger('offer.accept', new User(), array('dollars' => 24));

        $this->assertType('Zend\EventManager\ResponseCollection', $response);
    }

    /**
     * Verify that handlers, when triggered, return the expected response.
     *
     * @test
     */
    public function Verify_Event_Handler_Returns_Correct_Response() {
        $on       = new ReactQueue();
        $handle   = $on('offer.accept')->call(array(new OfferEventHandler(), 'accept'));
        $response = $on->trigger('offer.accept', new User(), array('dollars' => 24));

        $this->assertEquals('jane, I have accepted your offer of $24', $response[0]);
    }

    /**
     * Verify that event handlers, when triggered, modify by reference, the given context object.
     *
     * @test
     */
    public function Verify_Event_Handler_Modifies_Given_Instance() {
        $user       = new User();
        $user->name = 'John';

        $on         = new ReactQueue();
        $handle     = $on('offer.accept')->call(array(new OfferEventHandler(), 'accept'));
        $response   = $on->trigger('offer.accept', $user, array('dollars' => 24));

        $this->assertEquals('John, I have accepted your offer of $24', $response[0]);
    }

    /**
     * Verify that when provided an obviously invalid callback, an exception is thrown.
     *
     * @test
     * @expectedException   ReactQueue\Exception\InvalidCallbackException
     */
    public function Applying_Bad_Callback_Throws_Exception() {
        $on     = new ReactQueue();
        $handle = $on('e')->call('obviously-not-a-valid-callback');
    }

    /**
     * Ensure that invalid event names do not validate successfully.
     *
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
     * Ensure that all valid selector patterns validate as being valid selector patterns.
     *
     * @test
     * @dataProvider    provider_valid_selectors
     *
     * @param           $selector
     * @param           $isSelectorPattern
     *
     * @return          void
     */
    public function Ensure_That_Valid_Selectors_Are_Seen_As_Valid_Selectors($selector, $isSelectorPattern) {
        $this->assertEquals($isSelectorPattern, $this->reactQueue->isSelectorPattern($selector));
    }

    /**
     * Ensure that the expected selector type is returned based on provided selector pattern.
     *
     * @test
     * @dataProvider    provider_valid_selectors
     *
     * @param           $selector
     * @param           $isSelectorPattern  (ignored)
     * @param           $selectorType
     *
     * @return          void
     */
    public function Ensure_That_Expected_Selector_Type_Is_Retrieved($selector, $isSelectorPattern, $selectorType) {
        $this->assertEquals($selectorType, $this->reactQueue->getSelectorType($selector));
    }

    /**
     * Ensure that the expected regular expression is returned based on provided selector pattern.
     *
     * @test
     * @dataProvider    provider_valid_selectors
     *
     * @param           $selector
     * @param           $isSelectorPattern
     * @param           $selectorType
     * @param           $selectorRegex
     *
     * @return          void
     */
    public function Convert_Selector_Pattern_To_Regex_Then_Validate(
        $selector, $isSelectorPattern, $selectorType, $selectorRegex) {
        if (! $isSelectorPattern) {
            $this->setExpectedException('ReactQueue\Exception\InvalidSelectorPatternException');
        }

        $this->assertEquals($selectorRegex, $this->reactQueue->getSelectorPatternRegex($selector));
    }

    /**
     * Ensure that a selector pattern can cause a single handler to be called when a matching event is triggered.
     *
     * @return  void
     */
    public function Verify_Event_Selector_Pattern_Triggers_Multiple_Event() {
        $on               = new ReactQueue();
        $eventHandler     = array(new OfferEventHandler(), 'log');
        $handlerReference = $on('^=offer')->call($eventHandler);

        $acceptResponse   = $on->trigger('offer.accept',  new User(), array('dollars' => 24));
        $declineResponse  = $on->trigger('offer.decline', new User(), array('dollars' => 24));

        $this->assertType('Zend\EventManager\ResponseCollection', $acceptResponse);
        $this->assertType('Zend\EventManager\ResponseCollection', $acceptResponse);
    }

}
