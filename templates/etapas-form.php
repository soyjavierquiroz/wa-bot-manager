<?php
if (!is_user_logged_in()) {
  echo '<div class="alert alert-warning">Debes iniciar sesión para gestionar tus etapas.</div>';
  return;
}

global $wpdb;
$current_user_id = get_current_user_id();

$modo_editar = false;
$etapa = null;
$textos = $textos_html = [];

if (isset($_GET['editar_etapa'])) {
  $etapa_id = intval($_GET['editar_etapa']);
  $etapa = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE id = %d AND user_id = %d", $etapa_id, $current_user_id)
  );
  if ($etapa) {
    $modo_editar = true;
    $textos = json_decode($etapa->textos ?? '[]');
    $textos_html = json_decode($etapa->textos_html ?? '[]');
  }
}
?>

<div class="container my-5">
  <h2 class="mb-4"><?php echo $modo_editar ? 'Editar etapa' : 'Crear nueva etapa'; ?></h2>

  <?php if (isset($_GET['wa_etapa_status']) && $_GET['wa_etapa_status'] === 'ok'): ?>
    <div class="alert alert-success">✅ Etapa guardada correctamente.</div>
  <?php endif; ?>

  <form id="wa-etapa-form" method="post" enctype="multipart/form-data" class="wa-form">
    <?php if ($modo_editar): ?>
      <input type="hidden" name="etapa_id" value="<?php echo esc_attr($etapa->id); ?>">
    <?php endif; ?>

    <div class="mb-3">
      <label for="etapa_nombre" class="form-label">Nombre de la etapa *</label>
      <input type="text" class="form-control" id="etapa_nombre" name="etapa_nombre"
        value="<?php echo $modo_editar ? esc_attr($etapa->nombre) : ''; ?>" required>
    </div>

    <div class="mb-3">
      <label for="etapa_descripcion" class="form-label">Descripción</label>
      <textarea class="form-control" id="etapa_descripcion" name="etapa_descripcion" rows="6"><?php
        echo $modo_editar ? esc_textarea($etapa->descripcion) : ''; ?></textarea>
    </div>

    <div class="mb-3">
      <label for="etapa_imagen" class="form-label">Imagen <?php echo $modo_editar ? '(reemplazar actual)' : ''; ?> (JPG/PNG, máx. 1MB)</label>
      <input type="file" class="form-control" id="etapa_imagen" name="etapa_imagen" accept=".jpg,.jpeg,.png">
      <?php if ($modo_editar && !empty($etapa->imagen)): ?>
        <?php
          $archivo = basename($etapa->imagen);
          $url_imagen = site_url('/bot-media/imagenes/' . $archivo);
        ?>
        <div class="mt-2">
          <img src="<?php echo esc_url($url_imagen); ?>" class="img-thumbnail" alt="Imagen actual" width="150">
        </div>
      <?php endif; ?>

    </div>

    <hr>
    <h5>Textos planos</h5>
    <?php for ($i = 0; $i < 5; $i++): ?>
      <div class="mb-3">
        <label for="texto_<?php echo $i + 1; ?>" class="form-label">Texto plano <?php echo $i + 1; ?><?php echo $i === 0 ? ' *' : ''; ?></label>
        <input type="text" class="form-control" id="texto_<?php echo $i + 1; ?>" name="textos[]"
          value="<?php echo $modo_editar && isset($textos[$i]) ? esc_attr($textos[$i]) : ''; ?>"
          <?php echo $i === 0 ? 'required' : ''; ?>>
      </div>
    <?php endfor; ?>

    <hr>
    <h5>Textos enriquecidos</h5>
    <?php for ($i = 0; $i < 5; $i++): ?>
      <div class="mb-3">
        <label for="texto_html_<?php echo $i + 1; ?>" class="form-label">Texto enriquecido <?php echo $i + 1; ?><?php echo $i === 0 ? ' *' : ''; ?></label>
        <textarea class="form-control" id="texto_html_<?php echo $i + 1; ?>" name="textos_html[]" rows="10" <?php echo $i === 0 ? 'required' : ''; ?>><?php
          echo $modo_editar && isset($textos_html[$i]) ? esc_textarea($textos_html[$i]) : ''; ?></textarea>
      </div>
    <?php endfor; ?>

    <hr>
    <h5>Audios <?php echo !$modo_editar ? '(al menos 1 requerido)' : '(puedes agregar o eliminar)' ?></h5>

    <?php if ($modo_editar && !empty($etapa->id)) :
      $audios_existentes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM wa_bot_audios WHERE etapa_id = %d AND user_id = %d ORDER BY orden ASC", $etapa->id, $current_user_id)
      );
      if ($audios_existentes): ?>
        <div class="mb-3">
          <label class="form-label">Audios actuales:</label>
          <ul class="list-group">
            <?php foreach ($audios_existentes as $audio): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-music me-2"></i><?php echo esc_html($audio->archivo); ?></span>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="eliminar_audios[]" value="<?php echo esc_attr($audio->id); ?>" id="del-audio-<?php echo $audio->id; ?>">
                  <label class="form-check-label small" for="del-audio-<?php echo $audio->id; ?>">
                    Eliminar
                  </label>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
    <?php endif; endif; ?>

    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="mb-3">
        <label for="audio_<?php echo $i; ?>" class="form-label">Audio <?php echo $i; ?><?php echo (!$modo_editar && $i === 1) ? ' *' : ''; ?></label>
        <input type="file" class="form-control" id="audio_<?php echo $i; ?>" name="audios[]" accept="audio/mp3" <?php echo (!$modo_editar && $i === 1) ? 'required' : ''; ?>>
      </div>
    <?php endfor; ?>

    <input type="hidden" name="wa_etapa_nonce" value="<?php echo wp_create_nonce('guardar_etapa'); ?>">

    <button type="submit" class="btn btn-primary"><?php echo $modo_editar ? 'Actualizar etapa' : 'Guardar etapa'; ?></button>
  </form>

  <div id="wa-form-mensaje" class="mt-4"></div>
</div>
