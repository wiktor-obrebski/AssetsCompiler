<?php

namespace Minifier\View\Helper;

use Minifier\Adapter\AdapterInterface;


/**
 * Helper for getting bundle scripts path
 *
 * @author    Wiktor Obrębski
 */
class BundlePath extends \Zend\View\Helper\AbstractHelper
{
    protected $mode;
    protected $persistentPath;
    protected $persistentData;
    protected $developmentMode = null;

    private function persistentData()
    {
        if($this->persistentData == null) {
            $path = $this->getPersistentPath();

            if( file_exists($path) ) {
                $reader = new \Zend\Config\Reader\Xml();
                $this->persistentData = $reader->fromFile($path);
            }
        }

        return $this->persistentData;
    }

    /**
     * getting development mode flag - if it isn't setted to now,
     * trying get it from 'minifier'->'development_mode' configuration
     *
     * @return boolean
     */
    public function getDevelopmentMode() {
        if( $this->developmentMode == null ) {
            $sl = $this->getView()->getHelperPluginManager()->getServiceLocator();

            $config = $sl->get('config');

            $this->developmentMode = isset( $config['minifier']['development_mode'] )
                ? $config['minifier']['development_mode'] : null;
        }
        return $this->developmentMode;
    }

    /**
     * setting development mode flag.
     *
     * @param string $newdevelopmentMode
     */
    public function setDevelopmentMode($developmentMode) {
        $this->developmentMode = $developmentMode;

        return $this;
    }


    /**
     * xml file path where persistent data are stored
     *
     * @return string
     */
    public function getPersistentPath() {
        if( $this->persistentPath == null ) {
            $sl = $this->getView()->getHelperPluginManager()->getServiceLocator();

            $config = $sl->get('config');
            $this->persistentPath = $config['minifier']['persistent_file'];
        }
        return $this->persistentPath;
    }

    /**
     * setting xml file path where persistent data are stored
     *
     * @param string $newpersistentPath
     */
    public function setPersistentPath($persistentPath) {
        $this->persistentPath = $persistentPath;

        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function pathes( $bundle_name )
    {
        $sl = $this->getView()->getHelperPluginManager()->getServiceLocator();

        $data = $this->persistentData();
        $mode = $this->mode;

        if( !isset( $data[$mode][$bundle_name] ) ) {
            throw new \DomainException( sprintf(
                '%s: Bundle "%s" not exists.',
                __METHOD__,
                $bundle_name
            ));
        }

        $base_path = $this->getView()->basePath();
        if( $this->getDevelopmentMode() ) {
            $sources = $data[$mode][$bundle_name]['sources']['file'];
            $sources = is_scalar($sources) ? array( $sources ) : $sources;
            return array_map( function( $file ) use( $base_path ) {
                return $base_path . $file;
            }, $sources );
        }
        else {
            $file = $data[$mode][$bundle_name]['filepath'];
            return array( $base_path . $file );
        }
    }


    public function __invoke( $bundle_name = null, $action = 'prepend' )
    {
        if( $bundle_name == null ) return $this;

        $files = $this->pathes( $bundle_name );

        foreach( $files as $file ) {
            switch( $this->getMode() ) {
                case AdapterInterface::MODE_JS:
                    $this->getView()->headScript()->{$action . 'File'}( $file );
                    break;
                case AdapterInterface::MODE_CSS:
                    $this->getView()->headLink()->{$action . 'Stylesheet'}( $file );
                    break;
            }

        }

        return $this;
    }
}