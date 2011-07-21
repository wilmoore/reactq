ReactQ: A minimalist DSL for pub/sub in PHP
===========================================

**Homepage**:       [http://reactq.com](http://reactq.com)  
**Git**:            [http://github.com/wilmoore/reactq](http://github.com/wilmoore/reactq)  
**Author**:         Wil Moore III   
**Contributors**:   See Contributors section below  
**Copyright**:      2011-2011   
**License**:        MIT License     
**Latest Version**: 0.2.0
**Release Date**:   April 18th 2011 


Summary
-------

ReactQ is a minimalist API for managing events, event subscriptions, and event handlers in-process.

ReactQ, when integrated into your application, allows you to declare (trigger) that an event has happened. There is no
requirement for any listeners to be subscribed to a given event, it is completely opt-in. If listeners are subscribed,
they will be handed the event's context (object) and any handlers associated with the listener will be executed.

Multiple listeners can be subscribed to a single event. Event handlers are executed in subscription order by default;
however, this can be controlled by setting explicit numeric priorities when subscribing the listeners. Higher numbers
give a higher priority. Higher priority items are executed ahead of lower priority items. In other words, an event
handler with a priority of 10 will be executed before an event handler with a priority of 5, even if the latter was
subscribed earlier.

The classic publish/subscribe use-case is to trigger an event and have an event handler serve as a logger to log the
details of that event. Suppose you wanted to record a log of every article that has been published through a CMS. In
this case, the context object would be the "article" object itself which might have accessible properties such as:

1. dateTimePublished
2. modifiedBy

The logger would access these properties and record them to a storage backend. This mitigates the need to write logging
code directly to your model and/or service classes, allows the event handling to be tested in isolation, and allows for
flexible and well-defined extension points.

From an extremely academic perspective, at work here is the observer (publish/subscribe) pattern, also known as signals
and slots. See http://en.wikipedia.org/wiki/Signals_and_slots and http://en.wikipedia.org/wiki/Publish/subscribe and
http://en.wikipedia.org/wiki/Event-driven_programming


Features
--------

**1. jQuery-like attribute selectors**: A shortcut to subscribing a single listener to multiple events is to specify
an event selector pattern. In other words, instead of subscribing to a single event represented as a string, you will
subscribe to a set of (one or more) events that match a pattern. These patterns are similar to jQuery attribute
selectors.

**2. Honors priority**: ReactQ, in addition to allowing callbacks to serve as event handlers, it allows a priority
to be associated with the event handler for cases when you need that level of granularity.

**3. Flexibility with minimal ceremony**: ReactQ was built with ease-of-integration in mind; however, it is meant to
provide extreme flexibility. ReactQ can be used for trivial tasks like logging and caching. It can also be used for
non-trivial tasks like data filtering, request/response header manipulation, or access-rights checking. Whatever the
task, the syntax is the same simple-chainable internal DSL.

**4. No Global Registry**: ReactQ is quite opinionated in this regard as it does not provide a global registry. This
should help to mitigate potential hidden dependency issues. You can of course choose to use your own global registry
and pass ReactQ around that way if you feel the need; however, dependency injection is the cleaner, more maintainable,
and easier to debug methodology, but YMMV.

**5. Testable**: ReactQ is fully unit-tested using PHPUnit.

**6. Built on the shoulders of giants**: ReactQ currently wraps the well-tested and mature Zend\EventManager
component (built for Zend Framework 2). FYI, I may factor out Zend\EventManager as it is currently very difficult to track updates given Zend\EventManager does not have it's own repo (you need to checkout the entire project) and there is _currently_ no way to manage specific ZF components without taking the entire project (besides "cp").


Requirements
------------

*   [required] PHP 5.3+
*   [optional] PHPUnit 3.4.15 is required to execute the test suite (phpunit --version)


Installing
----------

**PEAR:**

    Not Yet Supported

**Git submodule:**

    $ git submodule add https://github.com/wilmoore/reactq.git {path/to/your/vendor/directory}/reactq

*replace "{path/to/your/vendor/directory}" the path to your vendor library directory*
 
**Get the entire source tree:**

    $ git clone https://github.com/wilmoore/reactq.git {path/to/your/vendor/directory}/reactq

*replace "{path/to/your/vendor/directory}" the path to your vendor library directory*
    
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

-   **2011-04-21**: Released version 0.2.0. This release added a new Zend Framework Application Resource Plugin
    for ReactQueue configuration.

        -   application.ini
        resources.reactqueue.config.path = CONFIGS_PATH "/reactq/events.yml"

        -   events.yml
        "article.published":
          callback:    mybiz\domain\article\event\Published
          priority:    1
          description: >
            Acknowledges that the context article has been published, copies the history,
            warms the cache, adds it to the search index, then sends an email notification.

        "article.unpublished":
          callback:    mybiz\domain\article\event\Unpublished
          priority:    1
          description: >
            Acknowledges that the context article has been unpublished, removes it from the
            search index, deletes the cache, then sends an email notification.

-   **2011-04-21**: Released version 0.1.1. This release includes only minor documentation updates and cleanup.

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

