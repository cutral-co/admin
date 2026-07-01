<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->json('cc_json')->nullable();
            $table->json('bcc_json')->nullable();
            $table->json('reply_to_json')->nullable();

            $table->string('mailable_class');
            $table->string('template_key');
            $table->string('view_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('mailer_name')->nullable();

            $table->string('status')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('provider_message_id')->nullable();

            $table->json('payload_json')->nullable();
            $table->json('meta_json')->nullable();

            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('triggered_by_user_id')->nullable();

            $table->timestamps();

            $table->index('to_email');
            $table->index('template_key');
            $table->index('created_at');
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
