<?php

namespace App\Repositories;

use DateTime;

class AvailabilityRepository extends EntityRepository implements AvailabilityRepositoryInterface
{

    /**
     * @param string $propertyId
     * @param DateTime|null $fromDate
     * @param DateTime|null $toDate
     * @return array
     */
    public function findByPropertyAndDate(string $propertyId, ?DateTime $fromDate, ?DateTime $toDate): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from($this->getClassName(), 'c')
            ->where('c.property_id = :property_id')
            ->andWhere('c.quantity > 0')
            ->orderBy('c.date', "ASC")
            ->setParameter(':property_id', $propertyId);


        if (isset($fromDate)) {
            $qb->andWhere('c.date >= :fromDate')
                ->setParameter('fromDate', $fromDate);
        }

        if (isset($toDate)) {
            $qb->andWhere('c.date <= :toDate')
                ->setParameter('toDate', $toDate);
        }

        return $qb->getQuery()->getResult();
    }
}
