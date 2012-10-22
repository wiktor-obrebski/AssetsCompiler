<?php

namespace AssetsCompiler\Minifier\Adapter;

/**
 * Minifier adapter interface
 */
interface JsAdapterInterface
{
    function compileJs( $files_pathes, $output_file );
}