<?php
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🧪 Probando configuración de email...\n";
    
    // Crear un objeto mock para testing
    $org = (object) ['name' => 'Hydrosite Test'];
    $member = (object) ['email' => 'cristianenriquecarvajal@gmail.com', 'full_name' => 'Cristian Test'];
    
    Mail::to('cristianenriquecarvajal@gmail.com')->send(new NotificationMail(
        'Test Email Hydrosite',
        'Este es un email de prueba desde el sistema Hydrosite',
        $org,
        $member
    ));
    
    echo "✅ Email enviado exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
