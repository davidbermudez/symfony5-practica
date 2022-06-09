<?php

namespace App\EventSubscriber;

use App\Repository\ConferenceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigEventSubscriber implements EventSubscriberInterface
{
   
    public static function getSubscribedEvents(): array
    {
        return [
            'Symfony\Component\HttpKernel\Event\ControllerEven' => 'onSymfony\Component\HttpKernel\Event\ControllerEven',
        ];
    }
}
