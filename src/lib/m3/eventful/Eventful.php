<?php

/**
 * @namespace
 */
namespace m3\eventful;
      use InvalidArgumentException;

class Eventful {

    /**
     * @access  private
     * @var     callback[]
     */
    private $listeners;

    public function __construct() {}

    /**
     * @access  public
     * @param   string      $eventName
     * @param   callback    $callable
     * @param   integer     $priority
     *
     * @return  void
     */
    public function listen($eventName, $callable, $priority = 1000) {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('$callable is not a valid callback.');
        }
    }

    public function listeners() {}

}
