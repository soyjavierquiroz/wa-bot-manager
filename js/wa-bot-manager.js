jQuery(document).ready(function ($) {
  $('#wa-etapa-form').on('submit', function (e) {
    e.preventDefault();

    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');
    var $mensaje = $('#wa-form-mensaje');
    $mensaje.empty();

    $btn.prop('disabled', true);
    $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Guardando etapa...');

    // 🔎 Validación de audios
    let errores = [];
    const audios = $('input[type="file"][name="audios[]"]');
    let audiosValidos = 0;

    audios.each(function () {
      const file = this.files[0];
      if (!file) return;

      const ext = file.name.split('.').pop().toLowerCase();
      const maxSize = 2 * 1024 * 1024;

      if (ext !== 'mp3') {
        errores.push(`⚠️ El archivo "${file.name}" no es un MP3 válido.`);
        return;
      }

      if (file.size > maxSize) {
        errores.push(`⚠️ El archivo "${file.name}" excede el tamaño permitido (2MB).`);
        return;
      }

      audiosValidos++;
    });

    if (audiosValidos === 0) {
      errores.push('⚠️ Debes subir al menos 1 audio MP3 válido.');
    }

    if (errores.length > 0) {
      $btn.prop('disabled', false).html('Guardar etapa');
      $mensaje.html('<div class="alert alert-danger">' + errores.join('<br>') + '</div>');
      return false;
    }

    // 🔄 Continuar con validación de nombre y envío AJAX
    let nombreOriginal = $('#etapa_nombre').val();
    let nombreSlug = nombreOriginal.trim().toLowerCase()
      .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
      .replace(/\s+/g, "_").replace(/[^a-z0-9_]/g, "");

    $.post(waBotManager.ajaxurl, {
      action: 'wa_check_etapa_existente',
      nombre: nombreSlug
    }, function (response) {
      if (response.success && response.data.existe) {
        $btn.prop('disabled', false).html('Guardar etapa');
        $mensaje.html('<div class="alert alert-warning">⚠️ Ya existe una etapa con ese nombre. Por favor elige otro.</div>');
      } else {
        let formData = new FormData($form[0]);

        $.ajax({
          url: window.location.href,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
            if (response.success) {
              $btn.prop('disabled', false).html('Guardar etapa');
              $mensaje.html('<div class="alert alert-success">✅ Etapa guardada correctamente.</div>');
              $form[0].reset();
            } else {
              let errorMsg = response.data || '❌ Error inesperado al guardar la etapa.';
              $btn.prop('disabled', false).html('Guardar etapa');
              $mensaje.html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
          },
          error: function (xhr, status, error) {
            $btn.prop('disabled', false).html('Guardar etapa');
            $mensaje.html('<div class="alert alert-danger">❌ Error AJAX: ' + error + '</div>');
          }
        });
      }
    });
  });
});
