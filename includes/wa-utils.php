<?php

if (!defined('ABSPATH')) exit;

// Verifica si la etapa es del usuario actual
function wa_etapa_belongs_to_user($etapa_id, $user_id = null) {
    global $wpdb;
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    return (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wa_bot_etapas WHERE id = %d AND user_id = %d",
        $etapa_id, $user_id
    ));
}

// Devuelve la etapa por ID si es del usuario
function wa_get_etapa($etapa_id) {
    global $wpdb;
    $user_id = get_current_user_id();

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM wa_bot_etapas WHERE id = %d AND user_id = %d",
        $etapa_id, $user_id
    ));
}

// Devuelve audios por etapa
function wa_get_audios($etapa_id) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wa_bot_audios WHERE etapa_id = %d ORDER BY orden ASC",
        $etapa_id
    ));
}

// Devuelve la URL accesible de una imagen
function wa_etapa_media_url($tipo, $archivo) {
    return site_url("/bot-media/{$tipo}/" . urlencode($archivo));
}
