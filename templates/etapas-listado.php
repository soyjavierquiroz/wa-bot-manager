<?php
if (!is_user_logged_in()) return;

global $wpdb;
$user_id = get_current_user_id();

$etapas = $wpdb->get_results(
  $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE user_id = %d ORDER BY created_at DESC", $user_id)
);

if (!$etapas) {
  echo '<div class="alert alert-info">No has creado ninguna etapa todavía.</div>';
  return;
}
?>

<h3 class="mb-4">Tus etapas creadas</h3>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
  <?php foreach ($etapas as $etapa): 
    $slug = esc_html($etapa->nombre);
    $textos = json_decode($etapa->textos ?? '[]');

    $imagen_url = null;
    if (!empty($etapa->imagen)) {
      $archivo = basename($etapa->imagen);
      $imagen_url = site_url("/bot-media/imagenes/{$archivo}");
    }

    $audios = $wpdb->get_results(
      $wpdb->prepare("SELECT * FROM wa_bot_audios WHERE user_id = %d AND etapa_id = %d ORDER BY orden ASC", $user_id, $etapa->id)
    );
  ?>
  <div class="col">
    <div class="card h-100 shadow-sm">
      <?php if ($imagen_url): ?>
        <img src="<?php echo esc_url($imagen_url); ?>" class="card-img-top" alt="Imagen de etapa">
      <?php endif; ?>
      <div class="card-body">
        <h5 class="card-title"><?php echo esc_html($etapa->nombre); ?></h5>
        <?php if ($etapa->descripcion): ?>
          <p class="card-text"><?php echo esc_html($etapa->descripcion); ?></p>
        <?php endif; ?>
        <?php if (!empty($textos)): ?>
          <ul class="list-group list-group-flush mb-2">
            <?php foreach ($textos as $txt): ?>
              <li class="list-group-item small"><?php echo esc_html($txt); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if ($audios): ?>
          <div class="mt-2">
            <strong>Audios:</strong>
            <?php foreach ($audios as $index => $a): 
              $audio_url = site_url('/bot-media/audios/' . urlencode($a->archivo));
            ?>
              <div class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-2">
                <span><strong>Audio <?php echo $index + 1; ?></strong></span>
                <button 
                  type="button"
                  class="btn btn-outline-primary btn-sm play-audio-btn" 
                  data-bs-toggle="modal" 
                  data-bs-target="#audioModal"
                  data-audio-url="<?php echo esc_url($audio_url); ?>">
                  <i class="bi bi-play-circle"></i> Reproducir
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-footer text-muted small">
        Creado el: <?php echo date('d M Y H:i', strtotime($etapa->created_at)); ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Modal global para reproducción de audio -->
<div class="modal fade" id="audioModal" tabindex="-1" aria-labelledby="audioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="audioModalLabel">Reproducir audio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <audio id="modalAudioPlayer" controls style="width: 100%;">
          <source id="modalAudioSource" src="" type="audio/mpeg">
          Tu navegador no soporta el elemento de audio.
        </audio>
      </div>
    </div>
  </div>
</div>

<!-- Script para activar reproducción en la modal -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const audioModal = document.getElementById('audioModal');
    const audioPlayer = document.getElementById('modalAudioPlayer');
    const audioSource = document.getElementById('modalAudioSource');

    document.querySelectorAll('.play-audio-btn').forEach(button => {
      button.addEventListener('click', function () {
        const audioUrl = this.getAttribute('data-audio-url');
        audioSource.src = audioUrl;
        audioPlayer.load();
        setTimeout(() => {
          audioPlayer.play();
        }, 300); // pequeño delay para evitar bloqueo en algunos navegadores
      });
    });

    audioModal.addEventListener('hidden.bs.modal', function () {
      audioPlayer.pause();
      audioPlayer.currentTime = 0;
    });
  });
</script>
