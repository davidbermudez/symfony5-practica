<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class NotifyController extends AbstractController
{
    /**
     * @Route("/notify", name="app_notify")
     */
    public function create(NotifierInterface $notifier)
    {
        // ...

        // Create a Notification that has to be sent
        // using the "email" channel
        $notification = (new Notification('New Invoice', ['email', 'chat/telegram']))
            ->content('You got a new invoice for 15 EUR.')
            ->importance(Notification::IMPORTANCE_MEDIUM);

        // The receiver of the Notification
        $recipient = new Recipient(
            $user->getEmail(),
            //$user->getPhonenumber()
        );

        // Send the notification to the recipient
        $notifier->send($notification, $recipient);

        // ...
    }
}
