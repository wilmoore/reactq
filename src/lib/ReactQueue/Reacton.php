<?php
/**
 * @package     reacton
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace reacton;
      use reacton\exception\InvalidSelectorException,
          reacton\exception\InvalidEventNameException,
          reacton\exception\MethodNotFoundException,
          reacton\exception\UndefinedSelectorException,
          Zend\EventManager\EventManager,
          Zend\EventManager\ResponseCollection,
          Zend\Stdlib\Exception\InvalidCallbackException,
          Zend\Stdlib\CallbackHandler;

/**
 * main entry-point where listeners are bound and events are triggered.
 *
 * @package     reacton
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class Reacton {

    /**
     * Perl Compatible Regex Selector Pattern
     * @const
     */
    const PCRE_SELECTOR_PATTERN = '@^(?<selectorType>\^=|\$=|\*=|~=|!=)?(?<eventName>[a-z0-9.:_-]+$)@i';

    /**
     * textual representation of valid selector components
     * @const
     */
    const VALID_SELECTOR_TEXT   = 'letters, numbers, colon (:), dot (.), and optionally, prefixed with a jquery-compatible attribute selector.';

    /**
     * current selector (reset to null once callback is successfully bound).
     * @var null|string
     */
    private $selector           = null;

    /**
     * instance of EventManager
     * @var EventManager
     */
    private $eventManager       = null;

    /**
     * @var string
     *      Class representing the event being emitted
     */
    protected $eventClass = 'Zend\EventManager\Event';

    /**
     * blocks the use of '$identifier' so as to not support static event management.
     * @return void
     */
    public function __construct() {
        $this->eventManager = new EventManager();
    }

    /**
     * shortcut to the 'setSelector' method.
     * @param   string  $selector
     * @return  Reacton $this
     */
    public function __invoke($selector) {
        return $this->setSelector($selector);
    }

    /**
     * set the current topic selector.
     * @param   string  $selector
     * @return  Reacton $this
     */
    public function setSelector($selector) {
        if (!$this->isValidSelector($selector)) {
            $message  = 'The selector provided is invalid'.PHP_EOL;
            $message .= 'A valid selector consists of '.self::VALID_SELECTOR_TEXT;
            throw new InvalidSelectorException($message);
        }
        $this->selector = $selector;

        return $this;
    }

    /**
     * retrieve current selector
     * @return  string
     */
    public function getSelector() { return $this->selector; }

    /**
     * Designate a PHP callable to be serve as the event
     * handler which is executed when the corresponding event has been triggered.
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
            // currently this is the only way to invoke validation and potentially generate an Exception
            $handler->getCallback();
        } catch(InvalidCallbackException $e) {
            // detach then re-throw component-specific exception
            $this->eventManager->detach($handler);
            throw new exception\InvalidCallbackException($e->getMessage());
        }

        return $handler;
    }

    /**
     * has the function been applied (based on given handler)
     *
     * @param   CallbackHandler $handler
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
     * @return  ResponseCollection All handler return values
     */
    public function trigger($event, $target, $arguments = array()) {
        if (! $this->isValidEventName($event)) {
            throw new InvalidEventNameException("'$event' is not a valid event name");
        }

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
     * Is given selector a regular string or special query (using jquery attribute selector syntax)?
     *
     *      Selector Type                   Example Selector       Matched Value
     *      =============                   ================       =============
     * 1.   basic string:                   'offer.accept'      => 'offer.accept'
     *      NOTE: valid strings consist of letters, numbers, dot (.), colon (:) -- spaces are not allowed
     * 2.   jquery-attribute-beginsWith:    '^=offer'           => 'offer.accept'       || 'offer.decline'
     * 3.   jquery-attribute-endsWith:      '$=login'           => 'user.login'         || 'bot.login'
     * 4.   jquery-attribute-containsString:'*=post'            => 'article.post'       || 'article-post.remove'
     * 5.   jquery-attribute-containsWord:  '~=post'            => 'article.post'       || 'article.post.remove'
     * 6.   jquery-attribute-doesNotEqual:  '!=offer.accept'    => 'not.offer.accept'
     *
     * @see     http://api.jquery.com/category/selectors/
     * @return  void
     */
    public function isValidSelector($selector) {
        return (boolean) $this->getPatternMatch($selector);
    }

    /**
     * Is given event name valid?
     * @param   string
     * @return  boolean
     */
    public function isValidEventName($eventName) {
        $matches = $this->getPatternMatch($eventName);
        return count($matches)
            && empty($matches['selectorType']);
    }

    /**
     * retrieve selector pattern matches array
     * @param   string
     * @return  array
     */
    public function getPatternMatch($selector) {
        preg_match(self::PCRE_SELECTOR_PATTERN, $selector, $matches);
        return $matches;
    }

    /**
     * Throw an exception instead of generating a fatal error on method calls that do not exist
     *
     * @throws  exception\MethodNotFoundException
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
