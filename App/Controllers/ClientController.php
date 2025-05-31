<?php

namespace App\Controllers;

use Core\Contracts\ResourceController;
use Core\Controller;
use App\Services\Implementations\ClientDefault; 
use App\Services\Interfaces\ClientService;  
use Core\Decorators\Description;
use Core\Decorators\Route;
use ErrorException; 

#[Route('/api/v1')]
class ClientController extends Controller implements ResourceController
{
    private ClientService $clientService;

    public function __construct()
    {
        parent::__construct();
        $this->clientService = new ClientDefault();
    }

    #[Description("Récupère la liste de tous les clients.")]
    public function index()
    {
        try {
            $clients = $this->clientService->getClients($this->request->param());
            $this->json($clients); 
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 500);
        } 
    }

    #[Description("Affiche les détails d'un client en utilisant son identifiant.")]
    public function show($id)
    {
        try {
            $client = $this->clientService->getClient((int)$id); 
            $this->json($client);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404); 
        } 
    }

    #[Description("Crée un nouvel client.")]
    public function store()
    {
        try {
            $data = $this->request->all();
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'] ?? null;
            $newClient = $this->clientService->createClient($name, $email, $phone);
            $this->json($newClient, 201);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400); 
        } 
    }

    #[Description("Met à jour les informations d'un client.")]
    public function update($id)
    {
        try {
            $data = $this->request->all();
            $updatedClient = $this->clientService->updateClient((int)$id, $data);
            $this->json($updatedClient);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Description("Supprime un client à partir de son identifiant.")]
    public function destroy($id)
    {
        try {
            $deletedClient = $this->clientService->deleteClient((int)$id);
            $this->json($deletedClient);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404);
        } 
    }
}