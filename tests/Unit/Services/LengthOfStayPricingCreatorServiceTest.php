<?php

namespace Tests\Unit\Services;

use App\Entities\Availability;
use App\Entities\Price;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Tests\TestCase;

class LengthOfStayPricingCreatorServiceTest extends TestCase
{

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->losService = \App::make('App\Services\LengthOfStayPricingCreatorService');
    }

    /**
     * @param array $availabilitiesData
     * @return Collection
     */
    private function getAvailabilityCollection(array $availabilitiesData): Collection
    {
        $artistCollection = new ArrayCollection();

        foreach ($availabilitiesData as $availability) {
            $entity = new Availability();

            $entity->setId($availability["id"]);
            $entity->setPropertyId($availability["property_id"]);
            $entity->setDate($availability["date"]);
            $entity->setQuantity($availability["quantity"]);
            $entity->setArrivalAllowed($availability["arrival_allowed"]);
            $entity->setDepartureAllowed($availability["departure_allowed"]);
            $entity->setMinimumStay($availability["minimum_stay"]);
            $entity->setMinimumStay($availability["maximum_stay"]);
            $entity->setVersion($availability["version"]);

            $artistCollection->add($entity);
        }

        return $artistCollection;
    }

    /**
     * @param array $pricesData
     * @return Collection
     */
    private function getPriceCollection(array $pricesData): Collection
    {
        $artistCollection = new ArrayCollection();

        foreach ($pricesData as $price) {
            $entity = new Price();

            $entity->setId($price["id"]);
            $entity->setPropertyId($price["property_id"]);
            $entity->setDuration($price["duration"]);
            $entity->setAmount($price["amount"]);
            $entity->setCurrency($price["currency"]);
            $entity->setPersons($price["persons"]);
            $entity->setWeekdays($price["weekdays"]);
            $entity->setMinimumStay($price["minimum_stay"]);
            $entity->setMaximumStay($price["maximum_stay"]);
            $entity->setExtraPersonPrice($price["extra_person_price"]);
            $entity->setExtraPersonPriceCurrency($price["extra_person_currency"]);
            $entity->setPeriodFrom($price["period_from"]);
            $entity->setPeriodTill($price["period_till"]);
            $entity->setVersion($price["version"]);

            $artistCollection->add($entity);
        }

        return $artistCollection;
    }


    public function testValidLOSData(): void
    {
        $propertyId = "00000000-0000-0000-0000-000000000000";
        $date = new \DateTime("2017-01-01");

        $availabilities = [
            [
                "id" => 1,
                "property_id" => $propertyId,
                "date" => $date,
                "quantity" => 1,
                "arrival_allowed" => 1,
                "departure_allowed" => 1,
                "minimum_stay" => 1,
                "maximum_stay" => 21,
                "version" => 0,
            ]
        ];


        /**
         * case #1:
         * duration = 1, amount = 9900, currency = EUR, persons = 1|2|3, extra_person_price is 2000
         *
         * Example: duration = 1, amount = 9900, currency = EUR, persons = 1|2|3,
         * extra_person_price is 2000 means that the nightly rate is 99.00 EUR for 1 person.
         * For two persons the price is 99.00 + 20.00 = 119.00 EUR.
         * For three persons the price is 119.00 + 20.00 = 139.00 EUR
         */

        $price_variant1 = [
            "id" => 1,
            "property_id" => $propertyId,
            "duration" => 1,
            "amount" => 9900,
            "currency" => "EUR",
            "persons" => [1, 2, 3],
            "weekdays" => [0, 1, 2, 3, 4, 5, 6],
            "minimum_stay" => 1,
            "maximum_stay" => 1,
            "extra_person_price" => 2000,
            "extra_person_currency" => "EUR",
            "period_from" => new \DateTime("2017-01-01"),
            "period_till" => new \DateTime("2017-01-21"),
            "version" => 0,
        ];

        $test = $this->losService->getLOS(
            $this->getAvailabilityCollection($availabilities)->toArray(),
            $this->getPriceCollection([$price_variant1])->toArray()
        );

        $result = $test[$date->format("Y-m-d")];
        $this->assertNotEmpty($result);

        $this->assertTrue($result[1][0] === 9900); // for 1 persons for first day.
        $this->assertTrue($result[2][0] === 11900); // for 2 persons for first day.
        $this->assertTrue($result[3][0] === 13900); // for 3 persons for first day.


        /**
         * case #2:
         * duration = 1, amount = 9900, currency = EUR, persons = 2|3|4|5|6, extra_person_price is 2000
         *
         *
         * Example: duration = 1, amount = 9900, currency = EUR, persons = 2|3|4|5|6,
         * extra_person_price is 2000 means that the nightly rate is 99.00 EUR for 2 persons.
         * For two persons the price is 99.00. For three persons the price is 99.00 + 20.00 = 119.00 EUR.
         * For four persons the price is 119.00 + 20.00 = 139.00 EUR
         */

        $price_variant2 = [
            "id" => 2,
            "property_id" => $propertyId,
            "duration" => 1,
            "amount" => 9900,
            "currency" => "EUR",
            "persons" => [2, 3, 4, 5, 6],
            "weekdays" => [0, 1, 2, 3, 4, 5, 6],
            "minimum_stay" => 1,
            "maximum_stay" => 1,
            "extra_person_price" => 2000,
            "extra_person_currency" => "EUR",
            "period_from" => new \DateTime("2017-01-01"),
            "period_till" => new \DateTime("2017-01-21"),
            "version" => 0,
        ];

        $test = $this->losService->getLOS(
            $this->getAvailabilityCollection($availabilities)->toArray(),
            $this->getPriceCollection([$price_variant2])->toArray()
        );

        $result = $test[$date->format("Y-m-d")];
        $this->assertNotEmpty($result);
        $this->assertTrue($result[2][0] === 9900); // for 2 persons for first day.
        $this->assertTrue($result[3][0] === 11900); // for 3 persons for first day.
        $this->assertTrue($result[4][0] === 13900); // for 4 persons for first day.
        $this->assertTrue($result[5][0] === 15900); // for 5 persons for first day.
        $this->assertTrue($result[6][0] === 17900); // for 6 persons for first day.


        /**
         * case #3:
         * duration = 1, amount = 9900, currency = EUR, persons = 1|2|3, extra_person_price is 2000
         * duration = 1, amount = 9900, currency = EUR, persons = 2|3|4|5|6, extra_person_price is 2000
         *
         * Example: case 1 and case 2 prices entities,
         * In that case we should check the cheapest price for each row (date + persons)
         *
         * [1.1] Nightly rate is 99.00 EUR for 1 person.
         * [1.2] Nightly rate is 99.00 + 20.00 = 119.00 EUR for 2 persons.
         * [1.3] Nightly rate is 99.00 + 20.00 + 20.00 = 139.00 EUR for 2 persons.
         *
         * [2.2] Nightly rate is 99.00 EUR for 2 persons.
         * [2.3] Nightly rate is 99.00 + 20.00 = 119.00 EUR for 3 persons.
         *
         * Condition [2.2] < [1.2] | (99.00 < 119.00) = true, the cheapest price is from 2.2 for 2 persons.
         * Condition [2.3] < [1.3] | (119.00 < 139.00) = true, the cheapest price is from 2.3 for 3 persons.
         *
         * For 1 person the price is 99.00.
         * For 2 persons the price is 99.00.
         * For 3 persons the price is 119.00.
         */
        $price_variant3 = [
            $price_variant1,
            $price_variant2
        ];

        $test = $this->losService->getLOS(
            $this->getAvailabilityCollection($availabilities)->toArray(),
            $this->getPriceCollection($price_variant3)->toArray()
        );

        $result = $test[$date->format("Y-m-d")];
        $this->assertNotEmpty($result);

        $this->assertTrue($result[1][0] === 9900); // for 1 person for first day.
        $this->assertTrue($result[2][0] === 9900); // for 2 persons for first day.
        $this->assertTrue($result[3][0] === 11900); // for 3 persons for first day.
        $this->assertTrue($result[4][0] === 13900); // for 4 persons for first day.
        $this->assertTrue($result[5][0] === 15900); // for 5 persons for first day.
        $this->assertTrue($result[6][0] === 17900); // for 6 persons for first day.
    }
}
