<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rifa;

class SincronizarProgresosPremios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'premios:sincronizar {--rifa= : ID específico de rifa para sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los progresos de premios para todas las rifas o una específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rifaId = $this->option('rifa');
        
        if ($rifaId) {
            // Sincronizar una rifa específica
            $rifa = Rifa::find($rifaId);
            if (!$rifa) {
                $this->error("No se encontró la rifa con ID: {$rifaId}");
                return 1;
            }
            
            $this->info("Sincronizando progreso de premios para rifa ID: {$rifaId}");
            $rifa->actualizarProgresosPremios();
            $this->info("✅ Sincronización completada para rifa: {$rifa->nombre}");
            
        } else {
            // Sincronizar todas las rifas
            $rifas = Rifa::with('premios.niveles')->get();
            $total = $rifas->count();
            
            $this->info("Sincronizando progresos de premios para {$total} rifas...");
            
            $bar = $this->output->createProgressBar($total);
            $bar->start();
            
            foreach ($rifas as $rifa) {
                $rifa->actualizarProgresosPremios();
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("✅ Sincronización completada para todas las rifas");
        }
        
        return 0;
    }
}
