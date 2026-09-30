import ftplib
import os

hosts = ["sistema.lupack.co", "lupack.co", "ftp.lupack.co", "sistema.empaqueslupa.com", "ftp.empaqueslupa.com", "5.9.79.107"]
FTP_PORT = 21
FTP_USER = "empaque1"
FTP_PASS = "3ZCwdN$AX*y4wEC"
REMOTE_BASE = "/sistema.empaqueslupa.com"

LOCAL_BASE = r"c:\xampp\htdocs\sistema - copia"

files_to_upload = [
    r"routes\web.php",
    r"app\Http\Controllers\ComprobanteController.php",
    r"app\Http\Controllers\OrdentrabajoController.php",
    r"app\Http\Controllers\StatusProduccionController.php",
    r"app\Http\Controllers\CrmCotizacionController.php",
    r"app\Http\Controllers\AsistenciaController.php",
    r"app\Empleado.php",
    r"app\CrmCotizacionDetalle.php",
    r"resources\views\pdf\cotizacion.blade.php",
    r"resources\assets\js\components\DetallesOrden.vue",
    r"resources\assets\js\components\KioscoPantalla.vue",
    r"resources\assets\js\components\Empleados.vue",
    r"resources\assets\js\components\empleado\NuevoEmpleado.vue",
    r"resources\assets\js\components\Pedidos.vue",
    r"resources\assets\js\components\partes\Pedido.vue",
    r"resources\assets\js\components\partes\customProducto.vue",
    r"resources\assets\js\components\partes\Ordencard.vue",
    r"resources\assets\js\components\partes\SelectorColor.vue",
    r"resources\assets\js\components\Articulo.vue",
    r"resources\assets\js\components\crm\CrmCotizaciones.vue",
    r"resources\assets\js\components\partes\Cotizacion.vue",
    r"resources\assets\js\components\partes\Cotizador0.vue",
    r"resources\assets\js\components\partes\CotizadorR.vue",
    r"resources\assets\js\components\partes\CotizadorG.vue",
    r"resources\assets\js\components\partes\CotizadorM.vue",
    r"resources\assets\js\components\partes\CotizadorS.vue",
    r"app\Http\Controllers\SuperadminIdeController.php",
    r"AGENTS.md",
    r"public\js\app.js",
    r"public\0.js",
    r"public\1.js",
    r"public\colorsc.json",
    r"public\colorsp.json",
    r"public\colorsu.json",
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
        
        if not os.path.exists(local_file):
            print(f"ERROR: Local file no existe: {local_file}")
            continue

        remote_dir = os.path.dirname(remote_path)
        parts = remote_dir.split('/')
        path_acc = ""
        for part in parts:
            if not part:
                continue
            path_acc += "/" + part
            try:
                ftp.cwd(path_acc)
            except Exception:
                try:
                    ftp.mkd(path_acc)
                except Exception as e:
                    pass

        print(f"Subiendo: {rel_path} -> {remote_path}")
        with open(local_file, "rb") as f:
            ftp.storbinary(f"STOR {remote_path}", f)
        print(f" OK: {rel_path}")

    ftp.quit()
    print("Proceso de subida completado.")

if __name__ == "__main__":
    upload()
