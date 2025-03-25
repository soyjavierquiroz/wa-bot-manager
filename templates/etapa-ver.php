<?php
if (!is_user_logged_in()) {
  echo '<div class="alert alert-warning">Debes iniciar sesión para ver esta etapa.</div>';
  return;
}

global $wpdb;
$user_id = get_current_user_id();

// Validar ID por URL
$etapa_id = isset($_GET['etapa_id']) ? intval($_GET['etapa_id']) : 0;
if (!$etapa_id) {
  echo '<div class="alert alert-danger">Etapa no especificada.</div>';
  return;
}

// Verificar propiedad de la etapa
$etapa = $wpdb->get_row(
  $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE id = %d AND user_id = %d", $etapa_id, $user_id)
);

if (!$etapa) {
  echo '<div class="alert alert-danger">No se encontró la etapa o no tienes permiso para verla.</div>';
  return;
}

// Decodificar
$textos = json_decode($etapa->textos ?? '[]');
$textos_html = json_decode($etapa->textos_html ?? '[]');

// Imagen
$imagen_url = null;
if (!empty($etapa->imagen)) {
  $archivo = basename($etapa->imagen);
  $imagen_url = site_url("/bot-media/imagenes/{$archivo}");
}

// Audios
$audios = $wpdb->get_results(
  $wpdb->prepare("SELECT * FROM wa_bot_audios WHERE etapa_id = %d AND user_id = %d ORDER BY orden ASC", $etapa->id, $user_id)
);
?>

<div class="container my-5">
  <h2 class="mb-4"><?php echo esc_html($etapa->nombre); ?></h2>

  <?php if ($imagen_url): ?>
    <div class="mb-4 text-center">
      <img src="<?php echo esc_url($imagen_url); ?>" class="img-fluid rounded shadow-sm" alt="Imagen de la etapa">
    </div>
  <?php endif; ?>

  <?php if (!empty($etapa->descripcion)): ?>
    <p class="lead"><?php echo esc_html($etapa->descripcion); ?></p>
  <?php endif; ?>

  <?php if (!empty($textos)): ?>
    <hr>
    <h5>Textos</h5>
    <ul class="list-group mb-3">
      <?php foreach ($textos as $txt): ?>
        <li class="list-group-item"><?php echo esc_html($txt); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if (!empty($textos_html)): ?>
    <h5>Textos enriquecidos</h5>
    <ul class="list-group mb-4">
      <?php foreach ($textos_html as $html): ?>
        <li class="list-group-item"><?php echo wp_kses_post($html); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if (!empty($audios)): ?>
    <h5>Audios</h5>
    <div class="row g-3">
      <?php foreach ($audios as $index => $a): 
        $audio_url = site_url('/bot-media/audios/' . urlencode($a->archivo));
      ?>
        <div class="col-md-6">
          <div class="card p-3 shadow-sm h-100">
            <p class="mb-2"><strong>Audio <?php echo $index + 1; ?></strong></p>
            <audio controls style="width: 100%;">
              <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
              Tu navegador no soporta el elemento de audio.
            </audio>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
