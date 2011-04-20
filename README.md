ReactQ: A minimalist DSL for PHP event handling
===============================================

**Homepage**:       [http://reactq.com](http://reactq.com)  
**Git**:            [http://github.com/wmoore/reactq](http://github.com/wmoore/reactq)  
**Author**:         Wil Moore III   
**Contributors**:   See Contributors section below  
**Copyright**:      2011-2011   
**License**:        MIT License 
**Latest Version**: 0.1.0-beta  
**Release Date**:   April 18th 2011 


Summary
-------

ReactQ is an extremely terse little API for managing events and event subscriptions, and event handlers in-process.

This means that when integrated into your application, you can declare that an event has happened (trigger event) and if
any listeners have been subscribed to that type of event, any handlers associated with those listeners will be executed.

The classic use-case is to trigger an event and have loggers setup to log that event. This mitigates the need to write
logging code directly to your model and/or service classes.

Multiple listeners can be subscribed to a single event. Listener event handlers are executed in order of subscription
by default; however, this can be controlled by setting explicit priorities when subscribing the listeners.

A shortcut to subscribing a single listener to multiple events is to specify an event pattern to subscribe to. In other
words, instead of subscribing to a single event represented as a string, you will subscribe to a set of (one or more)
events that match a pattern. These patterns are similar to jQuery attribute selectors.

From an extremely academic perspective, at work here is the observer (publish/subscribe) pattern, also known as signals
and slots. See http://en.wikipedia.org/wiki/Signals_and_slots.


Features
--------

**1. Opinionated**: ReactQ does not deal with static events. This mitigates the potential hidden dependency issues.

**2. Flexibility with minimal ceremony**: ReactQ was built with ease-of-integration in mind; however, it is meant to
provide extreme flexibility. ReactQ can be used for trivial tasks like logging and caching. It can also be used for
non-trivial tasks like data filtering, request/response header manipulation, or access-rights checking. Whatever the
task, the syntax is the same simple-chainable internal DSL.
                                                                             
**3. Honors priority**: ReactQueue, in addition to allowing callbacks to serve as event handlers, it allows a priority
to be associated with the event handler for cases when you need that level of granularity.

**4. Built on the shoulders of giants**: ReactQueue currently wraps the well-tested and mature Zend\EventManager
component (built for Zend Framework 2).

**5. Testable**: ReactQ is fully unit-tested using PHPUnit.


Requirements
------------

*   [required] PHP 5.3+
*   [optional] PHPUnit 3.4.15 is required to execute the test suite (phpunit --version)


Installing
----------

**PEAR:**

    Not Yet Supported

**Git Submodule (use this if your project's source is tracked in a Git submodule):**

    $ git submodule add https://github.com/wilmoore/reactq.git src/lib/vendor/reactq/src/lib/ReactQueue

*replace "src/lib/vendor/" with your own path preference*
 
**Get the entire source repository:**

    $ git clone git://github.com/wilmoore/reactq.git src/lib/vendor/reactq/src/lib/ReactQueue

*replace "src/lib/vendor/" with your own path preference*
    
You will need to add ReactQ and Zend to your application’s autoloader. ReactQ by default, includes the Symfony2 universal class loader. If your application does not already use an autoloader, you may use the Symfony2 autoloader distributed with ReactQ. See "autoloader.php.dist".


Autoloading
-----------

**Via Symfony2 universal class loader**:

    $loader->registerNamespaces(array(
        'ReactQueue' => __DIR__.'/src/lib/vendor/reactq/src/lib',
        'Zend'       => __DIR__.'/src/lib/vendor/zend/library',
    ));


**Via Zend_Loader_Autoloader**:

*Ensure that the ReactQueue and Zend directories are under the include_path.*

    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('ReactQueue');


**Via Zend_Loader_Autoloader (applicaiton.ini)**:

*Ensure that the ReactQueue and Zend directories are under the include_path.*

    autoloadernamespaces.ReactQueue = "ReactQueue"


**Via any other include_path-based autoloader**:

*Ensure that the ReactQueue and Zend directories are under the include_path.*


Running Tests
-------------

**You don't yet have the source checked-out**:

    $ git clone git://github.com/wilmoore/reactq.git projects/reactq
    $ cd $!
    $ phpunit --testdox

**You already have the source checked-out**:

    $ cd projects/reactq
    $ phpunit --testdox

**You already have the source checked-out and you want to see verbose output rather than pretty output**:

    $ cd projects/reactq
    $ phpunit --verbose


Dependencies
------------

3rd party dependencies are located under the directory src/lib/vendor. These libraries are included as 'git submodules'
and can be updated by executing the following command:

    git submodule update --init --recursive

*   Zend\\EventManager
*   Symfony\\Component\\ClassLoader


Changelog
---------

-   **2011-04-20**: Released version 0.1.0-beta. This release provides the ability to define event selector
    patterns similar to jQuery attribute selectors. For instance, you could define a listener for the event
    'article.published'; however, if you for instance, wanted a log listener to be attached to each event
    that started with the text 'article', you'd create an event selector like:

    $react->on('^=article')->call(function(){ // logging code here... });

-   **2011-04-18**: Released version 0.0.2 -- still a proof of concept. Refactored several methods for clarity
    and did some docblock comment tidying.

-   **2011-04-17**: Released version 0.0.1-DEV as a proof of concept. The goal is to start testing against a few
    production applications but with a very limited scope. For instance, request logging using only string-based
    event identifiers.


Contributors
------------

Special thanks to the following people for submitting patches:

* no patches submitted ATM


Copyright
---------

ReactQ &copy; 2011-2011 by [Wil Moore III](mailto:wil.moore@wilmoore.com).  
ReactQ is licensed under the MIT license.  Please see the LICENSE file for more information.    

