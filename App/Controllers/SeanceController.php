<?php

namespace App\Controllers;

use Core\Contracts\ResourceController;
use Core\Controller;
use App\Services\Implementations\SeanceDefault; 
use App\Services\Interfaces\SeanceService; 
use Core\Decorators\Description;
use Core\Decorators\Route;
use ErrorException; 

#[Route('/api/v1')] 
class SeanceController extends Controller implements ResourceController
{
    private SeanceService $seanceService;

    public function __construct()
    {
        parent::__construct();
        $this->seanceService = new SeanceDefault();
    }

    #[Description("Récupère la liste de toutes les séances.")]
    public function index()
    {
        try {
            $seances = $this->seanceService->getSeances($this->request->param());
            $this->json($seances);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 500);
        } 
    }

    #[Description("Affiche les détails d'une séance en utilisant son identifiant.")]
    public function show($id)
    {
        try {
            $seance = $this->seanceService->getSeance((int)$id);
            $this->json($seance);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404);
        }
    }

    #[Description("Crée une nouvelle séance (2D ou 3D).")]
    public function store()
    {
        try {
            $data = $this->request->all();
            $newSeance = $this->seanceService->createSeance($data);
            $this->json($newSeance, 201);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Description("Met à jour les informations d'une séance.")]
    public function update($id)
    {
        try {
            $data = $this->request->all();
            
            if (empty($data)) {
                throw new ErrorException("No data provided for update.", 400);
            }

            $updatedSeance = $this->seanceService->updateSeance((int)$id, $data);
            $this->json($updatedSeance);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Description("Supprime une séance à partir de son identifiant.")]
    public function destroy($id)
    {
        try {
            $deletedSeance = $this->seanceService->deleteSeance((int)$id);
            $this->json($deletedSeance);
        } catch (ErrorException $e) {
            $this->json(["error" => $e->getMessage()], $e->getCode() ?: 404);
        }
    }
}