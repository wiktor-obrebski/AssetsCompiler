<?php

namespace Minifier\Adapter;

/**
 * Minifier adapter interface
 */
interface AdapterInterface
{
    const MODE_CSS = 'css';
    const MODE_JS  = 'js';

    function compile( $files_pathes, $output_file, $mode );
}