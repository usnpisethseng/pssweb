<?php
/**
 * VFM - veno file manager: ajax/streamvid.php
 *
 * Stream videos
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
require_once dirname(dirname(__FILE__)).'/class/class.setup.php';
require_once dirname(dirname(__FILE__)).'/class/class.gatekeeper.php';
$setUp = new SetUp();
$gateKeeper = new GateKeeper();

if (!$gateKeeper->isAccessAllowed() && $setUp->getConfig('share_playvideo') !== true) {
    die('Access denied');
}
// $get = htmlspecialchars($_GET['vid']);
$get = filter_input(INPUT_GET, 'vid', FILTER_SANITIZE_SPECIAL_CHARS);
require_once dirname(dirname(__FILE__)).'/class/class.videostream.php';
if ($get) {
    $stream = new VideoStream($get);
    $stream->_start();
}
exit;