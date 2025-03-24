jQuery(document).ready(function ($) {
    $('#wa-etapa-form').on('submit', function () {
      var $btn = $(this).find('button[type="submit"]');
      $btn.prop('disabled', true);
      $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando etapa...');
    });
  });
  