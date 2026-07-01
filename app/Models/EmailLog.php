<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'to_email',
        'to_name',
        'cc_json',
        'bcc_json',
        'reply_to_json',
        'mailable_class',
        'template_key',
        'view_name',
        'subject',
        'mailer_name',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'provider_message_id',
        'payload_json',
        'meta_json',
        'related_type',
        'related_id',
        'triggered_by_user_id',
    ];

    protected $casts = [
        'cc_json' => 'array',
        'bcc_json' => 'array',
        'reply_to_json' => 'array',
        'payload_json' => 'array',
        'meta_json' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
