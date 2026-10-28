<?php
declare(strict_types=1);

/**
 * Envoie un e-mail texte. En local (debug), l'e-mail est écrit dans
 * storage/logs/mails.log au lieu d'être envoyé.
 */
function send_mail(string $to, string $subject, string $body): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (config('app.debug')) {
        $entry = sprintf("=== %s\nÀ : %s\nSujet : %s\n\n%s\n\n", now(), $to, $subject, $body);
        return file_put_contents(BASE_PATH . '/storage/logs/mails.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }
    $from = (string) config('mail.from');
    $fromName = mb_encode_mimeheader((string) config('mail.from_name', config('app.name')), 'UTF-8');
    $headers = [
        'From'                      => "$fromName <$from>",
        'Reply-To'                  => (string) config('legal.email', $from),
        'MIME-Version'              => '1.0',
        'Content-Type'              => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
    ];
    return mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, $headers, '-f' . $from);
}
