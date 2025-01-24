<?php

namespace App\Services;

use Illuminate\Contracts\Foundation\MaintenanceMode;

class MaintenanceModeService implements MaintenanceMode
{
    public function active(): bool
    {
        return false; // Altere para "true" se quiser ativar o modo manutenção.
    }

    public function data(): array
    {
        return []; // Dados adicionais do modo manutenção.
    }

    public function activate(array $payload): void
    {
        // Implementação se necessário.
    }

    public function deactivate(): void
    {
        // Implementação se necessário.
    }
}
