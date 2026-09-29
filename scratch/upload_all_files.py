import ftplib
import os
import time

FTP_HOST = "5.9.79.107"
FTP_PORT = 21
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"

LOCAL_BASE = r"c:\xampp\htdocs\sistema - copia"
REMOTE_SISTEMA = "/sistema.empaqueslupa.com"

LOCAL_DIST = r"c:\xampp\htdocs\sistema - copia\weblupa\dist"
REMOTE_PUBLIC_HTML = "/public_html"

def get_ftp():
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    return ftp

def ensure_remote_dir(ftp, remote_dir_path):
    parts = remote_dir_path.strip("/").split("/")
    current = ""
    for part in parts:
        if not part:
            continue
        current += "/" + part
        try:
            ftp.cwd(current)
        except Exception:
            try:
                ftp.mkd(current)
            except Exception:
                pass

def upload_file(ftp, local_path, remote_path):
    remote_dir = os.path.dirname(remote_path)
    ensure_remote_dir(ftp, remote_dir)
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    print(f"[OK] {remote_path}")

def sync_all():
    print("=== INICIANDO SUBIDA TOTAL A FTP ===")
    ftp = get_ftp()

    # 1. Archivos Backend modificados
    backend_files = [
        r"app\Slider.php",
        r"app\Http\Controllers\SliderController.php",
        r"resources\assets\js\components\web\WebSliders.vue",
        r"public\js\app.js",
        r"public\index.php"
    ]

    print("\n--- Subiendo Archivos Backend ---")
    for rel in backend_files:
        local_p = os.path.join(LOCAL_BASE, rel)
        remote_p = (REMOTE_SISTEMA + "/" + rel.replace("\\", "/")).replace("//", "/")
        try:
            upload_file(ftp, local_p, remote_p)
        except Exception as e:
            print(f"[FALLO BACKEND] {rel}: {e}")

    # 2. Archivos Frontend (public_html)
    print("\n--- Subiendo Frontend Compilado (public_html) ---")
    dist_count = 0
    for root, dirs, files in os.walk(LOCAL_DIST):
        for file in files:
            local_path = os.path.join(root, file)
            rel_path = os.path.relpath(local_path, LOCAL_DIST)
            remote_path = (REMOTE_PUBLIC_HTML + "/" + rel_path.replace("\\", "/")).replace("//", "/")
            try:
                upload_file(ftp, local_path, remote_path)
                dist_count += 1
            except Exception as e:
                print(f"[FALLO DIST] {rel_path}: {e}")

    try:
        ftp.quit()
    except Exception:
        pass

    print(f"\n=== PROCESO DE SUBIDA COMPLETADO EXITOSAMENTE ===")
    print(f"Backend actualizados: {len(backend_files)}")
    print(f"Archivos frontend en public_html: {dist_count}")

if __name__ == "__main__":
    sync_all()
