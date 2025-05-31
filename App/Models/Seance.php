<?php

namespace App\Models;

use JsonSerializable;

abstract class Seance implements JsonSerializable
{
    protected $id;
    protected $film;
    protected $horaire; 
    protected $prix;
    protected $salle;
    protected $placesDisponibles;

    public function __construct( $id, $film, $horaire, $prix, $salle, $placesDisponibles) {
        $this->id = $id;
        $this->film = $film;
        $this->horaire = $horaire;
        $this->prix = $prix;
        $this->salle = $salle;
        $this->placesDisponibles = $placesDisponibles;
    }

    abstract public function getType(): string;

    public function getCalculatedPrice(): float
    {
        return $this->prix;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'film' => $this->film,
            'horaire' => $this->horaire,
            'prix' => $this->prix, 
            'salle' => $this->salle,
            'placesDisponibles' => $this->placesDisponibles,
            'type' => $this->getType(),
            'calculatedPrice' => $this->getCalculatedPrice() 
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function getFilm(): string { return $this->film; }
    public function getHoraire(): string { return $this->horaire; }
    public function getPrix(): float { return $this->prix; }
    public function getSalle(): string { return $this->salle; }
    public function getPlacesDisponibles(): int { return $this->placesDisponibles; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setFilm(string $film): void { $this->film = $film; }
    public function setHoraire(string $horaire): void { $this->horaire = $horaire; }
    public function setPrix(float $prix): void { $this->prix = $prix; }
    public function setSalle(string $salle): void { $this->salle = $salle; }
    public function setPlacesDisponibles(int $placesDisponibles): void { $this->placesDisponibles = $placesDisponibles; }
}