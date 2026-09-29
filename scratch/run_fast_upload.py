import ftplib
import os
import sys
import time

FTP_HOST = "5.9.79.107"
FTP_PORT = 21
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"

LOCAL_DIST = r"c:\xampp\htdocs\sistema - copia\weblupa\dist"
REMOTE_TARGET = "/public_html"

def get_ftp():
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    return ftp

def sync():
    print("Iniciando subida rápida...", flush=True)
    ftp = get_ftp()
    count = 0
    for root, dirs, files in os.walk(LOCAL_DIST):
        for file in files:
            local_path = os.path.join(root, file)
            rel_path = os.path.relpath(local_path, LOCAL_DIST)
            remote_path = (REMOTE_TARGET + "/" + rel_path.replace("\\", "/")).replace("//", "/")
            remote_dir = os.path.dirname(remote_path)
            
            parts = remote_dir.strip("/").split("/")
            cur = ""
            for p in parts:
                if not p:
                    continue
                cur += "/" + p
                try:
                    ftp.cwd(cur)
                except Exception:
                    try:
                        ftp.mkd(cur)
                    except Exception:
                        pass

            for attempt in range(3):
                try:
                    with open(local_path, "rb") as f:
                        ftp.storbinary(f"STOR {remote_path}", f)
                    count += 1
                    print(f"[{count}] {rel_path}", flush=True)
                    break
                except Exception as e:
                    time.sleep(1)
                    try:
                        ftp = get_ftp()
                    except Exception:
                        pass

    try:
        ftp.quit()
    except Exception:
        pass

    print(f"=== SUBIDA FINALIZADA CON ÉXITO ({count} archivos) ===", flush=True)

if __name__ == "__main__":
    sync()
