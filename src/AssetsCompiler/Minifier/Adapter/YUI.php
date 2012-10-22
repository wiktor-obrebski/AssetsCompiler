<?php

namespace AssetsCompiler\Minifier\Adapter;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class YUI implements JsAdapterInterface, CssAdapterInterface
{
    protected $exec;

    protected $js_flags;
    protected $css_flags;

    public function __construct( $options = null )
    {
        $this->exec = $options['exec'];

        $this->js_flags = $options['js'];
        $this->css_flags = $options['css'];
    }

    /**
     * simple contact files with given pathes (YUI only compress single file)
     */
    private function concatFiles($files)
    {
        $tmp_file_path = tempnam( sys_get_temp_dir(), '_' );

        $tmp_file = fopen( $tmp_file_path, 'a' );
        foreach( $files as $file ) {
            $contenct = file_get_contents( $file );
            fwrite( $tmp_file, $contenct );
        }
        fclose( $tmp_file );

        return $tmp_file_path;
    }

    private function compile( $files_pathes, $output_file, $type, $flags )
    {
        $concatPath = $this->concatFiles( $files_pathes );
        $cmd = 'java -jar ' . $this->exec;
        foreach ($flags as $key => $value) {
            $cmd .= ' ' . $key . ' ' . $value;
        }
        $cmd .= ' ' . $concatPath;
        $cmd .= sprintf( ' --type %s -o %s', $type, $output_file );

        exec( $cmd );
        return true;
    }

    public function compileJs( $files_pathes, $output_file )
    {
        return $this->compile( $files_pathes, $output_file, 'js', $this->js_flags );
    }

    public function compileCss( $files_pathes, $output_file )
    {
        return $this->compile( $files_pathes, $output_file, 'css', $this->css_flags );
    }
}