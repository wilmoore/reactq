<?php
/**
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace m3\eventful\event\listener;

/**
 * This defines an event listener's metadata (event name, callback)
 *
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */
class Definition {

    /** @var callback */
    private $callback;
    /** @var string   */
    private $eventName;

    /**
     * @param   string      $eventName
     * @param   callback    $callback
     *
     * @return  void
     */
    public function __construct($eventName, $callback) {
        $this->callback  = $callback;
        $this->eventName = $eventName;
    }

    /**
     * @return  callback
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * @return  string
     */
    public function getEventName() {
        return $this->eventName;
    }

    /**
     * String representation of the callback
     * @return  null|string
     */
    public function __toString() {
        return is_callable($this->getCallback(), true, $text)
             ? $text
             : null;
    }

}