<?php
/**
 * User: pculka
 * Date: 27.4.2014
 * Time: 19:14
 */
function srt2vtt($srt) {
// default configuration
$srt2vttConf = array(
    "appendCopyrightInfo"   => false,
    "autoConvertEncoding"   => true,
    "sourceEncodingList"    => array('ISO-8859-1', 'Windows-1255', 'ISO-8859-8', 'UTF-8' ), 
    "debug"                 => true,
);
if (file_exists("srt2vtt.conf.php"))
    require_once("srt2vtt.conf.php");

try {
    // access the get, check for valid filename
    //$file = realpath(html_entity_decode($_GET['i']));
    //$fn  = filter_var($file, FILTER_VALIDATE_REGEXP, array( "options" => array( "regexp" => '%^[^"<>|:*?]+\.srt$%m') ) );

    //if ($fn === false) {
    //    throw new Exception(_("Specified subtitle source is invalid"));
    //}

    if (isset($srt)) {
        // convert to utf.
        $charset = mb_detect_encoding($srt,"Windows-1255, ISO-8859-8, UTF-8");
        $srt = iconv($charset,"UTF-8",$srt);
        // Break to lines.
        $subtitleLines = explode ("\n", $srt);

        if (empty($subtitleLines)) throw new Exception(_("Subtitle file empty"));
        // prepare the result

        $result = "WEBVTT\n\n\n";
        define('SRT_STATE_SUBNUMBER', 0);
        define('SRT_STATE_TIME',      1);
        define('SRT_STATE_TEXT',      2);
        define('SRT_STATE_BLANK',     3);

        $subs    = array();
        $state   = SRT_STATE_SUBNUMBER;
        $subNum  = 0;
        $subText = '';
        $subTime = '';

        foreach($subtitleLines as $line) {
            switch($state) {
                case SRT_STATE_SUBNUMBER:
                    $subNum = trim($line);
                    $state  = SRT_STATE_TIME;
                    break;

                case SRT_STATE_TIME:
                    $subTime = trim($line);
                    $state   = SRT_STATE_TEXT;
                    break;

                case SRT_STATE_TEXT:
                    if (trim($line) == '') {
                        $sub = new stdClass;
                        $sub->number = $subNum;
                        list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                        $sub->text   = $subText;
                        $subText     = '';
                        $state       = SRT_STATE_SUBNUMBER;
                        $subs[]      = $sub;
                    } else {
                        $subText .= trim($line)."\n";
                    }
                    break;
            }
        }

        foreach ($subs as $sub) {
            $result .= $sub->number."\n";
            $result .= str_replace(',', '.', $sub->startTime)." --> ".str_replace(',', '.', $sub->stopTime)."\n";
            $result .= $sub->text."\n\n";
        }


        //header('Content-type: text/plain; charset=utf-8');
        return $result;

    } else {
        throw new Exception(_("File not found"));
    }

} catch (Exception $e) {
    header('Content-type: text/plain; charset=utf-8');
    $result = "WEBVTT\n\n\n";
    $result .= "1\n";
    $result .= "00:00:00.000 --> 00:10:00.000\n";
    $result .= $e->getMessage()."\n\n\n";
    $subNum = 2;
    echo $result;
} finally {
    // append information if configured
    // #todo: get highest sub number, increment, add specified amount of wait time and display the disclaimer (if specified);
    if ($srt2vttConf['appendCopyrightInfo']) {
        echo (int)$subNum."\n";
        echo "00:00:00.001 --> 00:10:00.000\n";
        echo "Compiled by srt2vtt by pculka inside OpenApp Video System";
    }

}
}