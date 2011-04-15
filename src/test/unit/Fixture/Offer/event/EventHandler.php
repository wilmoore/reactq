<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueue\Test\Fixture\Offer\Event;
      use Zend\EventManager\Event;

/**
 * Offer event handler
 *
 * @package     Reacton
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class EventHandler {

    public function accept(Event $event) {
        $name    = $event->getTarget()->name;
        $dollars = $event->getParam('dollars');

        return sprintf('%s, I have accepted your offer of $%s', $name, $dollars);
    }

}
