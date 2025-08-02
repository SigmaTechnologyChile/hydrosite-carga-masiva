<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteService216 extends Command
{
    protected $signature = 'service:delete-216';
    protected $description = 'Elimina todas las lecturas del servicio 216';

    public function handle()
    {
        $this->info('=== ELIMINACIÓN DEL SERVICIO 216 ===');

        try {
            // 1. Buscar el servicio 216
            $servicio = DB::table('services')->where('nro', 216)->first();
            
            if (!$servicio) {
                $this->error('❌ Servicio 216 no encontrado.');
                return 1;
            }
            
            $this->info("✅ Servicio 216 encontrado:");
            $this->info("   - ID: {$servicio->id}");
            $this->info("   - Member ID: {$servicio->member_id}");
            $this->info("   - Org ID: {$servicio->org_id}");
            
            // 2. Obtener información del miembro
            $member = DB::table('members')->where('id', $servicio->member_id)->first();
            if ($member) {
                $this->info("   - Cliente: {$member->full_name}");
                $this->info("   - RUT: {$member->rut}");
            }
            
            // 3. Contar lecturas existentes
            $lecturas = DB::table('readings')->where('service_id', $servicio->id)->count();
            $this->info("   - Lecturas registradas: {$lecturas}");
            
            if ($lecturas > 0) {
                // 4. Confirmar eliminación
                if ($this->confirm("¿Está seguro de eliminar {$lecturas} lecturas del servicio 216?")) {
                    $this->info('🗑️ Eliminando lecturas del servicio 216...');
                    
                    DB::beginTransaction();
                    try {
                        $deletedReadings = DB::table('readings')->where('service_id', $servicio->id)->delete();
                        DB::commit();
                        
                        $this->info("✅ Se eliminaron {$deletedReadings} lecturas.");
                        
                        // Log para auditoria
                        Log::info("Eliminación manual del servicio 216 completada", [
                            'service_id' => $servicio->id,
                            'member_name' => $member->full_name ?? 'N/A',
                            'member_rut' => $member->rut ?? 'N/A',
                            'deleted_readings' => $deletedReadings
                        ]);
                        
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("❌ Error al eliminar: " . $e->getMessage());
                        return 1;
                    }
                } else {
                    $this->info('Operación cancelada.');
                    return 0;
                }
            } else {
                $this->info('✅ No hay lecturas para eliminar.');
            }
            
            $this->info('✅ PROCESO COMPLETADO');
            $this->info('El servicio 216 ya no aparecerá en las vistas.');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
