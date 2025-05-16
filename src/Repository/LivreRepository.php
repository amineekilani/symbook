<?php
// src/Repository/LivreRepository.php
namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livre>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * Custom method to find books by filters (title, editeur, category)
     * @return Livre[] Returns an array of Livre objects based on filters
     */
    public function findByFilters(?string $title = null, ?string $editeur = null, ?string $categoryId = null)
    {
        $qb = $this->createQueryBuilder('l');

        if ($title) {
            $qb->andWhere('l.titre LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if ($editeur) {
            $qb->andWhere('l.editeur LIKE :editeur')
                ->setParameter('editeur', '%' . $editeur . '%');
        }

        if ($categoryId) {
            $qb->andWhere('l.categorie = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery();
    }
}
