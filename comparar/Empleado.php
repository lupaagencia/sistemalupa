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
        'info_medica'


    ];
    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id', 'id');
    }
}
