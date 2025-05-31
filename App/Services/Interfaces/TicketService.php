<?php

namespace App\Services\Interfaces;

use App\Models\Ticket;

interface TicketService
{
    public function getTicket(int $id): Ticket;

    public function getTickets(?array $filters = null): array;

    public function createTicket(int $clientId, int $seanceId, int $nombrePlaces): Ticket;

    public function updateTicket(int $ticketId, array $data): Ticket;

    public function deleteTicket(int $ticketId): Ticket;
}