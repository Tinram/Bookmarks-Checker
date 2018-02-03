#!/usr/bin/env php
<?php

/**
    * Bookmarks Checker
    *
    * Verify links in a Chrome or Firefox exported bookmarks file.
    *
    * usage:          php bookmarks_checker.php <file>
    *
    * @author         Martin Latter <copysense.co.uk>
    * @copyright      Martin Latter 11/02/2016
    * @version        0.05
    * @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
    * @link           https://github.com/Tinram/Bookmarks-Checker.git
*/


declare(strict_types = 1); /* remove for PHP < 7 */

define('DUB_EOL', PHP_EOL . PHP_EOL);
define('DEFAULT_FILE', 'bookmarks.html');

ini_set('user_agent', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0');


/* filename */
if ( ! isset($_SERVER['argv'][1]))
{
    if (file_exists(DEFAULT_FILE))
    {
        $sFilename = DEFAULT_FILE;
    }
    else
    {
        $sUsage =
            PHP_EOL . ' ' .
            str_replace('_', ' ', ucwords(basename(__FILE__, '.php'), '_')) .
            DUB_EOL .
            "\tusage: " . basename(__FILE__) . ' [filename]' .
            DUB_EOL;

        die($sUsage);
    }
}
else
{
    $sFilename = $_SERVER['argv'][1];
}

/* no such file */
if ( ! file_exists($sFilename))
{
    die(PHP_EOL . ' ' . $sFilename . ' does not exist in this directory!' . DUB_EOL);
}


$sHtml = file_get_contents($sFilename);

$rxPattern = '/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU'; /* avoid attributes: by chirp.com.au */

preg_match_all($rxPattern, $sHtml, $aMatches, PREG_SET_ORDER);


$aLinks = [];

foreach ($aMatches as $aLinkEntity)
{
    $aLinks[] = [ $aLinkEntity[1], $aLinkEntity[2] ];
}

echo PHP_EOL;


if (empty($aLinks))
{
    die(' No links extracted from ' . $sFilename . DUB_EOL);
}

$fTS = microtime(true);

checkLinks($aLinks);

$fTE = microtime(true);

echo PHP_EOL . ' URL parse time: ' . sprintf('%01.3f', $fTE - $fTS) . ' secs' . DUB_EOL;


function checkLinks(array $aLinks)
{
    $iCount = 0;
    $iFails = 0;

    echo ' ' . count($aLinks) . ' links being checked ...' . DUB_EOL;

    foreach ($aLinks as $aLink)
    {
        $rFile = @fopen($aLink[0], 'r');

        if ($rFile)
        {
            fclose($rFile);
        }
        else
        {
            if ( ! $iFails)
            {
                echo ' failures: ' . DUB_EOL;
            }

            echo "\t" . $aLink[1] . '  |  ' . $aLink[0] . PHP_EOL;
            $iFails++;
        }

        $iCount++;
    }

    echo PHP_EOL . ' ' . $iFails . ' links failed';
    echo PHP_EOL . ' ' . ($iCount - $iFails) . ' links verified' . PHP_EOL;
}
