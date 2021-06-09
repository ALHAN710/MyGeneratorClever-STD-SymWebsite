<?php

namespace App\MessageHandler;

use App\Entity\Contacts;
//use App\Entity\User;
use Twig\Environment;
use Twilio\Rest\Client;
use Symfony\Component\Mime\Email;
use App\Message\UserNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UserNotificationHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $em;

    private Environment $twig;

    private TexterInterface $texter;

    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, Environment $twig, TexterInterface $texter, Client $twilio, string $fromNumber, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->texter = $texter;
        $this->twilio = $twilio;
        $this->fromNumber = $fromNumber;
        $this->mailer = $mailer;
    }

    public function __invoke(UserNotificationMessage $notifMessage)
    {
        $contact = $this->em->find(Contacts::class, $notifMessage->getUserId());
        if ($contact) {
            if ($notifMessage->getNotifType() === 'Email') {
                $object = 'ALERTE My Energy Clever !!!';
                $to = $contact->getUser() !== null ? $contact->getUser()->getEmail() : $contact->getEmail();
                $email = (new Email())
                    ->from('alhadoumpascal@gmail.com')
                    //->from('donotreply@portal-myenergyclever.com')
                    ->to($to)
                    //->addTo('cabrelmbakam@gmail.com')
                    //->cc('cabrelmbakam@gmail.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject($object)
                    ->text($notifMessage->getMessage());
                //->html('<p>See Twig integration for better HTML integration!</p>');

                //sleep(10);
                $this->mailer->send($email);
            } else if ($notifMessage->getNotifType() === 'SMS') {
                $phoneNumber = $contact->getUser() !== null ? $contact->getUser()->getPhoneNumber() : $contact->getPhoneNumber();
                $phoneNumber = $contact->getCountryCode() . $phoneNumber;
                $firstName = $contact->getUser() !== null ? $contact->getUser()->getFirstName() : $contact->getFirstName();
                $mess = "Hi {$firstName}, Alerte !!!. " . $notifMessage->getMessage();
                $sms = new SmsMessage(
                    // the phone number to send the SMS message to
                    $phoneNumber,
                    // the message
                    $mess
                );
                //sleep(10);
                $this->texter->send($sms);

                /*$toName = $user->getFirstName();
                $toNumber = $user->getPhoneNumber();
        
                $this->twilio->messages->create($toNumber, [
                    'from' => $this->fromNumber,
                    'body' => "Hi $toName! Test async send sms"
                ]);*/
            }
        }
    }
}
