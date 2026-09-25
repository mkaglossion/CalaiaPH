<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TwoFactorService
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const WINDOW = 1;

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(User $user): ?string
    {
        if (blank($user->two_factor_secret)) {
            return null;
        }

        return Crypt::decryptString((string) $user->two_factor_secret);
    }

    public function provisioningUri(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) config('app.name', 'CalaiaPH'));
        $account = rawurlencode($user->email ?: $user->username ?: 'account-'.$user->getKey());

        return 'otpauth://totp/'.$issuer.':'.$account
            .'?secret='.$secret
            .'&issuer='.$issuer
            .'&algorithm=SHA1'
            .'&digits='.self::DIGITS
            .'&period='.self::PERIOD;
    }

    public function verifyCode(?string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if ($secret === null || ! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timeSlice = (int) floor(time() / self::PERIOD);

        for ($offset = -self::WINDOW; $offset <= self::WINDOW; $offset++) {
            if (hash_equals($this->totp($secret, $timeSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function currentCode(string $secret): string
    {
        return $this->totp($secret, (int) floor(time() / self::PERIOD));
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(5).'-'.Str::random(5));
        }

        return $codes;
    }

    /**
     * @param array<int, string> $codes
     */
    public function encryptRecoveryCodes(array $codes): string
    {
        $hashes = array_map(fn (string $code): string => $this->hashRecoveryCode($code), $codes);

        return Crypt::encryptString(json_encode(array_values($hashes), JSON_THROW_ON_ERROR));
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $hashes = $this->recoveryCodeHashes($user);
        $submittedHash = $this->hashRecoveryCode($code);
        $matchedIndex = null;

        foreach ($hashes as $index => $hash) {
            if (hash_equals($hash, $submittedHash)) {
                $matchedIndex = $index;
                break;
            }
        }

        if ($matchedIndex === null) {
            return false;
        }

        unset($hashes[$matchedIndex]);

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($hashes), JSON_THROW_ON_ERROR)),
        ])->save();

        return true;
    }

    public function hasEnabledTwoFactor(User $user): bool
    {
        return filled($user->two_factor_secret) && $user->two_factor_confirmed_at !== null;
    }

    public function maskedAccount(User $user): string
    {
        $email = (string) $user->email;

        if (! str_contains($email, '@')) {
            return $user->username ?: 'your account';
        }

        [$name, $domain] = explode('@', $email, 2);
        $prefix = Str::substr($name, 0, min(2, max(1, Str::length($name))));

        return $prefix.'****@'.$domain;
    }

    /**
     * @return array<int, string>
     */
    private function recoveryCodeHashes(User $user): array
    {
        if (blank($user->two_factor_recovery_codes)) {
            return [];
        }

        $json = Crypt::decryptString((string) $user->two_factor_recovery_codes);
        $decoded = json_decode($json, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private function hashRecoveryCode(string $code): string
    {
        $normalized = Str::upper(str_replace([' ', '-'], '', trim($code)));

        return hash_hmac('sha256', $normalized, (string) Config::get('app.key'));
    }

    private function totp(string $secret, int $timeSlice): string
    {
        $key = $this->base32Decode($secret);
        $counter = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $binary): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        $encoded = '';

        foreach (str_split($binary) as $character) {
            $bits .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }

        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }

            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = Str::upper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?? '');
        $bits = '';
        $decoded = '';

        foreach (str_split($secret) as $character) {
            $position = strpos($alphabet, $character);

            if ($position === false) {
                continue;
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }

        return $decoded;
    }
}
