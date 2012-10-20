<?php

namespace Minifier;

/**
 * @author Wiktor Obrębski
 */
class Minifier
{
    const OUTPUT_PATTERN = "%s-%s";

    protected $adapter;
    protected $options;
    protected $persistentPath;
    protected $bundles = array();
    protected $publicDirectory;

    /**
     * @param Adapter\AdapterInterface $adapter
     */
    public function __construct( Adapter\AdapterInterface $adapter )
    {
        $this->setAdapter( $adapter );
    }

    /**
     * returning public directory path
     *
     * @return string
     */
    public function getPublicDirectory() {
        return $this->publicDirectory;
    }

    /**
     * setting public directory path
     *
     * @param String $newpublicDirectory
     */
    public function setPublicDirectory($publicDirectory) {
        $this->publicDirectory = $publicDirectory;

        return $this;
    }

    /**
     * Sets the minify adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Minifier
     */
    public function setAdapter( Adapter\AdapterInterface $adapter )
    {
        $this->adapter = $adapter;
        return $this;
    }


    /**
     * Returns the minify adapter
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @return Adapter\AdapterInterface|null
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * getting path of file where persistent data will be stored
     *
     * @return string
     */
    public function getPersistentPath()
    {
        return $this->persistentPath;
    }

    /**
     * setting path for file where persistent data will be stored
     *
     * @param String $newpersistentPath
     */
    public function setPersistentPath($persistentPath)
    {
        $this->persistentPath = $persistentPath;
        return $this;
    }

    /**
     * Sets the array of bundles options to be used by
     * this Minifier.
     *
     * @param  array $options The array of bundles
     * @return Minifier
     */
    public function setOptions( $options )
    {
        $this->options = $options;
        return $this;
    }

    /**
     * matching md5 from files path array (using files content)
     *
     * @param $files array of absolute files path
     */
    protected function bundleMd5( $files )
    {
        $sum = '';
        foreach ($files as $file_path) {
            if( $file_path === false ) continue;
            $sum .= md5_file( $file_path );
        }
        return md5( $sum );
    }

    /**
     * loading persistant data from xml file, setted by setPersistentPath method
     */
    protected function loadPersistentData()
    {
        $path = $this->persistentPath;

        if( file_exists( $path ) ) {
            $reader = new \Zend\Config\Reader\Xml();
            return $reader->fromFile($path);
        }

        return array();
    }

    /**
     * storing persistant data to xml file, setted by setPersistentPath method
     */
    protected function storePersistentData( $data )
    {
        $path = $this->persistentPath;

        // Create the config object
        $config = new \Zend\Config\Config($data, true);
        $writer = new \Zend\Config\Writer\Xml();
        return $writer->toFile( $path, $config);
    }

    /**
     * generate packed bundle file, if md5 of included files has changed.
     * @return array with files md5 and output file relative path
     */
    protected function generateBundle( $name, $bundle, $mode, $config )
    {
        $public_dir = $this->getPublicDirectory();

        $output_dir = $public_dir . DIRECTORY_SEPARATOR . $this->options[$mode]['output_dir'];
        $output_dir = realpath( $output_dir );

        $rel_output_dir = $this->options[$mode]['output_dir'];

        $ext = $mode;

        $name_pattern = isset( $bundle['filename'] ) ? $bundle['filename'] : static::OUTPUT_PATTERN;
        $name_pattern .= '.' . $ext;

        $sources = is_scalar( $bundle['sources'] ) ? array( $bundle['sources'] ) : $bundle['sources'];
        $files = array_map( function( $file ) use( $public_dir ) {
            return realpath( $public_dir . DIRECTORY_SEPARATOR . $file );
        }, $sources );

        $md5 = $this->bundleMd5( $files );

        $output_name = sprintf($name_pattern, $name, $md5);
        $output_path = $output_dir . DIRECTORY_SEPARATOR . $output_name;
        $rel_output_path = $rel_output_dir . DIRECTORY_SEPARATOR . $output_name;


        $last_md5 = isset($config['md5']) ? $config['md5'] : null;
        //files not change
        if( $md5 == $last_md5 && file_exists( $output_path ) ) return false;

        $result = $this->getAdapter()->compile( $files, $output_path, $mode );

        if( $result ) {
            return array( 'md5' => $md5, 'filepath' => $rel_output_path );
        }
        return false;
    }

    /**
     * compile all bundles files defined by configuration - generate output
     * files, by using setted adapter.
     */
    public function compile()
    {
        $modes  = [ Adapter\AdapterInterface::MODE_JS, Adapter\AdapterInterface::MODE_CSS ];
        $pers_config = $this->loadPersistentData();

        foreach( $modes as $mode ) {
            if( empty( $this->options[$mode] ) ) continue;

            $bundles_options = $this->options[$mode];

            #$bundles    = $this->resolveToFiles( $bundles_options );
            $output_dir = $this->getPublicDirectory();
            if( !is_dir( $output_dir ) ) mkdir( $output_dir );

            foreach ($bundles_options['list'] as $name => $bundle) {
                $loc_config = isset( $pers_config[$mode][$name] ) ? $pers_config[$mode][$name] : array();

                $result = $this->generateBundle( $name, $bundle, $mode, $loc_config );
                if( $result == false ) continue;
                $pers_config[$mode][$name] = $result;
                $pers_config[$mode][$name]['sources'] = array( 'file' => $bundle['sources'] );
            }
        }
        $this->storePersistentData( $pers_config );
    }
}