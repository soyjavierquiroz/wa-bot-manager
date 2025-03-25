<?php
if (!current_user_can('manage_options')) {
  wp_die('No tienes permisos para ver esta página.');
}

global $wpdb;

// Obtener usuarios con etapas
$usuarios = $wpdb->get_results("
  SELECT u.ID, u.display_name, u.user_email, COUNT(e.id) AS total_etapas
  FROM {$wpdb->users} u
  JOIN wa_bot_etapas e ON e.user_id = u.ID
  GROUP BY u.ID
  ORDER BY total_etapas DESC
");
?>
<div class="wrap">
  <h1 class="wp-heading-inline">Usuarios con Etapas</h1>

  <?php if (empty($usuarios)): ?>
    <p>No hay usuarios con etapas creadas.</p>
  <?php else: ?>
    <table class="widefat fixed striped">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Email</th>
          <th>Etapas</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?php echo esc_html($u->display_name); ?></td>
            <td><?php echo esc_html($u->user_email); ?></td>
            <td><?php echo intval($u->total_etapas); ?></td>
            <td>
              <a href="<?php echo admin_url('admin.php?page=wa-bot-manager-admin&ver_usuario=' . $u->ID); ?>" class="button button-small">
                👁 Ver etapas
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
// Ver etapas de un usuario específico
if (isset($_GET['ver_usuario'])) {
  $usuario_id = intval($_GET['ver_usuario']);
  $usuario = get_userdata($usuario_id);

  if (!$usuario) {
    echo '<div class="notice notice-error"><p>Usuario no encontrado.</p></div>';
  } else {
    $etapas_usuario = $wpdb->get_results(
      $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE user_id = %d ORDER BY created_at DESC", $usuario_id)
    );

    echo '<hr>';
    echo '<h2>Etapas de ' . esc_html($usuario->display_name) . '</h2>';

    if (empty($etapas_usuario)) {
      echo '<p>Este usuario no tiene etapas creadas.</p>';
    } else {
      echo '<table class="widefat fixed striped">';
      echo '<thead><tr><th>Nombre</th><th>Descripción</th><th>Fecha</th><th>Audios</th></tr></thead><tbody>';

      foreach ($etapas_usuario as $etapa) {
        $audios = $wpdb->get_results(
          $wpdb->prepare("SELECT archivo FROM wa_bot_audios WHERE etapa_id = %d ORDER BY orden ASC", $etapa->id)
        );

        echo '<tr>';
        echo '<td>' . esc_html($etapa->nombre) . '</td>';
        echo '<td>' . esc_html(wp_trim_words($etapa->descripcion, 10)) . '</td>';
        echo '<td>' . date('d M Y H:i', strtotime($etapa->created_at)) . '</td>';
        echo '<td><strong>' . count($audios) . '</strong></td>';
        echo '</tr>';

        if ($audios) {
          echo '<tr><td colspan="4" style="background:#f9f9f9;">';
          echo '<ul style="margin:0.5em 0 0 1em;">';

          foreach ($audios as $index => $audio) {
            $audio_url = esc_url(site_url('/bot-media/audios/' . urlencode($audio->archivo)));
            echo '<li style="margin-bottom: 6px;">';
            echo 'Audio ' . ($index + 1) . ' ';
            echo '<button class="button button-small reproducir-audio" data-audio-src="' . $audio_url . '">▶️ Reproducir</button>';
            echo '</li>';
          }

          echo '</ul>';
          echo '</td></tr>';
        }
      }

      echo '</tbody></table>';
    }
  }
}
?>

<!-- Reproductor global oculto -->
<audio id="adminAudioPlayer" style="display:none; margin-top: 20px;" controls></audio>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const audioPlayer = document.getElementById('adminAudioPlayer');

  document.querySelectorAll('.reproducir-audio').forEach(btn => {
    btn.addEventListener('click', function () {
      const src = this.getAttribute('data-audio-src');
      audioPlayer.style.display = 'block';
      audioPlayer.src = src;
      audioPlayer.load();
      audioPlayer.play();
      window.scrollTo({
        top: audioPlayer.offsetTop - 100,
        behavior: 'smooth'
      });
    });
  });
});
</script>
