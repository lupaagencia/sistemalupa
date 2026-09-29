<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividad';
    protected $fillable =['user_id','fecha','hora','actividad'];
    public function user()
    {
        $user=$this->belongsTo('App\User');
        return $user;
    }
}
