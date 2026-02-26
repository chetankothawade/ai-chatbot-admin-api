<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageMetadata extends Model
{
    use HasFactory;

    protected $table = 'message_metadata';

    protected $fillable = [
        'message_id',
        'type',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    // 🔗 Relationship

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}