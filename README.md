# WA Bot Manager

**Versión:** 1.6  
**Desarrollado por:** [Soy Javier Quiroz](https://github.com/SoyJavierQuiroz)  
**Compatibilidad:** WordPress 5.0+ | Bootstrap 5 | Thrive Architect compatible

---

## 📌 ¿Qué es este plugin?

`wa-bot-manager` permite a usuarios registrados (vía MemberPress) **crear, editar y administrar etapas personalizadas** que alimentan un bot de WhatsApp llamado `whatsapp-audio-bot` (Node.js).  
Todo desde un formulario amigable en el frontend **y desde un panel de administración en el backend**.

---

## 🚀 Funcionalidades Principales

✅ Formulario vía shortcode `[wa_bot_etapas]`  
✅ Listado de etapas personales: `[wa_bot_listado]`  
✅ Vista pública de una etapa: `[wa_bot_ver_etapa]`  
✅ Panel de administración (admin menu `WA Bot Admin`)  

---

## 🎯 Características

- Nombre único por usuario
- Descripción opcional
- Hasta 5 textos planos + 5 enriquecidos
- Imagen opcional (JPG/PNG, máx. 1MB)
- Hasta 5 audios MP3 (máx. 2MB c/u)
- Validación de nombre duplicado
- Validación AJAX de archivos
- Subida de archivos a carpetas externas del bot

---

## 🗃️ Base de Datos

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
  archivo VARCHAR(255) NOT NULL,
  orden INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
📁 Archivos subidos
swift
Copiar
Editar
/home/whatsapp-audio-bot/imagenes_etapa/{user_id}_{slug}.jpg
/home/whatsapp-audio-bot/audios_pregrabados/{user_id}_{slug}_{n}.mp3
🔐 Seguridad y Buenas Prácticas
✅ Nonces en cada solicitud
✅ Verificación de propiedad del recurso
✅ Sanitización de entradas (textos, HTML, archivos)
✅ Rollback si falla subida de audios
✅ Protección contra accesos directos al sistema de archivos

🧪 Testing
Probado con:

WordPress 6.x

MemberPress

Thrive Architect

Bootstrap 5

LiteSpeed + CyberPanel

Node.js Bot

📦 Instalación
bash
Copiar
Editar
git clone https://github.com/SoyJavierQuiroz/wa-bot-manager.git wp-content/plugins/wa-bot-manager
Actívalo desde el panel de WordPress
Agrega shortcodes en las páginas correspondientes:

[wa_bot_etapas] en /crear-etapa/

[wa_bot_listado] en /mis-etapas/

[wa_bot_ver_etapa] en /ver-etapa/

🔮 Próximas versiones
Filtro por fecha en admin

Exportación CSV

Webhooks al bot

Soporte multilenguaje