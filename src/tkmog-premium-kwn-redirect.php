<?php
/**
 * Plugin Name: TkmoG Premium KWN File Redirect
 * Description: Redirects https://www.zahleinfachperhandyrechnung.de/Nummern-für-Kurzwahldienste-TKG-$120-Liste to the most recent version of Nummern-für-Kurzwahldienste-TKG-$120-[timestamp].xlsx
 *
 * Upload happens every 16th of the month.
 *
 * Testfile is available on https://www.zahleinfachperhandyrechnung.de/dev/uploadtest
 * uploaded every day
 *
 *
 * Version: 1.0
 * Author: MS@mdk.digital
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

function latest_file_redirect() {
    // Get WordPress uploads directory
    $upload_dir = wp_upload_dir();
    $uploads_directory_absolute_path = $upload_dir['basedir'];

    // Define file pattern
    $filename = "testfileUpload";
    $pattern = $uploads_directory_absolute_path . "/" . $filename . "-*" . ".csv"; // Update to match your filename structure

    // Get files matching the pattern
    $files = glob($pattern);

    if (!$files) {
        wp_die("No files found.");
    }

    // Sort by modification time (newest first)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $latest_file = basename($files[0]);
    wp_redirect($upload_dir['baseurl'] . "/" . $latest_file, 302);
    exit;
}


add_action('init', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/dev2/uploadtest') !== false) {
        latest_file_redirect();
    }
});
