<?php

namespace App\Repositories;

use App\Models\Client;
use Core\Facades\RepositoryMutations;
use PDO;

class ClientRepository extends RepositoryMutations
{
    public function __construct()
    {
        parent::__construct('clients');
    }

    protected function mapper(array $data): object
    {
        $id = $this->get($data, 'id');
        $name = $this->get($data, 'name');
        $email = $this->get($data, 'email');
        $phone = $this->get($data, 'phone');

        return new Client($id, $name, $email, $phone);
    }

    public function findAllClients(): array
    {
        $stmt = $this->db->getPdo()->query("SELECT * FROM $this->tableName;");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->arrayMapper($data); 
    }

    public function findById(int $id): Client
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM $this->tableName WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            throw new \Exception("Client with ID $id not found.");
        }
        return $this->mapper($data);
    }

    public function findByEmail(string $email): ?Client
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM {$this->tableName} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapper($data);
    }

    public function countTicketsByClient(int $clientId): int
    {
        $stmt = $this->db->getPdo()->prepare("SELECT COUNT(*) FROM tickets WHERE client_id = :client_id AND statut IN ('VENDU', 'RÉSERVÉ')");
        $stmt->execute(['client_id' => $clientId]);
        return (int) $stmt->fetchColumn();
    }
}