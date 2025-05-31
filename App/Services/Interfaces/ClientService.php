<?php

namespace App\Services\Interfaces;

use App\Models\Client;

interface ClientService
{
    public function getClient(int $id): Client;

    public function getClients(?array $filters = null): array;

    public function createClient(string $name, string $email, ?string $phone): Client;

    public function updateClient(int $clientId, array $data): Client;

    public function deleteClient(int $clientId): Client;
}