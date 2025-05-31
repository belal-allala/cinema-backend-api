<?php

namespace App\Models;

class Seance2D extends Seance
{
    private string $qualiteImage;

    public function __construct($id, $film, $horaire, $prix, $salle, $placesDisponibles, $qualiteImage ) {
        parent::__construct($id, $film, $horaire, $prix, $salle, $placesDisponibles);
        $this->qualiteImage = $qualiteImage;
    }

    public function getType(): string
    {
        return '2D';
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'qualiteImage' => $this->qualiteImage
        ]);
    }

    public function getQualiteImage(): string
    {
        return $this->qualiteImage;
    }

    public function setQualiteImage(string $qualiteImage): void
    {
        $this->qualiteImage = $qualiteImage;
    }
}