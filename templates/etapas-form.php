<?php
if (!is_user_logged_in()) {
  echo '<div class="alert alert-warning">Debes iniciar sesión para gestionar tus etapas.</div>';
  return;
}
$current_user_id = get_current_user_id();
?>

<?php if (isset($_GET['wa_etapa_status']) && $_GET['wa_etapa_status'] === 'ok'): ?>
  <div class="alert alert-success">✅ Etapa guardada correctamente.</div>
<?php endif; ?>

<div class="container my-5">
  <h2 class="mb-4">Crear nueva etapa</h2>
  <form id="wa-etapa-form" class="wa-form" enctype="multipart/form-data" method="post">
    <div class="mb-3">
      <label for="etapa_nombre" class="form-label">Nombre de la etapa *</label>
      <input type="text" class="form-control" id="etapa_nombre" name="etapa_nombre" required>
    </div>

    <div class="mb-3">
      <label for="etapa_descripcion" class="form-label">Descripción</label>
      <textarea class="form-control" id="etapa_descripcion" name="etapa_descripcion" rows="2"></textarea>
    </div>

    <div class="mb-3">
      <label for="etapa_imagen" class="form-label">Imagen (opcional, JPG/PNG, máx. 1MB)</label>
      <input type="file" class="form-control" id="etapa_imagen" name="etapa_imagen" accept=".jpg,.jpeg,.png">
    </div>

    <hr>
    <h5>Textos planos</h5>
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="mb-3">
        <label for="texto_<?php echo $i; ?>" class="form-label">Texto plano <?php echo $i; ?><?php echo $i === 1 ? ' *' : ''; ?></label>
        <input type="text" class="form-control" id="texto_<?php echo $i; ?>" name="textos[]" <?php echo $i === 1 ? 'required' : ''; ?>>
      </div>
    <?php endfor; ?>

    <hr>
    <h5>Textos enriquecidos</h5>
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="mb-3">
        <label for="texto_html_<?php echo $i; ?>" class="form-label">Texto enriquecido <?php echo $i; ?><?php echo $i === 1 ? ' *' : ''; ?></label>
        <textarea class="form-control" id="texto_html_<?php echo $i; ?>" name="textos_html[]" rows="2" <?php echo $i === 1 ? 'required' : ''; ?>></textarea>
      </div>
    <?php endfor; ?>

    <hr>
    <h5>Audios (al menos 1 requerido)</h5>
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <div class="mb-3">
        <label for="audio_<?php echo $i; ?>" class="form-label">Audio <?php echo $i; ?> <?php echo $i === 1 ? '*' : ''; ?></label>
        <input type="file" class="form-control" id="audio_<?php echo $i; ?>" name="audios[]" accept="audio/mp3" <?php echo $i === 1 ? 'required' : ''; ?>>
      </div>
    <?php endfor; ?>

    <input type="hidden" name="wa_etapa_nonce" value="<?php echo wp_create_nonce('guardar_etapa'); ?>">

    <button type="submit" class="btn btn-primary">Guardar etapa</button>
  </form>

   <div id="wa-form-mensaje" class="mt-4"></div>
</div>
