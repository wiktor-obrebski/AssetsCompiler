<?php

namespace AssetsCompiler\Minifier;

/**
 * @author Wiktor Obrębski
 */
class Minifier
{
    const OUTPUT_PATTERN = "%s-%s";

    protected $jsAdapter, $cssAdapter;
    protected $progression;
    protected $options;
    protected $persistentPath;
    protected $bundles = array();
    protected $publicDirectory;

    /**
     * @param Adapter\JsAdapterInterface $jsAdapter
     * @param Adapter\CssAdapterInterface $cssAdapter
     */
    public function __construct( Adapter\JsAdapterInterface $jsAdapter = null,
                                 Adapter\CssAdapterInterface $cssAdapter = null)
    {
        $this->setJsAdapter( $jsAdapter )
             ->setCssAdapter( $cssAdapter );
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
     * Sets the js minify adapter
     *
     * @param  Adapter\JsAdapterInterface $adapter
     * @return Minifier
     */
    public function setJsAdapter( Adapter\JsAdapterInterface $adapter )
    {
        $this->jsAdapter = $adapter;
        return $this;
    }

    /**
     * Sets the css minify adapter
     *
     * @param  Adapter\CssAdapterInterface $adapter
     * @return Minifier
     */
    public function setCssAdapter( Adapter\CssAdapterInterface $adapter )
    {
        $this->cssAdapter = $adapter;
        return $this;
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
     * Sets the progression object to display the progression
     * of bundles compilation of the Minifier.
     *
     * @param  Progression $progression
     * @return Minifier
     */
    public function setProgression(Progression $progression )
    {
        $this->progression = $progression;
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

        $dir = dirname($path);
        if(!is_dir($dir)) mkdir($dir, 0755, true);

        // Create the config object
        $config = new \Zend\Config\Config($data, true);
        $writer = new \Zend\Config\Writer\Xml();
        return $writer->toFile( $path, $config);
    }

    private function initializeFile($path)
    {
        $file = fopen($path, 'w');
        fclose($file);
    }

    protected function compileFiles( $files, $output_path, $mode )
    {
        # only create empty file if we not have input files
        $this->initializeFile($output_path);
        if(count($files) == 0) return true;
        switch ($mode) {
            case 'js':
                return $this->jsAdapter->compileJs($files, $output_path);
            case 'css':
                return $this->cssAdapter->compileCss($files, $output_path);
        }
    }

    public function getFilesList($bundleName, $mode)
    {
        $bundle = $this->options[$mode]['list'][$bundleName];

        $files = is_scalar( $bundle['sources'] ) ? array( $bundle['sources'] ) : $bundle['sources'];

        if(!empty($bundle['directories'])) {
            $dirs = $bundle['directories'];
            $publicDir = $this->getPublicDirectory();
            $realPublicDir = realpath($publicDir);
            $pattern = '/\.' . $mode . '$/';
            foreach($dirs as $dir) {
                $relpath = $publicDir . $dir;
                $path = realpath($relpath);
                $it   = new \RecursiveDirectoryIterator($path);
                $objects = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::SELF_FIRST);

                foreach($objects as $file => $object){
                    if(preg_match($pattern, $file)) {
                        $relfile = substr($file, strlen($realPublicDir));
                        if(!in_array($relfile, $files)) {
                            $files []= $relfile;
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * generate packed bundle file, if md5 of included files has changed.
     * @return array with files md5 and output file relative path
     */
    protected function generateBundle( $name, $bundle, $mode, $config, $force )
    {
        $public_dir = $this->getPublicDirectory();

        $output_dir = $public_dir . DIRECTORY_SEPARATOR . $this->options[$mode]['output_dir'];
        if( !is_dir( $output_dir ) ) mkdir( $output_dir, 0755, true );

        $output_dir = realpath( $output_dir );

        $rel_output_dir = $this->options[$mode]['output_dir'];

        $ext = $mode;

        $name_pattern = isset( $bundle['filename'] ) ? $bundle['filename'] : static::OUTPUT_PATTERN;
        $name_pattern .= '.' . $ext;

        $sources = $this->getFilesList($name, $mode);
        $files = array_map( function( $file ) use( $public_dir ) {
            return realpath( $public_dir . DIRECTORY_SEPARATOR . $file );
        }, $sources );

        $md5 = $this->bundleMd5( $files );

        $output_name = sprintf($name_pattern, $name, $md5);
        $output_path = $output_dir . DIRECTORY_SEPARATOR . $output_name;
        $rel_output_path = $rel_output_dir . DIRECTORY_SEPARATOR . $output_name;

        if( !$force ) {
            $last_md5 = isset($config['md5']) ? $config['md5'] : null;
            //files not change
            if( $md5 == $last_md5 && file_exists( $output_path ) ) return false;
        }

        $result = $this->compileFiles( $files, $output_path, $mode );

        if( $result ) {
            return array( 'md5' => $md5, 'filepath' => $rel_output_path );
        }
        return false;
    }

    /**
     * compile all bundles files defined by configuration - generate output
     * files, by using setted adapter.
     * @param bool $force
     */
    public function compile( $force = false)
    {
        $modes  = [ 'js', 'css' ];
        $pers_config = $this->loadPersistentData();

        foreach( $modes as $mode ) {
            if( empty( $this->options[$mode] ) ) continue;

            $this->progression->displayBundleStart( $mode );

            $bundles_options = $this->options[$mode];

            $count_mode_name_bundle = sizeof($this->options[$mode]['list']);
            $count = 0;

            foreach ($bundles_options['list'] as $name => $bundle) {
                $loc_config = isset( $pers_config[$mode][$name] ) ? $pers_config[$mode][$name] : array();
                $result = $this->generateBundle( $name, $bundle, $mode, $loc_config, $force );

                $percent = $result == false ? 100 : ceil(100*(++$count) / $count_mode_name_bundle);
                $this->progression->displayBundlePercent( $percent );

                if( $result == false ) continue;

                $pers_config[$mode][$name] = $result;
                $pers_config[$mode][$name]['sources'] = array( 'file' => $bundle['sources'] );
            }

            $this->progression->displayBundleEnd();
        }

        $this->storePersistentData( $pers_config );
    }
}