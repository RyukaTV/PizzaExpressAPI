<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Produit;

class ProduitController extends AbstractController{
    private $doctrine;

    public  function __construct(ManagerRegistry $doctrine) {
        $this->doctrine = $doctrine;
    }

    #[Route('/api/produits', methods: ['GET'])]
    #[Security(name: null)]
    #[OA\Get(description: 'Récupère tous les produits')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits'
    )]
    #[OA\Tag(name: 'produits')]
    public function getData() {
        $entityManager = $this->doctrine->getManager();
        $data = $entityManager->getRepository(Produit::class)->findAll();
        return new Response($this->json($data));
    }
}