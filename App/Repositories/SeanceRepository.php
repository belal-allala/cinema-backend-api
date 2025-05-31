<?php

namespace App\Repositories;

use App\Models\Seance;
use App\Models\Seance2D;
use App\Models\Seance3D;
use Core\Facades\RepositoryMutations;
use PDO;

class SeanceRepository extends RepositoryMutations
{
    public function __construct()
    {
        parent::__construct('seances');
    }

    protected function mapper(array $data): object
    {
        $id = $this->get($data, 'id');
        $film = $this->get($data, 'film');
        $horaire = $this->get($data, 'horaire');
        $prix =  $this->get($data, 'prix');
        $salle = $this->get($data, 'salle');
        $placesDisponibles = (int) $this->get($data, 'places_disponibles');
        $typeSeance = $this->get($data, 'type_seance');

        if ($typeSeance === '2D') {
            $qualiteImage = $this->get($data, 'qualite_image');
            return new Seance2D($id, $film, $horaire, $prix, $salle, $placesDisponibles, $qualiteImage);
        } elseif ($typeSeance === '3D') {
            $technologie3D = $this->get($data, 'technologie_3d');
            $lunettesIncluses = (bool) $this->get($data, 'lunettes_incluses'); 
            return new Seance3D($id, $film, $horaire, $prix, $salle, $placesDisponibles, $technologie3D, $lunettesIncluses);
        }
    }

    public function findAllSeances(): array
    {
        $stmt = $this->db->getPdo()->query("SELECT * FROM $this->tableName;");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->arrayMapper($data);
    }

    public function findById(int $id): Seance
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM $this->tableName WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception("Seance with ID $id not found.");
        }

        return $this->mapper($data);
    }
    
    public function updatePlacesAvailable(int $seanceId, int $newPlaces): bool
    {
        return $this->update(['places_disponibles' => $newPlaces], ['id' => $seanceId]);
    }
}