<?php

namespace Pmc\Blog\Modules\Users\Service;

use Pmc\Blog\Modules\Users\Event\LoginTokenCreated;
use Pmc\Blog\Modules\Users\Query\Factory;
use Pmc\MessageBus\Listener;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;


/**
 * Send emails when certain events are triggered.
 *
 * @author Gargoyle <g@rgoyle.com>
 */
class MailSender implements Listener
{

    /**
     * @var Factory
     */
    private $userQueryFactory;
    
    private $fromAddress;
    private $fromName;
    private $notificationAddress;
    private $sendgridApiKey;

    public function __construct(array $config, Factory $userQueryFactory)
    {
        $this->userQueryFactory = $userQueryFactory;
        
        $this->fromAddress = $config['fromAddress'];
        $this->fromName = $config['fromName'];
        $this->notificationAddress = $config['notificationAddress'];
        $this->sendgridApiKey = $config['sendgridApiKey'];
    }
    
    public function getObservables(): array
    {
        return [
            LoginTokenCreated::class
        ];
    }

    public function notify($message): void
    {
        switch (get_class($message)) {
            case LoginTokenCreated::class:
                $this->notifyLoginTokenCreatedEvent($message);
                break;
            default:
                return;
        }
    }

    private function createMailer()
    {
        $transport = Swift_SmtpTransport::newInstance('smtp.sendgrid.net', 587, 'tls');
        $transport->setUsername('apikey');
        $transport->setPassword($this->sendgridApiKey);
        
        $mailer = Swift_Mailer::newInstance($transport);
        return $mailer;
    }
    
    private function notifyLoginTokenCreatedEvent(LoginTokenCreated $event)
    {
        $user = $this->userQueryFactory->createUserDetailsByIdQuery('', ['userId' => (string)$event->aggregateId()])->result();
        if (empty($user)) {
            return;
        }
        
        $mailer = $this->createMailer();        
        $message = Swift_Message::newInstance();
        $message->setTo([(string)$user['emailAddress'] => (string)$user['fullName']]);
        $message->setFrom($this->fromAddress, $this->fromName);
        $message->setSubject("Magic Login Link");
        $message->setBody($this->getLoginTokenBody('https://dev.teammentalhealth.co.uk/accunt/login', (string)$event->getToken()), 'text/html');
        $mailer->send($message);
    }
    
    private function getLoginTokenBody(string $tokenLoginPageUrl, string $token): string
    {
        return <<<EOF
<html>
<body>
<h1>Here's your login link!</h1>
<p> 
    This will allow you to login and change your password if you have forgoten it, or if you don't want to type your password on a public computer.
</p>
<p>
    Simply click the link below, and you'll be automatically logged in and directed to your profile page.
</p>
<p>
    <a href="{$tokenLoginPageUrl}?loginToken={$token}">{$tokenLoginPageUrl}?loginToken={$token}</a>
</p>
<p>
    Thanks<br>
    Team Mental Health
</p>
</body>
</html>
EOF;
    }

}
