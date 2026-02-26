<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'role'
    ];

    // 🔗 Relationships

    public function chat()
    {
        return $this->belongsTo(ChatSession::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}