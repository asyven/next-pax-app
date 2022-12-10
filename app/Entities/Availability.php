<?php

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="availabilities")
 */
class Availability
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
     * @ORM\Column(type="date", nullable=false)
     */
    private DateTime $date;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $quantity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $arrival_allowed;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $departure_allowed;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $minimum_stay;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maximum_stay;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
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
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->date->format("Y-m-d");
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getArrivalAllowed(): int
    {
        return $this->arrival_allowed;
    }

    /**
     * @param int $arrival_allowed
     */
    public function setArrivalAllowed(int $arrival_allowed): void
    {
        $this->arrival_allowed = $arrival_allowed;
    }

    /**
     * @return int
     */
    public function getDepartureAllowed(): int
    {
        return $this->departure_allowed;
    }

    /**
     * @param int $departure_allowed
     */
    public function setDepartureAllowed(int $departure_allowed): void
    {
        $this->departure_allowed = $departure_allowed;
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
