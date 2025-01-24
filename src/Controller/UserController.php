<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;

use App\Service\JsonConverter;
use App\Entity\User;

class UserController extends AbstractController
{

    private $jsonConverter;
    private $passwordHasher;
    private $doctrine;

    public  function __construct(JsonConverter $jsonConverter, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        $this->passwordHasher = $passwordHasher;
        $this->jsonConverter = $jsonConverter;
        $this->doctrine = $doctrine;
    }

    #[Route('/api/token', methods: ['POST'])]
    #[Security(name: null)]
    #[OA\Post(description: 'Connexion à l\'API')]
    #[OA\Response(
        response: 200,
        description: 'Un token'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', default: 'admin@admin.fr'),
                new OA\Property(property: 'password', type: 'string', default: 'password')
            ]
        )
    )]
    #[OA\Tag(name: 'utilisateurs')]
    public function logUser(JWTTokenManagerInterface $JWTManager)
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['email']) || empty($data['password'])) {
            return new Response('Identifiants invalides', 401);
        }

        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            throw $this->createNotFoundException();
        }
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new Response('Identifiants invalides', 401);
        }

        $token = $JWTManager->create($user);
        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/myself', methods: ['GET'])]
    #[OA\Get(description: 'Retourne l\'utilisateur authentifié')]
    #[OA\Response(
        response: 200,
        description: 'L\'utilisateur correspondant au token passé dans le header',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Tag(name: 'utilisateurs')]
    public function getUtilisateur(JWTEncoderInterface $jwtEncoder, Request $request)
    {
        $tokenString = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        $userToken = $jwtEncoder->decode($tokenString);
        $entityManager = $this->doctrine->getManager();
        $userDatabase = $entityManager->getRepository(User::class)->findOneBy(['email' => $userToken['email']]);

        if ($userDatabase == null) {
            return new Response('Aucun utilisateur ne correspond à ce token', 404);
        }

        return new Response($this->jsonConverter->encodeToJson($userDatabase->serialize()));
    }

    #[Route('/api/users/changeEmail', methods: ['POST'])]
    #[OA\Post(description: 'Retourne l\'utilisateur modifé authentifié')]
    #[OA\Response(
        response: 201,
        description: 'L\'utilisateur avec ses informations modifiés',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', default: 'admin@admin.fr')
            ]
        )
    )]
    #[OA\Tag(name: 'utilisateurs')]
    public function ChangeUserEmail(JWTTokenManagerInterface $JWTManager, JWTEncoderInterface $jwtEncoder, Request $request)
    {
        $tokenString = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        $userToken = $jwtEncoder->decode($tokenString);
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['email'])) {
            return new Response('Bad Request', 400);
        }

        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $userToken['email']]);

        if ($user) {
            $user->setEmail($data["email"]);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        $token = $JWTManager->create($user);
        return new JsonResponse(['token' => $token, 'user' => $this->jsonConverter->encodeToJson($user->serialize())]);
    }

    #[Route('/api/users/changeName', methods: ['POST'])]
    #[OA\Post(description: 'Retourne l\'utilisateur modifé authentifié')]
    #[OA\Response(
        response: 201,
        description: 'L\'utilisateur avec ses informations modifiés',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'prenom', type: 'string', default: 'username')
            ]
        )
    )]
    #[OA\Tag(name: 'utilisateurs')]
    public function ChangeUserName(JWTTokenManagerInterface $JWTManager, JWTEncoderInterface $jwtEncoder, Request $request)
    {
        $tokenString = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        $userToken = $jwtEncoder->decode($tokenString);
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['prenom'])) {
            return new Response('Bad Request', 400);
        }

        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $userToken['email']]);

        if ($user) {
            $user->setUsername($data["prenom"]);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        $token = $JWTManager->create($user);
        return new JsonResponse(['token' => $token, 'user' => $this->jsonConverter->encodeToJson($user->serialize())]);
    }

    #[Route('/api/users/changePassword', methods: ['POST'])]
    #[OA\Post(description: 'Retourne l\'utilisateur modifé authentifié')]
    #[OA\Response(
        response: 201,
        description: 'L\'utilisateur avec ses informations modifiés',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'password', type: 'string', default: 'password'),
                new OA\Property(property: 'repassword', type: 'string', default: 'repassword')
            ]
        )
    )]
    #[OA\Tag(name: 'utilisateurs')]
    public function ChangeUserPassword(JWTTokenManagerInterface $JWTManager, JWTEncoderInterface $jwtEncoder, Request $request)
    {
        $tokenString = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        $userToken = $jwtEncoder->decode($tokenString);
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || $data == null || empty($data['password']) || empty($data['repassword'])) {
            return new Response('Bad Request', 400);
        }

        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $userToken['email']]);

        if ($user && $data["password"] == $data["repassword"]) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data["password"]));
            $entityManager->persist($user);
            $entityManager->flush();
        }
        $token = $JWTManager->create($user);
        return new JsonResponse(['token' => $token, 'user' => $this->jsonConverter->encodeToJson($user->serialize())]);
    }
}
