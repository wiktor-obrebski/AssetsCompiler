<?php

namespace Minifier\Adapter;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class Closure implements JsAdapterInterface
{
    protected $compiler_path;
    protected $flags;

    public function __construct( $options = null )
    {
        $this->compiler_path = $options['jar'];
        $this->flags = $options['flags'];
    }

    public function compile( $files_pathes, $output_file, $mode )
    {
        switch ( $mode ) {
            case AdapterInterface::MODE_CSS:
                throw new \DomainException('NOT WORKING');
            case AdapterInterface::MODE_JS:
                $cmds = array_map( function( $file ){
                    return sprintf('--js %s', $file );
                }, $files_pathes );
                $cmd = sprintf('java -jar %s %s', $this->compiler_path,
                         implode( ' ', $cmds ) );
                foreach ($this->flags as $key => $value_pack) {
                    if( is_scalar( $value_pack ) ) $value_pack = array( $value_pack );
                    foreach ($value_pack as $value) {
                        $cmd .= ' ' . $key . ' ' . $value;
                    }
                }
                $cmd .= ' > ' . $output_file;
                exec( $cmd );
                return true;
        }
        return false;
    }
}