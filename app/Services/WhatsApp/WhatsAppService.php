<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $endpoint = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = (string) config('services.fonnte.token', '');
    }

    public function send(string $to, string $message): bool
    {
        if ($this->token === '') {
            Log::warning('WhatsApp: FONNTE_TOKEN belum dikonfigurasi, pesan tidak terkirim.', [
                'to'      => $to,
                'message' => $message,
            ]);

            return false;
        }

        $normalizedTo = $this->normalizeNumber($to);

        if ($normalizedTo === '') {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $this->token])
                ->asForm()
                ->post($this->endpoint, [
                    'target'  => $normalizedTo,
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp: Fonnte API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'to'     => $normalizedTo,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp: Gagal mengirim pesan', [
                'to'      => $normalizedTo,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        if ($number === '') {
            return '';
        }

        // Konversi 08xx → 628xx
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }
}
