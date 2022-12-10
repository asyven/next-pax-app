<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;

class EntityRepository extends BaseEntityRepository
{
    /**
     * @param object $entity
     * @return void
     */
    public function add(object $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
