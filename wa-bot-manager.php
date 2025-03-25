<?php
/*
Plugin Name: WA Bot Manager
Description: Administra tus etapas personalizadas para el bot de WhatsApp basado en Node.js
Version: 1.0.4
Author: Soy Javier Quiroz
*/

if (!defined('ABSPATH')) exit;

// Constantes
define('WA_BOT_MANAGER_VERSION', '1.0.0');
define('WA_BOT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('WA_BOT_MANAGER_URL', plugin_dir_url(__FILE__));

// Hooks
add_action('init', 'wa_bot_register_shortcodes');
add_action('init', 'wa_bot_handle_etapa_submission');
add_action('wp_enqueue_scripts', 'wa_bot_enqueue_assets');

// Enqueue de CSS/JS
function wa_bot_enqueue_assets() {
    wp_enqueue_style('wa-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', [], '5.3.2');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
    wp_enqueue_style('wa-bot-style', WA_BOT_MANAGER_URL . 'css/wa-bot-manager.css', [], WA_BOT_MANAGER_VERSION);

    wp_enqueue_script('wa-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.2', true);
    wp_enqueue_script('wa-bot-script', WA_BOT_MANAGER_URL . 'js/wa-bot-manager.js', ['jquery'], WA_BOT_MANAGER_VERSION, true);

    wp_localize_script('wa-bot-script', 'waBotManager', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}

// Shortcodes
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

// Slug friendly
function wa_sanitize_slug($string) {
    $string = strtolower(trim($string));
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-z0-9_]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string);
    return trim($string, '_');
}

// AJAX verificar existencia de etapa
add_action('wp_ajax_wa_check_etapa_existente', function () {
    if (!is_user_logged_in()) wp_send_json_error('No autorizado');

    global $wpdb;
    $user_id = get_current_user_id();
    $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
    $nombre = wa_sanitize_slug($nombre);

    $etapa_id = isset($_POST['etapa_id']) ? intval($_POST['etapa_id']) : 0;

    $query = "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d AND nombre = %s";
    $params = [$user_id, $nombre];

    if ($etapa_id > 0) {
        $query .= " AND id != %d";
        $params[] = $etapa_id;
    }

    $existe = $wpdb->get_var($wpdb->prepare($query, ...$params));
    wp_send_json_success(['existe' => $existe > 0]);
});

