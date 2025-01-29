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

class ProduitController extends AbstractController
{
    private $jsonConverter;

    public  function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/api/sectionProduits/{id}/produits', methods: ['POST'])]
    #[OA\Post(description: 'ajoute un nouveau produit')]
    #[OA\Response(
        response: 201,
        description: 'ajoute un nouveau produit'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'produitName', type: 'string', default: 'newSectionName'),
                new OA\Property(property: 'produitDescription', type: 'string', default: 'new description produit'),
                new OA\Property(property: 'produitImage', type: 'string'),
                new OA\Property(property: 'produitPrice', type: 'float'),
            ]
        )
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function addProduit(ManagerRegistry $doctrine, Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['produitName']) || empty($data['produitDescription']) || empty($data['produitImage']) || empty($data['produitPrice'])) {
            return new Response('Bad Request', 400);
        }   
        $produit = null;
        $base64_image = $data["produitImage"];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $matches)) {
            $imageFormat = $matches[1];
            $imageName = $data["produitName"]. "." . $imageFormat;
            $destinationPath = "./../public/images/" . $imageName;

            $imageData = substr($base64_image, strpos($base64_image, ',') + 1);
            $binaryData = base64_decode($imageData);
            if ($binaryData !== false) {
                file_put_contents($destinationPath, $binaryData);
                $entityManager = $doctrine->getManager();
                $section= $entityManager->getRepository(SectionProduit::class)->find($id);
                if ($section) {
                    $produit = new Produit();
                    $produit->setName($data['produitName']);
                    $produit->setDescription($data["produitDescription"]);
                    $produit->setImagePath("/images/".$imageName);
                    $produit->setPrice($data["produitPrice"]);
                    $produit->setSectionProduit($section);
                    $produit->setSelected(false);

                    $entityManager->persist($produit);
                    $entityManager->flush();
                }      
            }     
        } else {
            return new Response('Image invalide', 405);
        }
        return new Response($this->jsonConverter->encodeToJson($produit));
    }

    #[Route('/api/sectionProduits/{sectionId}/produits/{id}', methods: ['DELETE'])]
    #[OA\Post(description: 'ajoute un nouveau produit')]
    #[OA\Response(
        response: 204,
        description: 'ajoute un nouveau produit'
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function deleteProduit(ManagerRegistry $doctrine, $sectionId, $id)
    {
        $entityManager = $doctrine->getManager();
        $section= $entityManager->getRepository(SectionProduit::class)->find($sectionId);
        if ($section) {
            $produit = $entityManager->getRepository(Produit::class)->findOneBy(["sectionProduit" => $section, "id"=> $id]);
            if (file_exists("./../public".$produit->getImagePath())) {
                if (unlink("./../public".$produit->getImagePath())) {
                    $entityManager->remove($produit);
                    $entityManager->flush();
                }
            }
        }
        return new Response("Produit sucessfully deleted");
    }


    #[Route('/api/sectionProduits/{sectionId}/produits/{id}', methods: ['PUT'])]
    #[OA\Post(description: 'ajoute un nouveau produit')]
    #[OA\Response(
        response: 201,
        description: 'ajoute un nouveau produit'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'produitName', type: 'string', default: 'newSectionName'),
                new OA\Property(property: 'produitDescription', type: 'string', default: 'new description produit'),
                new OA\Property(property: 'produitImage', type: 'string'),
                new OA\Property(property: 'produitPrice', type: 'float'),
            ]
        )
    )]
    #[OA\Tag(name: 'SectionProduit')]
    public function updateProduit(ManagerRegistry $doctrine, Request $request, $id, $sectionId)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['produitName']) || empty($data['produitDescription']) || empty($data['produitImage']) || empty($data['produitPrice'])) {
            return new Response('Bad Request', 400);
        }   
        $produit = null;
        $base64_image = $data["produitImage"];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $matches)) {
            $imageFormat = $matches[1];
            $imageName = $data["produitName"]. "." . $imageFormat;
            $destinationPath = "./../public/images/" . $imageName;

            $imageData = substr($base64_image, strpos($base64_image, ',') + 1);
            $binaryData = base64_decode($imageData);
            if ($binaryData !== false) {
                file_put_contents($destinationPath, $binaryData);
                $entityManager = $doctrine->getManager();
                $section= $entityManager->getRepository(SectionProduit::class)->find($id);
                if ($section) {
                    $produit = new Produit();
                    $produit->setName($data['produitName']);
                    $produit->setDescription($data["produitDescription"]);
                    $produit->setImagePath("/images/".$imageName);
                    $produit->setPrice($data["produitPrice"]);
                    $produit->setSectionProduit($section);
                    $produit->setSelected(false);

                    $entityManager->persist($produit);
                    $entityManager->flush();
                }      
            }     
        } else {
            return new Response('Image invalide', 405);
        }
        return new Response($this->jsonConverter->encodeToJson($produit));
    }
}
