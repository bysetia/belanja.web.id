<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Chatify\Traits\UUID;

class ChMessage extends Model
{
    use UUID;
    protected $table = 'ch_messages';
    
      
    protected $fillable = ['from_id', 'to_id', 'body', 'attachment', 'seen', 'created_at', 'updated_at'];
    
      public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_id', 'id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_id', 'id');
    }
}
