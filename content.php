<?php
// Festivly settings page. Included by FPP's plugin.php, so $settings exists.

$festivlyConfigFile = $settings['configDirectory'] . '/plugin.fpp-festivly.json';
$festivlyPluginDir = dirname(__FILE__);
$festivlyMessage = '';

function festivly_read_config($path) {
    $defaults = array(
        'displayId' => '',
        'token' => '',
        'webhookUrl' => 'https://www.festivly.com/api/fpp/webhook',
        'syncSchedule' => 1,
    );
    if (!file_exists($path)) {
        return $defaults;
    }
    $decoded = json_decode(file_get_contents($path), true);
    if (!is_array($decoded)) {
        return $defaults;
    }
    return array_merge($defaults, $decoded);
}

function festivly_write_config($path, $config) {
    file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function festivly_ensure_cron($pluginDir) {
    $marker = 'fpp-festivly-poll';
    $line = '* * * * * /usr/bin/python3 ' . $pluginDir . '/callbacks --poll >/dev/null 2>&1 # ' . $marker;
    $current = shell_exec('crontab -l 2>/dev/null');
    $current = is_string($current) ? $current : '';
    if (strpos($current, $marker) !== false) {
        return;
    }
    $next = rtrim($current) . "\n" . $line . "\n";
    $tmp = tempnam(sys_get_temp_dir(), 'festivly');
    if ($tmp === false) {
        return;
    }
    file_put_contents($tmp, $next);
    shell_exec('crontab ' . escapeshellarg($tmp));
    unlink($tmp);
}

if (isset($_POST['festivly_save']) || isset($_POST['festivly_test'])) {
    $webhook = trim($_POST['webhookUrl'] ?? '');
    if ($webhook === '' || strpos($webhook, 'https://') !== 0) {
        $webhook = 'https://www.festivly.com/api/fpp/webhook';
    }
    $config = array(
        'displayId' => trim($_POST['displayId'] ?? ''),
        'token' => trim($_POST['token'] ?? ''),
        'webhookUrl' => $webhook,
        'syncSchedule' => isset($_POST['syncSchedule']) ? 1 : 0,
    );
    festivly_write_config($festivlyConfigFile, $config);
    festivly_ensure_cron($festivlyPluginDir);
    $festivlyMessage = 'Saved.';

    if (isset($_POST['festivly_test'])) {
        $cmd = 'python3 ' . escapeshellarg($festivlyPluginDir . '/callbacks') . ' --sync 2>&1';
        $output = shell_exec($cmd);
        $festivlyMessage = 'Test update: ' . trim((string) $output);
    }
}

$festivly = festivly_read_config($festivlyConfigFile);
?>
<div class="container">
  <h2>Festivly</h2>
  <p>
    Send live status, the current song, and tonight's schedule to your
    <a href="https://festivly.app" target="_blank" rel="noopener">festivly.app</a> listing.
    Copy the display id and token from
    Festivly → Dashboard → Edit → Integrations.
    Schedule sync overwrites nightly on/off times and days. Season dates stay on Festivly.
  </p>
  <?php if ($festivlyMessage !== '') { ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($festivlyMessage, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php } ?>
  <form method="post">
    <div class="form-group">
      <label for="festivlyDisplayId">Display ID</label>
      <input class="form-control" id="festivlyDisplayId" name="displayId" value="<?php echo htmlspecialchars($festivly['displayId'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-group">
      <label for="festivlyToken">Token</label>
      <input class="form-control" id="festivlyToken" name="token" type="password" value="<?php echo htmlspecialchars($festivly['token'], ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
    </div>
    <div class="form-group">
      <label for="festivlyWebhook">Webhook URL</label>
      <input class="form-control" id="festivlyWebhook" name="webhookUrl" value="<?php echo htmlspecialchars($festivly['webhookUrl'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="form-group form-check">
      <input class="form-check-input" id="festivlySync" name="syncSchedule" type="checkbox" value="1" <?php echo !empty($festivly['syncSchedule']) ? 'checked' : ''; ?>>
      <label class="form-check-label" for="festivlySync">Sync nightly hours and days from the FPP schedule</label>
    </div>
    <button class="buttons btn-success" name="festivly_save" type="submit" value="1">Save</button>
    <button class="buttons" name="festivly_test" type="submit" value="1">Send test update</button>
  </form>
</div>
