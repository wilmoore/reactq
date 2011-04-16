<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueue;
      use ReactQueue\Exception\InvalidSelectorException,
          ReactQueue\Exception\InvalidEventNameException,
          ReactQueue\Exception\MethodNotFoundException,
          ReactQueue\Exception\UndefinedSelectorException,
          Zend\EventManager\EventManager,
          Zend\EventManager\ResponseCollection,
          Zend\Stdlib\Exception\InvalidCallbackException,
          Zend\Stdlib\CallbackHandler;

/**
 * Main entry-point
 *
 * This is where listeners are bound and events are triggered.
 *
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class ReactQueue {

    /**
     * Perl Compatible Regex Selector Pattern
     *
     * @const
     */
    const PCRE_SELECTOR_PATTERN = '@^(?<selectorType>\^=|\$=|\*=|~=|!=)?(?<eventName>[a-z0-9.:_-]+$)@i';

    /**
     * Textual representation of valid selector components
     *
     * @const
     */
    const VALID_SELECTOR_TEXT   = 'letters, numbers, colon (:), dot (.), and optionally, prefixed with a jquery-like attribute selector.';

    /**
     * Invalid selector message (hopefully you don't see this one often)
     *
     * @const
     */
    const INVALID_SELECTOR_MSG  = "The selector provided is invalid.\nA valid selector consists of %s.";

    /**
     * Current selector (reset to null once callback is successfully bound).
     *
     * @var null|string
     */
    private $selector           = null;

    /**
     * EventManager instance
     *
     * @var EventManager
     */
    private $eventManager       = null;

    /**
     * Class representing the event being emitted
     *
     * @var string
     */
    protected $eventClass = 'Zend\EventManager\Event';

    /**
     * Constructor
     *
     * Instantiates backend EventManager which supports handler priorities.
     *
     * @return void
     */
    public function __construct() {
        $this->eventManager = new EventManager();
    }

    /**
     * Shortcut to the 'setSelector' method
     *
     * @param   string      $selector
     *
     * @return  ReactQueue  $this
     */
    public function __invoke($selector) {
        return $this->setSelector($selector);
    }

    /**
     * Shortcut to the 'setSelector' method.
     *
     * @param   string      $selector
     *
     * @return  ReactQueue  $this
     */
    public function on($selector) {
        return $this->setSelector($selector);
    }

    /**
     * Sets the current topic selector.
     *
     * @param   string      $selector
     *
     * @return  ReactQueue  $this
     */
    public function setSelector($selector) {
        if (! $this->isValidSelector($selector)) {
            throw new InvalidSelectorException(
                sprintf(self::INVALID_SELECTOR_MSG, self::VALID_SELECTOR_TEXT)
            );
        }
        $this->selector = $selector;

        return $this;
    }

    /**
     * Retrieves current selector
     *
     * @return  string
     */
    public function getSelector() { return $this->selector; }

    /**
     * Designate a PHP callable to serve as the event handler which is executed
     * when the corresponding event has been triggered.
     *
     * A PHP Callable is anything that evaluates to true if passed to is_callable
     *
     * The last argument indicates a priority at which the event should be executed. By default, this value is 1;
     * however, you may set it for any integer value. Higher values have higher priority (i.e., execute first).
     *
     * @param   callback    $callback
     *                      PHP callback
     *
     * @param   integer     $priority
     *                      If provided, the priority at which to register the callback
     *
     * @return  HandlerAggregate|CallbackHandler (in order to later 'detach')
     *          describes the event handler combination .
     */
    public function apply($callback, $priority = 1) {
        if (empty($this->selector)) { throw new UndefinedSelectorException('No event selector has been defined.'); }

        // copy and reset selector property
        $selector       = $this->selector;
        $this->selector = null;

        // utilize copied selector as event and attach/apply callback with a priority
        $handler = $this->eventManager->attach($selector, $callback, $priority);

        try {
            /**
             * @todo    currently this is the only way to invoke validation and potentially generate an Exception
             * @todo    refactor once callback validation is cleaned-up
             */
            $handler->getCallback();
        } catch(InvalidCallbackException $e) {
            // detach then re-throw component-specific Exception
            $this->eventManager->detach($handler);
            throw new Exception\InvalidCallbackException($e->getMessage());
        }

        return $handler;
    }

    /**
     * Has the function been applied (based on given handler)
     *
     * @param   CallbackHandler $handler
     *
     * @return  boolean
     */
    public function isApplied(CallbackHandler $handler) {
        $event    = $handler->getEvent();
        $handlers = $this->eventManager->getHandlers($event);

        // utilize priority queue API to find out if the handler was inserted
        return $handlers->contains($handler);
    }

    /**
     * Trigger all handlers for a given event.
     *
     * Also ensures that a selector pattern can't be used to trigger an event directly as patterns
     * are stored along-side normal string-based events; however, unlike the string-based events
     * they should not be triggered directly.
     *
     * @param   string              $event
     *                              name of the event to be triggered
     *
     * @param   string|object       $target
     *                              class or object instance corresponding to the operational "target"
     *                              Example: if we triggered an event called "article.post", our target would likely
     *                                       be an object instance of say $article. Target could also be a service that
     *                                       is composed of several domain entities.
     *
     * @param   array|ArrayAccess   $arguments
     *                              arguments hash to be passed to the event handler
     *
     * @return  ResponseCollection  All handler return values
     */
    public function trigger($event, $target, $arguments = array()) {
        if (! $this->isValidEventName($event)) {
            throw new InvalidEventNameException("'$event' is not a valid event name");
        }

        // always use an array for iteration
        $events    = (array) $event;
        $responses = new ResponseCollection();

        foreach ($events as $event) {
            $responseCollection = $this->eventManager->triggerUntil($event, $target, $arguments, function(){
                return false;
            });

            foreach ($responseCollection as $response) {
                $responses->push($response);
            }
        }

        return $responses;
    }

    /**
     * Is the given selector valid?
     *
     * A valid selector is one of:
     *  -   a regular string
     *  -   a special jquery-attribute-selector-like pattern
     *
     *      Selector Type                   Example Selector       Matched Value
     *      =============                   ================       =============
     * 1.   basic string:                   'offer.accept'      => 'offer.accept'
     *      NOTE: valid strings consist of letters, numbers, dot (.), colon (:) -- spaces are not allowed
     *      
     * 2.   jquery-attribute-beginsWith:    '^=offer'           => 'offer.accept'       || 'offer.decline'
     * 3.   jquery-attribute-endsWith:      '$=login'           => 'user.login'         || 'bot.login'
     * 4.   jquery-attribute-containsString:'*=post'            => 'article.post'       || 'article-post.remove'
     * 5.   jquery-attribute-containsWord:  '~=post'            => 'article.post'       || 'article.post.remove'
     * 6.   jquery-attribute-doesNotEqual:  '!=offer.accept'    => 'not.offer.accept'
     *
     * @link    http://api.jquery.com/category/selectors/
     *
     * @return  boolean
     */
    public function isValidSelector($selector) {
        // cast returned array to boolean (empty array == false)
        return (boolean) $this->getPatternMatch($selector);
    }

    /**
     * Is given event name valid?
     *
     * @param   string
     *
     * @return  boolean
     */
    public function isValidEventName($eventName) {
        // retrieves the pattern matches array
        $matches = $this->getPatternMatch($eventName);

        // a valid event name _MUST_ include only the 'eventName' capture group
        return isset($matches['eventName'])
            && empty($matches['selectorType']);
    }

    /**
     * Retrieves selector pattern matches array
     *
     * If there are no matches, an empty array will be returned which can be
     * evaluated as (boolean) which would equate to -false-
     *
     * @param   string
     *
     * @return  array
     */
    public function getPatternMatch($selector) {
        // execute PCRE potentially populating the $matches variable on success
        preg_match(self::PCRE_SELECTOR_PATTERN, $selector, $matches);

        // NOTE: to view named capture groups see 'self::PCRE_SELECTOR_PATTERN'
        return $matches;
    }

    /**
     * Throws an exception instead of generating a fatal error on method calls that do not exist
     *
     * because fatal errors suck and catching exceptions produces quite less suckage :)
     *
     * @throws  Exception\MethodNotFoundException
     * @param   $name
     * @param   $arguments
     *
     * @return  void
     */
    public function __call($name, $arguments) {
        throw new MethodNotFoundException(sprintf(
            'method "%s" does not exist in class "%s"', $name, __CLASS__
        ));
    }

}
