<?php

/**
 * Twig Extension for the Slim Framework
 *
 * @author      Bernard van Niekerk <frizzy@paperjaw.com>
 * @copyright   2013 Bernard van Niekerk
 * @link        https://github.com/frizzy/SlimTwigExtension
 * @license     http://paperjaw.com/license
 * @version     0.1.0
 * @package     SlimTwigExtension
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Frizzy\Slim\Twig;

use Slim\Slim;
use Twig_Extension;
use Twig_SimpleFunction;
use InvalidArgumentException;

/**
 * Extension
 */
class Extension extends Twig_Extension
{
    /**
     * Get name
     *
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'slim';
    }
    
    /**
     * Get functions
     *
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('render_route_name', array($this, 'renderRoute')),
            new Twig_SimpleFunction('render_route_path', array($this, 'renderRoutePath')),
            new Twig_SimpleFunction('render_template', array($this, 'renderTemplate')),
            new Twig_SimpleFunction('path', array($this, 'getPath')),
            new Twig_SimpleFunction('url', array($this, 'getUrl'))
        );
    }
    
    /**
     * Render route
     *
     * @param string $name       Route name
     * @param array  $parameters Route parameters
     * @param string $appName    Application name
     *
     * @return string Route output
     */
    public function renderRoute($name, array $parameters = array(), $appName = 'default')
    {
        if (! Slim::getInstance($appName)->router->hasNamedRoute($name)) {
            throw new InvalidArgumentException(sprintf('No named route "%s"', $name));
        }
        $route = Slim::getInstance($appName)->router->getNamedRoute($name);
        $route->setParams($parameters);
        ob_start();
        $route->dispatch();

        return ob_get_clean();
    }

    /**
     * Render route path
     *
     * @param string $path    Path
     * @param string $method  HTTP method
     * @param string $appName Application name
     *
     * @return string Route output
     */
    public function renderRoutePath($path, $method = 'GET', $appName = 'default')
    {
        $routes = Slim::getInstance($appName)->router->getMatchedRoutes($method, $path, true);
        foreach ($routes as $route) {
            ob_start();
            $route->dispatch();

            return ob_get_clean();    
        }
        throw new InvalidArgumentException(sprintf(
            'No route matching path "%s" with method "%s"',
            $path,
            $method
        ));
    }
    
    /**
     * Render template
     *
     * @param string $template Template
     * @param array  $data     View data
     * @param string $appName  Application name
     *
     * @return string Template output
     */
    public function renderTemplate($template, array $data = array(), $appName = 'default')
    {
        $view = Slim::getInstance($appName)->view;
        foreach ($data as $name => $value) {
            $view->set($name, $value);
        }
        return $view->render($template);
    }
    
    /**
     * Get path
     *
     * @param string $name       Route name
     * @param array  $parameters Route parameters
     * @param string $appName    Application name
     *
     * @return string Route path
     */
    public function getPath($name, $parameters = array(), $appName = 'default')
    {
        return Slim::getInstance($appName)->urlFor($name, $parameters);
    }

    /**
     * Get URL
     *
     * @param array  $options Options
     * @param string $appName Application name
     *
     * @return string Base URL
     */
    public function getUrl(array $options = array(), $appName = 'default')
    {
        $request = Slim::getInstance($appName)->request;
        if (isset($options['scheme']) || isset($options['port'])) {
            $scheme = isset($options['scheme']) ? $options['scheme'] : $request->getScheme();
            $port   = isset($options['port']) ? ':' . $options['port'] : '';
            $url    = sprintf('%s://%s%s', $scheme, $request->getHost(), $port);
        } else {
            $url = $request->getUrl();
        }
        if (isset($options['script_name']) && $options['script_name']) {
            $url .= $request->getScriptName();
        }
        if (isset($options['path'])) {
            $url .= '/' . ltrim($options['path'], '/');
        }
        
        return $url;
    }
}