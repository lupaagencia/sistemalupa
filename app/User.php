<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'empleado_id','usuario', 'password','condicion','idrol','notificar_ventas','email'
    ];
    
    public $timestamps = false;
    public function comprobante(){
        return $this->hasOne('App\Comprobante');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'condicion' => 'integer',
        'notificar_ventas' => 'integer',
    ];

    public function rol(){
        return $this->belongsTo(Rol::class,'idrol', 'nombre');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id', 'id');
    }

    public function isSuperadmin()
    {
        return $this->idrol === 'Superadministrador';
    }

    public function isAdmin()
    {
        return in_array($this->idrol, ['Administrador', 'Superadministrador']);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}