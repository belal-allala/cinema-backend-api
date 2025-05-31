<?php

namespace App\Services\Implementations;

use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Services\Interfaces\ClientService;
use ErrorException;

class ClientDefault implements ClientService
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository = new ClientRepository())
    {
        $this->clientRepository = $clientRepository;
    }

    public function getClient(int $id): Client
    {
        try {
            return $this->clientRepository->findById($id);
        } catch (\Exception $e) {
            throw new ErrorException("Client with ID $id not found.", 404);
        }
    }

    public function getClients(?array $filters = null): array
    {
        return $this->clientRepository->findAllClients();
    }

    public function createClient(string $name, string $email, ?string $phone): Client
    {
        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];

        $newClientId = $this->clientRepository->save($data); 

        if ($newClientId > 0) { 
            return $this->clientRepository->findById($newClientId);
        }
        throw new ErrorException("Failed to create client.", 500); 
    }

    public function updateClient(int $clientId, array $data): Client
    {
        $client = $this->clientRepository->findById($clientId); 

        if (!$client) {
            throw new ErrorException("Client with ID $clientId not found.", 404);
        }

        $updateData = [];
        $allowedFields = ['name', 'email', 'phone'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new ErrorException("No valid fields provided for update.", 400);
        }

        if ($this->clientRepository->update($updateData, ['id' => $clientId])) {
            return $this->clientRepository->findById($clientId); 
        }

        throw new ErrorException("Failed to update client.", 500);
    }

    public function deleteClient(int $clientId): Client
    {
        $clientToDelete = $this->clientRepository->findById($clientId); 

        if (!$clientToDelete) {
            throw new ErrorException("Client with ID $clientId not found.", 404);
        }

        if ($this->clientRepository->delete(['id' => $clientId])) {
            return $clientToDelete;
        }

        throw new ErrorException("Failed to delete client.", 500);
    }
}