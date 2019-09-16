<?php
// -----
// A tool to create a report identifying the notifiers present in a store's file-system.
//
require 'includes/application_top.php';

$report_file = DIR_FS_LOGS . '/notifier_report_' . date('Ymd_His') . '.txt';
error_log('Start notifier report (v1.0.0), created ' . date('Y-m-d H:i:s') . PHP_EOL, 3, $report_file);

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DIR_FS_CATALOG));
$it->rewind();
while ($it->valid()) {
    if (!$it->isDot() && pathinfo($it->key(), PATHINFO_EXTENSION) == 'php') {
        $lines = file($it->key());
        for ($i = 0, $n = count($lines), $found_semi = false, $found_first = false; $i < $n; $i++) {
            // -----
            // Determine if the current line contains a '->notify', continuing if not.
            //
            $next_pos = strpos($lines[$i], '->notify');
            if ($next_pos === false) {
                continue;
            }
            
            // -----
            // The notification's event and parameters list ends with a semi-colon.
            // 
            // Build up the parameters present in the notification, for output once the semi-colon (or
            // end-of-file) is found.
            //
            $parameters = trim($lines[$i]);
            $start_line = $i;
            $notify_end_found = strpos($parameters, ';');
            while ($i < $n && $notify_end_found === false) {
                if ($i != $start_line) {
                    $parameters .= ' ' . trim($lines[$i]);
                }
                $notify_end_found = strpos($parameters, ';');
                $i++;
            }
            
            // -----
            // If we get here, there was "something" found ... so output the current record.
            //
            if (!$found_first) {
                $found_first = true;
                error_log(PHP_EOL . $it->key() . PHP_EOL, 3, $report_file);
            }
            error_log ("\t\tline#$start_line: $parameters\n", 3, $report_file);
        }
    }
    $it->next();
}

echo "Report created: $report_file.";

require DIR_WS_INCLUDES . 'application_bottom.php';
