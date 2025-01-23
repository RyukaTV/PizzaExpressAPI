<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Produit;
use App\Entity\SectionProduit;

class AppFixtures extends Fixture
{

    private $passwordHasher;

    public  function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $userAdmin = new User();
        $userAdmin->setEmail('admin@admin.fr');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setPassword($this->passwordHasher->hashPassword($userAdmin, 'adminPassword'));
        $userAdmin->setLoyaltyPoints(2);
        $manager->persist($userAdmin);

        $userStandard1 = new User();
        $userStandard1->setEmail('olivier@tintin.fr');
        $userStandard1->setRoles(['ROLE_USER']);
        $userStandard1->setPassword($this->passwordHasher->hashPassword($userStandard1, 'password'));
        $userStandard1->setLoyaltyPoints(0);
        $manager->persist($userStandard1);

        $section1 = new SectionProduit();
        $section1->setSectionName("Nos Pizzas");
        $manager->persist($section1);

        $pizzas = [
            ['title' => 'Margherita', 'description' => 'Tomate, mozzarella, basilic frais.', 'price' => 10.75, 'imagePath'=> '/images/Margherita.jpg'],
            ['title' => 'Pepperoni', 'description' => 'Pepperoni, mozzarella, sauce tomate.', 'price' => 12.5, 'imagePath'=> '/images/Pepperoni.jpg'],
            ['title' => 'Végétarienne', 'description' => 'Légumes frais, mozzarella, sauce tomate.', 'price' => 11, 'imagePath'=> '/images/Végétarienne.jpg'],
            ['title' => 'Quatre Fromages', 'description' => 'Mozzarella, gorgonzola, parmesan, chèvre, miel, sauce tomate.', 'price' => 13.5, 'imagePath'=> '/images/Quatre Fromages.jpg'],
            ['title' => 'Reine', 'description' => 'Jambon, champignons, mozzarella, sauce tomate.', 'price' => 12.0, 'imagePath'=> '/images/Reine.jpg'],
            ['title' => 'Calzone', 'description' => 'Pizza pliée, jambon, mozzarella, ricotta, sauce tomate.', 'price' => 13.0, 'imagePath'=> '/images/Calzone.jpg'],
            ['title' => 'Diavola', 'description' => 'Salami piquant, mozzarella, sauce tomate, olives.', 'price' => 14.0, 'imagePath'=> '/images/Diavola.jpg'],
            ['title' => 'Napolitaine', 'description' => 'Anchois, câpres, olives, sauce tomate, mozzarella.', 'price' => 13.0, 'imagePath'=> '/images/Napolitaine.jpg'],
            ['title' => 'Capricciosa', 'description' => 'Artichauts, jambon, champignons, olives, mozzarella, sauce tomate.', 'price' => 14.5, 'imagePath'=> '/images/Capricciosa.jpg'],
        ];

        foreach ($pizzas as $key) {
            $produit = new Produit();
            $produit->setName($key["title"]);
            $produit->setDescription($key["description"]);
            $produit->setPrice($key["price"]);
            $produit->setImagePath($key["imagePath"]);
            $produit->setSectionProduit($section1);
            $manager->persist($produit);
        }

        $manager->flush();
    }
}
