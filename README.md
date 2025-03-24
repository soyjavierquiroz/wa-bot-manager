# WA Bot Manager

**Versión:** 1.3.0  
**Desarrollado por:** [Soy Javier Quiroz](https://github.com/SoyJavierQuiroz)  
**Compatibilidad:** WordPress 5.0+ | Bootstrap 5 | Thrive Architect compatible

---

## 🧩 ¿Qué es este plugin?

`wa-bot-manager` permite a usuarios registrados (vía MemberPress) **crear, editar y administrar etapas personalizadas** que alimentan un bot de WhatsApp llamado `whatsapp-audio-bot` (Node.js).  
Todo desde un formulario amigable en el frontend, con validaciones, subida de archivos y almacenamiento en MySQL.

---

## 🚀 Funcionalidades

✅ Formulario vía shortcode `[wa_bot_etapas]`  
✅ Crea etapas con:
- Nombre único por usuario
- Descripción (opcional)
- Hasta 5 textos planos (1 requerido)
- Hasta 5 textos enriquecidos (1 requerido)
- 1 imagen JPG/PNG (opcional, máx. 1MB)
- 1–5 audios MP3 (al menos 1 requerido, máx. 2MB c/u)

✅ Subida de archivos a carpetas del bot:  
/home/whatsapp-audio-bot/imagenes_etapa/{user_id}{etapa}.jpg
/home/whatsapp-audio-bot/audios_pregrabados/{user_id}{etapa}_{n}.mp3

pgsql
Copiar
Editar

✅ Validación en tiempo real de nombres duplicados  
✅ UX con Bootstrap 5 + Loader + Alertas AJAX  
✅ Código robusto y seguro (sanitización, nonces, límites)

---

## 🗄️ Base de Datos

### `wa_bot_etapas`
```sql
CREATE TABLE wa_bot_etapas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT,
  textos JSON DEFAULT NULL,
  textos_html JSON DEFAULT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
wa_bot_audios
sql
Copiar
Editar
CREATE TABLE wa_bot_audios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  etapa_id INT NOT NULL,
  nombre_archivo VARCHAR(255) NOT NULL,
  orden INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
🔧 Requisitos del servidor
PHP 7.4+

WordPress con acceso a /wp-content/plugins

Acceso de escritura a las rutas del bot:

/home/whatsapp-audio-bot/audios_pregrabados

/home/whatsapp-audio-bot/imagenes_etapa

⚠️ Asegúrate de que el usuario PHP (ej. appku5709) tenga permisos de escritura en esas carpetas.

☁️ Migración a otro servidor
Copiar el plugin a /wp-content/plugins/wa-bot-manager

Restaurar las tablas wa_bot_etapas y wa_bot_audios

Crear y ajustar las carpetas del bot con permisos correctos

Verificar que el docRoot en OpenLiteSpeed permita acceso desde PHP al sistema de archivos

Si es necesario, crear symlinks en wp-content/uploads/bot_audios

📦 Instalación
Clonar el repo:

bash
Copiar
Editar
git clone https://github.com/SoyJavierQuiroz/wa-bot-manager.git wp-content/plugins/wa-bot-manager
Activar el plugin desde el administrador de WordPress

Insertar el shortcode [wa_bot_etapas] en una página Thrive

🧪 Testing
Este plugin ha sido probado con:

MemberPress (restricción de acceso)

Thrive Architect

Bootstrap 5

LiteSpeed + CyberPanel

Node.js bot funcionando en /home/whatsapp-audio-bot/

🛡️ Seguridad y Buenas Prácticas
Nonces verificados en cada solicitud

Sanitización exhaustiva (sanitize_text_field, wp_kses_post, etc.)

Validaciones de MIME, tamaño y extensión de archivos

Rollback automático si no se sube al menos 1 audio

Rutas personalizadas no expuestas al navegador

🔜 Próximas versiones
Listado dinámico de etapas por usuario

Edición de etapas existentes

Borrado seguro con verificación

Webhook al bot tras guardar

Soporte multilenguaje

¿Dudas, sugerencias o pull requests? ¡Bienvenidos!
Este plugin es parte de la arquitectura de automatización de whatsapp-audio-bot.