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
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/api/sectionProduits', methods: ['POST'])]
    #[OA\Post(description: 'ajoute une nouvelle section de produit')]
    #[OA\Response(
        response: 201,
        description: 'ajoute une nouvelle section de produit'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'sectionName', type: 'string', default: 'newSectionName')
            ]
        )
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function addSectionProduit(ManagerRegistry $doctrine, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['sectionName'])) {
            return new Response('Bad Request', 400);
        }

        $entityManager = $doctrine->getManager();
        $section = new SectionProduit();
        $section->setSectionName($data['sectionName']);
        $entityManager->persist($section);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($data));
    }

    #[Route('/api/sectionProduits/{id}', methods: ['PUT'])]
    #[OA\Put(description: 'modifie une section de produit')]
    #[OA\Response(
        response: 201,
        description: 'modifie une section de produit'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'sectionName', type: 'string', default: 'newSectionName')
            ]
        )
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function updateSectionProduit(ManagerRegistry $doctrine, Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['sectionName'])) {
            return new Response('Bad Request', 400);
        }

        $entityManager = $doctrine->getManager();
        $section = $entityManager->getRepository(SectionProduit::class)->find($id);

        if ($section) {
            $section->setSectionName($data['sectionName']);
            $entityManager->persist($section);
            $entityManager->flush();
        }

        return new Response($this->jsonConverter->encodeToJson($data));
    }

    #[Route('/api/sectionProduits/{id}', methods: ['DELETE'])]
    #[OA\Put(description: 'supprime une section de produit')]
    #[OA\Response(
        response: 204,
        description: 'supprime une section de produit'
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function deleteSectionProduit(ManagerRegistry $doctrine, $id)
    {

        $entityManager = $doctrine->getManager();
        $section = $entityManager->getRepository(SectionProduit::class)->find($id);

        if ($section) {
            $entityManager->remove($section);
            $entityManager->flush();
        }

        return new Response("Section Produit sucessfully deleted");
    }
}
