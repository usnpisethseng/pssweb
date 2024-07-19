<?php
/**
 * VFM - Veno File Manager - updater
 *
 * PHP version >= 5.3
 *
 * @category  PHP
 * @package   VenoFileManager
 * @author    Nicola Franchini <support@veno.it>
 * @copyright 2022 Nicola Franchini
 * @license   Exclusively sold on CodeCanyon
 * @link      https://filemanager.veno.it/
 */
require_once dirname(__FILE__).'/class.vfmupdater.php';
?>
<div class="row">
    <div class="col-xl-6">

<h4>Current version: <?php echo VFM_VERSION; ?></h4>
<p class="small"><a target="_blank" class="text-primary" href="<?php echo VFM_updater()->_update_url; ?>logs/?slug=vfm"> Log History</a><br>
    Last checked on <?php echo date('l jS \of F Y h:i:s A'); ?>. <a class="text-primary" href="javascript:window.location=window.location.href;"><u>Check Again</u></a>.
</p>
<?php
$updates = VFM_updater()->checkUpdates();
$response = $updates ? json_decode($updates, true) : false;

$messages = ($response && isset($response['messages'])) ? $response['messages'] : array();
foreach ($messages as $message) {
    echo '<div class="mb-3 alert alert-light">'.$message.'</div>';
}
$updateclass = 'd-none';
$licenseclass = 'is-invalid';

$update_btn = isset($response['latest']) && $response['latest'] == 1 ? 'Re-Install the latest version' : 'Start update';

if (isset($response['license']) && $response['license'] === 1) {
    $updateclass = '';
    $licenseclass = 'is-valid';
}
if (isset($response['logs'])) {
    echo '<div class="p-3 border mb-3 small">'. $response['logs'].'</div>';
}
?>
        <div class="<?php echo $updateclass; ?>">
            <div class="shadow bg-white p-3 mb-3 border border-4 border-warning border-end-0 border-top-0 border-bottom-0">Important: Before updating, please back up your <code>/vfm-admin/</code> directory.</div>
            <div class="mb-3"><button type="button" class="btn btn-primary start-update"><i class="bi bi-arrow-right-short"></i> <?php echo $update_btn; ?></button></div>
            <div class="update-output mb-3"></div>
            <div class="countdown mb-3"></div>
        </div>

    </div>
    <div class="col-xl-6">
        <div class="card mb-3">
             <div class="card-header">
                License
            </div>
            <div class="card-body purchase-key-group">
                <form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>?section=updates&action=update">
                <div class="input-group mb-3">
                    <label for="purchase-code" class="input-group-text">Purchase code</label>
                    <input type="text" class="form-control fake-key <?php echo $licenseclass; ?>" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXX">
                    <input type="text" class="form-control <?php echo $licenseclass; ?>" id="purchase-code" name="license_key" placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXX">
                    <button disabled type="submit" class="btn btn-primary update-purchase-code"><i class="bi bi-arrow-repeat"></i></button>
                </div>
                <div><a class="btn btn-outline-primary btn-sm" target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"><i class="bi bi-key-fill"></i> Find my key</a></div>
                </form>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                Server requirements
            </div>
            <div class="card-body">
                <ul class="list-group rounded-0 border-0">
<?php
$serverCheck = VFM_updater()->serverCheck();
foreach ($serverCheck['details'] as $value) {
    $checkclass = $value['enabled'] == true ? ' bg-opacity-25 bg-success' : ' bg-opacity-25 bg-danger';
    $checkicon = $value['enabled'] == true ? '<i class="bi bi-check"></i>' : '<i class="bi bi-x"></i>';
    ?>
                    <li class="list-group-item border border-white<?php echo $checkclass; ?>"><?php echo $checkicon; ?> <?php echo $value['text']; ?></li>
    <?php
}
?>   
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card update-output-error d-none">
            <div class="card-header"><i class="bi bi-exclamation-circle"></i> Error log</div>
            <div class="card-body bg-danger bg-opacity-25">
                <pre class="small update-output-error" style="max-height: 400px;"></pre>
            </div>
        </div>
    </div>     
</div>
