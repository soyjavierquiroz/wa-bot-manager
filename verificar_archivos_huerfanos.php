<?php
require_once('wp-load.php'); // Ajusta la ruta si pones este archivo en otro lugar

global $wpdb;

// Audios
$audio_dir = '/home/whatsapp-audio-bot/audios_pregrabados/';
$archivos = glob($audio_dir . '*.mp3');
$db_audios = $wpdb->get_col("SELECT archivo FROM wa_bot_audios");

echo "<h3>Audios huérfanos:</h3><ul>";
foreach ($archivos as $archivo_path) {
  $archivo = basename($archivo_path);
  if (!in_array($archivo, $db_audios)) {
    echo "<li>$archivo</li>";
    // unlink($archivo_path); // ← Descomenta si quieres eliminar automáticamente
  }
}
echo "</ul>";

// Imágenes
$img_dir = '/home/whatsapp-audio-bot/imagenes_etapa/';
$imagenes = glob($img_dir . '*.{jpg,jpeg,png}', GLOB_BRACE);
$db_imagenes = $wpdb->get_col("SELECT imagen FROM wa_bot_etapas");

echo "<h3>Imágenes huérfanas:</h3><ul>";
foreach ($imagenes as $img_path) {
  if (!in_array($img_path, $db_imagenes)) {
    echo "<li>" . basename($img_path) . "</li>";
    // unlink($img_path); // ← Descomenta si quieres eliminar automáticamente
  }
}
echo "</ul>";
