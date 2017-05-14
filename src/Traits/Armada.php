<?php

namespace Jaxon\Sentry\Traits;

use Jaxon\Jaxon;
use Jaxon\Sentry\Classes\Base;
use Jaxon\Utils\Container;
use Jaxon\Utils\Traits\Config;
use Jaxon\Utils\Traits\View;
use Jaxon\Utils\Traits\Session;
use Jaxon\Utils\Traits\Manager;
use Jaxon\Utils\Traits\Event;
use Jaxon\Utils\Traits\Validator;

use stdClass, Exception;

trait Armada
{
    protected $bSetupCalled = false;

    protected $appConfig = null;

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    abstract protected function jaxonSetup();

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    abstract protected function jaxonCheck();

    /**
     * Wrap the Jaxon response into an HTTP response and send it back to the browser.
     *
     * @param  $code        The HTTP Response code
     *
     * @return HTTP Response
     */
    abstract public function httpResponse($code = '200');

    /**
     * Get the Jaxon response.
     *
     * @return Response
     */
    public function ajaxResponse()
    {
        return jaxon()->sentry()->ajaxResponse();
    }

    /**
     * Add a view renderer with an id
     *
     * @param string                $sId                The unique identifier of the view renderer
     * @param Closure               $xClosure           A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer($sId, $xClosure)
    {
        jaxon()->sentry()->addViewRenderer($sId, $xClosure);
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     */
    private function _jaxonSetup()
    {
        if(($this->bSetupCalled))
        {
            return;
        }

        $jaxon = jaxon();
        $sentry = $jaxon->sentry();
        // Use the Composer autoloader. It's important to call this before triggers and callbacks.
        $jaxon->useComposerAutoloader();

        // Set this object as the Armada in the DI container.
        // Now it will be returned by a call to jaxon()->module().
        if(get_class($this) != 'Jaxon\\Armada\\Armada')
        {
            Container::getInstance()->setArmada($this);
        }

        // Event before setting up the module
        $sentry->triggerEvent('pre.setup');

        // Setup the Sentry library
        $sentry->setup();

        // Set the module/package/bundle specific specific options
        $this->jaxonSetup();

        // Event before the module has set the config
        $sentry->triggerEvent('pre.config');

        // Jaxon application settings
        $sentry->setClassNamespaces($this->appConfig);

        // Save the view namespaces
        $sentry->setViewNamespaces($this->appConfig);

        // Event after the module has read the config
        $sentry->triggerEvent('post.config');

        // Event before checking the module
        $sentry->triggerEvent('pre.check');

        $this->jaxonCheck();

        // Event after checking the module
        $sentry->triggerEvent('post.check');

        // Event after setting up the module
        $sentry->triggerEvent('post.setup');

        $this->bSetupCalled = true;
    }

    /**
     * Get the session object
     *
     * @return object
     */
    public function session()
    {
        $this->_jaxonSetup();
        return jaxon()->sentry()->getSessionManager();
    }

    /**
     * Register the Jaxon classes.
     *
     * @return void
     */
    public function register()
    {
        $this->_jaxonSetup();
        jaxon()->registerClasses();
    }

    /**
     * Get a Jaxon request to a given class.
     *
     * @param  string  $classname the class name
     * 
     * @return object  The request to the class
     */
    public function request($classname)
    {
        return jaxon()->sentry()->request($classname);
    }

    /**
     * Get a plugin instance.
     *
     * @param  string  $name the plugin name
     * 
     * @return object  The plugin instance
     */
    public function plugin($name)
    {
        return jaxon()->plugin($name);
    }

    /**
     * Register a specified Jaxon class.
     *
     * @param string            $sClassName             The name of the class to be registered
     * @param array             $aOptions               The options to register the class with
     *
     * @return void
     */
    public function registerClass($sClassName, array $aOptions = array())
    {
        $this->_jaxonSetup();
        jaxon()->registerClass($sClassName, $aOptions);
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function script($bIncludeJs = false, $bIncludeCss = false)
    {
        $this->_jaxonSetup();
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js()
    {
        $this->_jaxonSetup();
        return jaxon()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css()
    {
        $this->_jaxonSetup();
        return jaxon()->getCss();
    }

    /**
     * Set the init callback, used to initialise controllers.
     *
     * @param  callable         $callable               The callback function
     * @return void
     */
    public function onInit($callable)
    {
        jaxon()->sentry()->setInitCallback($callable);
    }

    /**
     * Set the pre-request processing callback.
     *
     * @param  callable         $callable               The callback function
     * @return void
     */
    public function onBefore($callable)
    {
        jaxon()->sentry()->setBeforeCallback($callable);
    }

    /**
     * Set the post-request processing callback.
     *
     * @param  callable         $callable               The callback function
     * 
     * @return void
     */
    public function onAfter($callable)
    {
        jaxon()->sentry()->setAfterCallback($callable);
    }

    /**
     * Set the processing error callback.
     *
     * @param  callable         $callable               The callback function
     * 
     * @return void
     */
    public function onInvalid($callable)
    {
        jaxon()->sentry()->setInvalidCallback($callable);
    }

    /**
     * Set the processing exception callback.
     *
     * @param  callable         $callable               The callback function
     * 
     * @return void
     */
    public function onError($callable)
    {
        jaxon()->sentry()->setErrorCallback($callable);
    }

    /**
     * Check if the current request is a Jaxon request.
     *
     * @return boolean  True if the request is Jaxon, false otherwise.
     */
    public function canProcessRequest()
    {
        $this->_jaxonSetup();
        return jaxon()->canProcessRequest();
    }

    /**
     * Process the current Jaxon request.
     *
     * @return void
     */
    public function processRequest()
    {
        $this->_jaxonSetup();
        // Process Jaxon Request
        jaxon()->sentry()->processRequest();
    }
}
