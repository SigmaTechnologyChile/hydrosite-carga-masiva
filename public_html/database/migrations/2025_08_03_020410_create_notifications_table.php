<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status')->default('draft'); // draft, sent, failed
            $table->string('target_type')->nullable(); // all, sectors, users
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            
            // Métodos de envío
            $table->string('send_method')->default('email'); // app, email, sms
            $table->boolean('send_app')->default(false);
            $table->boolean('send_email')->default(true);
            $table->boolean('send_sms')->default(false);
            
            // Estados por método
            $table->string('app_status')->nullable(); // pending, sent, failed, read
            $table->string('email_status')->nullable(); // pending, sent, failed
            $table->string('sms_status')->nullable(); // pending, sent, failed
            
            // Timestamps de envío
            $table->timestamp('app_sent_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamp('app_read_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            
            // Errores
            $table->text('app_error')->nullable();
            $table->text('email_error')->nullable();
            $table->text('sms_error')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['org_id', 'created_at']);
            $table->index(['org_id', 'email_status']);
            $table->index(['recipient_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
