<?php
/**
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace m3\eventful;
      use SplPriorityQueue as PriorityQueue;

/**
 * This data structure is where listeners are stored along with a priority.
 *
 * @package     Eventful
 * @copyright   (c) Wil Moore III <wil.moore+eventful@wilmoore.com>
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */
class Listeners extends PriorityQueue {

    /**
     * @access  protected
     * @var     integer
     */
    protected $serial = PHP_INT_MAX;

    /**
     * @param  mixed    $value
     * @param  integer  $priority
     * @return void
     */
    public function insert($value, $priority) {
        parent::insert($value, array($priority, $this->serial--));
    }
}
