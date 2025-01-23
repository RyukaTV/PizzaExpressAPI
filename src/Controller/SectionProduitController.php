<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\SectionProduit;
use App\Entity\Produit;
use App\Service\JsonConverter;

class SectionProduitController extends AbstractController
{
    private $jsonConverter;

    public  function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/api/sectionProduits', methods: ['GET'])]
    #[Security(name: null)]
    #[OA\Get(description: 'Récupère tous les produits')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits'
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function getData(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $data = $entityManager->getRepository(SectionProduit::class)->findAll();
        return new Response($this->jsonConverter->encodeToJson($data));
    }

    #[Route('/api/sectionProduits/selected', methods: ['GET'])]
    #[Security(name: null)]
    #[OA\Get(description: 'Récupère tous les produits sélectionnés')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits sélectionnés'
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function getSelectedProducts(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $data = $entityManager->getRepository(Produit::class)->findBy(['selected' => true]);

        return new Response($this->jsonConverter->encodeToJson($data));
    }
}
