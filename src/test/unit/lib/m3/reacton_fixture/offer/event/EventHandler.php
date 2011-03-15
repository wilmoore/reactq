<?php
/**
 * offer event handler fixture
 *
 * @package     reacton
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace reacton_fixture\offer\event;

/**
 * offer event handler fixture
 *
 * @package     Reacton
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class EventHandler {

    public function accept(\Zend\EventManager\Event $event) {
        $name    = $event->getTarget()->name;
        $dollars = $event->getParam('dollars');

        return sprintf('%s, I have accepted your offer of $%s', $name, $dollars);
    }

}
