<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'id', 'org_id', 'member_id', 'locality_id', 'nro', 'nombre', 'telefono', 'order_by', 'numero', 'rut', 'sector'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class, 'locality_id');
    }
    
    public function organization()
    {
        return $this->belongsTo(Org::class, 'org_id');
    }
}
