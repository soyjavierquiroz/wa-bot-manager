# WA Bot Manager

Plugin de WordPress para gestionar etapas personalizadas para un bot de WhatsApp en Node.js.

Version: 1.3.0
Autor: @soyjavierquiroz


📁 Requisitos del servidor para wa-bot-manager
Para que el plugin pueda guardar los recursos correctamente:

📦 Carpetas del bot:
/home/whatsapp-audio-bot/audios_pregrabados/

/home/whatsapp-audio-bot/imagenes_etapa/

👤 Usuario PHP requerido:
El proceso PHP de WordPress debe tener permisos de escritura

En CyberPanel/OpenLiteSpeed, este suele ser el usuario asignado en el bloque extprocessor del vHost

✅ Permisos sugeridos:
bash
Copiar
Editar
chown -R [USUARIO_PHP]:nogroup /home/whatsapp-audio-bot/audios_pregrabados
chown -R [USUARIO_PHP]:nogroup /home/whatsapp-audio-bot/imagenes_etapa
chmod -R 755 /home/whatsapp-audio-bot/audios_pregrabados
chmod -R 755 /home/whatsapp-audio-bot/imagenes_etapa
🔐 Seguridad:
No se requieren symlinks ni acceso web directo a estas carpetas

No es necesario modificar open_basedir ni permitir allowSymbolLink en producción

