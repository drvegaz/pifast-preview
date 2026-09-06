<?php
declare(strict_types=1);

function pf_header_unsafe(string $value): bool
{
    return (bool) preg_match('/[\r\n\0]/', $value);
}

/**
 * Builds the raw message parts for a felanmälan email, without sending it.
 * Kept separate from pf_send_form_mail() so the MIME construction can
 * be tested independently of the mail transport.
 *
 * @param array{data:string,mime:string,filename:string}|null $attachment
 * @return array{subject:string,headers:string,body:string}|null null if inputs fail header-injection checks
 */
function pf_build_felanmalan_mime(
    string $fromAddress,
    ?string $replyTo,
    string $subject,
    string $bodyText,
    ?array $attachment
): ?array {
    if (pf_header_unsafe($subject) || pf_header_unsafe($fromAddress)) {
        return null;
    }
    if ($replyTo !== null && pf_header_unsafe($replyTo)) {
        return null;
    }

    $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");

    $headers = [];
    $headers[] = 'From: ' . $fromAddress;
    if ($replyTo !== null && $replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $headers[] = 'MIME-Version: 1.0';

    if ($attachment === null) {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        return ['subject' => $encodedSubject, 'headers' => implode("\r\n", $headers), 'body' => $bodyText];
    }

    $boundary = 'PF_' . bin2hex(random_bytes(16));
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

    $body = '--' . $boundary . "\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $bodyText . "\r\n\r\n";
    $body .= '--' . $boundary . "\r\n";
    $body .= 'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['filename'] . "\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= 'Content-Disposition: attachment; filename="' . $attachment['filename'] . "\"\r\n\r\n";
    $body .= chunk_split(base64_encode($attachment['data']), 76, "\r\n") . "\r\n";
    $body .= '--' . $boundary . "--\r\n";

    return ['subject' => $encodedSubject, 'headers' => implode("\r\n", $headers), 'body' => $body];
}

/**
 * @param array{data:string,mime:string,filename:string}|null $attachment
 */
function pf_send_form_mail(
    string $to,
    string $fromAddress,
    ?string $replyTo,
    string $subject,
    string $bodyText,
    ?array $attachment
): bool {
    $message = pf_build_felanmalan_mime($fromAddress, $replyTo, $subject, $bodyText, $attachment);
    if ($message === null) {
        return false;
    }
    return mail($to, $message['subject'], $message['body'], $message['headers']);
}
