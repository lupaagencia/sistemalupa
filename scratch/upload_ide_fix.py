import ftplib
import os

FTP_HOST = "sistema.empaqueslupa.com"
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"

files = [
    (r"app\Http\Controllers\SuperadminIdeController.php", "/sistema.empaqueslupa.com/app/Http/Controllers/SuperadminIdeController.php"),
    (r"public\js\app.js", "/sistema.empaqueslupa.com/public/js/app.js"),
    (r"resources\assets\js\components\superadmin\WebIde.vue", "/sistema.empaqueslupa.com/resources/assets/js/components/superadmin/WebIde.vue")
]

print("Conectando FTP...")
ftp = ftplib.FTP()
ftp.connect(FTP_HOST, 21, timeout=30)
ftp.login(FTP_USER, FTP_PASS)

for local_path, remote_path in files:
    print(f"Subiendo {local_path} -> {remote_path}...")
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    print(f"OK: {local_path}")

ftp.quit()
print("¡SUBIDA COMPLETADA CON ÉXITO!")
