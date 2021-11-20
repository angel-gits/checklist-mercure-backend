<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/tasks")
 */
class TaskController extends AbstractController
{
    private $em;
    private $hub;

    public function __construct(EntityManagerInterface $em, HubInterface $hub)
    {
        $this->em = $em;
        $this->hub = $hub;
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function getCurrentTasks(): JsonResponse
    {
        $date = new DateTime('now');
        $date->format('d-m-y');

        $list = $this->em->getRepository(Task::class)->findBy(['date' => $date], ['date' => 'ASC']);

        return $this->json(['current tasks' => $list]);
    }

    /**
     * @Route("", name="tasks", methods={"GET"})
     */
    public function getTasks(): JsonResponse
    {
        $list = $this->em->getRepository(Task::class)->findBy([], ['date' => 'ASC']);

        return new JsonResponse(['tasks' => $list]);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function addTask(Request $request): RedirectResponse
    {
        $data = json_decode($request->getContent(), true);
        $description = $data['description'] ?? null;
        $dateStr = $data['date'];

        $task = new Task($data['name'], new DateTime($dateStr) , (int) $data['status'], $description);

        $this->em->persist($task);
        $this->em->flush();

        $update = new Update(
            '/api/checklist',
            json_encode(['message' => 'New task has been added']),
        );
        $this->hub->publish($update);

        return $this->redirectToRoute('tasks');
    }

    /**
     * @Route("/{taskId}", methods={"GET"})
     */
    public function getTask(int $taskId, Request $request): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->findOneBy(['id' => $taskId]);

        return $this->json($task);
    }

    /**
     * @Route("/{taskId}", methods={"PATCH"})
     */
    public function updateTask(int $taskId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $date = $data['date'] ?? null;
        $status = $data['status'] ?? null;
        $done = $data['done'] ?? null;

        $task = $this->em->getRepository(Task::class)->findOneBy(['id' => $taskId]);
        if(!is_null($name)) {
            $task->setName($name); 
        }
        if(!is_null($description)) {
            $task->setDescription($description);
        }
        if(!is_null($date)) {
            $task->setDate(new DateTime($date)); 
        }
        if(!is_null($status)) {
            $task->setStatus((int)$status);
        }
        if(!is_null($done)) {
            $task->setDone($done);
        }

        $this->em->flush();

        $update = new Update(
            '/api/checklist',
            json_encode(['message' => 'Task ' . $taskId . ' has been updated']),
        );
        $this->hub->publish($update);

        $list = $this->em->getRepository(Task::class)->findBy([], ['date' => 'ASC']);
        return new JsonResponse(['tasks' => $list]);
    }

    /**
     * @Route("/{taskId}", methods={"DELETE"})
     */
    public function deleteTask(int $taskId): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->findOneBy(['id' => $taskId]);
        if(!is_null($task)) {
            $this->em->remove($task);
            $this->em->flush();
        }

        $update = new Update(
            '/api/checklist',
            json_encode(['message' => 'Task ' . $taskId . ' has been deleted']),
        );
        $this->hub->publish($update);

        $list = $this->em->getRepository(Task::class)->findBy([], ['date' => 'ASC']);
        return new JsonResponse(['tasks' => $list]);
    }
}
