<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Livre;
use App\Entity\Categorie;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LivresFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($j=1; $j<=5; $j++)
        {
            $faker=Factory::create('fr_FR');
            $cat=new Categorie();
            $names=['Roman', 'Science Fiction', 'Fantasy', 'Horreur', 'Policier'];
            $cat->setLibelle($names[$j-1]);
            $slug=str_replace(' ', '-', $cat->getLibelle());
            $slug=strtolower($slug);
            $cat->setSlug($slug);
            $cat->setLibelle($names[$j-1])
                ->setSlug($slug)
                ->setDescription($faker->text)
            ;
            $manager->persist($cat);
            for($i=1; $i<=100; $i++)
            { 
                $livre=new Livre();
                $titre=$faker->name;
                $slug=str_replace(' ', '-', $titre);
                $slug=strtolower($slug);
                $livre->setTitre($titre)
                    ->setSlug($slug)
                    ->setIsbn($faker->isbn13())
                    ->setResume($faker->text)
                    ->setEditeur($faker->company)
                    ->setDateedition($faker->dateTimeBetween("-30 years", "now"))
                    ->setImage("https://picsum.photos/500/?id=".$i)
                    ->setPrix($faker->randomFloat(nbMaxDecimals:2, min:10, max:700))
                    ->setCategorie($cat)
                ;
                $manager->persist($livre);
            }
        }
        $manager->flush();
    }
}