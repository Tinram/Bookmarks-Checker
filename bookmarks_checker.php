#!/usr/bin/env php
<?php

/**
    * Bookmarks Checker
    *
    * Verify links in a Chrome or Firefox exported bookmarks file using cURL multi.
    *
    * Usage:          php bookmarks_checker.php [file]
    *
    * @author         Martin Latter
    * @copyright      Martin Latter 15/01/2019
    * @version        0.10
    * @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
    * @link           https://github.com/Tinram/Bookmarks-Checker.git
*/


declare(strict_types=1);

require('classes/url_checker.class.php');

use Tinram\URLChecker2\URLChecker2;

define('DUB_EOL', PHP_EOL . PHP_EOL);
define('DEFAULT_FILE', 'bookmarks.html');
define('LOG_FILE', 'bookmarks_checker.log');
define('BATCH_SIZE', 200); /* size of each cURL request batch */


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
    $aLinks[] = [ 'url' => $aLinkEntity[1], 'name' => $aLinkEntity[2] ];
}

if (count($aLinks) === 0)
{
    die(' No links extracted from ' . $sFilename . DUB_EOL);
}

echo PHP_EOL . ' ' . count($aLinks) . ' links being checked ...' . DUB_EOL;

$oChecker = new URLChecker2($aLinks);

echo PHP_EOL . ' ' . $oChecker->getURLFails() . ' links failed';
echo PHP_EOL . ' ' . ($oChecker->getURLTotal() - $oChecker->getURLFails()) . ' links verified' . DUB_EOL;
