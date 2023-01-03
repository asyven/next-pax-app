<?php

namespace App\Services;

use App\Entities\Availability;
use App\Entities\Price;
use App\Repositories\AvailabilityRepositoryInterface;
use App\Repositories\PriceRepositoryInterface;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

class LengthOfStayPricingCreatorService implements LengthOfStayPricingCreatorServiceInterface
{
    private PriceRepositoryInterface $priceRepository;

    private AvailabilityRepositoryInterface $availabilityRepository;

    public function __construct(
        PriceRepositoryInterface $priceRepository,
        AvailabilityRepositoryInterface $availabilityRepository
    ) {
        $this->priceRepository = $priceRepository;
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * @param string $propertyId
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @return array
     * @throws Exception
     */
    public function create(string $propertyId, DateTime $dateFrom, DateTime $dateTo): array
    {

        $availabilities = $this->availabilityRepository->findByPropertyAndDate($propertyId, $dateFrom, $dateTo);
        $prices = $this->priceRepository->findByPropertyAndDate($propertyId, $dateFrom, $dateTo);

        // uncomment for benchmark los table creating speed, prints not included
        // \Illuminate\Support\Benchmark::dd(fn() => $this->getLOS($availabilities, $prices, $dateFrom, $dateTo));

        $los = $this->getLOS($availabilities, $prices, $dateFrom, $dateTo);
        $table = $this->_printLOSTable($los);

        return ["table" => $table, "los" => $los];
    }

    /**
     * @param Availability[] $availabilities
     * @param Price[] $prices
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @return array
     * @throws Exception
     */
    public function getLOS(array $availabilities, array $prices, DateTime $dateFrom, DateTime $dateTo): array
    {
        // los table
        $los = [];
        // temporary helper table
        $temp = [];

        // create key valued array helper with availabilities
        $availabilityDates = [];
        foreach ($availabilities as $availability) {
            $formattedDate = $availability->getDateString();
            $availabilityDates[$formattedDate] = $availability;
        }

        // generate period of arrival dates for los table
        $period = self::createDatePeriod(self::getDateString($dateFrom), self::getDateString($dateTo));

        if ($period == null) {
            return [];
        }

        // iterate every day for specified date range
        foreach ($period as $arrivalDate) {
            $arrivalDateString = self::getDateString($arrivalDate);

            // skip if arrival isset or arrival denied
            $arrivalAvailability = $availabilityDates[$arrivalDateString] ?? false;
            if (!$arrivalAvailability || !$arrivalAvailability->getArrivalAllowed()) {
                continue;
            }

            // get prices by arrival date
            /** @var Price[] $currentPeriodPrices */
            $currentPeriodPrices = self::filterValidPricesForArrivalDate($prices, $arrivalDate, $availabilityDates);

            // skip if no possible prices for arrival date
            if (count($currentPeriodPrices) == 0) {
                continue;
            }

            self::_fillPeriodByPrice($arrivalDateString, $currentPeriodPrices, $availabilityDates, $temp);

            if (!isset($temp[$arrivalDateString])) {
                continue;
            }

            $previousDateAmount = [];
            foreach ($temp[$arrivalDateString] as $personsCount => $dateObject) {
                foreach ($dateObject as $dateString => $priceObject) {
                    if (isset($los[$arrivalDateString][$personsCount][$dateString])) {
                        continue;
                    }

                    /** @var Price $price */
                    $price = $priceObject["price"];

                    $isDepartureAllowed = $priceObject["departure"];
                    $dates = $isDepartureAllowed ? $priceObject["dates"] : $priceObject["departureDates"];

                    $amount = $price->getAmount() + ($previousDateAmount[$personsCount] ?? 0);
                    $extraAmount = $price->getExtraPersonPrice();
                    $personIndex = array_search($personsCount, $price->getPersons());
                    $priceDuration = $price->getDuration();


                    foreach ($dates as $i => $subDate) {
                        $subDateString = self::getDateString($subDate);

                        if ($i !== 0 && $i % $priceDuration === 0) {
                            $amount += $price->getAmount();
                        }

                        $isExtraPerson = $personIndex !== 0;
                        $currentDayPrice = $isExtraPerson ? $amount + ($extraAmount * $personIndex) : $amount;
                        $los[$arrivalDateString][$personsCount][$subDateString] = $currentDayPrice;
                    }

                    $previousDateAmount[$personsCount] = $amount;

                    if (!$isDepartureAllowed) {
                        break;
                    }
                }
            }
        }

        return $los;
    }

    /**
     * @param string $arrivalDateString
     * @param array $currentPeriodPrices
     * @param array $availabilityDates
     * @param array $temp
     * @return void
     */
    private function _fillPeriodByPrice(string $arrivalDateString, array $currentPeriodPrices, array $availabilityDates, array &$temp): void
    {
        // generate period of 21 day
        $twentyOneNight = self::createDatePeriod($arrivalDateString, "$arrivalDateString +21 days");

        foreach ($currentPeriodPrices as $price) {
            $persons = $price->getPersons();

            // iterate 21 day and fill Price Entities for maximum possible period
            foreach ($twentyOneNight as $date) {
                $periods = self::getMaximumValidPeriod($date, $price, $availabilityDates);

                if (empty($periods[0])) {
                    continue 2;
                }

                list($validDates, $departureValidDates) = $periods;

                $endDate = end($validDates);
                $endDateString = self::getDateString($endDate);
                $dateString = self::getDateString($date);
                $isDeparturePossible = (bool)$availabilityDates[$endDateString]->getDepartureAllowed() ?? false;

                if ($date < $price->getPeriodFrom() || $date > $price->getPeriodTill()) {
                    continue;
                }

                // fill persons
                foreach ($persons as $personsCount) {
                    if ($arrivalDateString === $dateString && !$availabilityDates[$dateString]->getDepartureAllowed()) {
                        continue;
                    }

                    // check cheapest price
                    if (!isset($temp[$arrivalDateString][$personsCount][$dateString]) ||
                        $temp[$arrivalDateString][$personsCount][$dateString]["price"]->getAmount() > $price->getAmount()) {
                        $temp[$arrivalDateString][$personsCount][$dateString] = [
                            "price" => $price,
                            "dates" => $validDates,
                            "departure" => $isDeparturePossible,
                            "departureDates" => $departureValidDates,
                        ];
                    }
                }
            }
        }
    }

    /**
     * @param array $prices
     * @param DateTime $arrivalDate
     * @param array $availabilityDates
     * @return array
     * @throws Exception
     */
    private function filterValidPricesForArrivalDate(
        array $prices,
        DateTime $arrivalDate,
        array $availabilityDates
    ): array {
        $arrivalDateString = self::getDateString($arrivalDate);

        /** @var Availability $arrivalAvailability */
        $arrivalAvailability = $availabilityDates[$arrivalDateString] ?? false;
        if (!$arrivalAvailability || !$arrivalAvailability->getArrivalAllowed()) {
            return [];
        }

        $maxDepartureDateString = self::addDaysToDate($arrivalDateString, $arrivalAvailability->getMaximumStay() - 1);
        $maxDepartureDate = new DateTime($maxDepartureDateString);

        $pricesInValidDiapason = [];
        $hasArrivalPrice = false;

        $lastPrice = end($prices);
        /** @var Price[] $prices */
        foreach ($prices as $price) {
            $valid = true;

            // check valid duration
            if ($price->getDuration() == 0) {
                $valid = false;
            }

            $pricePeriodFrom = $price->getPeriodFrom();
            $pricePeriodTill = $price->getPeriodTill();

            if ($pricePeriodFrom > $maxDepartureDate || $arrivalDate > $pricePeriodTill) {
                $valid = false;
            }

            if ($arrivalDate >= $pricePeriodFrom &&
                $arrivalDate <= $pricePeriodTill &&
                self::validateDayOfWeek($arrivalDate, $price->getWeekdays())
            ) {
                $hasArrivalPrice = true;
            }

            if ($valid) {
                $pricesInValidDiapason[] = $price;
            }

            // check if empty price for arrival date
            if ($lastPrice === $price && !$hasArrivalPrice) {
                $pricesInValidDiapason = [];
            }
        }

        return $pricesInValidDiapason;
    }

    /**
     * @param DateTime $arrivalDate
     * @param Price $price
     * @param $availabilityDates
     * @return array
     */
    private function getMaximumValidPeriod(
        DateTime $arrivalDate,
        Price $price,
        $availabilityDates
    ): array {

        $arrivalDateString = self::getDateString($arrivalDate);

        /** @var Availability $arrivalAvailability */
        $arrivalAvailability = $availabilityDates[$arrivalDateString] ?? false;
        if (!$arrivalAvailability) {
            return [];
        }

        $priceTill = $price->getPeriodTill();
        $daysToTill = (int)$priceTill->diff($arrivalDate)->format('%a') + 1;
        $minimumStay = max($arrivalAvailability->getMinimumStay(), $price->getMinimumStay());

        if ($minimumStay > $daysToTill) {
            return [];
        }

        $maximumStay = min($arrivalAvailability->getMaximumStay(), $price->getMaximumStay(), $daysToTill);

        $period = self::createDatePeriod($arrivalDateString, self::addDaysToDate($arrivalDateString, $maximumStay));
        $priceWeekDays = $price->getWeekdays();

        $periodArray = [];
        $lastDepartureAllowedIndex = 0;

        /** @var DateTime $day */
        foreach ($period as $index => $day) {
            $dayString = self::getDateString($day);
            /** @var Availability $dayAvailability */
            $dayAvailability = $availabilityDates[$dayString] ?? false;

            if (!$dayAvailability || $dayAvailability->getQuantity() < 1) {
                break;
            }

            if ($day > $priceTill) {
                break;
            }

            if (!self::validateDayOfWeek($day, $priceWeekDays)) {
                break;
            }

            if ($dayAvailability->getDepartureAllowed()) {
                $lastDepartureAllowedIndex = $index;
            }

            $periodArray[] = $day;
        }


        // clear if dates less than minimum stay
        self::_splicePeriodIfNeeded($periodArray, $price);

        // splice array if last departure date in the middle of the period
        $departureValidPeriodArray = array_merge($periodArray);
        array_splice($departureValidPeriodArray, $lastDepartureAllowedIndex + 1);

        // clear if period dates with the departure at end less than minimum stay
        if (count($periodArray) !== count($departureValidPeriodArray)) {
            self::_splicePeriodIfNeeded($departureValidPeriodArray, $price);
        }

        return [$periodArray, $departureValidPeriodArray];
    }


    /**
     * @param array $periodArray
     * @param Price $price
     * @return void
     */
    private function _splicePeriodIfNeeded(array &$periodArray, Price $price): void
    {
        $count = count($periodArray);
        if ($count < $price->getMinimumStay() || $count < $price->getDuration()) {
            $periodArray = [];
        }

        // slice if duration is too short
        if ($count > 0 && $count % $price->getDuration() != 0) {
            $offset = (int)floor($price->getDuration() / $count);
            array_splice($periodArray, 0, $offset);
        }
    }

    /**
     * @param string $start
     * @param string $end
     * @param string $interval
     * @return DatePeriod|null
     */
    private function createDatePeriod(string $start, string $end, string $interval = "P1D"): ?DatePeriod
    {
        try {
            return new DatePeriod(
                new DateTime($start),
                new DateInterval($interval),
                new DateTime($end),
            );
        } catch (Exception $exception) {
            Log::error($exception);
            return null;
        }
    }

    /**
     * @param string $dateString
     * @param int $days
     * @return string
     */
    private function addDaysToDate(string $dateString, int $days): string
    {
        try {
            return self::getDateString(new DateTime($dateString . " +" . $days . " days"));
        } catch (Exception $exception) {
            Log::error($exception);
            return $dateString;
        }
    }

    /**
     * @param DateTime $date
     * @return string
     */
    private function getDateString(DateTime $date): string
    {
        return $date->format("Y-m-d");
    }

    /**
     * @param DateTime $date
     * @param array $weekDays
     * @return bool
     */
    private function validateDayOfWeek(DateTime $date, array $weekDays): bool
    {
        $dayOfWeek = (int)$date->format('N') - 1;
        return in_array($dayOfWeek, $weekDays);
    }

    /**
     * returns LOS table generated by sprintf
     *
     * @param array $losData
     * @return string
     */
    private function _printLOSTable(array $losData): string
    {
        /**
         * @var array $headerData
         */
        $headerData = array_merge(["Date/Nights", "P"], range(1, 21));

        $table = $this->_printTableRow($headerData);
        $table .= PHP_EOL;

        ksort($losData);
        foreach ($losData as $date => $losRow) {
            foreach ($losRow as $person => $prices) {
                $prices = array_map(
                    function ($p) {
                        return sprintf("%.2f", $p / 100);
                    },
                    $prices
                );
                $table .= $this->_printTableRow(array_merge([$date, $person], array_values($prices)));
            }
            $table .= PHP_EOL;
        }

        return $table;
    }

    /**
     * return string row with data for LOS table
     *
     * @param array $args
     * @return string
     */
    private function _printTableRow(array $args): string
    {

        $args = $args + array_fill(0, 23, "-");
        $days = implode(
            "",
            array_map(
                function () {
                    return " %8s |";
                },
                range(1, 21)
            )
        );
        return sprintf("|%12s |%2s |" . $days . PHP_EOL, ...$args);
    }
}
