<?php


namespace App\Controller;

use App\Service\AgendaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * Return an ICS file.
     * @Route("/", name="index")
     * @param AgendaService $service
     * @return Response
     */
    public function index(AgendaService $service)
    {
        $calendar = $service->getParsedCalendar();
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
    public function beforeParsedAgenda(AgendaService $service)
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
    public function test(AgendaService $service)
    {
        $calendar = $service->getParsedCalendar();
        return new Response($calendar);
    }
}
