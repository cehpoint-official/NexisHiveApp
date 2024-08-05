<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedNotification extends Model
{
    use HasFactory;
    public $table = "saved_notifications";

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function post()
    {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }

}
