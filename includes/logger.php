<?php

class OctopusEmailLogger {

    private static function get_log_path() {
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/logs/';
        wp_mkdir_p($log_dir);
        return $log_dir . 'email-log.php';
    }

    public static function log($data = []) {
        $log_path = self::get_log_path();

        $entry = [
            'timestamp' => current_time('mysql'),
            'status'    => $data['status'] ?? 'UNKNOWN',
            'to'        => sanitize_email($data['to'] ?? ''),
            'files'     => array_map('sanitize_text_field', $data['files'] ?? []),
            'type'      => sanitize_text_field($data['type'] ?? ''),
            'mode'      => sanitize_text_field($data['mode'] ?? ''),
            'user'      => $data['user'] ?? wp_get_current_user()->user_login,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        // Fallback: als logbestand niet bestaat, initialiseer
        $existing = file_exists($log_path) ? include $log_path : [];

        // Max 500 entries bewaren
        if (count($existing) >= 500) {
            array_shift($existing);
        }

        $existing[] = $entry;

        // Herbewaren als PHP array
        file_put_contents($log_path, "<?php return " . var_export($existing, true) . ";");
    }

    public static function get_all() {
        $log_path = self::get_log_path();
        return file_exists($log_path) ? include $log_path : [];
    }

    public static function clear() {
        $log_path = self::get_log_path();
        if (file_exists($log_path)) {
            unlink($log_path);
        }
    }
}
