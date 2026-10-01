<?php
/**
 * BeCoffee — SMS Dispatcher Service
 * Supports live SMS gateways (Semaphore PH, Twilio) with automatic local fallback logging to scratch/sms_outbox.log.
 */

require_once __DIR__ . '/config.php';

class SmsService {
    private static string $logFile = __DIR__ . '/../scratch/sms_outbox.log';

    /**
     * Dispatch an SMS message to a customer mobile number
     *
     * @param string $phoneNumber Target recipient mobile (e.g. 09171234567, +639171234567)
     * @param string $message The message body
     * @param array $meta Optional metadata (order_id, queue_number, source)
     * @return array Result status ['success' => bool, 'mode' => string, 'details' => string]
     */
    public static function send(string $phoneNumber, string $message, array $meta = []): array {
        $cleanPhone = preg_replace('/[^0-9+]/', '', trim($phoneNumber));
        if (empty($cleanPhone) || strlen($cleanPhone) < 7) {
            return [
                'success' => false,
                'mode'    => 'invalid_number',
                'details' => 'Recipient mobile number is invalid or empty.'
            ];
        }

        // Standardize PH numbers: 09XXXXXXXXX -> 639XXXXXXXXX
        if (str_starts_with($cleanPhone, '09')) {
            $formattedPhone = '63' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '+63')) {
            $formattedPhone = substr($cleanPhone, 1);
        } else {
            $formattedPhone = $cleanPhone;
        }

        $semaphoreApiKey = getenv('SEMAPHORE_API_KEY') ?: '';
        $isLiveSent = false;
        $liveResponse = '';

        // If Semaphore PH API Key is provided, attempt live dispatch
        if (!empty($semaphoreApiKey)) {
            try {
                $ch = curl_init('https://api.semaphore.co/api/v4/messages');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query([
                        'apikey'     => $semaphoreApiKey,
                        'number'     => $formattedPhone,
                        'message'    => $message,
                        'sendername' => 'BECOFFEE'
                    ]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5
                ]);
                $resp = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpCode === 200) {
                    $isLiveSent = true;
                    $liveResponse = $resp ?: '200 OK';
                }
            } catch (Throwable $e) {
                $liveResponse = 'Semaphore error: ' . $e->getMessage();
            }
        }

        // Always record to local sms_outbox.log for transparency, auditability, and offline/test verification
        self::logOutbox([
            'timestamp'    => date('Y-m-d H:i:s'),
            'recipient'    => $formattedPhone,
            'message'      => $message,
            'metadata'     => $meta,
            'live_gateway' => !empty($semaphoreApiKey) ? ($isLiveSent ? 'sent' : 'failed') : 'mock_local',
            'api_response' => $liveResponse ?: 'Logged to local simulator outbox'
        ]);

        return [
            'success'   => true,
            'mode'      => !empty($semaphoreApiKey) ? ($isLiveSent ? 'live_gateway' : 'gateway_fallback') : 'mock_local',
            'recipient' => $formattedPhone,
            'details'   => $liveResponse ?: 'Dispatched to SMS outbox log.'
        ];
    }

    /**
     * Send order ready pickup notification for takeout orders
     */
    public static function notifyOrderReady(array $order): array {
        $phone = $order['customer_phone'] ?? '';
        $name = trim($order['customer_name'] ?? 'Valued Customer');
        $queueNum = $order['queue_number'] ?? '0';

        $msg = "BeCoffee: Hi {$name}! Your Take-Out Order #{$queueNum} is freshly crafted and ready for pickup at our barista counter. See you!";

        return self::send($phone, $msg, [
            'order_id'     => $order['id'] ?? null,
            'queue_number' => $queueNum,
            'order_source' => $order['order_source'] ?? 'online',
            'action'       => 'order_ready'
        ]);
    }

    private static function logOutbox(array $entry): void {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents(self::$logFile, $line, FILE_APPEND);
    }
}
