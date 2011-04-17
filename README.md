ReactQ: A Reactive Event Queue for PHP
======================================

**Homepage**:       [http://reactq.com](http://reactq.com)   
**Git**:            [http://github.com/wmoore/reactq](http://github.com/wmoore/reactq)   
**Author**:         Wil Moore III
**Contributors**:   See Contributors section below    
**Copyright**:      2011-2011    
**License**:        MIT License    
**Latest Version**: 0.0.1-DEV
**Release Date**:   April 20th 2011    

Summary
-------

ReactQ is an extremely terse little API for managing events and event
subscriptions, and event handlers in-process.

This means that when integrated into your application, you can declare
that an event has happened (trigger event) and if any listeners have been
subscribed to that type of event, any handlers associated with those listeners
will be executed.

The classic use-case is to trigger an event and have loggers setup to log
that event. This mitigates the need to write logging code directly to your
model and/or service classes.

Multiple listeners can be subscribed to a single event. Listener event handlers
are executed in order of subscription by default; however, this can be controlled
by setting explicit priorities when subscribing the listeners.

A shortcut to subscribing a single listener to multiple events is to specify an
event pattern to subscribe to. In other words, instead of subscribing to a single
event represented as a string, you will subscribe to a set of (one or more) events
that match a pattern. These patterns are similar to jQuery attribute selectors.

Features
--------

**1. Provides an elegant plug-in API with minimal ceremony**: ReactQ was built
with ease-of-integration in mind; however, it is also meant to provide extreme
flexibility. It can be used for trivial tasks such as logging and caching.
It can also be used for non-trivial tasks such as application filtering for
access-rights checking, data filtering, or data validation.
                                                                             
**2. ReactQueue is quite opinionated**: ReactQueue does not deal with static
events. This mitigates the potential issues surrounding hidden dependencies.

**3. Honors Priority**: ReactQueue allows callbacks to be added with a specific
priority so functions can be called in a preferred order.


Installing
----------

To install ReactQ via PEAR:

    Not Yet Supported

To install ReactQ via Github:

    $ git clone git://github.com/wilmoore/reactq.git src/lib/vendor/reactq/src/lib/ReactQueue
    

Autoloading
-----------

Via Symfony Classloader:

$loader->registerNamespaces(array(
    'ReactQueue' => __DIR__.'/src/lib/vendor/reactq/src/lib',
    'Zend'       => __DIR__.'/src/lib/vendor/zend/library',
));


Via Zend_Loader_Autoloader:

* Ensure that the ReactQueue and Zend directories are under the include_path.
* $autoloader = Zend_Loader_Autoloader::getInstance();
* $autoloader->registerNamespace('ReactQueue');


Via Zend_Loader_Autoloader (applicaiton.ini):

* Ensure that the ReactQueue and Zend directories are under the include_path.
* autoloadernamespaces.ReactQueue = "ReactQueue"


Via any other include_path-based autoloader:

* Ensure that the ReactQueue and Zend directories are under the include_path.


Running Tests
-------------

You don't yet have the source checked-out:

    $ git clone git://github.com/wilmoore/reactq.git projects/reactq
    $ cd $!
    $ phpunit --testdox

You already have the source checked-out:

    $ cd projects/reactq
    $ phpunit --testdox

You already have the source checked-out and you want to see verbose output rather than pretty output:

    $ cd projects/reactq
    $ phpunit --verbose


Dependencies
------------

3rd party dependencies are located under the directory src/lib/vendor.
These libraries are included as 'git submodules' and can be updated
via executing the following command:

git submodule update --init --recursive

1 - Zend\EventManager
2 - Symfony\Component\ClassLoader


Changelog
---------

-   **2011-04-20**: Released version 0.0.1 as a proof of concept. The goal is to start
    testing against a few production applications but with a very limited scope. For
    instance, request logging using only string-based event identifiers.


Contributors
------------

Special thanks to the following people for submitting patches:

* no patches submitted ATM


Copyright
---------

ReactQ &copy; 2011-2011 by [Wil Moore III](mailto:wil.moore@wilmoore.com).
ReactQ is licensed under the MIT license.
Please see the {file:LICENSE} and {file:LEGAL} documents for more information.

