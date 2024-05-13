<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Models\EventPublisher;

class EventController extends AbstractController
{
    protected EventPublisher $publisher;

    public function __construct()
    {
        $this->publisher = new EventPublisher();
    }

    #[Route('/event/register', name: 'event_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $event = json_decode($request->getContent(), true);
            $this->publisher->publishEvent($event, $event['idAccount']);
        } catch (\Throwable $e) {

        }

        return new Response(json_encode(['ok']));
    }
}