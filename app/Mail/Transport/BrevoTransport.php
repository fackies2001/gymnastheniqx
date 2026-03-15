<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;

class BrevoTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            $to = [];
            foreach ($email->getTo() as $address) {
                $to[] = [
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?? $address->getAddress()
                ];
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => [
                        'email' => config('mail.from.address'),
                        'name' => config('mail.from.name'),
                    ],
                    'to' => $to,
                    'subject' => $email->getSubject(),
                    'htmlContent' => $email->getHtmlBody() ?? $email->getTextBody(),
                ]);

            \Log::error('Brevo Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
                'api_key_set' => !empty($this->apiKey)
            ]);

            if ($response->failed()) {
                throw new \Exception('Brevo API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Brevo Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
