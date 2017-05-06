<?php

/**
 * Namespaces.php - A trait for managing namespaces in view/template renderers.
 *
 * @package jaxon-sentry
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-sentry
 */

namespace Jaxon\Sentry\View;

trait Namespaces
{
    /**
     * The template directories
     *
     * @var array
     */
    protected $aDirectories = array();

    /**
     * The directory of the current template
     *
     * @var string
     */
    protected $sDirectory = array();

    /**
     * The extension of the current template
     *
     * @var string
     */
    protected $sExtension = array();

    /**
     * Add a namespace to this template renderer
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace($sNamespace, $sDirectory, $sExtension = '')
    {
        $this->aDirectories[$sNamespace] = array('path' => $sDirectory, 'ext' => $sExtension);
    }

    /**
     * Find the namespace of the template being rendered
     *
     * @param string        $sNamespace         The namespace name
     *
     * @return void
     */
    public function setCurrentNamespace($sNamespace)
    {
        $this->sDirectory = '';
        $this->sExtension = '';
        if(key_exists($sNamespace, $this->aDirectories))
        {
            $this->sDirectory = rtrim($this->aDirectories[$sNamespace]['path'], '/') . '/';
            $this->sExtension = '.' . ltrim($this->aDirectories[$sNamespace]['ext'], '.');
        }
    }
}
