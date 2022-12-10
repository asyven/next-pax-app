<?php

namespace App\Repositories;

use DateTime;
use Doctrine\Persistence\ObjectRepository;

interface AvailabilityRepositoryInterface extends ObjectRepository
{

    public function findByPropertyAndDate(string $propertyId, DateTime $fromDate, DateTime $toDate) : array;
}
