<?php
require_once 'bootstrap/app.php';

$app = app();

echo "Verificando organizaciones:\n";
$orgs = \App\Models\Org::select('id', 'name')->get();
foreach($orgs as $org) {
    echo "ID: {$org->id} - Nombre: {$org->name}\n";
}

echo "\nVerificando notificaciones:\n";
$notifications = \App\Models\Notification::select('org_id')->distinct()->get();
foreach($notifications as $notification) {
    echo "Notificaciones para org_id: {$notification->org_id}\n";
}

echo "\nContando notificaciones por organización:\n";
$counts = \App\Models\Notification::selectRaw('org_id, count(*) as total')->groupBy('org_id')->get();
foreach($counts as $count) {
    echo "Org ID {$count->org_id}: {$count->total} notificaciones\n";
}
?>
