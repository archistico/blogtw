<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    #[Route('/email', name: 'email')]
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('emilie@rollandin.it')
            ->to('emilie.rollandin@gmail.com')
            ->subject('Invio di prova')
            ->html('Corpo della mail');
        
        $mailer->send($email);

        return new Response('Email sent');

    }
}
