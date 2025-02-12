<?php
/**
 * Plugin Name: TkmoG Premium KWN File Redirect
 * Description: Redirects https://www.zahleinfachperhandyrechnung.de/Nummern-für-Kurzwahldienste-TKG-$120-Liste to the most recent version of Nummern-für-Kurzwahldienste-TKG-$120-[timestamp].csv
 *
 * Upload happens every 16th of the month.
 *
 * Testfile is available on https://www.zahleinfachperhandyrechnung.de/dev2/uploadtest
 * uploaded every day
 *
 *
 * Version: 1.2
 * Author: MS@mdk.digital
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define mappings of URL paths to directory(not the internal path) and filename patterns
function get_url_to_pattern_mappings() {
    return [
        '/dev2/uploadtest' => [
            'directory' => '',
            'base_url' => '',
            'filename_pattern' => 'testfileUpload-*.csv'
        ],
        '/Nummern-für-Kurzwahldienste-TKG-$120-Liste' => [
            'directory' => '',
            'base_url' => '',
            'filename_pattern' => 'Nummern-für-Kurzwahldienste-TKG-§120-*.csv'
        ],
        //'/another/url' => [
        //    'directory' => '/custom-folder',
        //    'base_url' => '',
        //    'filename_pattern' => 'anotherFilePattern-*.txt'
        //],
        // More mappings can be added here
    ];
}

function latest_file_redirect($directory, $base_url, $filename_pattern) {
    if (!$directory || $directory === '') {
        // Get WordPress uploads url as default
        $upload_dir = wp_upload_dir();
        // set directory to absolute path of uploads url
        $directory = trailingslashit($upload_dir['basedir']);
    }
    if (!$base_url || $base_url === '') {
        // Get WordPress uploads url as default
        $upload_url = wp_upload_dir();
        // set directory to absolute path of uploads url
        $base_url = trailingslashit($upload_url['baseurl']);
    }

    //sanitize directory
    // checks whether the directory exists and is inside uploads
    $internalPath = realpath($directory);
    if (!$internalPath || strpos($internalPath, wp_upload_dir()['basedir']) !== 0) {
        error_log("Tkmog-Premium-KWN Plugin error: " . "Invalid directory: $internalPath"); // pattern
        wp_die("Invalid directory.");
    }

    // Define file pattern
    $pattern = trailingslashit($directory) . $filename_pattern;

    // Get files matching the pattern
    $files = glob($pattern);

    // Trap for missing files
    if (!$files  || !is_array($files)) {
        $upload_dir = wp_upload_dir();
        error_log("Tkmog-Premium-KWN Plugin error: " . "Basedir: " . $upload_dir['basedir']); // Absolute path
        error_log("Tkmog-Premium-KWN Plugin error: " . "Baseurl: " . $upload_dir['baseurl']); // Public URL
        error_log("Tkmog-Premium-KWN Plugin error: " . "No files found for: $pattern"); // pattern
        wp_die("No files found.");
       // wp_safe_redirect(home_url('/404'), 404);
        exit;
    }

    // Sort by modification time (newest first)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Redirect user to the latest file
    $latest_file = basename($files[0]);
    wp_safe_redirect($base_url . "/" . $latest_file, 302);
    exit;
}


// Hook into WordPress 'init' action
add_action('init', function () {
    $request_uri = urldecode($_SERVER['REQUEST_URI']);
    $mappings = get_url_to_pattern_mappings();

    foreach ($mappings as $url_path => $params) {
        if (parse_url($request_uri, PHP_URL_PATH) === $url_path) {
            latest_file_redirect($params['directory'], $params['base_url'], $params['filename_pattern']);
        }
    }
});
