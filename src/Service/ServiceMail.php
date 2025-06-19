<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ServiceMail
{
    private MailerInterface $mailer;
    private Environment $templating;
    public function __construct(
        MailerInterface $mailer,
        Environment $templating
    )
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
    }
    public function send(
        string $from,
        string $sujet,
        array $emails,
        array $emailsCopie,
        string $templatePath,
        ?array $filespath = [],
        ?array $parametersTemplate = []
    )
    {
        $message = (new Email())
            ->subject($sujet)
            ->from($from)
            ->html(
                $parametersTemplate !== null ? $this->templating->render($templatePath, $parametersTemplate) : $this->templating->render($templatePath),
            );

        foreach($emails as $email) {
            $message->addTo($email);
        }

        foreach($emailsCopie as $copie) {
            $message->addCc($copie);
        }

        if(count($filespath) > 0) {
            foreach($filespath as $filepath) {
                $message->attachFromPath($filepath);
            }
        }

        $this->mailer->send($message);
    }
}