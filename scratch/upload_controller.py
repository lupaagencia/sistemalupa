import ftplib
import os

hosts = ["sistema.lupack.co", "lupack.co", "ftp.lupack.co", "sistema.empaqueslupa.com", "ftp.empaqueslupa.com", "5.9.79.107"]
FTP_PORT = 21
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"
REMOTE_BASE = "/sistema.empaqueslupa.com"

LOCAL_BASE = r"c:\xampp\htdocs\sistema - copia"

files_to_upload = [
    r"app\Http\Controllers\SuperadminIdeController.php",
]

def upload():
    ftp = None
    for host in hosts:
        for pasv in [True, False]:
            try:
                print(f"Conectando a FTP {host} (PASV={pasv})...")
                f = ftplib.FTP()
                f.connect(host, FTP_PORT, timeout=15)
                f.login(FTP_USER, FTP_PASS)
                f.set_pasv(pasv)
                ftp = f
                print(f"Conectado con éxito a {host}.")
                break
            except Exception as e:
                print(f"Falló {host} (PASV={pasv}): {e}")
        if ftp:
            break

    if not ftp:
        print("ERROR: No se pudo conectar a ningún host FTP.")
        return

    for rel_path in files_to_upload:
        local_file = os.path.join(LOCAL_BASE, rel_path)
        remote_path = REMOTE_BASE + "/" + rel_path.replace("\\", "/")
        print(f"Subiendo: {rel_path} -> {remote_path}")
        with open(local_file, "rb") as f:
            ftp.storbinary(f"STOR {remote_path}", f)
        print(f" OK: {rel_path}")

    ftp.quit()
    print("Proceso de subida rápida completado.")

if __name__ == "__main__":
    upload()
