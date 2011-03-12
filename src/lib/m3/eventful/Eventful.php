<?php
/**
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace m3\eventful;
      use InvalidArgumentException;

/**
 * This is the main entry-point where listeners are bound and events are triggered.
 *
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */
class Eventful {

    const DEFAULT_PRIORITY = 1000;

    /**
     * @access  private
     * @var     callback[]
     */
    private $listeners;

    public function __construct() {}

    /**
     * @access  public
     * @param   string      $eventName
     * @param   callback    $callback
     * @param   integer     $priority
     *
     * @return  void
     */
    public function listen($eventName, $callback, $priority = self::DEFAULT_PRIORITY) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('$callback is not a valid callback.');
        }
    }

    public function listeners() {}

}
