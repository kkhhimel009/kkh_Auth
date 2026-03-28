<?php
$authFile = 'access.txt';
$msgFile = 'message.txt';

if (!file_exists($authFile)) { touch($authFile); chmod($authFile, 0777); }
if (!file_exists($msgFile)) { touch($msgFile); chmod($msgFile, 0777); }

function updateKey($filePath, $targetKey, $action) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newList = [];
    foreach ($lines as $line) {
        $clean = str_replace('×', '', trim($line));
        if ($clean !== $targetKey && !empty($clean)) { $newList[] = $line; }
    }
    if ($action == 'auth') { $newList[] = $targetKey; }
    if ($action == 'ban') { $newList[] = $targetKey . "×"; }
    file_put_contents($filePath, implode(PHP_EOL, array_unique($newList)) . PHP_EOL);
}

if (isset($_GET['action']) && isset($_GET['key'])) {
    updateKey($authFile, trim($_GET['key']), $_GET['action']);
    header("Location: admin.php"); exit;
}

if (isset($_POST['add_manual'])) {
    $key = trim($_POST['manual_key']);
    if (!empty($key)) { updateKey($authFile, $key, 'auth'); }
}

if (isset($_POST['update_msg'])) { file_put_contents($msgFile, $_POST['user_message']); }
?>
<!DOCTYPE html><html><head><title>Secure Admin Panel</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
    .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 850px; margin: auto; margin-bottom: 20px; }
    textarea, input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
    th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    .btn { padding: 8px 12px; border-radius: 6px; text-decoration: none; color: white; font-size: 11px; font-weight: bold; display: inline-block; border: none; cursor: pointer; }
    .btn-auth { background: #28a745; } .btn-ban { background: #dc3545; } .btn-lock { background: #fd7e14; } .btn-del { background: #6c757d; }
</style></head>
<body>
<div class="card">
    <h2>Global App Notice</h2>
    <form method="POST">
        <textarea name="user_message"><?php echo @file_get_contents($msgFile); ?></textarea>
        <button type="submit" name="update_msg" style="margin-top:10px; width:100%; background:#007bff; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer;">SAVE MESSAGE FOR ALL USERS</button>
    </form>
</div>
<div class="card">
    <h2>Manual Add Device</h2>
    <form method="POST" style="display:flex; gap:10px;">
        <input type="text" name="manual_key" placeholder="Paste 6-Digit Device Code" required>
        <button type="submit" name="add_manual" style="background:#333; color:white; border:none; padding:0 30px; border-radius:8px; cursor:pointer;">APPROVE</button>
    </form>
</div>
<div class="card">
    <h2>Active Management</h2>
    <table>
        <tr style="background:#f8f9fa;"><th>Device Key</th><th>Current Status</th><th>Individual Actions</th></tr>
        <?php
        $lines = file($authFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_unique($lines) as $l) {
            $isB = (strpos($l, '×') !== false); $clean = str_replace('×', '', $l);
            echo "<tr><td><code>$clean</code></td><td>".($isB?"<b style='color:red'>BANNED</b>":"<b style='color:green'>AUTHORIZED</b>")."</td><td>";
            echo "<a href='?action=auth&key=$clean' class='btn btn-auth'>UNBAN / AUTH</a> ";
            echo "<a href='?action=ban&key=$clean' class='btn btn-ban'>BAN USER</a> ";
            echo "<a href='?action=lock&key=$clean' class='btn btn-lock'>LOCK</a> ";
            echo "<a href='?action=lock&key=$clean' class='btn btn-del' onclick='return confirm(\"Delete?\")'>DELETE</a></td></tr>";
        }
        ?>
    </table>
</div>
</body></html>
