# WA Bot Manager

**Versión:** 1.3.0  
**Autor:** [@soyjavierquiroz](https://github.com/soyjavierquiroz)  
**Descripción:**  
Plugin de WordPress que permite a cada usuario registrado (vía MemberPress) gestionar sus propias etapas personalizadas para un bot de WhatsApp basado en Node.js. Las etapas incluyen texto, audio e imagen, y se almacenan directamente en las carpetas del bot y la base de datos compartida.

---

## 🧩 Funcionalidades principales

- Crear, editar y eliminar etapas desde el frontend (mediante shortcode).
- Validación de archivos:
  - Imagen JPG/PNG (opcional, máx. 1MB)
  - 5 textos planos (mínimo 1 requerido)
  - 5 textos enriquecidos (mínimo 1 requerido)
  - 5 audios MP3 (mínimo 1 requerido, máx. 2MB cada uno)
- Guardado de archivos en el servidor del bot.
- Integración directa con base de datos MySQL del bot.
- Interfaz amigable con Bootstrap 5 y compatible con Thrive Architect.
- Feedback visual con loader al enviar formulario.

---

## 🛠️ Requisitos técnicos

### 📌 Servidor

- WordPress 6.0+
- PHP 7.4 o superior
- MySQL (compartida con el bot)
- Servidor con acceso a carpetas del bot (`/home/whatsapp-audio-bot`)

### 📦 Estructura de carpetas del bot

Debe existir:

/home/whatsapp-audio-bot/ ├── imagenes_etapa/ └── audios_pregrabados/

bash
Copiar
Editar

### 👤 Permisos necesarios

El usuario que ejecuta PHP (por ejemplo: `appku5709` en CyberPanel) debe tener permisos de escritura:

```bash
chown -R appku5709:nogroup /home/whatsapp-audio-bot/audios_pregrabados
chown -R appku5709:nogroup /home/whatsapp-audio-bot/imagenes_etapa
chmod -R 755 /home/whatsapp-audio-bot/audios_pregrabados
chmod -R 755 /home/whatsapp-audio-bot/imagenes_etapa
⚙️ Instalación
Clonar el repositorio o descargar como ZIP

Subir la carpeta wa-bot-manager a wp-content/plugins/

Activar el plugin desde el administrador de WordPress

🧪 Uso
En cualquier página o entrada, agregar el shortcode:

csharp
Copiar
Editar
[wa_bot_etapas]
Esto mostrará el formulario para crear etapas, solo visible para usuarios logueados.

📤 Estructura en base de datos
Tabla wa_bot_etapas:

sql
Copiar
Editar
CREATE TABLE IF NOT EXISTS wa_bot_etapas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT,
  textos JSON DEFAULT NULL,
  textos_html JSON DEFAULT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Tabla wa_bot_audios:

sql
Copiar
Editar
CREATE TABLE IF NOT EXISTS wa_bot_audios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  etapa_id INT NOT NULL,
  nombre_archivo VARCHAR(255) NOT NULL,
  orden INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
🔄 Versionado
Este repositorio usa versionado semántico (SemVer).

Versión actual: v1.3.0
📋 To-Do / Próximas funciones
 Listar etapas creadas por el usuario

 Editar o eliminar etapas

 Lógica para llamadas desde Node.js al endpoint /send-audio

 Panel de administración para moderadores (opcional)

 Logs de actividad y control de errores

🧠 Créditos
Desarrollado por @soyjavierquiroz
Bot WhatsApp base: whatsapp-audio-bot

🔒 Licencia
Este plugin es de uso privado o comunitario (dependiendo del objetivo final). Podés agregar una licencia MIT o GPL si decidís abrirlo a otros.