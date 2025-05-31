<?php

namespace App\Services\Interfaces;

use App\Models\Seance; 
use App\Models\Seance2D;
use App\Models\Seance3D;

interface SeanceService
{
    public function getSeance(int $id): Seance;

    public function getSeances(?array $filters = null): array;

    public function createSeance(array $data): Seance;

    public function updateSeance(int $seanceId, array $data): Seance;

    public function deleteSeance(int $seanceId): Seance;

    public function updatePlacesAvailable(int $seanceId, int $delta): Seance;
}