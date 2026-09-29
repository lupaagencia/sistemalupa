# Workspace Rules & Instructions - Sistema Empaques Lupa

## FTP Deployment & Credentials

Whenever the user asks to "subir archivos", "sube los archivos", "desplegar", "subir al FTP", or similar commands, use the stored FTP credentials to upload all updated project files to the production server.

### FTP Credentials
- **Host**: `sistema.empaqueslupa.com` (or `ftp.empaqueslupa.com`)
- **Port**: 21
- **Username**: `empaque1`
- **Password**: `3ZCwdN$AX*y4wEC`
- **Remote Base Path**: `/sistema.empaqueslupa.com/`

*(Alternative FTP account if needed)*:
- **Username**: `planta@empaqueslupa.com`
- **Password**: `c2ltVDNlVDE2KV8p`

### Rules for Uploading
1. When the user triggers the command **"subir archivos"** or **"sube los archivos"**, automatically detect all files that were modified during the session (PHP controllers, Vue components, compiled `public/js/app.js`, Blade views, migrations, etc.).
2. Execute a Python script using `ftplib` to connect to `sistema.empaqueslupa.com` on port 21, login with user `empaque1` and password `3ZCwdN$AX*y4wEC`, and upload the files to `/sistema.empaqueslupa.com/<relative_path>`.
3. Report the result of the upload to the user.
