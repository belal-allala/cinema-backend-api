<?php

namespace App\Models;

class Seance3D extends Seance
{
    private $technologie3D;
    private $lunettesIncluses;
    private const LUNETTES_PRICE = 20.00;

    public function __construct($id, $film, $horaire, $prix, $salle, $placesDisponibles, $technologie3D, $lunettesIncluses) 
    {
        parent::__construct($id, $film, $horaire, $prix, $salle, $placesDisponibles);
        $this->technologie3D = $technologie3D;
        $this->lunettesIncluses = $lunettesIncluses;
    }

    public function getType(): string
    {
        return '3D';
    }

    public function getCalculatedPrice(): float
    {
        $basePrice = parent::getCalculatedPrice();
        return $this->lunettesIncluses ? $basePrice + self::LUNETTES_PRICE : $basePrice;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'technologie3D' => $this->technologie3D,
            'lunettesIncluses' => $this->lunettesIncluses
        ]);
    }

    public function getTechnologie3D(): string
    {
        return $this->technologie3D;
    }

    public function getLunettesIncluses(): bool
    {
        return $this->lunettesIncluses;
    }
    
    public function setTechnologie3D(string $technologie3D): void
    {
        $this->technologie3D = $technologie3D;
    }

    public function setLunettesIncluses(bool $lunettesIncluses): void
    {
        $this->lunettesIncluses = $lunettesIncluses;
    }
}