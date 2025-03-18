<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Livres;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LivresFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=1; $i<=100; $i++)
        {
            $faker=Factory::create('fr_FR'); 
            $livre=new Livres();
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
            ->setPrix($faker->randomFloat(nbMaxDecimals:2, min:10, max:700));
            $manager->persist($livre);
        }
        $manager->flush();
    }
}