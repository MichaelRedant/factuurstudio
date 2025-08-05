<?php

class Exporter
{
    public static function zip_last_batch($dir, $zip_path, $only_xml = false)
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = glob($dir . '*');
    if (empty($files)) {
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    foreach ($files as $file) {
        if (!is_file($file)) continue;

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($only_xml && strtolower($ext) !== 'xml') continue;

        $zip->addFile($file, basename($file));
    }

    $zip->close();
    return true;
}

}
