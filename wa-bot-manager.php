<?php
/*
Plugin Name: WA Bot Manager
Description: Administra tus etapas personalizadas para el bot de WhatsApp basado en Node.js
Version: 1.0.0
Author: Soy Javier Quiroz
*/

if (!defined('ABSPATH')) exit;

// Constantes
define('WA_BOT_MANAGER_VERSION', '1.0.0');
define('WA_BOT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('WA_BOT_MANAGER_URL', plugin_dir_url(__FILE__));

// Hooks
add_action('init', 'wa_bot_register_shortcode');
add_action('wp_enqueue_scripts', 'wa_bot_enqueue_assets');

// Carga Bootstrap y archivos del plugin
function wa_bot_enqueue_assets() {
    wp_enqueue_style('wa-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', [], '5.3.2');
    wp_enqueue_style('wa-bot-style', WA_BOT_MANAGER_URL . 'css/wa-bot-manager.css', [], WA_BOT_MANAGER_VERSION);

    wp_enqueue_script('wa-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.2', true);
    wp_enqueue_script('wa-bot-script', WA_BOT_MANAGER_URL . 'js/wa-bot-manager.js', ['jquery'], WA_BOT_MANAGER_VERSION, true);

    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

    wp_localize_script('wa-bot-script', 'waBotManager', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}

// Shortcode base
function wa_bot_register_shortcode() {
    add_shortcode('wa_bot_etapas', 'wa_bot_render_etapas_shortcode');
}

function wa_bot_render_etapas_shortcode() {
    ob_start();
    include WA_BOT_MANAGER_PATH . 'templates/etapas-form.php';
    return ob_get_clean();
}

add_action('init', 'wa_bot_handle_etapa_submission');

// Depuración temporal - remover luego
error_log(print_r($_FILES['audios'], true));

function wa_bot_handle_etapa_submission() {
    $is_ajax = (defined('DOING_AJAX') && DOING_AJAX) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (!isset($_POST['wa_etapa_nonce']) || !wp_verify_nonce($_POST['wa_etapa_nonce'], 'guardar_etapa')) return;
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    global $wpdb;

    $nombre_raw = sanitize_text_field($_POST['etapa_nombre']);
    $nombre = wa_sanitize_slug($nombre_raw);

    $etapa_existente = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d AND nombre = %s",
          $user_id, $nombre
        )
      );
      
      if ($etapa_existente > 0) {
        if ($is_ajax) {
            wp_send_json_error('⚠️ Ya existe una etapa con ese nombre. Por favor elige otro diferente.');
        } else {
            wp_die('⚠️ Ya existe una etapa con ese nombre. Por favor elige otro diferente.');
        }          
      }      
    
    $descripcion = sanitize_textarea_field($_POST['etapa_descripcion']);
    $textos = array_filter(array_map('sanitize_text_field', $_POST['textos']));
    $textos_html = array_filter(array_map('wp_kses_post', $_POST['textos_html']));

    if (!$nombre || count($textos) < 1 || count($textos_html) < 1) {
        $msg = 'Faltan campos requeridos.';
        if ($is_ajax) wp_send_json_error($msg);
        else wp_die($msg);
    }    

    // Subir imagen
    $imagen_path = null;
    if (!empty($_FILES['etapa_imagen']['tmp_name'])) {
        if ($_FILES['etapa_imagen']['size'] > 1048576) {
            $msg = 'La imagen excede 1MB.';
            if ($is_ajax) wp_send_json_error($msg);
            else wp_die($msg);
        }
        
        $ext = pathinfo($_FILES['etapa_imagen']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            $msg = 'Formato de imagen no válido.';
            if ($is_ajax) wp_send_json_error($msg);
            else wp_die($msg);
        }        

        $imagen_filename = "{$user_id}_{$nombre}." . $ext;
        $imagen_path = "/home/whatsapp-audio-bot/imagenes_etapa/{$user_id}_{$nombre}." . $ext;

        if (!move_uploaded_file($_FILES['etapa_imagen']['tmp_name'], $imagen_path)) {
            error_log("❌ Falló al mover imagen a: " . $imagen_path);
        }
    }

    // Insertar etapa
    $wpdb->insert('wa_bot_etapas', [
        'user_id' => $user_id,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'textos' => json_encode(array_values($textos)),
        'textos_html' => json_encode(array_values($textos_html)),
        'imagen' => $imagen_path
    ]);

    $etapa_id = $wpdb->insert_id;
    error_log("🧪 Insert ID de etapa: $etapa_id");


    // Subir audios
    $audio_dir = "/home/whatsapp-audio-bot/audios_pregrabados/";
    $audios_subidos = 0;

    if (!isset($_FILES['audios'])) {
        if ($is_ajax) {
            wp_send_json_error('No se encontraron archivos de audio en la solicitud.');
        } else {
            wp_die('No se encontraron archivos de audio en la solicitud.');
        }              
    }

    $orden_actual = 1;

    $audios_subidos = 0;

    foreach ($_FILES['audios']['tmp_name'] as $i => $tmp_name) {
        if (!$tmp_name || !is_uploaded_file($tmp_name)) {
            continue;
        }

        $filename = $_FILES['audios']['name'][$i];
        $size = $_FILES['audios']['size'][$i];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validaciones
        if ($ext !== 'mp3') {
            error_log("Archivo omitido por extensión inválida: $filename");
            continue;
        }

        if ($size > 2 * 1024 * 1024) {
            error_log("Archivo omitido por tamaño: $filename");
            continue;
        }

        // Evita fallo por mime_type inconsistente en algunos servidores
        $nombre_archivo = "{$user_id}_{$nombre}_" . ($i + 1) . ".mp3";
        $destino = $audio_dir . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $destino)) {
            $wpdb->insert('wa_bot_audios', [
                'user_id' => $user_id,
                'etapa_id' => $etapa_id,
                'archivo' => $nombre_archivo,
                'orden' => $i + 1
            ]);
            $audios_subidos++;
        } else {
            error_log("❌ Falló al mover audio a: " . $destino);
        }
    }

    

    if ($audios_subidos === 0) {
        $wpdb->delete('wa_bot_etapas', ['id' => $etapa_id]);
        $msg = 'Debes subir al menos 1 audio válido (MP3, máx. 2MB).';
        if ($is_ajax) wp_send_json_error($msg);
        else wp_die($msg);
    }    

    if ($is_ajax) {
        wp_send_json_success('Etapa guardada correctamente.');
    } else {
        wp_redirect(add_query_arg('wa_etapa_status', 'ok', wp_get_referer()));
        exit;
    }
    
}


function wa_sanitize_slug($string) {
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9_]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string); // evita ___
    return trim($string, '_');
}

add_action('wp_ajax_wa_check_etapa_existente', 'wa_check_etapa_existente');
function wa_check_etapa_existente() {
    if (!is_user_logged_in()) {
        wp_send_json_error('No autorizado');
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';

    // Normalizar igual que en PHP
    $nombre = wa_sanitize_slug($nombre);

    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d AND nombre = %s",
        $user_id, $nombre
    ));

    if ($existe > 0) {
        wp_send_json_success(['existe' => true]);
    } else {
        wp_send_json_success(['existe' => false]);
    }
}

add_shortcode('wa_bot_listado', 'wa_bot_render_listado_etapas');

function wa_bot_render_listado_etapas() {
    ob_start();
    include WA_BOT_MANAGER_PATH . 'templates/etapas-listado.php';
    return ob_get_clean();
}
