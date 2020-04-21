<?php


namespace App\Controller;

use App\Service\AgendaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * Show available routes
     * @Route("/", name="index")
     * @return Response
     */
    public function index(): Response
    {
        return new JsonResponse([
            "routes" => [
                "original" => "/original",
                "raw" => "/raw",
                "parsed" => "/agenda"
            ]
        ]);
    }

    /**
     * Return an ICS file.
     * @Route("/agenda", name="agenda")
     * @param AgendaService $service
     * @return Response
     */
    public function parsedAgenda(AgendaService $service): Response
    {
        $calendar = $service->getParsedAgenda();
        $response = new Response($calendar);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            "agenda.ics"
        );
        $response->headers->set("Content-Disposition", $disposition);
        return $response;
    }

    /**
     * Return the ICS file before be parsed.
     * @Route("/original", name="original")
     * @param AgendaService $service
     * @return Response
     */
    public function originalAgenda(AgendaService $service): Response
    {
        $calendar = $service->getOriginalAgenda();
        return new Response($calendar);
    }

    /**
     * Return an ICS file in text format.
     * Useful for test and debugging.
     * @Route("/raw", name="raw")
     * @param AgendaService $service
     * @return Response
     */
    public function rawAgenda(AgendaService $service): Response
    {
        $calendar = $service->getParsedAgenda();
        return new Response($calendar);
    }
}
