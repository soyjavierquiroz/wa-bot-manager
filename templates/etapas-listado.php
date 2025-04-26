<?php
if (!is_user_logged_in()) {
  echo '<div class="alert alert-warning">Debes iniciar sesión para ver tus etapas.</div>';
  return;
}

global $wpdb;
$user_id = get_current_user_id();

$etapas = $wpdb->get_results(
  $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE user_id = %d ORDER BY created_at DESC", $user_id)
);
?>

<div class="container my-5">
  <h2 class="mb-4">Mis Etapas</h2>

  <?php if (empty($etapas)): ?>
    <div class="alert alert-info">Aún no has creado etapas.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($etapas as $etapa): ?>
            <tr data-etapa-id="<?php echo esc_attr($etapa->id); ?>">
              <td><?php echo esc_html($etapa->nombre); ?></td>
              <td><?php echo esc_html(wp_trim_words($etapa->descripcion, 10)); ?></td>
              <td><?php echo date('d M Y H:i', strtotime($etapa->created_at)); ?></td>
              <td class="text-end">
                <a href="<?php echo esc_url(add_query_arg('etapa_id', $etapa->id, site_url('/escritorio/contenidos/etapas/ver-etapa/'))); ?>" class="btn btn-sm btn-outline-secondary me-1">
                  <i class="bi bi-eye"></i> Ver
                </a>
                <a href="<?php echo esc_url(add_query_arg('editar_etapa', $etapa->id, site_url('/escritorio/contenidos/etapas/crear-etapa/'))); ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i> Editar
                </a>
                <button class="btn btn-sm btn-outline-danger wa-eliminar-etapa-btn">
                  <i class="bi bi-trash"></i> Eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Script de eliminación vía AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const deleteButtons = document.querySelectorAll('.wa-eliminar-etapa-btn');

  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      const etapaId = row.dataset.etapaId;

      if (!etapaId) return;

      if (!confirm('¿Seguro que deseas eliminar esta etapa? Esta acción eliminará imagen y audios asociados.')) {
        return;
      }

      btn.disabled = true;
      btn.innerText = 'Eliminando...';

      fetch(waBotManager.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'wa_eliminar_etapa',
          etapa_id: etapaId,
          _wpnonce: '<?php echo wp_create_nonce("wa_eliminar_etapa_nonce"); ?>'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          row.remove();

          const alert = document.createElement('div');
          alert.className = 'alert alert-success mt-3';
          alert.innerText = '✅ Etapa eliminada exitosamente.';

          const container = document.querySelector('.container');
          container.prepend(alert);

          setTimeout(() => alert.remove(), 5000); // Ocultar mensaje tras 5 seg
        }
        else {
          alert('❌ ' + data.data);
          btn.disabled = false;
          btn.innerText = 'Eliminar';
        }
      })
      .catch(() => {
        alert('❌ Error al eliminar. Intenta de nuevo.');
        btn.disabled = false;
        btn.innerText = 'Eliminar';
      });
    });
  });
});
</script>
