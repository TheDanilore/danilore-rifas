<?php

namespace App\Observers;

use App\Models\Rifa;

class RifaObserver
{
    /**
     * Handle the Rifa "created" event.
     */
    public function created(Rifa $rifa): void
    {
        //
    }

    /**
     * Handle the Rifa "updated" event.
     */
    public function updated(Rifa $rifa): void
    {
        // Verificar si cambió el campo boletos_vendidos
        if ($rifa->isDirty('boletos_vendidos')) {
            // Actualizar automáticamente los progresos de premios
            $rifa->actualizarProgresosPremios();
        }
    }

    /**
     * Handle the Rifa "deleted" event.
     */
    public function deleted(Rifa $rifa): void
    {
        //
    }

    /**
     * Handle the Rifa "restored" event.
     */
    public function restored(Rifa $rifa): void
    {
        //
    }

    /**
     * Handle the Rifa "force deleted" event.
     */
    public function forceDeleted(Rifa $rifa): void
    {
        //
    }
}
