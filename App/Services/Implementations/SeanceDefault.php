<?php

namespace App\Services\Implementations;

use App\Models\Seance;
use App\Models\Seance2D;
use App\Models\Seance3D;
use App\Repositories\SeanceRepository;
use App\Services\Interfaces\SeanceService;
use ErrorException;

class SeanceDefault implements SeanceService
{
    private SeanceRepository $seanceRepository;

    public function __construct(SeanceRepository $seanceRepository = new SeanceRepository())
    {
        $this->seanceRepository = $seanceRepository;
    }

    public function getSeance(int $id): Seance
    {
        try {
            return $this->seanceRepository->findById($id);
        } catch (\Exception $e) {
            throw new ErrorException("Seance with ID $id not found.", 404);
        }
    }

    public function getSeances(?array $filters = null): array
    {
        return $this->seanceRepository->findAllSeances();
    }

    public function createSeance(array $data): Seance
    {
        if (!isset($data['film'], $data['horaire'], $data['prix'], $data['salle'], $data['placesDisponibles'], $data['type_seance'])) {
            throw new ErrorException("Missing required fields for seance creation.", 400);
        }

        if (!is_numeric($data['prix']) || $data['prix'] <= 0) {
            throw new ErrorException("Price must be a positive number.", 400);
        }

        if (!is_numeric($data['placesDisponibles']) || $data['placesDisponibles'] < 0) {
            throw new ErrorException("Available places must be a non-negative number.", 400);
        }

        if (!in_array($data['type_seance'], ['2D', '3D'])) {
            throw new ErrorException("Invalid seance type. Must be '2D' or '3D'.", 400);
        }

        $seanceData = [
            'film' => $data['film'],
            'horaire' => $data['horaire'],
            'prix' => (float)$data['prix'],
            'salle' => $data['salle'],
            'places_disponibles' => (int)$data['placesDisponibles'],
            'type_seance' => $data['type_seance']
        ];

        if ($data['type_seance'] === '2D') {
            if (!isset($data['qualiteImage'])) {
                throw new ErrorException("Missing 'qualiteImage' for 2D seance.", 400);
            }
            $seanceData['qualite_image'] = $data['qualiteImage'];
            $seanceData['technologie_3d'] = null; 
            $seanceData['lunettes_incluses'] = null;
        } elseif ($data['type_seance'] === '3D') {
            if (!isset($data['technologie3D'], $data['lunettesIncluses'])) {
                throw new ErrorException("Missing 'technologie3D' or 'lunettesIncluses' for 3D seance.", 400);
            }
            $seanceData['qualite_image'] = null; 
            $seanceData['technologie_3d'] = $data['technologie3D'];
            $seanceData['lunettes_incluses'] = filter_var($data['lunettesIncluses'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($seanceData['lunettes_incluses'])) {
                throw new ErrorException("'lunettesIncluses' must be a boolean value (true/false).", 400);
            }
        }

        $newSeanceId = $this->seanceRepository->save($seanceData);

        if ($newSeanceId > 0) {
            return $this->seanceRepository->findById($newSeanceId);
        }

        throw new ErrorException("Failed to create seance.", 500);
    }

    public function updateSeance(int $seanceId, array $data): Seance
    {
        $seance = $this->seanceRepository->findById($seanceId);

        if (!$seance) {
            throw new ErrorException("Seance with ID $seanceId not found.", 404);
        }

        $updateData = [];
        $allowedCommonFields = ['film', 'horaire', 'prix', 'salle', 'placesDisponibles'];

        foreach ($allowedCommonFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'prix' && (!is_numeric($data[$field]) || $data[$field] <= 0)) {
                    throw new ErrorException("Price must be a positive number.", 400);
                }
                if ($field === 'placesDisponibles' && (!is_numeric($data[$field]) || $data[$field] < 0)) {
                    throw new ErrorException("Available places must be a non-negative number.", 400);
                }
                $updateData[str_replace('Disponibles', '_disponibles', $field)] = $data[$field]; 
            }
        }

        if ($seance->getType() === '2D') {
            if (isset($data['qualiteImage'])) {
                $updateData['qualite_image'] = $data['qualiteImage'];
            }
        } elseif ($seance->getType() === '3D') {
            if (isset($data['technologie3D'])) {
                $updateData['technologie_3d'] = $data['technologie3D'];
            }

            if (isset($data['lunettesIncluses'])) {
                $updateData['lunettes_incluses'] = filter_var($data['lunettesIncluses'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (is_null($updateData['lunettes_incluses'])) {
                    throw new ErrorException("'lunettesIncluses' must be a boolean value (true/false).", 400);
                }
            }
        }
        
        if (isset($data['type_seance']) && $data['type_seance'] !== $seance->getType()) {
             throw new ErrorException("Changing seance type (2D/3D) is not allowed after creation.", 400);
        }


        if (empty($updateData)) {
            throw new ErrorException("No valid fields provided for update.", 400);
        }

        if ($this->seanceRepository->update($updateData, ['id' => $seanceId])) {
            return $this->seanceRepository->findById($seanceId);
        }

        throw new ErrorException("Failed to update seance.", 500);
    }

    public function deleteSeance(int $seanceId): Seance
    {
        $seanceToDelete = $this->seanceRepository->findById($seanceId);

        if (!$seanceToDelete) {
            throw new ErrorException("Seance with ID $seanceId not found.", 404);
        }

        if ($this->seanceRepository->delete(['id' => $seanceId])) {
            return $seanceToDelete;
        }

        throw new ErrorException("Failed to delete seance.", 500);
    }
    
    public function updatePlacesAvailable(int $seanceId, int $delta): Seance
    {
        $seance = $this->seanceRepository->findById($seanceId);

        if (!$seance) {
            throw new ErrorException("Seance with ID $seanceId not found.", 404);
        }

        $newPlaces = $seance->getPlacesDisponibles() + $delta;

        if ($newPlaces < 0) {
            throw new ErrorException("Not enough available places for seance with ID $seanceId.", 409);
        }

        if ($this->seanceRepository->updatePlacesAvailable($seanceId, $newPlaces)) {
            $seance->setPlacesDisponibles($newPlaces); 
            return $seance; 
        }

        throw new ErrorException("Failed to update available places for seance with ID $seanceId.", 500);
    }
}