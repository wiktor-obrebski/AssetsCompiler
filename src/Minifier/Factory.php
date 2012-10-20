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
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $config = $serviceLocator->get('config');
        $options = $config['minifier'];

        $adapter_class = $options['adapter'];

        if (!class_exists($adapter_class)) {
            throw new \DomainException(sprintf(
                '%s expects the "adapter" attribute to resolve to an existing class; received "%s"',
                __METHOD__,
                $adapter_class
            ));
        }

        $persistent_path = $options['persistent_file'];
        $public_dir = $options['public_dir'];

        $bundles_options = isset( $options['bundles'] ) ? $options['bundles'] : null;

        unset($options['adapter']);
        if( isset( $options['options'] ) ) {
            $options = $options['options'];
        }

        $adapter = new $adapter_class( $options );
        $minifier = new Minifier( $adapter );
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
