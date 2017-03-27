<?php

namespace AssetsCompiler\Minifier;

/**
 * @author Bastien Moinet
 */
class Progression
{
    protected $progressionMode;

    public function __construct($progressionMode)
    {
        $this->progressionMode = $progressionMode;
    }

    /**
     * display the started mode name (js or css)
     * @param $bundleMode string mode name to output
     */
    public function displayBundleStart($bundleMode)
    {
        if (false === $this->progressionMode) {
            return;
        }

        // output mode name with 5 ending spaces
        echo str_pad($bundleMode . ':', 5, ' ', STR_PAD_RIGHT) . '  0 %';
    }

    /**
     * display the percentage of bundles compilation of a mode
     * @param string $bundlePercent percentage to output
     */
    public function displayBundlePercent($bundlePercent)
    {
        if (false === $this->progressionMode) {
            return;
        }

        // move 5 characters backward
        echo "\033[5D";

        // output is always 5 characters long
        echo str_pad($bundlePercent, 3, ' ', STR_PAD_LEFT) . ' %';
    }

    /**
     * display the ending mode line, with a final EOL
     */
    public function displayBundleEnd()
    {
        if (false === $this->progressionMode) {
            return;
        }

        // output only a new line
        echo "\n";
    }
}