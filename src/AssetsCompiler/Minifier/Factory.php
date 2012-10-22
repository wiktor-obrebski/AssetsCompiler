<?php

namespace AssetsCompiler\Minifier;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use AssetsCompiler\Minifier\Minifier;
use AssetsCompiler\Minifier\View\Helper\BundlePath;

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

    private function resolveAdapterOptions( $options )
    {
        //check that classes exist, make shorter configuration possible
        foreach( array( 'js_adapter', 'css_adapter' ) as $key ) {
             $options[$key] = is_string( $options[$key] ) ?
                array( 'class' => $options[$key] ) : $options[$key];

            $this->checkClass( $options[$key]['class'] );

            if( !isset( $options[$key]['options']['exec'] ) ) {
                $options[$key]['options']['exec'] = '';
            }
            if( !isset( $options[$key]['options']['flags'] ) ) {
                $options[$key]['options']['flags'] = array();
            }
        }

        return $options;
    }

    /**
     * are adapters configuration equal - when they 'class' are equal,
     * and their 'exec' are equal (or only one is set)
     */
    private function adaptersEqual( $adap1, $adap2 )
    {
        return ( $adap1['class'] === $adap2['class'] ) && (
            ( empty( $adap1['options']['exec'] ) ^ empty( $adap2['options']['exec'] ) ) ||
            ( $adap1['options']['exec'] === $adap2['options']['exec'] )
        );
    }

    /**
     * preparing adapters objects and return them as array ( js_adpater, css_adapter )
     */
    private function createAdapters( $options )
    {
        $options = $this->resolveAdapterOptions( $options );
        $js_adapter_class = $options['js_adapter']['class'];
        $css_adapter_class = $options['css_adapter']['class'];

        if( $this->adaptersEqual( $options['js_adapter'], $options['css_adapter'] ) ) {
            $exec = empty( $options['js_adapter']['options']['exec'] ) ?
                    $options['css_adapter']['options']['exec'] :
                    $options['js_adapter']['options']['exec'];

            $js_adapter = new $js_adapter_class( array(
                'exec' => $exec,
                'js'   => $options['js_adapter']['options']['flags'],
                'css'  => $options['css_adapter']['options']['flags'],
            ));
            return array( $js_adapter, $js_adapter );
        }
        else {
           $js_adapter = new $js_adapter_class( array(
                'exec' => $options['js_adapter']['options']['exec'],
                'js'   => $options['js_adapter']['options']['flags'],
                'css'  => array(),
            ));
           $css_adapter = new $css_adapter_class( array(
                'exec' => $options['css_adapter']['options']['exec'],
                'css'  => $options['css_adapter']['options']['flags'],
                'js'   => array(),
            ));
            return array( $js_adapter, $css_adapter );
        }
    }

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $config = $serviceLocator->get('config');
        $options = $config['minifier'];

        $persistent_path = $options['persistent_file'];
        $public_dir = $options['public_dir'];
        list( $jsAdapter, $cssAdapter ) = $this->createAdapters( $options );
        $bundles_options = isset( $options['bundles'] ) ? $options['bundles'] : null;

        $minifier = new Minifier( $jsAdapter, $cssAdapter );
        $minifier->setOptions( $bundles_options )
                 ->setPersistentPath( $persistent_path )
                 ->setPublicDirectory( $public_dir );
        return $minifier;
    }

    public function createJsHelper()
    {
        $helper = new BundlePath();
        $helper->setMode( 'js' );
        return $helper;
    }

    public function createCssHelper()
    {
        $helper = new BundlePath();
        $helper->setMode( 'css' );
        return $helper;
    }
}
