<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_doc',
        'num_doc',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'telefono',
        'direccion',
        'correo',
        'estado_civil',
        'num_hijos',
        'cargo',
        'area',
        'tipo_contrato',
        'fecha_ingreso',
        'fecha_finalizacion',
        'salario',
        'auxilio_transporte',
        'tipo_jornada',
        'turno',
        'horas_semanales',
        'num_cuenta_banco',
        'banco',
        'tipo_cuenta',
        'num_afiliacion_social',
        'pension',
        'eps',
        'arl',
        'nivel_estudio',
        'titulos',
        'certificaciones',
        'idiomas',
        'habilidades',
        'Experiencia',
        'foto',
        'talla_dotacion',
        'img_doc',
        'pdf_hoja',
        'pdf_contrato',
        'contacto_esposa',
        'contacto_padres',
        'contacto_emergencia',
        'info_medica',
        'codigo_qr',
        'turno_id',
        'huella_dactilar'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id', 'id');
    }

    public function turnoAsignado()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }
}
