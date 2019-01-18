<?php

declare(strict_types=1);

namespace Tinram\URLChecker2;

final class URLChecker2
{
    /**
        * URLChecker2
        *
        * URL Tester using cURL multi for concurrency.
        *
        * Coded to PHP 7.0
        *
        * @author         Martin Latter
        * @copyright      Martin Latter, 15/01/2019
        * @version        0.02
        * @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link           https://github.com/Tinram/Bookmarks-Checker.git
    */


    /* @var string, user agent */
    private $sUserAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0';

    /* @var array, cURL options holder */
    private $aOpts = [];

    /* @var array, URLs holder */
    private $aURLs = [];

    /* @var string, default logfile name */
    private $sLogfile = 'url_checker.log';

    /* @var int, default cURL request batch size */
    private $iBatchSize = 100;

    /* @var int, total URLs queried */
    private $iURLTotal = 0;

    /* @var int, count of URL failed responses */
    private $iURLFails = 0;


    public function __construct(array $aURLs = null)
    {
        if (is_null($aURLs))
        {
            die(__METHOD__ . '() requires an array of URLs to be passed.' . PHP_EOL);
        }

        $this->sLogfile = (defined('LOG_FILE')) ? LOG_FILE : $this->sLogfile;
        $this->iBatchSize = (defined('BATCH_SIZE')) ? BATCH_SIZE : $this->iBatchSize;

        $this->aURLs = $aURLs;
        $this->setup();
        $aBatches = $this->createBatches();
        $this->logWrite('start', true);
        $sMessage = $this->runner($aBatches);
        $this->outputMessage($sMessage);
    }


    /**
        * Set-up cURL options
        *
        * @return  void
    */

    private function setup()
    {
        $this->aOpts =
        [
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_NOBODY => true,
            CURLOPT_USERAGENT => $this->sUserAgent,
            CURLOPT_FAILONERROR => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ];
    }


    /**
        * Split array of URLs into chunks/batches for cURL processing.
        *
        * @return  array
    */

    private function createBatches(): array
    {
        return array_chunk($this->aURLs, $this->iBatchSize);
    }


    /**
        * Process URL arrays by cURL multi, deliberately enforcing a slight delay per batch.
        *
        * @return  string, time details
    */

    private function runner($aBatches): string
    {
        $fTS = microtime(true);
        $sOutput = '';

        # enforce delay on cURL multi to reduce server overload/dropped connections
        foreach ($aBatches as $aBatch)
        {
            $this->process($aBatch);
            usleep(1000);
        }

        $fTE = microtime(true);
        $sOutput .= PHP_EOL . PHP_EOL . ' See generated logfile ' . $this->sLogfile . PHP_EOL;
        $sOutput .= sprintf(' URL parse time: %01.3f', $fTE - $fTS) . ' s' . PHP_EOL;

        return $sOutput;
    }


    /**
        * Process URL array with cURL multi.
        *
        * @return  void
    */

    private function process(array $aURLs)
    {
        $aCurlHandles = [];
        $aURLNames = [];
        $iRunning = 0;
        $aValidHTTPCodes = [200, 203, 206, 300, 301, 302, 303, 304, 307, 308];

        $rMh = curl_multi_init();

        foreach ($aURLs as $aPair)
        {
            if (strpos($aPair['url'], 'file:') !== false || strpos($aPair['url'], 'place:') !== false)
            {
                continue;
            }

            $rCh = curl_init($aPair['url']);
            curl_setopt_array($rCh, $this->aOpts);
            curl_multi_add_handle($rMh, $rCh);
            $aCurlHandles[ $aPair['url'] ] = $rCh;
            $aURLNames[ $aPair['url'] ] = $aPair['name'];
        }

        # execute cURL handles
        do
        {
            $iSH = curl_multi_exec($rMh, $iRunning);
        }
        while ($iSH === CURLM_CALL_MULTI_PERFORM);

        # for Windows cURL multi hanging, credit: xxavalanchexx@gmail.com
        if (curl_multi_select($rMh) === -1)
        {
            usleep(100);
        }

        while ($iRunning && $iSH === CURLM_OK)
        {
            do
            {
                $iSH2 = curl_multi_exec($rMh, $iRunning);
            }
            while ($iSH2 === CURLM_CALL_MULTI_PERFORM);
        }

        # grab URL content, remove handles
        foreach ($aCurlHandles as $rCh)
        {
            $aResults = curl_getinfo($rCh);

            $this->iURLTotal++;

            $sName = isset($aURLNames[$aResults['url']]) ? $aURLNames[$aResults['url']] : '-';

            if ( ! in_array($aResults['http_code'], $aValidHTTPCodes))
            {
                echo ' broken | ' . $aResults['url'] . ' | ' . $aResults['http_code'] . ' | ' . $aResults['total_time'] . PHP_EOL;
                $this->iURLFails++;
                $this->logWrite($aResults['url'] . ' | ' . $aResults['http_code'] . ' | ' . $aResults['total_time']  . ' | ' . $sName . ' | ****');
            }
            else
            {
                $this->logWrite($aResults['url'] . ' | ' . $aResults['http_code'] . ' | ' . $aResults['total_time']);
            }

            curl_multi_remove_handle($rMh, $rCh);
        }

        curl_multi_close($rMh);
    }


    /**
        * Log messages to file.
        *
        * @param   string $sMessage, message to log
        * @param   boolean $bTimestamp, toggle to include/omit timestamp on line
        * @return  void
    */

    private function logWrite(string $sMessage = '', bool $bTimestamp = false)
    {
        if (empty($sMessage))
        {
            return;
        }

        if ($bTimestamp)
        {
            $sMessage = $this->getTimestamp() . ' | ' . $sMessage;
        }

        $iLogWrite = file_put_contents($this->sLogfile, $sMessage . PHP_EOL, FILE_APPEND);

        if (!$iLogWrite)
        {
            die('could not write to logfile ' . $this->sLogfile . PHP_EOL);
        }
    }


    /**
        * Return a timestamp with a custom format.
        *
        * @return  string, custom date format
    */

    private function getTimestamp(): string
    {
        return date('Y-m-d H:i:s P T');
    }


    /**
        * Output message.
        *
        * @param   string $sMessage, message to print
        * @return  void
    */

    private function outputMessage(string $sMessage = '')
    {
        if (empty($sMessage))
        {
            return;
        }
        else
        {
            echo $sMessage;
        }
    }


    /**
        * Getter for total URLs queried.
        *
        * @return  integer
    */

    public function getURLTotal(): int
    {
        return $this->iURLTotal;
    }


    /**
        * Getter for count of URL failed responses.
        *
        * @return  integer
    */

    public function getURLFails(): int
    {
        return $this->iURLFails;
    }
}
