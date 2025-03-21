<?php
restrict_access(Access::$ROOT);

$log_file = Path::LOGS . "/" . Router::getParameter("log_file", numeric: false);

if (!file_exists($log_file)) {
    Router::abort("File not found");
}

$v = new Validator(action: "download");
if ($v->valid()) {
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename="' . basename($log_file) . '"');
    header("Content-Length: " . filesize($log_file));
    readfile($log_file);
    exit;
}

$lines = file($log_file);
$lines = array_reverse($lines);

function getLogLevelDetails($level)
{
    $details = [
        'DEBUG' => ['icon' => 'fa-bug', 'class' => 'debug'],
        'INFO' => ['icon' => 'fa-info-circle', 'class' => 'info'],
        'NOTICE' => ['icon' => 'fa-exclamation-circle', 'class' => 'notice'],
        'WARNING' => ['icon' => 'fa-exclamation-triangle', 'class' => 'warning'],
        'ERROR' => ['icon' => 'fa-times-circle', 'class' => 'error'],
        'CRITICAL' => ['icon' => 'fa-skull-crossbones', 'class' => 'critical'],
        'ALERT' => ['icon' => 'fa-bell', 'class' => 'alert'],
        'EMERGENCY' => ['icon' => 'fa-siren', 'class' => 'emergency'],
    ];

    return $details[strtoupper($level)] ?? ['icon' => 'fa-question-circle', 'class' => ''];
}

page("Logs")->css("view_logs.css");
?>
<form method="post" hx-boost="false">
    <?= $v ?>
    <?= actions()->back("/admin/logs")->submit("Download") ?>
</form>
<div class="log-container">
    <table>
        <thead>
            <tr>
                <th>Level</th>
                <th>Date</th>
                <th>Log Entry</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($lines as $line) {
                // Use regex to extract the date
                preg_match('/\[(.*?)\]/', $line, $matches);
                $date = $matches[1] ?? 'N/A';

                // Convert and format the date
                try {
                    $dateTime = new DateTime($date);
                    $dateFormatted = $dateTime->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $dateFormatted = $date;
                }

                // Remove the date from the line
                $line = preg_replace('/\[(.*?)\]/', '', $line, 1);

                // Extract the log level (DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)
                preg_match('/^.*?\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):/', $line, $levelMatches);
                $level = $levelMatches[1] ?? 'UNKNOWN';

                // Get the icon and class for the log level
                $logDetails = getLogLevelDetails($level);
                $icon = $logDetails['icon'];
                $class = $logDetails['class'];

                // Remove the level from the line
                $line = preg_replace('/^.*?\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):/', '', $line, 1);
                ?>
                <tr>
                    <td class="level-column <?= $class ?>"><i class="fa <?= $icon; ?>"></i> <?= $level; ?>
                    </td>
                    <td>
                        <?= $dateFormatted; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($line); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>