// Manejador de creación/edición
function wa_bot_handle_etapa_submission() {
    if (!isset($_POST['wa_etapa_nonce']) || !wp_verify_nonce($_POST['wa_etapa_nonce'], 'guardar_etapa')) return;
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    global $wpdb;

    $etapa_id = isset($_POST['etapa_id']) ? intval($_POST['etapa_id']) : 0;
    $modo_editar = $etapa_id > 0;

    // Limitar a máximo 10 etapas por usuario
    if (!$modo_editar) {
        $total = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d", $user_id
        ));
    
        if ($total >= 10) {
        $msg = '❌ Has alcanzado el límite de 10 etapas permitidas.';
        $is_ajax ? wp_send_json_error($msg) : wp_die($msg);
        }
    }  

    $nombre_raw = sanitize_text_field($_POST['etapa_nombre']);
    $nombre = wa_sanitize_slug($nombre_raw);

    // Validación de duplicado solo al crear
    if (!$modo_editar) {
        // En creación: nombre debe ser único
        $existe = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d AND nombre = %s",
            $user_id, $nombre
        ));
        if ($existe > 0) {
            $msg = '⚠️ Ya existe una etapa con ese nombre. Usa otro.';
            $is_ajax ? wp_send_json_error($msg) : wp_die($msg);
        }
    } else {
        // En edición: validar que si el nombre cambió, no haya duplicados
        $etapa_anterior = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wa_bot_etapas WHERE id = %d AND user_id = %d",
            $etapa_id, $user_id
        ));
        if (!$etapa_anterior) {
            $msg = 'No tienes permiso para editar esta etapa.';
            $is_ajax ? wp_send_json_error($msg) : wp_die($msg);
        }
        if ($etapa_anterior->nombre !== $nombre) {
            $existe = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM wa_bot_etapas WHERE user_id = %d AND nombre = %s AND id != %d",
                $user_id, $nombre, $etapa_id
            ));
            if ($existe > 0) {
                $msg = '⚠️ Ya existe otra etapa con ese nombre.';
                $is_ajax ? wp_send_json_error($msg) : wp_die($msg);
            }
        }
    }

    // Campos básicos
    $descripcion = sanitize_textarea_field($_POST['etapa_descripcion']);
    $textos = array_filter(array_map('sanitize_text_field', $_POST['textos']));
    $textos_html = array_filter(array_map('wp_kses_post', $_POST['textos_html']));

    if (count($textos) < 1 || count($textos_html) < 1) {
        wp_die('Faltan campos requeridos.');
    }

    // Imagen
    $imagen_path = null;
    if (!empty($_FILES['etapa_imagen']['tmp_name'])) {
        $ext = pathinfo($_FILES['etapa_imagen']['name'], PATHINFO_EXTENSION);
        $imagen_filename = "{$user_id}_{$nombre}." . strtolower($ext);
        $imagen_path = "/home/whatsapp-audio-bot/imagenes_etapa/{$imagen_filename}";
        move_uploaded_file($_FILES['etapa_imagen']['tmp_name'], $imagen_path);
    }

    // Insertar o actualizar etapa
    $data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'textos' => json_encode($textos),
        'textos_html' => json_encode($textos_html),
    ];
    if ($imagen_path) $data['imagen'] = $imagen_path;

    if ($modo_editar) {
        $wpdb->update('wa_bot_etapas', $data, ['id' => $etapa_id, 'user_id' => $user_id]);
    } else {
        $data['user_id'] = $user_id;
        $etapa_insertada = $wpdb->insert('wa_bot_etapas', $data);
        $etapa_id = $wpdb->insert_id;
    }

    // Eliminar audios marcados
    if (!empty($_POST['eliminar_audios'])) {
        $audios = array_map('intval', $_POST['eliminar_audios']);
        foreach ($audios as $audio_id) {
            $archivo = $wpdb->get_var($wpdb->prepare(
                "SELECT archivo FROM wa_bot_audios WHERE id = %d AND etapa_id = %d AND user_id = %d",
                $audio_id, $etapa_id, $user_id
            ));
            if ($archivo && file_exists("/home/whatsapp-audio-bot/audios_pregrabados/$archivo")) {
                unlink("/home/whatsapp-audio-bot/audios_pregrabados/$archivo");
            }
            $wpdb->delete('wa_bot_audios', ['id' => $audio_id]);
        }
    }

    // Subida de audios
    $audios_validos = 0;
    if (!empty($_FILES['audios']['tmp_name'])) {
        foreach ($_FILES['audios']['tmp_name'] as $i => $tmp_name) {
            if (!$tmp_name || !is_uploaded_file($tmp_name)) continue;

            $ext = strtolower(pathinfo($_FILES['audios']['name'][$i], PATHINFO_EXTENSION));
            if ($ext !== 'mp3') continue;

            $nombre_archivo = "{$user_id}_{$nombre}_" . ($i + 1) . ".mp3";
            $destino = "/home/whatsapp-audio-bot/audios_pregrabados/$nombre_archivo";

            if (move_uploaded_file($tmp_name, $destino)) {
                $wpdb->insert('wa_bot_audios', [
                    'user_id' => $user_id,
                    'etapa_id' => $etapa_id,
                    'archivo' => $nombre_archivo,
                    'orden' => $i + 1
                ]);
                $audios_validos++;
            }
        }
    }

    // Validación mínima de audio solo si se está creando
    if (!$modo_editar && $audios_validos === 0) {
        $wpdb->delete('wa_bot_etapas', ['id' => $etapa_id]);
        wp_die('Debes subir al menos 1 audio válido (MP3).');
    }

    wp_redirect(add_query_arg('wa_etapa_status', 'ok', wp_get_referer()));
    exit;
}

// AJAX eliminar etapa
add_action('wp_ajax_wa_eliminar_etapa', 'wa_eliminar_etapa_handler');

function wa_eliminar_etapa_handler() {
  if (!is_user_logged_in()) wp_send_json_error('No autorizado');

  if (!isset($_POST['etapa_id']) || !wp_verify_nonce($_POST['_wpnonce'], 'wa_eliminar_etapa_nonce')) {
    wp_send_json_error('Petición inválida');
  }

  global $wpdb;
  $user_id = get_current_user_id();
  $etapa_id = intval($_POST['etapa_id']);

  // Verifica propiedad
  $etapa = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wa_bot_etapas WHERE id = %d AND user_id = %d",
    $etapa_id, $user_id
  ));

  if (!$etapa) {
    wp_send_json_error('No tienes permiso para eliminar esta etapa.');
  }

  // Eliminar imagen física si existe
  if (!empty($etapa->imagen) && file_exists($etapa->imagen)) {
    unlink($etapa->imagen);
  }

  // Obtener audios y eliminar archivos
  $audios = $wpdb->get_results($wpdb->prepare(
    "SELECT archivo FROM wa_bot_audios WHERE etapa_id = %d AND user_id = %d",
    $etapa_id, $user_id
  ));

  $audio_dir = "/home/whatsapp-audio-bot/audios_pregrabados/";

  foreach ($audios as $a) {
    $archivo_path = $audio_dir . $a->archivo;
    if (file_exists($archivo_path)) {
      unlink($archivo_path);
    }
  }

  // Eliminar registros
  $wpdb->delete('wa_bot_audios', ['etapa_id' => $etapa_id]);
  $wpdb->delete('wa_bot_etapas', ['id' => $etapa_id]);

  wp_send_json_success('Etapa eliminada correctamente.');
}

