<?php

class MailerFactory
{
    public static function createActivationEmail(string $address, string $token): Mailer
    {
        $template = EmailTemplates::activationEmail($token);
        return Mailer::create()->createEmail($address, $template['subject'], $template['content']);
    }

    public static function createEventPublicationEmail(Event $event, RecipientType $recipients_type = RecipientType::EVENT_GROUPS): Mailer
    {
        $template = EmailTemplates::eventPublicationEmail($event);
        $mailer = Mailer::create();
        $emails = RecipientResolver::getEventEmails($event, $recipients_type);
        $users = self::formatEmailsForBulk($emails);

        if (!empty($users)) {
            $mailer->createBulkEmails($users, $template['subject'], $template['content']);
        }
        return $mailer;
    }

    public static function createEventMessageEmail(Event $event, Message $message, User $sender, RecipientType $recipients = RecipientType::REGISTERED_USERS): Mailer
    {
        $template = EmailTemplates::eventMessageEmail($event, $message);
        $mailer = Mailer::create();
        $emails = RecipientResolver::getEventEmails($event, $recipients);

        logger()->debug("Emails : " . print_r($emails, true));

        $users = self::formatEmailsForBulk($emails);
        if (!empty($users)) {
            $mailer->createBulkEmails($users, $template['subject'], $template['content'])->send();
        }
        return $mailer;
    }

    public static function formatEmailsForBulk(array $emails): array
    {
        $users = [];
        foreach ($emails as $email) {
            $users[$email['real_email']] = '';
        }
        return $users;
    }
}

class EmailTemplates
{
    public static function activationEmail(string $token): array
    {
        $base_url = env("BASE_URL");
        $app_name = config("name", "Intranose");

        return [
            'subject' => "Activation du compte " . $app_name,
            'content' => "Voici le lien pour activer ton compte: $base_url/activation?token=$token"
        ];
    }

    public static function eventPublicationEmail(Event $event): array
    {
        $app_name = config("name", "Intranose");
        $staging_prefix = env("STAGING") ? "[STAGING] " : "";
        $footer = self::eventEmailFooter($event);

        return [
            'subject' => $staging_prefix . "Nouvel événement sur " . $app_name,
            'content' => "<h3>Un nouvel événement a été publié sur $app_name !</h3><br>" . $footer
        ];
    }

    public static function eventMessageEmail(Event $event, Message $message): array
    {
        $appName = config("name", "Intranose");
        $staging_prefix = env("STAGING") ? "[STAGING] " : "";

        $content = self::buildMessageEmailHtml($event, $message);

        return [
            'subject' => $staging_prefix . "$appName - Nouvel message sur l'évenement $event->name",
            'content' => $content
        ];
    }

    private static function buildMessageEmailHtml(Event $event, Message $message): string
    {
        $footer = self::eventEmailFooter($event);
        $message_html = (new Parsedown)->text($message->content);
        $event_name = htmlspecialchars($event->name);
        $sender_name = htmlspecialchars($message->sender->first_name . ' ' . $message->sender->last_name);
        $sent_at = $message->sentAt->format("d/m H:i");

        return <<<EOD
        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Linklub Email</title>
        </head>
        <body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#ffffff;">
        
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
            <tr>
                <td>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:20px;text-align:center;background-color:#ffffff;">
                                <h1 style="margin:0;font-size:22px;color:#333333;font-family:Arial,Helvetica,sans-serif;line-height:1.2;">
                                    Nouveau message sur l'évenement "$event_name"
                                </h1>
                            </td>
                        </tr>
                    </table>

                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background-color:#e8e8e8;">
                        <tr>
                            <td style="padding:20px;">
                                <p style="margin:0;font-size:12px;color:#777777;font-family:Arial,Helvetica,sans-serif;line-height:1.4;">
                                    De : $sender_name<br>
                                    Envoyé le : $sent_at<br>
                                </p>
                                <div style="margin:0;font-size:14px;color:#333333;font-family:Arial,Helvetica,sans-serif;line-height:1.4;">
                                    $message_html
                                </div>
                            </td>
                        </tr>
                    </table>

                    $footer
                    
                </td>
            </tr>
        </table>
        </body>
        </html>
        EOD;
    }

    private static function eventEmailFooter(Event $event): string
    {
        $base_url = env("BASE_URL");
        $event_date = $event->deadline->format('d/m/Y');
        $event_name = htmlspecialchars($event->name);
        $compagny_name = env("INTRANOSE") ? "Le Nose" : "Linklub";

        return <<<EOD
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background-color:#e8e8e8;margin-top:20px;">
            <tr>
                <td style="padding:10px;">
                    <h3 style="margin:0 0 15px 0;font-size:16px;color:#111111;font-family:Arial,Helvetica,sans-serif;line-height:1.3;">Détails de l'événement</h3>
                    <p style="margin:0 0 10px 0;font-size:14px;color:#333333;font-family:Arial,Helvetica,sans-serif;line-height:1.4;">
                        <strong>Nom :</strong> $event_name<br>
                        <strong>Deadline d'inscription :</strong> $event_date
                    </p>
                    <p style="margin:0 0 15px 0;font-size:14px;color:#333333;font-family:Arial,Helvetica,sans-serif;line-height:1.4;">
                        <a href="$base_url/evenements/$event->id" style="color:#0066cc;text-decoration:underline;">Voir les informations</a><br>
                        <a href="$base_url/evenements/$event->id/inscription" style="color:#0066cc;text-decoration:underline;">S'inscrire</a>
                    </p>
                    <p style="margin:0;font-size:12px;color:#777777;font-family:Arial,Helvetica,sans-serif;line-height:1.4;">
                        A bientôt pour de nouveaux événements !<br>
                        Linklub<br>
                    </p>
                </td>
            </tr>
        </table>
        EOD;
    }
}

enum RecipientType: string
{
    case EVENT_GROUPS = 'event_groups';
    case REGISTERED_USERS = 'registered_users';
    case UNREGISTERED_USERS = 'unregistered_users';
    case ALL_USERS = 'all_users';
    case NO_USERS = 'no_users';
}

class RecipientResolver
{
    public static function getEventEmails(Event $event, RecipientType $recipients_type): array
    {
        $users = match ($recipients_type) {
            RecipientType::EVENT_GROUPS => $event->groups->isEmpty()
            ? UserService::getActiveUserList()
            : UserService::getGroupMembersForEvent($event),

            RecipientType::REGISTERED_USERS => UserService::getRegisteredUsersForEvent($event),
            RecipientType::UNREGISTERED_USERS => UserService::getUnregisteredUsersForEvent($event),
            RecipientType::ALL_USERS => UserService::getActiveUserList(),
            RecipientType::NO_USERS => []
        };

        // Convert users to email format and filter out null/empty emails
        return array_values(array_filter(
            array_map(
                fn($user) => ["real_email" => $user->real_email],
                $users
            ),
            fn($email) => !empty($email['real_email'])
        ));
    }
}