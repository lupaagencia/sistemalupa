import ftplib
import os

FTP_HOST = "5.9.79.107"
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"

print("--- Uploading Backend Files ---")
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

backend_files = [
    ("app/Slider.php", "/sistema.empaqueslupa.com/app/Slider.php"),
    ("app/Http/Controllers/SliderController.php", "/sistema.empaqueslupa.com/app/Http/Controllers/SliderController.php"),
    ("resources/assets/js/components/web/WebSliders.vue", "/sistema.empaqueslupa.com/resources/assets/js/components/web/WebSliders.vue"),
    ("public/js/app.js", "/sistema.empaqueslupa.com/public/js/app.js")
]

for local_rel, remote_abs in backend_files:
    local_abs = os.path.abspath(local_rel)
    if os.path.exists(local_abs):
        remote_dir = os.path.dirname(remote_abs).replace('\\', '/')
        # Ensure remote dir exists
        dirs = [d for d in remote_dir.split('/') if d]
        curr = ""
        for d in dirs:
            curr += "/" + d
            try:
                ftp.cwd(curr)
            except:
                ftp.mkd(curr)
        
        with open(local_abs, 'rb') as f:
            ftp.storbinary(f"STOR {remote_abs}", f)
        print(f"[BACKEND OK] {local_rel} -> {remote_abs}")

ftp.quit()
print("Backend upload complete.")
