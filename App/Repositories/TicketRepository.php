<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Models\Client;
use App\Models\Seance;
use Core\Facades\RepositoryMutations;
use PDO;
use Exception;

class TicketRepository extends RepositoryMutations
{
    private ClientRepository $clientRepository;
    private SeanceRepository $seanceRepository;

    public function __construct( ClientRepository $clientRepository = new ClientRepository(), SeanceRepository $seanceRepository = new SeanceRepository())
    {
        parent::__construct('tickets');
        $this->clientRepository = $clientRepository;
        $this->seanceRepository = $seanceRepository;
    }

    protected function mapper(array $data): object
    {
        $id = $this->get($data, 'id');
        $clientId = (int) $this->get($data, 'client_id');
        $seanceId = (int) $this->get($data, 'seance_id');
        $nombrePlaces = (int) $this->get($data, 'nombre_places');
        $montantTotal = (float) $this->get($data, 'montant_total');
        $statut = $this->get($data, 'statut');
        $client = null;
        $seance = null;

        try {
            $client = $this->clientRepository->findById($clientId);
        } catch (Exception $e) {
            error_log("Client with ID $clientId not found for ticket $id: " . $e->getMessage());
        }
        
        try {
            $seance = $this->seanceRepository->findById($seanceId);
        } catch (Exception $e) {
            error_log("Seance with ID $seanceId not found for ticket $id: " . $e->getMessage());
        }

        return new Ticket($id, $clientId, $seanceId, $nombrePlaces, $montantTotal, $statut, $client, $seance);
    }

    public function findAllTickets(): array
    {
        $stmt = $this->db->getPdo()->query("SELECT * FROM $this->tableName;");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->arrayMapper($data);
    }

    public function findById(int $id): Ticket
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM $this->tableName WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception("Ticket with ID $id not found.");
        }

        return $this->mapper($data);
    }

    public function countClientPlacesForSeance(int $clientId, int $seanceId): int
    {
        $stmt = $this->db->getPdo()->prepare("SELECT COALESCE(SUM(nombre_places), 0) FROM {$this->tableName} WHERE client_id = :client_id AND seance_id = :seance_id AND statut IN ('VENDU', 'RÉSERVÉ')");
        $stmt->execute(['client_id' => $clientId, 'seance_id' => $seanceId]);
        return (int) $stmt->fetchColumn();
    }
}