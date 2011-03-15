<?php
/**
 * @package     reacton
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace m3\reacton;
      use PHPUnit_Framework_TestCase as TestCase,
          reacton_fixture\domain\entity\User,
          reacton_fixture\offer\event\EventHandler as OfferEventHandler,
          Zend\Stdlib\CallbackHandler;

/**
 * @package     reacton
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class ReactonTest extends TestCase {

    /**
     * re-usable Reacton instance
     * @var Reacton
     */
    private $reacton;

    /**
     * retrieve valid selectors
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
     * retrieve invalid selectors
     * @return array
     */
    public function provider_invalid_selectors() {
        return array(
            array(null),                // null
            array(''),                  // empty string
            array('offer accepted'),    // string with spaces
            array('offer/accepted'),    // string with forward-slash
            array('offer\accepted'),    // string with back-slash
            array('offer.Œccepted'),    // unicode string
            array('@offer.accepted'),   // contains @
            array('offer!accepted'),    // contains !
            array('offer#accepted'),    // contains #
        );
    }

    /**
     * retrieve Zend Event Manager methods that should not be exposed
     * @return  array
     */
    public function provider_zem_unexposed_methods() {
        return array(
            //    $methodName,  $arguments
            array('attach',     array('offer.accept', function(){return;})),
            array('detach',     array(new CallbackHandler('event.name', function(){}))),
        );
    }

    /**
     * before each test, do...
     */
    public function setUp() {
        $this->reacton = new Reacton();
    }

    /**
     * @test
     */
    public function Can_Instantiate_Eventful_Object() {
        $this->assertType(__NAMESPACE__.'\Reacton', new Reacton());
    }

    /**
     * @test
     * @param           $selector
     * @dataProvider    provider_valid_selectors
     * @return          void
     */
    public function Valid_Selectors_Validate($selector) {
        $this->assertTrue(
            $this->reacton->isValidSelector($selector)
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
            $this->reacton->isValidSelector($selector)
        );
    }

    /**
     * @test
     * @dataProvider        provider_zem_unexposed_methods
     * @expectedException   m3\reacton\exception\MethodNotFoundException
     */
    public function Calling_ZEM_Methods_Directly_Throws_Exception($methodName, array $arguments = array()) {
        $on = new Reacton();
        call_user_func_array(array($on, $methodName), $arguments);
    }

    /**
     * @test
     */
    public function Verify_Applied_Function() {
        $on     = new Reacton();
        $handle = $on('offer.accept')->apply(function(){return;});

        $this->assertTrue($on->isApplied($handle));
    }

    /**
     * @test
     */
    public function Verify_Event_Handler_Returns_Any_Response() {
        $on       = new Reacton();
        $handle   = $on('offer.accept')->apply(array(new OfferEventHandler(), 'accept'));
        $response = $on->trigger('offer.accept', new User(), array('dollars' => 24));

        $this->assertType('Zend\EventManager\ResponseCollection', $response);
    }

    /**
     * @test
     */
    public function Verify_Event_Handler_Returns_Correct_Response() {
        $on       = new Reacton();
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

        $on         = new Reacton();
        $handle     = $on('offer.accept')->apply(array(new OfferEventHandler(), 'accept'));
        $response   = $on->trigger('offer.accept', $user, array('dollars' => 24));

        $this->assertEquals('John, I have accepted your offer of $24', $response[0]);
    }

    /**
     * @test
     * @expectedException   m3\reacton\exception\InvalidCallbackException
     */
    public function Applying_Bad_Callback_Throws_Exception() {
        $on     = new Reacton();
        $handle = $on('e')->apply('obviously-not-a-valid-callback');
    }
}
