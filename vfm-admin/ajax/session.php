<?php
/**
 * VFM - veno file manager: ajax/session.php
 *
 * Set session vars
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
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
) {
    exit;
}
require_once dirname(dirname(__FILE__)).'/class/class.setup.php';
require_once dirname(dirname(__FILE__)).'/class/class.utils.php';
require_once dirname(dirname(__FILE__)).'/class/class.gatekeeper.php';

$setUp = new SetUp();
$gateKeeper = new GateKeeper();

if (!$gateKeeper->isAccessAllowed()) {
    die();
}
// update list view
$listview = filter_input(INPUT_POST, "listview", FILTER_SANITIZE_SPECIAL_CHARS);
// $listview = htmlspecialchars($_POST['listview']);
if ($listview) {
    $listdefault = $setUp->getConfig('list_view') ? $setUp->getConfig('list_view') : 'list';
    $listtype = $listview ? $listview : $listdefault;
    $_SESSION['listview'] = $listtype;
}

// update table paging length
$ilength = filter_input(INPUT_POST, "iDisplayLength", FILTER_VALIDATE_INT);
if ($ilength) {
    $_SESSION['ilength'] = $ilength;
}

$sort_col = filter_input(INPUT_POST, "sort_col", FILTER_VALIDATE_INT);
$sort_order = filter_input(INPUT_POST, "sort_order", FILTER_SANITIZE_SPECIAL_CHARS);
// $sort_order = htmlspecialchars($_POST['sort_order']);
if ($sort_col && $sort_order) {
    $_SESSION['sort_col'] = $sort_col;
    $_SESSION['sort_order'] = $sort_order;
}
$dirlength = filter_input(INPUT_POST, "dirlength", FILTER_VALIDATE_INT);
if ($dirlength) {
    $_SESSION['dirlength'] = $dirlength;
}
$sort_dir_col = filter_input(INPUT_POST, "sort_dir_col", FILTER_VALIDATE_INT);
$sort_dir_order = filter_input(INPUT_POST, "sort_dir_order", FILTER_SANITIZE_SPECIAL_CHARS);
// $sort_dir_order = htmlspecialchars($_POST['sort_dir_order']);
if ($sort_dir_col && $sort_dir_order) {
    $_SESSION['sort_dir_col'] = $sort_dir_col;
    $_SESSION['sort_dir_order'] = $sort_dir_order;
}
exit;
