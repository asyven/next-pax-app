<?php

namespace App\Http\Controllers;

use App\Services\LengthOfStayPricingCreatorService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    private LengthOfStayPricingCreatorService $losService;

    public function __construct(
        EntityManager $entityManager,
        LengthOfStayPricingCreatorService $losService
    ) {
        $this->losService = $losService;

        parent::__construct($entityManager);
    }

    /**
     * @param string $propertyId
     * @param string|null $from
     * @param string|null $to
     * @return Application|ResponseFactory|Factory|View|Response
     */
    public function index(
        string $propertyId = "71438849-47cb-4b00-82de-34fff691f017",
        ?string $from = "2017-01-01",
        ?string $to = "2017-12-31"
    ) {
        try {
            $dateFrom = new DateTime($from);
            $dateTo = new DateTime($to);
        } catch (\Exception $exception) {
            return response(json_encode([
                "error" => $exception->getCode(),
                "message" => $exception->getMessage()
            ]));
        }

        $data = $this->losService->create($propertyId, $dateFrom, $dateTo);

        return view("los", $data);
    }
}
