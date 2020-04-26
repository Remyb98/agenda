<?php


namespace App\Controller;

use App\Service\AgendaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     * @param AgendaService $service
     * @return Response
     */
    public function parsedAgenda(Request $request, AgendaService $service): Response
    {
        $calendar = $service->getParsedAgenda($this->getGroups($request));
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
     * @param Request $request
     * @param AgendaService $service
     * @return Response
     */
    public function originalAgenda(Request $request, AgendaService $service): Response
    {
        $calendar = $service->getOriginalAgenda($this->getGroups($request));
        return new Response($calendar);
    }

    /**
     * Return an ICS file in text format.
     * Useful for test and debugging.
     * @Route("/raw", name="raw")
     * @param Request $request
     * @param AgendaService $service
     * @return Response
     */
    public function rawAgenda(Request $request, AgendaService $service): Response
    {
        $calendar = $service->getParsedAgenda($this->getGroups($request));
        return new Response($calendar);
    }

    private function getGroups(Request $request): string
    {
        $groups = $request->get("groups");
        return $groups === null ? "all" : preg_replace("/(?!,)\D/", "", $groups);
    }
}
