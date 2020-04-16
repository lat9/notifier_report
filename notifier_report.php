<?php
// -----
// A tool to create a report identifying the notifiers present in a store's file-system.
//
require 'includes/application_top.php';

// -----
// Add support for generation of a markdown (.md) output file.
//
if (isset($_GET['markdown'])) {
    $is_markdown = true;
    
    $filename_string = '#### %s' . PHP_EOL . '```';
    $notifier_string = '#%1$s: %2$s';
    
    $report_file = DIR_FS_LOGS . '/notifier_report_' . date('Ymd_His') . '.md';
    $md_header = 
        '---' . PHP_EOL .
        'title: Notifier Report' . PHP_EOL .
        'description: Zen Cart Notifier Report' . PHP_EOL .
        'category: code' . PHP_EOL .
        'type: codepage' . PHP_EOL .
        'weight: 10' . PHP_EOL .
        '---' . PHP_EOL . PHP_EOL .
        '<!-- RELEASETIME - update -->' . PHP_EOL;
    error_log($md_header . PHP_EOL, 3, $report_file);
} else {
    $is_markdown = false;
    
    $filename_string = '%s';
    $notifier_string = "\t\t" . 'line#$1$s: %2$s';
    
    $report_file = DIR_FS_LOGS . '/notifier_report_' . date('Ymd_His') . '.txt';
    error_log('Start notifier report (v1.0.0), created ' . date('Y-m-d H:i:s') . PHP_EOL, 3, $report_file);
}

$dir_fs_catalog = str_replace('\\', '/', DIR_FS_CATALOG);

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DIR_FS_CATALOG));
$it->rewind();
$found_first_file = false;
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
            $parameters = str_replace(
                array(
                    '$this->notify(',
                    '$zco_notifier->notify(',
                    '$zco_notifier->notify (',
                    '$GLOBALS[\'zco_notifier\']->notify(',
                    ');',
                ),
                '',
                $parameters
            );
            
            // -----
            // If we get here, there was "something" found ... so output the current record.
            //
            if (!$found_first) {
                $found_first = true;
                $file_stripped = str_replace($dir_fs_catalog, '', str_replace('\\', '/', $it->key()));
                error_log(PHP_EOL . sprintf($filename_string, $file_stripped) . PHP_EOL, 3, $report_file);
                
                if ($is_markdown && !$found_first_file) {
                    $found_first_file = true;
                    $filename_string = '```' . PHP_EOL . PHP_EOL . $filename_string;
                }
            }
            error_log(sprintf($notifier_string, $start_line, $parameters) . PHP_EOL, 3, $report_file);
        }
    }
    $it->next();
}

echo "Report created: $report_file.";

require DIR_WS_INCLUDES . 'application_bottom.php';
