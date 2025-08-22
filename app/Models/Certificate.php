<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'certificate_number',
        'file_name',
        'file_path',
        'issue_date',
        'valid_until',
        'type',
        'status',
        'issued_by',
        // Nuevos campos agregados
        'client_name',
        'client_ruc',
        'address',
        'city',
        'phone',
        'treated_area',
        'desinsectacion',
        'desinfeccion',
        'desratizacion',
        'otro_servicio',
        'producto_desinsectacion',
        'categoria_desinsectacion',
        'registro_desinsectacion',
        'producto_desinfeccion',
        'categoria_desinfeccion',
        'registro_desinfeccion',
        'producto_desratizacion',
        'categoria_desratizacion',
        'registro_desratizacion',
        'producto_otro',
        'categoria_otro',
        'registro_otro',
        'service_description',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
    ];

    // Certificate statuses
    const STATUS_VALID = 'valid';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';
    const STATUS_PENDING = 'pending';

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship with issuer (User)
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Check if certificate is valid
     */
    public function isValid()
    {
        return $this->status === self::STATUS_VALID && 
               $this->valid_until >= now();
    }

    /**
     * Check if certificate is expiring soon (within 30 days)
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->status === self::STATUS_VALID && 
               $this->valid_until <= now()->addDays($days);
    }
}