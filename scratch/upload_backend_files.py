import ftplib
import os

FTP_HOST = "5.9.79.107"
FTP_PORT = 21
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"
REMOTE_BASE = "/sistema.empaqueslupa.com"
LOCAL_BASE = r"c:\xampp\htdocs\sistema - copia"

files = [
    r"app\Slider.php",
    r"app\Http\Controllers\SliderController.php",
    r"resources\assets\js\components\web\WebSliders.vue",
    r"routes\web.php",
    r"public\js\app.js",
    r"public\index.php"
]

ftp = ftplib.FTP()
ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
ftp.login(FTP_USER, FTP_PASS)

for rel in files:
    local_path = os.path.join(LOCAL_BASE, rel)
    remote_path = (REMOTE_BASE + "/" + rel.replace("\\", "/")).replace("//", "/")
    remote_dir = os.path.dirname(remote_path)
    parts = remote_dir.strip("/").split("/")
    cur = ""
    for p in parts:
        cur += "/" + p
        try:
            ftp.cwd(cur)
        except Exception:
            try:
                ftp.mkd(cur)
            except Exception:
                pass
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    print(f"Uploaded: {remote_path}")

ftp.quit()
print("Backend upload complete!")
