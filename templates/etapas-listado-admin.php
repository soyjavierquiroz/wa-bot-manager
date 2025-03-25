<?php
if (!is_user_logged_in()) return;

global $wpdb;
$user_id = get_current_user_id();

// Obtener todas las etapas del usuario
$etapas = $wpdb->get_results(
  $wpdb->prepare("SELECT * FROM wa_bot_etapas WHERE user_id = %d ORDER BY created_at DESC", $user_id)
);
?>

<div class="container my-4">
  <h2 class="mb-4">Mis etapas</h2>

  <?php if (!$etapas): ?>
    <div class="alert alert-info">Aún no has creado ninguna etapa.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle table-hover">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($etapas as $etapa): ?>
            <tr id="etapa-<?php echo $etapa->id; ?>">
              <td><strong><?php echo esc_html($etapa->nombre); ?></strong></td>
              <td><?php echo esc_html(wp_trim_words($etapa->descripcion, 15)); ?></td>
              <td><?php echo date('d/m/Y H:i', strtotime($etapa->created_at)); ?></td>
              <td>
                <a href="<?php echo esc_url(add_query_arg('etapa_id', $etapa->id, site_url('/ver-etapa'))); ?>" class="btn btn-sm btn-info me-1">
                  Ver
                </a>
                <a href="<?php echo esc_url(add_query_arg('etapa_id', $etapa->id, site_url('/crear-etapa'))); ?>" class="btn btn-sm btn-warning me-1">
                  Editar
                </a>
                <button class="btn btn-sm btn-danger wa-eliminar-etapa-btn" data-id="<?php echo $etapa->id; ?>">
                  Eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
