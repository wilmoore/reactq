<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueue;
      use ReactQueue\Exception\InvalidSelectorException,
          ReactQueue\Exception\InvalidSelectorPatternException,
          ReactQueue\Exception\InvalidSelectorTypeException,
          ReactQueue\Exception\InvalidEventNameException,
          ReactQueue\Exception\MethodNotFoundException,
          ReactQueue\Exception\UndefinedSelectorException,
          Zend\EventManager\EventManager,
          Zend\EventManager\ResponseCollection,
          Zend\Stdlib\CallbackHandler,
          Zend\Stdlib\Exception\InvalidCallbackException;

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
    const PCRE_SELECTOR_PATTERN     = '@^(?<selectorType>\^=|\$=|\*=|~=|!=)?(?<eventName>[a-z0-9.:_-]+$)@i';

    /**
     * Textual representation of valid selector components
     *
     * @const
     */
    const VALID_SELECTOR_TEXT       = 'letters, numbers, colon (:), dot (.), and optionally, prefixed with a jquery-like attribute selector.';

    /**
     * Invalid selector message
     *
     * @const
     */
    const INVALID_SELECTOR_MSG      = "The selector provided is invalid.\nA valid selector consists of %s.";

    /**
     * Invalid event name message
     *
     * @const
     */
    const INVALID_EVENT_NAME_MSG    = "'%s' is not a valid event name.";

    /**
     * Invalid selector type message
     *
     * @const
     */
    const INVALID_SELECTOR_TYPE_MSG = "'%s' is not a valid selector type.";

    /**
     * Invalid selector pattern message
     *
     * @const
     */
    const INVALID_SELECTOR_PTRN_MSG = "'%s' is not a valid selector pattern.";

    /**
     * Current event selector
     *
     * @var null|string
     */
    private $selector           = null;

    /**
     * Stack of selector pattern event handlers
     *
     * @var array
     */
    private $patternHandler     = array();

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
    private $eventClass         = 'Zend\EventManager\Event';

    /**
     * Selector pattern regular expression templates
     *
     * Formatted for use with sprintf.
     *
     * @var string
     */
    protected $regexTemplate    = array(
        '^=' => '/^%s/',            // jquery-attribute-beginsWith
        '$=' => '/%s$/',            // jquery-attribute-endsWith
        '*=' => '/.*%s.*/',         // jquery-attribute-containsString
        '~=' => '/.*\b%s\b.*/',     // jquery-attribute-containsWord
        '!=' => '/^.*(?!%s).*$/',   // jquery-attribute-doesNotEqual
    );

    /**
     * Constructor
     *
     * Instantiates backend EventManager which supports handler priorities.
     *
     * @return void
     */
    public function __construct() {
        $this->eventManager     = new EventManager();
    }

    /**
     * alias of 'on' method
     *
     * @param   string      $selector
     *
     * @return  ReactQueue  $this
     */
    public function __invoke($selector) {
        return $this->on($selector);
    }

    /**
     * Sets the current event selector
     *
     * @param   string      $selector
     *
     * @return  ReactQueue  $this
     */
    public function on($selector) {
        if (! $this->isValidSelector($selector)) {
            throw new InvalidSelectorException(sprintf(self::INVALID_SELECTOR_MSG, self::VALID_SELECTOR_TEXT));
        }
        $this->selector = $selector;

        return $this;
    }

    /**
     * Retrieves the current event selector
     *
     * @return  string
     */
    public function getSelector() { return $this->selector; }

    /**
     * Designate a PHP callable to serve as the event handler which is executed when the corresponding event has been
     * triggered.  A PHP Callable is anything that evaluates to true when passed to is_callable().
     *
     * The second argument indicates a priority at which the event should be executed. By default, this value is 1;
     * however, you may set it for any integer value. Higher values have higher priority (i.e., execute first).
     *
     * @param   callback    $callback
     *                      PHP callback
     *
     * @param   integer     $priority
     *                      If provided, the priority at which to register the callback
     *
     * @return  HandlerAggregate|CallbackHandler (in order to later 'detach')
     *          describes the event handler combination.
     */
    public function call($callback, $priority = 1) {
        if (empty($this->selector)) { throw new UndefinedSelectorException('No event selector has been defined.'); }

        // utilize copied selector as event and attach/apply callback with a priority
        $handler = $this->eventManager->attach($this->selector, $callback, $priority);

        try {
            /**
             * @todo    currently this is the only way to invoke validation and potentially generate an Exception
             * @todo    refactor once callback validation is cleaned-up
             */
            $handler->getCallback();
        } catch(InvalidCallbackException $e) {
            // detach then re-throw component-specific exception
            $this->eventManager->detach($handler);
            throw new Exception\InvalidCallbackException($e->getMessage());
        }

        // store selector pattern handlers locally
        if ($this->isSelectorPattern($this->selector)) {
            $this->patternHandler[$this->selector] = $handler;
            $this->eventManager->detach($handler);
        }

        return $handler;
    }

    /**
     * Retrieves the current list of selector pattern handlers.
     *
     * @return  boolean
     */
    public function getPatternHandlers() {
        return $this->patternHandler;
    }

    /**
     * Retrieve all event handlers for a given event.
     *
     * First looks for basic string event name, then, if applicable, tries to pull in more handlers via pattern matches.
     * 
     * @param   string   $event 
     *
     * @return  PriorityQueue
     */
    public function getHandlers($eventName) {
        // basic string events (still a PriorityQueue instance even if empty)
        $handlerQueue    = $this->eventManager->getHandlers($eventName);
        $patternHandlers = $this->getPatternHandlers();

        foreach ($patternHandlers as $selector => $handler) {
            if (preg_match($this->getSelectorPatternRegex($selector), $eventName)) {
                $handlerQueue->insert($handler, $handler->getOption('priority'));
            }
        }
        unset($selector, $handler);

        return $handlerQueue;
    }

    /**
     * Checks that the given Callback handler has been applied and is currently being tracked as an event subscriber.
     *
     * @param   CallbackHandler $handler
     *
     * @return  boolean
     */
    public function hasHandler(CallbackHandler $handler) {
        $event    = $handler->getEvent();
        $handlers = $this->eventManager->getHandlers($event);

        // utilize priority queue API to find out if the handler was inserted
        return $handlers->contains($handler);
    }

    /**
     * Trigger all handlers for a given event.
     *
     * @param   string|string[]     $event
     *                              name(s) of the event(s) to be triggered
     *
     * @param   string|object       $context
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
    public function trigger($event, $context, $argv = array()) {
        return $this->triggerUntil($event, $context, $argv, function(){
            return false;
        });
    }

    /**
     * Trigger handlers until return value of one causes a callback to 
     * evaluate to true
     *
     * Triggers handlers until the provided callback evaluates the return 
     * value of one as true, or until all handlers have been executed.
     * 
     * @param   string              $event 
     *
     * @param   string|object       $context
     *                              Object calling emit, or symbol describing context (such as static method name)
     *
     * @param   array|ArrayAccess   $argv
     *                              Array of arguments; typically, should be associative
     *
     * @param   Callable            $callback 
     *
     * @return  ResponseCollection
     *
     * @throws  InvalidCallbackException    if invalid callback provided
     */
    public function triggerUntil($event, $context, $argv, $callback) {
        if (!is_callable($callback)) { throw new InvalidCallbackException('Invalid callback provided'); }

        $responses = new ResponseCollection;
        $e         = new $this->eventClass($event, $context, $argv);
        $handlers  = $this->getHandlers($event);

        foreach ($handlers as $handler) {
            $responses->push(call_user_func($handler->getCallback(), $e));

            if ($e->propagationIsStopped()) {
                $responses->setStopped(true);
                break;
            }

            if (call_user_func($callback, $responses->last())) {
                $responses->setStopped(true);
                break;
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
     * Is given selector a selector pattern?
     *
     * @param   string
     *
     * @return  boolean
     */
    public function isSelectorPattern($selector) {
        return (boolean) $this->getSelectorType($selector);
    }

    /**
     * Retrieves the selector type for a given selector.
     *
     * @param   string
     *
     * @return  string|null
     */
    public function getSelectorType($selector) {
        // retrieves the pattern matches array
        $matches = $this->getPatternMatch($selector);

        return isset($matches['selectorType'])
             ? $matches['selectorType']
             : null;
    }

    /**
     * Retrieves the event name for a given selector.
     *
     * @param   string
     *
     * @return  string|null
     */
    public function getSelectorEventName($selector) {
        // retrieves the pattern matches array
        $matches = $this->getPatternMatch($selector);

        return isset($matches['eventName'])
             ? $matches['eventName']
             : null;
    }

    /**
     * Retrieve the selector pattern regex.
     *
     * @param   string
     *
     * @return  string|null
     */
    public function getSelectorPatternRegex($selector) {
        // we should only work with a $selector that is a valid "selector pattern"
        if (! $this->isSelectorPattern($selector)) {
            throw new InvalidSelectorPatternException(sprintf(self::INVALID_SELECTOR_PTRN_MSG, $selector));
        }

        $type = $this->getSelectorType($selector);
        $name = $this->getSelectorEventName($selector);
        
        // selector regular expression template must be defined, otherwise, selector type is invalid.
        if (empty($this->regexTemplate[$type])) {
            throw new InvalidSelectorTypeException(sprintf(self::INVALID_SELECTOR_TYPE_MSG, $type));
        }

        // retrieve the regular expression template
        $regexTemplate = $this->regexTemplate[$type];

        // interpolate the template string and return the result
        return sprintf($regexTemplate, $name);
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
     * Because fatal errors suck and catching exceptions produces a bit less suckage :)
     *
     * @throws  Exception\MethodNotFoundException
     * @param   $name
     * @param   $arguments
     *
     * @return  void
     */
    public function __call($name, $arguments) {
        throw new MethodNotFoundException(sprintf( 'method "%s" does not exist in class "%s"', $name, __CLASS__));
    }

}
