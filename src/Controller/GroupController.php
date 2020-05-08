<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/group")
 */
class GroupController extends AbstractController
{
    /**
     * @Route("/", name="group", methods={"GET"})
     * @param GroupRepository $repository
     * @return Response
     */
    public function showAll(GroupRepository $repository): Response
    {
        $group = $repository->findAll();

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GroupController.php',
        ]);
    }

    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id"="\d+"})
     * @param int $id
     * @param GroupRepository $repository
     * @return Response
     */
    public function show(int $id, GroupRepository $repository): Response
    {
        $group = $repository->find($id);
        if ($group === null) {
            $this->createNotFoundException();
        }
        return $this->json($group);
    }

    /**
     * @Route("/", name="group_add", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        if ($request->get("code") && $request->get("name")) {
            $group = new Group();
            $group
                ->setCode($request->get("code"))
                ->setName($request->get("name"))
            ;
            $manager->persist($group);
            $manager->flush();
        }
    }
}
