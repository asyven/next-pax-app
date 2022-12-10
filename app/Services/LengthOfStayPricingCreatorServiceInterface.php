<?php

namespace App\Services;

use DateTime;

interface LengthOfStayPricingCreatorServiceInterface
{
    public function create(string $propertyId, DateTime $dateFrom, DateTime $dateTo);
}
