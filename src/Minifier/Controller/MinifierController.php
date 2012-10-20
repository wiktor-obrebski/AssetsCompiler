<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Minifier\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;

class MinifierController extends AbstractActionController
{
    protected $verbose;

    public function minifyAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $this->verbose = $request->getParam('verbose') || $request->getParam('v');

        $this->log('Initializing..');

        $minifier = $this->getServiceLocator()->get('minifier');
        $minifier->compile();

        return array();
    }

    protected function log($text, $critical = false)
    {
        if( $this->verbose ) print( $text . PHP_EOL );
        return $this;
    }
}
