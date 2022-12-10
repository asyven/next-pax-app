<?php

namespace App\Repositories;

use DateTime;

class PriceRepository extends EntityRepository implements PriceRepositoryInterface
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
            ->orderBy('c.amount', "ASC")
            ->setParameter(':property_id', $propertyId);

        if (isset($fromDate)) {
            $qb->andWhere('c.period_from >= :period_from')
                ->setParameter(':period_from', $fromDate);
        }

        if (isset($toDate)) {
            $qb->andWhere('c.period_till <= :period_till')
                ->setParameter(':period_till', $toDate);
        }

        return $qb->getQuery()->getResult();
    }
}
