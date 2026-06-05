<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$paymentId = $argv[1] ?? null;

if (!$paymentId) {
    fwrite(STDERR, "Usage: php tools/paymongo_lookup.php <payment_id>\n");
    exit(1);
}

$secret = (string) config('services.paymongo.secret_key');

if ($secret === '') {
    fwrite(STDERR, "Missing PayMongo secret key configuration.\n");
    exit(1);
}

$client = new GuzzleHttp\Client([
    'headers' => [
        'Authorization' => 'Basic ' . base64_encode($secret . ':'),
        'Accept' => 'application/json',
    ],
    'timeout' => 30,
]);

try {
    $response = $client->get("https://api.paymongo.com/v1/payments/{$paymentId}");
    echo $response->getBody()->getContents();
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
