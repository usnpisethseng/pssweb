<?php
/**
 * VFM - veno file manager downloader
 *
 * PHP version >= 5.3
 *
 * @category  PHP
 * @package   VenoFileManager
 * @author    Nicola Franchini <support@veno.it>
 * @copyright 2013 Nicola Franchini
 * @license   Exclusively sold on CodeCanyon
 * @link      http://filemanager.veno.it/
 */
require_once dirname(__FILE__).'/class/class.setup.php';
$setUp = new SetUp();
if ($setUp->getConfig('debug_mode') === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
require_once dirname(__FILE__).'/class/class.gatekeeper.php';
require_once dirname(__FILE__).'/class/class.downloader.php';
require_once dirname(__FILE__).'/class/class.utils.php';
require_once dirname(__FILE__).'/class/class.logger.php';

// Redirect blacklisted IPs.
Utils::checkIP();

$setUp = new SetUp();

$gateKeeper = new GateKeeper();
$downloader = new Downloader();
$logger = new Logger();

$timeconfig = $setUp->getConfig('default_timezone');
$timezone = (strlen($timeconfig) > 0) ? $timeconfig : "UTC";
date_default_timezone_set($timezone);

$script_url = $setUp->getConfig('script_url');

$getzip = filter_input(INPUT_GET, 'zip', FILTER_SANITIZE_SPECIAL_CHARS);
$getfile = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
$hash = filter_input(INPUT_GET, 'h', FILTER_SANITIZE_SPECIAL_CHARS);
$supah = filter_input(INPUT_GET, 'sh', FILTER_SANITIZE_SPECIAL_CHARS);
$json_file = filter_input(INPUT_GET, 'share', FILTER_SANITIZE_SPECIAL_CHARS);

$alt = $setUp->getConfig('salt');
$altone = $setUp->getConfig('session_name');

$android = false;
$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
if (stripos($useragent, 'android') !== false) {
    $android = true;
}

/**
 * Download single file
 * (for non-logged users)
 */
if ($json_file && is_numeric($getfile)) {
    $filenum = $getfile;
    // Check sharing link.
    $share_json = '_content/share/'.$json_file.'.json';
    if (file_exists($share_json)) {
        $datarray = json_decode(file_get_contents($share_json), true);
        $time = $datarray['time'];
        $hash = $datarray['hash'];

        if (md5($time.$hash) !== $supah ) {
            Utils::setError('<i class="bi bi-slash-circle"></i> '.$setUp->getString("access_denied"));
            header('Location:'.$script_url);
            exit;
        }

        // Check expiration time.
        if ($downloader->checkTime($time) == true) {
            $pieces = explode(",", $datarray['attachments']);
            /**
             * ONE TIME DOWNLOADER
             * remove empty json file
             */
            if (!count($pieces)) {
                unlink($share_json);
            }
            // Check if file is listed in json.
            if (isset($pieces[$filenum])) {
                $getfile = $pieces[$filenum];

                // Check if requested file exists.
                if ($downloader->checkFile($getfile) == true) {
                    $headers = $downloader->getHeaders($getfile);

                    /**
                     * EDIT: BLOCK UNLOGGED USERS
                     * AND USERS WITHOUT ACCESS TO THAT FOLDER
                     */
                    // if (!$gateKeeper->isUserLoggedIn() || !$downloader->subDir($headers['dirname'])) {

                    //     Utils::setError('<i class="bi bi-slash-circle"></i> Access denied');

                    //     if (!$gateKeeper->isUserLoggedIn()) {
                    //         Utils::setError('<i class="bi bi-slash-circle"></i> Please log in to download the files');
                    //     }
                    //     $cleandir = '';
                    //     $userdirs = $gateKeeper->getUserInfo('dir');
                    //     $userpatharray = $userdirs !== null ? json_decode($userdirs, true) : array();
                    //     // check if user has only one folder
                    //     if (count($userpatharray) === 1) {
                    //         $cleandir = '?dir='.substr($setUp->getConfig('starting_dir').$userpatharray[0], 2);
                    //     }
                    //     header('Location:'.$script_url.$cleandir);
                    //     exit;
                    // }
                    /**
                     * EDIT-END: BLOCK UNLOGGED USERS
                     * AND USERS WITHOUT ACCESS TO THAT FOLDER
                     */

                    /**
                     * ONE TIME DOWNLOADER
                     * remove the item from the json file
                     */
                    if ($setUp->getConfig('one_time_download')) {
                        unset($pieces[$filenum]);
                        $datarray['attachments'] = implode(',', $pieces);
                        $send_dataarray = json_encode($datarray);
                        file_put_contents($share_json, $send_dataarray);
                    }
                    
                    if ($setUp->getConfig('direct_links')) {
                        if ($headers['content_type'] == 'audio/mp3') {
                            $logger->logPlay($headers['trackfile']);
                        } else {
                            $logger->logDownload($headers['trackfile']);
                        }
                        header('Location:'.$script_url.base64_decode($getfile));
                        exit;
                    }
                    if ($downloader->download(
                        $headers['file'], 
                        $headers['filename'], 
                        $headers['file_size'], 
                        $headers['content_type'],
                        $headers['disposition'],
                        $android 
                    ) === true
                    ) {
                        $logger->logDownload($headers['trackfile']);
                    }
                    exit;
                }
            }
        }
    }
}

/**
 * Download single file, play Audio or show PDF 
 * (for logged users)
 */
if ($getfile && $hash
    && $downloader->checkFile($getfile) == true
    && md5($alt.$getfile.$altone.$alt) === $hash
) {
    // $playmp3 = htmlspecialchars($_GET['audio']);
    $playmp3 = filter_input(INPUT_GET, 'audio', FILTER_SANITIZE_SPECIAL_CHARS);
    $headers = $downloader->getHeaders($getfile, $playmp3);

    if (($gateKeeper->isUserLoggedIn() 
        && $downloader->subDir($headers['dirname']) == true) 
        || $gateKeeper->isLoginRequired() == false
    ) {

        if ($setUp->getConfig('direct_links')) {
            if ($headers['content_type'] == 'audio/mp3') {
                $logger->logPlay($headers['trackfile']);
            } else {
                $logger->logDownload($headers['trackfile']);
            }
            header('Location:'.$script_url.base64_decode($getfile));
            exit;
        }

        if ($headers['content_type'] == 'audio/mp3') {
            $logger->logPlay($headers['trackfile']);
        }

        if ($downloader->download(
            $headers['file'],
            $headers['filename'],
            $headers['file_size'],
            $headers['content_type'],
            $headers['disposition'],
            $android
        ) === true
        ) {
            if ($headers['content_type'] !== 'audio/mp3') {
                $logger->logDownload($headers['trackfile']);
            }
        }
        exit;
    }
    Utils::setError('<i class="bi bi-slash-circle"></i> '.$setUp->getString("access_denied"));
    header('Location:'.$script_url);
    exit;
}

/**
 * Download zipped files
 */
if ($getzip) {
    // $supahzip = htmlspecialchars($_GET['n']);
    $supahzip = filter_input(INPUT_GET, 'n', FILTER_SANITIZE_SPECIAL_CHARS);
    $zip_json = dirname(__FILE__).'/tmp/'.$getzip.'.json';

    if (file_exists($zip_json)) {
        $datarray = json_decode(file_get_contents($zip_json), true);
        $time = $datarray['time'];
        $hash = $datarray['hash'];
        $folder = $datarray['dir'];
        $files = $datarray['files'];

        if (md5($time.$hash) !== $supahzip ) {
            Utils::setError('<i class="bi bi-slash-circle"></i> '.$setUp->getString("access_denied"));
            header('Location:'.$script_url);
            exit;
        }

        if ($folder || $files) {
            @set_time_limit(0);
            session_write_close();
            include dirname(__FILE__).'/assets/zipstream/autoload.php'; // https://github.com/Grandt/PHPZip
            $cleanpath = dirname(dirname(__FILE__)).'/'.ltrim($setUp->getConfig('starting_dir'), './');
        }

        if ($folder) {
            // $archivename = basename($folder);
            $folderpathinfo = Utils::mbPathinfo($cleanpath.$folder);
            $archivename = Utils::checkMagicQuotes($folderpathinfo['filename']);
            if (ob_get_level()) {
                ob_end_clean();
            }
            $zip = new \PHPZip\Zip\Stream\ZipStream($archivename.'.zip');

/**
 * Add comment to zip file and include a README.txt file
 */
// $zip->setComment("Zip file comment. on " . date('l jS \of F Y h:i:s A')); // Linux command to read comment: unzip -z path/to/zip/file.zip
// $zip->addFile("This is an example of readme file", $archivename.'/'."README.txt");

            $zip->addDirectoryContent($cleanpath.$folder, $archivename);
            $zip->finalize();
            $logger->logDownload($folder, true);
        }

        if ($files) {
            $archivename = 'zip-'.$time;
            if (ob_get_level()) {
                ob_end_clean();
            }
            $zip = new \PHPZip\Zip\Stream\ZipStream($archivename.'.zip');
/**
 * Add comment to zip file and include a README.txt file
 */
// $zip->setComment("Zip file comment.\nCreated on " . date('l jS \of F Y h:i:s A'));
// $zip->addFile("This is an example of readme file\r\n", $archivename.'/'."README.txt");

            foreach ($files as $file) {
                // $filepathinfo = Utils::mbPathinfo($cleanpath.$file);
                // $filename = Utils::checkMagicQuotes($filepathinfo['filename']).'.'.$filepathinfo['extension'];
                $zip->addLargeFile($cleanpath.$file, $archivename.'/'.basename($file), filectime($cleanpath.$file));
            }
            $zip->finalize();
            $logger->logDownload($files);
        }

        unlink($zip_json);
        exit;
    }
}
Utils::setError($setUp->getString("link_expired"));
header('Location:'.$script_url);
exit;
