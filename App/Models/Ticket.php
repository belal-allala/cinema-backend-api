<?php

namespace App\Models;

use JsonSerializable;

class Ticket implements JsonSerializable
{
    private $id;
    private $clientId;
    private $seanceId;
    private $nombrePlaces;
    private $montantTotal;
    private $statut; 

    private ?Client $client = null;
    private ?Seance $seance = null;

    public function __construct($id, $clientId, $seanceId, $nombrePlaces, $montantTotal, $statut, ?Client $client = null, ?Seance $seance = null) 
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->seanceId = $seanceId;
        $this->nombrePlaces = $nombrePlaces;
        $this->montantTotal = $montantTotal;
        $this->statut = $statut;
        $this->client = $client;
        $this->seance = $seance;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'clientId' => $this->clientId,
            'seanceId' => $this->seanceId,
            'nombrePlaces' => $this->nombrePlaces,
            'montantTotal' => $this->montantTotal,
            'statut' => $this->statut,
        ];

        if ($this->client) {
            $data['client'] = $this->client->jsonSerialize();
        }
        if ($this->seance) {
            $data['seance'] = $this->seance->jsonSerialize();
        }

        return $data;
    }

    public function getId(): ?int { return $this->id; }
    public function getClientId(): int { return $this->clientId; }
    public function getSeanceId(): int { return $this->seanceId; }
    public function getNombrePlaces(): int { return $this->nombrePlaces; }
    public function getMontantTotal(): float { return $this->montantTotal; }
    public function getStatut(): string { return $this->statut; }
    public function getClient(): ?Client { return $this->client; }
    public function getSeance(): ?Seance { return $this->seance; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setClientId(int $clientId): void { $this->clientId = $clientId; }
    public function setSeanceId(int $seanceId): void { $this->seanceId = $seanceId; }
    public function setNombrePlaces(int $nombrePlaces): void { $this->nombrePlaces = $nombrePlaces; }
    public function setMontantTotal(float $montantTotal): void { $this->montantTotal = $montantTotal; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setClient(?Client $client): void { $this->client = $client; }
    public function setSeance(?Seance $seance): void { $this->seance = $seance; }
}