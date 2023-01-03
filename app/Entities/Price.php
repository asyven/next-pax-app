<?php

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="prices")
 */
class Price
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="guid", nullable=false)
     */
    protected string $property_id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $duration;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $amount;

    /**
     * @ORM\Column(type="string", length=3, nullable=false)
     */
    private string $currency;

    /**
     * @ORM\Column(type="integer_array", length=255, nullable=false)
     */
    private array $persons;

    /**
     * @ORM\Column(type="integer_array", length=255, nullable=false)
     */
    private array $weekdays;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $minimum_stay;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maximum_stay;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $extra_person_price;

    /**
     * @ORM\Column(type="string", length=3, nullable=false)
     */
    private string $extra_person_price_currency;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private DateTime $period_from;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private DateTime $period_till;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Version()
     */
    private int $version;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPropertyId(): string
    {
        return $this->property_id;
    }

    /**
     * @param string $property_id
     */
    public function setPropertyId(string $property_id): void
    {
        $this->property_id = $property_id;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    /**
     * @param array $persons
     */
    public function setPersons(array $persons): void
    {
        $this->persons = $persons;
    }

    /**
     * @return array
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @param array $weekdays
     */
    public function setWeekdays(array $weekdays): void
    {
        $this->weekdays = $weekdays;
    }

    /**
     * @return int
     */
    public function getMinimumStay(): int
    {
        return $this->minimum_stay;
    }

    /**
     * @param int $minimum_stay
     */
    public function setMinimumStay(int $minimum_stay): void
    {
        $this->minimum_stay = $minimum_stay;
    }

    /**
     * @return int
     */
    public function getMaximumStay(): int
    {
        return $this->maximum_stay;
    }

    /**
     * @param int $maximum_stay
     */
    public function setMaximumStay(int $maximum_stay): void
    {
        $this->maximum_stay = $maximum_stay;
    }

    /**
     * @return int
     */
    public function getExtraPersonPrice(): int
    {
        return $this->extra_person_price;
    }

    /**
     * @param int $extra_person_price
     */
    public function setExtraPersonPrice(int $extra_person_price): void
    {
        $this->extra_person_price = $extra_person_price;
    }

    /**
     * @return string
     */
    public function getExtraPersonPriceCurrency(): string
    {
        return $this->extra_person_price_currency;
    }

    /**
     * @param string $extra_person_price_currency
     */
    public function setExtraPersonPriceCurrency(string $extra_person_price_currency): void
    {
        $this->extra_person_price_currency = $extra_person_price_currency;
    }

    /**
     * @return DateTime
     */
    public function getPeriodFrom(): DateTime
    {
        return $this->period_from;
    }

    /**
     * @return string
     */
    public function getPeriodFromString(): string
    {
        return $this->period_from->format("Y-m-d");
    }

    /**
     * @param DateTime $period_from
     */
    public function setPeriodFrom(DateTime $period_from): void
    {
        $this->period_from = $period_from;
    }

    /**
     * @return DateTime
     */
    public function getPeriodTill(): DateTime
    {
        return $this->period_till;
    }

    /**
     * @return string
     */
    public function getPeriodTillString(): string
    {
        return $this->period_till->format("Y-m-d");
    }

    /**
     * @param DateTime $period_till
     */
    public function setPeriodTill(DateTime $period_till): void
    {
        $this->period_till = $period_till;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
