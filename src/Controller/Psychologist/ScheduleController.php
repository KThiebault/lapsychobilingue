<?php

declare(strict_types=1);

namespace App\Controller\Psychologist;

use App\Entity\Schedule;
use App\Form\ScheduleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ScheduleController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/schedule' , name: 'psychologist_schedule_index', methods: 'GET')]
    public function index(): Response
    {
        $maxSchedule = 0;
        $days = $this->entityManager->getRepository(Schedule::class)->findByPsychologist($this->getUser()->getId());

        foreach ($days as $day) {
            $scheduleLength = count($day) - 1;
            $maxSchedule = $maxSchedule < $scheduleLength ? $scheduleLength : $maxSchedule;
        }

        return $this->render('user/psychologist/schedule/index.html.twig', [
            'days' => $days,
            'maxSchedule' => $maxSchedule ?? 0,
            'menu' => 'schedule'
        ]);
    }

    #[Route(path: '/schedule/create' , name: 'psychologist_schedule_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $form = $this->createForm(ScheduleType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule = $form->getData();
            $schedule->setPsychologist($this->getUser());

            $this->entityManager->persist($schedule);
            $this->entityManager->flush();
        }

        return $this->render('user/psychologist/schedule/create.html.twig', [
            'create_form' => $form->createView(),
            'menu' => 'schedule'
        ]);
    }

    #[Route(path: '/schedule/update/{id}', name: 'psychologist_schedule_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(int $id, Request $request): Response {
        $repository = $this->entityManager->getRepository(Schedule::class);
        $schedule = $repository->find($id);

        if ($schedule === null) {
            return $this->redirectToRoute('admin_blog_index');
        }

        $form = $this->createForm(ScheduleType::class, $schedule)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'horaire a bien été mis à jour.');

            return $this->redirectToRoute('psychologist_schedule_index', ['id' => $schedule->getId()]);
        }

        return $this->render('user/psychologist/schedule/update.html.twig', [
            'update_form' => $form->createView(),
            'menu' => 'schedule'
        ]);
    }

    #[Route(path: '/schedule/delete/{id}' , name: 'psychologist_schedule_delete', requirements: ['id' => '\d+'], methods: 'DELETE')]
    public function delete(int $id, Request $request): Response
    {
        $repository = $this->entityManager->getRepository(Schedule::class);
        $schedule = $repository->find($id);

        if ($schedule === null) {
            return $this->redirectToRoute('psychologist_schedule_index');
        }

        if ($this->isCsrfTokenValid('schedule_delete' . $schedule->getId(), $request->get('_token'))) {
            $this->entityManager->remove($schedule);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'horaire a bien été supprimé.');
        }

        return $this->redirectToRoute('psychologist_schedule_index');
    }
}