<?php

namespace App\Controllers;

use Core\Contracts\ResourceController;
use Core\Controller;
use App\Services\Implementations\TicketDefault;
use App\Services\Interfaces\TicketService;    
use Core\Decorators\Description;
use Core\Decorators\Route;
use ErrorException;

#[Route('/api/v1')] 
class TicketController extends Controller implements ResourceController
{
    private TicketService $ticketService;

    public function __construct()
    {
        parent::__construct();
        $this->ticketService = new TicketDefault();
    }

    #[Description("Récupère la liste de tous les tickets.")]
    public function index()
    {
        try {
            $tickets = $this->ticketService->getTickets($this->request->param());
            $this->json($tickets);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 500);
        } 
    }

    #[Description("Affiche les détails d'un ticket en utilisant son identifiant.")]
    public function show($id)
    {
        try {
            $ticket = $this->ticketService->getTicket((int)$id);
            $this->json($ticket);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404);
        }
    }

    #[Description("Crée un nouveau ticket de cinéma.")]
    public function store()
    {
        try {
            $data = $this->request->all();

            if (!isset($data['clientId'], $data['seanceId'], $data['nombrePlaces'])) {
                throw new ErrorException("Client ID, Seance ID, and number of places are required fields.", 400);
            }

            $clientId = (int)$data['clientId'];
            $seanceId = (int)$data['seanceId'];
            $nombrePlaces = (int)$data['nombrePlaces'];

            $newTicket = $this->ticketService->createTicket($clientId, $seanceId, $nombrePlaces);
            $this->json($newTicket, 201);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Description("Met à jour les informations d'un ticket.")]
    public function update($id)
    {
        try {
            $data = $this->request->all();
            
            if (empty($data)) {
                throw new ErrorException("No data provided for update.", 400);
            }

            $updatedTicket = $this->ticketService->updateTicket((int)$id, $data);
            $this->json($updatedTicket);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Description("Supprime un ticket à partir de son identifiant.")]
    public function destroy($id)
    {
        try {
            $deletedTicket = $this->ticketService->deleteTicket((int)$id);
            $this->json($deletedTicket);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404);
        }
    }
}