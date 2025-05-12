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
    public function findByFilters(?string $title, ?string $editeur, ?int $categoryId)
    {
        $qb = $this->createQueryBuilder('l');

        // Filter by title
        if ($title) {
            $qb->andWhere('l.titre LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        // Filter by editeur (publisher)
        if ($editeur) {
            $qb->andWhere('l.editeur LIKE :editeur')
                ->setParameter('editeur', '%' . $editeur . '%');
        }

        // Filter by category if a categoryId is provided
        if ($categoryId) {
            $qb->leftJoin('l.categorie', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        // Order by title or other criteria if needed
        $qb->orderBy('l.titre', 'ASC'); // Default ordering by title

        return $qb->getQuery()->getResult();
    }
}