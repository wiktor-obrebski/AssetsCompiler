<?php

namespace Minifier;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Minifier\Minifier;
use Minifier\Adapter\AdapterInterface;
use Minifier\View\Helper\BundlePath;

/**
 * @author Wiktor Obrębski
 */
class Factory implements FactoryInterface
{
    public function checkClass( $adapter_class )
    {
        if (!class_exists($adapter_class)) {
            throw new \DomainException(sprintf(
                '%s expects the "adapter" attribute to resolve to an existing class; received "%s"',
                __METHOD__,
                $adapter_class
            ));
        }
        return $this;
    }

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $config = $serviceLocator->get('config');
        $options = $config['minifier'];

        $js_adapter = $options['js_adapter'];
        $css_adapter = $options['css_adapter'];

        $js_adapter_class = $js_adapter['class'];
        $css_adapter_class = $css_adapter['class'];

        $this->checkClass( $js_adapter_class )->checkClass( $css_adapter_class );

        $persistent_path = $options['persistent_file'];
        $public_dir = $options['public_dir'];

        $bundles_options = isset( $options['bundles'] ) ? $options['bundles'] : null;

        unset($options['adapter']);
        if( isset( $options['options'] ) ) {
            $options = $options['options'];
        }

        $jsAdapterObj  = new $js_adapter_class( $js_adapter['options'] );
        $cssAdapterObj = null;
        if( $js_adapter == $css_adapter ) {
            $cssAdapterObj = $jsAdapterObj;
        }
        else {
            $cssAdapterObj = new $css_adapter_class( $css_adapter['options'] );
        }
        $minifier = new Minifier( $jsAdapterObj, $cssAdapterObj );
        $minifier->setOptions( $bundles_options )
                 ->setPersistentPath( $persistent_path )
                 ->setPublicDirectory( $public_dir );
        return $minifier;
    }

    public function createJsHelper()
    {
        $helper = new BundlePath();
        $helper->setMode( AdapterInterface::MODE_JS );
        return $helper;
    }

    public function createCssHelper()
    {
        $helper = new BundlePath();
        $helper->setMode( AdapterInterface::MODE_CSS );
        return $helper;
    }
}
