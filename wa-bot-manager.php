<?php
/*
Plugin Name: WA Bot Manager
Description: Administra tus etapas personalizadas para el bot de WhatsApp basado en Node.js
Version: 1.0.5
Author: Soy Javier Quiroz
*/

if (!defined('ABSPATH')) exit;

define('WA_BOT_MANAGER_VERSION', '1.0.5');
define('WA_BOT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('WA_BOT_MANAGER_URL', plugin_dir_url(__FILE__));

add_action('init', 'wa_bot_register_shortcodes');
add_action('wp_enqueue_scripts', 'wa_bot_enqueue_assets');
add_action('init', 'wa_bot_handle_etapa_submission');

function wa_bot_enqueue_assets() {
    wp_enqueue_style('wa-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
    wp_enqueue_style('wa-bot-style', WA_BOT_MANAGER_URL . 'css/wa-bot-manager.css');
}

function wa_bot_register_shortcodes() {
    add_shortcode('wa_bot_etapas', function () {
        ob_start();
        include WA_BOT_MANAGER_PATH . 'templates/etapas-form.php';
        return ob_get_clean();
    });
    add_shortcode('wa_bot_listado', function () {
        ob_start();
        include WA_BOT_MANAGER_PATH . 'templates/etapas-listado.php';
        return ob_get_clean();
    });
    add_shortcode('wa_bot_ver_etapa', function () {
        ob_start();
        include WA_BOT_MANAGER_PATH . 'templates/etapa-ver.php';
        return ob_get_clean();
    });
}

function wa_bot_handle_etapa_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['wa_etapa_nonce'])) return;
    if (!wp_verify_nonce($_POST['wa_etapa_nonce'], 'guardar_etapa')) return;
    if (!is_user_logged_in()) return;

    global $wpdb;
    $user_id = get_current_user_id();
    $etapa_id = isset($_POST['etapa_id']) ? intval($_POST['etapa_id']) : 0;
    $modo_editar = $etapa_id > 0;

    $nombre_raw = sanitize_text_field($_POST['etapa_nombre'] ?? '');
    $nombre = wa_sanitize_slug($nombre_raw);
    $descripcion = sanitize_textarea_field($_POST['etapa_descripcion'] ?? '');
    $textos = array_map('sanitize_text_field', $_POST['textos'] ?? []);
    $textos_html = array_map('wp_kses_post', $_POST['textos_html'] ?? []);

    $imagen = '';
    if (!empty($_FILES['etapa_imagen']['name'])) {
        $ext = pathinfo($_FILES['etapa_imagen']['name'], PATHINFO_EXTENSION);
        $filename = "{$user_id}_{$nombre}." . strtolower($ext);
        $target = "/home/whatsapp-audio-bot/imagenes_etapa/$filename";
        if (move_uploaded_file($_FILES['etapa_imagen']['tmp_name'], $target)) {
            $imagen = $target;
        }
    }

    $data = [
        'user_id' => $user_id,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'textos' => json_encode($textos),
        'textos_html' => json_encode($textos_html),
        'fecha_creado' => current_time('mysql')
    ];

    if (!empty($imagen)) {
        $data['imagen'] = $imagen;
    }

    if ($modo_editar) {
        $wpdb->update('wa_bot_etapas', $data, ['id' => $etapa_id]);
    } else {
        $wpdb->insert('wa_bot_etapas', $data);
        $etapa_id = $wpdb->insert_id;
    }

    // ✅ Eliminar audios seleccionados
    if (!empty($_POST['eliminar_audios'])) {
        foreach ($_POST['eliminar_audios'] as $audio_id) {
            $audio = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM wa_bot_audios WHERE id = %d AND etapa_id = %d AND user_id = %d",
                intval($audio_id), $etapa_id, $user_id
            ));

            if ($audio) {
                $audio_path = "/home/whatsapp-audio-bot/audios_pregrabados/" . $audio->archivo;
                if (file_exists($audio_path)) {
                    unlink($audio_path);
                }
                $wpdb->delete('wa_bot_audios', ['id' => $audio->id]);
            }
        }
    }

    // Subida de nuevos audios
    if (!empty($_FILES['audios']['tmp_name'])) {
        foreach ($_FILES['audios']['tmp_name'] as $i => $tmp_name) {
            if (!$tmp_name) continue;

            $ext = strtolower(pathinfo($_FILES['audios']['name'][$i], PATHINFO_EXTENSION));
            if ($ext !== 'mp3') continue;

            $audio_file = "{$user_id}_{$nombre}_" . ($i + 1) . ".mp3";
            $target_audio = "/home/whatsapp-audio-bot/audios_pregrabados/$audio_file";
            if (move_uploaded_file($tmp_name, $target_audio)) {
                $wpdb->insert('wa_bot_audios', [
                    'user_id' => $user_id,
                    'etapa_id' => $etapa_id,
                    'archivo' => $audio_file,
                    'orden' => $i + 1
                ]);
            }
        }
    }

    wp_redirect(add_query_arg('wa_etapa_status', 'ok', $_SERVER['REQUEST_URI']));
    exit;
}

function wa_sanitize_slug($string) {
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9_]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string);
    return trim($string, '_');
}
?>
