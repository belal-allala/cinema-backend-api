<?php

namespace App\Services\Implementations;

use App\Models\Ticket;
use App\Repositories\TicketRepository;
use App\Services\Interfaces\TicketService;
use App\Services\Interfaces\ClientService;
use App\Services\Interfaces\SeanceService;
use ErrorException;

class TicketDefault implements TicketService
{
    private const TVA_RATE = 0.20; //  TVA (20%)
    private const MAX_PLACES_PER_SEANCE_FOR_CLIENT = 5; // 5 places

    private TicketRepository $ticketRepository;
    private ClientService $clientService;
    private SeanceService $seanceService;

    public function __construct(TicketRepository $ticketRepository = new TicketRepository(), ClientService $clientService = new ClientDefault(), SeanceService $seanceService = new SeanceDefault()) 
    {
        $this->ticketRepository = $ticketRepository;
        $this->clientService = $clientService;
        $this->seanceService = $seanceService;
    }

    public function getTicket(int $id): Ticket
    {
        try {
            return $this->ticketRepository->findById($id);
        } catch (\Exception $e) {
            throw new ErrorException("Ticket with ID $id not found.", 404);
        }
    }

    public function getTickets(?array $filters = null): array
    {
        return $this->ticketRepository->findAllTickets();
    }

    public function createTicket(int $clientId, int $seanceId, int $nombrePlaces): Ticket
    {
        if ($nombrePlaces <= 0 || $nombrePlaces > self::MAX_PLACES_PER_SEANCE_FOR_CLIENT) {
            throw new ErrorException("Number of places must be between 1 and " . self::MAX_PLACES_PER_SEANCE_FOR_CLIENT . " for a single purchase.", 400);
        }

        $client = $this->clientService->getClient($clientId); 
        $seance = $this->seanceService->getSeance($seanceId);

        $currentPlacesForClientInThisSeance = $this->ticketRepository->countClientPlacesForSeance($clientId, $seanceId);
        if (($currentPlacesForClientInThisSeance + $nombrePlaces) > self::MAX_PLACES_PER_SEANCE_FOR_CLIENT) {
            throw new ErrorException("Client has already purchased or reserved " . $currentPlacesForClientInThisSeance . " places for this seance. Maximum allowed is " . self::MAX_PLACES_PER_SEANCE_FOR_CLIENT . " places per client per seance.", 409); 
        }

        if ($seance->getPlacesDisponibles() < $nombrePlaces) {
            throw new ErrorException("Not enough available places for this seance. Only " . $seance->getPlacesDisponibles() . " places left.", 409); 
        }

        // calculer le montant total
        $pricePerPlace = $seance->getCalculatedPrice(); 
        $subtotal = $nombrePlaces * $pricePerPlace;
        $montantTVA = $subtotal * self::TVA_RATE;
        $montantTotal = $subtotal + $montantTVA;

        // mettre à jour les places disponibles 
        $this->seanceService->updatePlacesAvailable($seanceId, -$nombrePlaces);

        $data = [
            'client_id' => $clientId,
            'seance_id' => $seanceId,
            'nombre_places' => $nombrePlaces,
            'montant_total' => $montantTotal,
            'statut' => 'VENDU'
        ];

        $newTicketId = $this->ticketRepository->save($data);

        if ($newTicketId > 0) {
            return $this->ticketRepository->findById($newTicketId);
        }

        throw new ErrorException("Failed to create ticket.", 500);
    }

    public function updateTicket(int $ticketId, array $data): Ticket
    {
        $ticket = $this->ticketRepository->findById($ticketId);

        if (!$ticket) {
            throw new ErrorException("Ticket with ID $ticketId not found.", 404);
        }

        $updateData = [];
        $allowedFields = ['nombre_places', 'statut'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $dbField = $field;
                if ($field === 'nombrePlaces') {
                    $dbField = 'nombre_places';
                    if (!is_numeric($data[$field]) || $data[$field] <= 0) {
                        throw new ErrorException("Number of places must be positive.", 400);
                    }
                }
                $updateData[$dbField] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new ErrorException("No valid fields provided for update.", 400);
        }

        if (isset($updateData['statut']) && $updateData['statut'] === 'ANNULÉ' && $ticket->getStatut() !== 'ANNULÉ') {
            $seance = $this->seanceService->getSeance($ticket->getSeanceId());
            $this->seanceService->updatePlacesAvailable($seance->getId(), $ticket->getNombrePlaces());
        } elseif (isset($updateData['statut']) && $updateData['statut'] !== 'ANNULÉ' && $ticket->getStatut() === 'ANNULÉ') {
            throw new ErrorException("Cannot change status from 'ANNULÉ' to another status. Create a new ticket instead.", 400);
        }

        if ($this->ticketRepository->update($updateData, ['id' => $ticketId])) {
            return $this->ticketRepository->findById($ticketId);
        }

        throw new ErrorException("Failed to update ticket.", 500);
    }

    public function deleteTicket(int $ticketId): Ticket
    {
        $ticketToDelete = $this->ticketRepository->findById($ticketId);

        if (!$ticketToDelete) {
            throw new ErrorException("Ticket with ID $ticketId not found.", 404);
        }

        if ($ticketToDelete->getStatut() !== 'ANNULÉ') {
            $seance = $this->seanceService->getSeance($ticketToDelete->getSeanceId());
            $this->seanceService->updatePlacesAvailable($seance->getId(), $ticketToDelete->getNombrePlaces());
        }

        if ($this->ticketRepository->delete(['id' => $ticketId])) {
            return $ticketToDelete;
        }

        throw new ErrorException("Failed to delete ticket.", 500);
    }
}