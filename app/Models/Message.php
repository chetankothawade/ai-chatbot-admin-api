<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false; // only created_at used

    protected $fillable = [
        'chat_id',
        'role',
        'content',
        'tokens',
        'response_time_ms',
        'parent_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    // 🔗 Relationships

    public function chat()
    {
        return $this->belongsTo(ChatSession::class, 'chat_id');
    }

    public function metadata()
    {
        return $this->hasMany(MessageMetadata::class);
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }
}