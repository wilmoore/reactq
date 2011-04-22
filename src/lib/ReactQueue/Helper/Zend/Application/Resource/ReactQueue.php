<?php
/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */

namespace ReactQueue\Helper\Zend\Application\Resource;
      use ReactQueue\ReactQueue as React,
          ReactQueue\Helper\Zend\Application\Resource\Exception\NoConfigPathSetException,
          Symfony\Component\Yaml\Yaml,
          Zend_Controller_Action_HelperBroker as HelperBroker,
          Zend_Application_Resource_ResourceAbstract as AbstractResource;

/**
 * @package     ReactQueue
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @copyright   Full license/copyright information can be found in the LICENSE file distributed with this source code.
 */
class ReactQueue extends AbstractResource {

    /**
     * Configuration File Path
     *
     * @var string
     */
    protected $configPath = null;

    /**
     * Provides a configured ReactQueue instance.
     *
     * Configuration drives:
     *  - Events to listen to
     *  - Handler Priority
     *
     * @return  array
     */
    public function init() {
        $this->configPath = $this->prepareOptions();
        return $this->configure();
    }

    /**
     * Configure ReactQueue based on Yaml configuration file.
     *
     * @return  array
     */
    protected function configure() {
        $config = Yaml::load($this->configPath);
        $react  = new React();

        foreach ($config as $selector => $event) {
            $react($selector)->call($event['callback'], $event['priority']);
        }

        return $react;
    }

    /**
     * Prepare (and check) the options for the configuration block
     *
     * @return  string
     */
    protected function prepareOptions() {
        // get configured options
        $options    = $this->getOptions();

        // locate the "config.path" block
        $configPath = isset($options['config']['path'])
                    ? $options['config']['path']
                    : null;
        $configPath = realpath($configPath);

        if (empty($configPath)) { throw new NoConfigPathSetException('No configuration file path has been set.'); }

        return $configPath;
    }

}
