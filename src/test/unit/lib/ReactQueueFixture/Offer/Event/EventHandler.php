<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueueFixture\Offer\Event;

/**
 * Offer event handler
 *
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class EventHandler {

    public function accept(\Zend\EventManager\Event $event) {
        $name    = $event->getTarget()->name;
        $dollars = $event->getParam('dollars');

        return sprintf('%s, I have accepted your offer of $%s', $name, $dollars);
    }

    public function log(\Zend\EventManager\Event $event) {
        $name    = $event->getTarget()->name;
        $dollars = $event->getParam('dollars');
        $status  = $event->getParam('status');

        return sprintf('(LOG MESSAGE) an offer was "%s" by "%s" for "$%s"', $status, $name, $dollars);
    }

}
