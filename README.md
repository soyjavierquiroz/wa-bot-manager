# WA Bot Manager

**Versión:** 1.5.0  
**Desarrollado por:** [Soy Javier Quiroz](https://github.com/SoyJavierQuiroz)  
**Compatibilidad:** WordPress 5.0+ | Bootstrap 5 | Thrive Architect compatible

---

## 🧩 ¿Qué es este plugin?

`wa-bot-manager` permite a usuarios registrados (vía MemberPress) **crear, ver, editar y administrar etapas personalizadas** que alimentan un bot de WhatsApp llamado `whatsapp-audio-bot` (Node.js).  
Todo desde el frontend mediante shortcodes, con validaciones, subida de archivos y control seguro desde base de datos.

---

## 🚀 Funcionalidades

### ✍️ Gestión de etapas desde el frontend:
- **[wa_bot_etapas]** – Formulario para crear/editar etapa
- **[wa_bot_listado]** – Tabla con tus etapas creadas (Ver / Editar / Eliminar)
- **[wa_bot_ver_etapa]** – Vista de detalle de etapa por ID

### 🧾 Cada etapa incluye:
- Nombre único (por usuario)
- Descripción opcional
- Textos planos (hasta 5)
- Textos enriquecidos (hasta 5)
- Imagen JPG/PNG opcional (máx. 1MB)
- Audios MP3 (1–5, máx. 2MB c/u)

### 🔐 Validaciones y seguridad:
- Nonces en cada acción
- Verificación de propiedad del recurso
- Verificación AJAX de nombres duplicados
- Rollback automático si no se sube al menos 1 audio válido
- Eliminación completa: base de datos y archivos físicos
- Límite configurable de etapas por usuario (por defecto: 10)

### 📂 Archivos se guardan en:
/home/whatsapp-audio-bot/audios_pregrabados/{user_id}{slug}{n}.mp3
/home/whatsapp-audio-bot/imagenes_etapa/{user_id}_{slug}.jpg

pgsql
Copiar
Editar

---

## 🗄️ Base de Datos

### Tabla `wa_bot_etapas`
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
Tabla wa_bot_audios
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
☁️ Instalación
bash
Copiar
Editar
git clone https://github.com/SoyJavierQuiroz/wa-bot-manager.git wp-content/plugins/wa-bot-manager
Activa el plugin desde WordPress

Crea 3 páginas y añade los shortcodes:

/crear-etapa/ → [wa_bot_etapas]

/ver-etapa/ → [wa_bot_ver_etapa]

/mis-etapas/ → [wa_bot_listado]

🧪 Probado con:
MemberPress

Thrive Architect

Bootstrap 5

LiteSpeed + CyberPanel

Node.js Bot (en producción)

🛡️ Buenas Prácticas
Archivos se eliminan si se borra la etapa

Nonces en AJAX

Permisos de usuario controlados

Validación de extensión y tamaño de archivos

Sanitización exhaustiva

🆕 Mejoras en v1.5
🔄 Reorganización completa en 3 páginas frontend

✅ Edición completa con precarga de datos

🧹 Eliminación segura con validación y confirmación visual

🔒 Verificación de propiedad de etapa

💾 Control de límite por usuario (10 etapas)

🧼 Script extra para buscar archivos huérfanos

🎧 Mejora en carga y gestión de audios existentes

¿Pull requests, ideas o bugs?
¡Bienvenidos!