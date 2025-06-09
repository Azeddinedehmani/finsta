<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used',
        'attempts'  // ADD THIS LINE
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
        'attempts' => 'integer'  // ADD THIS LINE
    ];

    /**
     * Generate a new reset code for the given email
     */
    public static function generateCode(string $email): string
    {
        // Invalider tous les codes précédents pour cet email
        self::where('email', $email)->update(['used' => true]);

        // Générer un nouveau code à 6 chiffres
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le nouveau code (valide pendant 10 minutes)
        self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
            'used' => false,
            'attempts' => 0  // ADD THIS LINE
        ]);

        return $code;
    }

    /**
     * Verify if the code is valid (REPLACE YOUR EXISTING METHOD)
     */
    public static function verifyCode(string $email, string $code): bool
    {
        $resetCode = self::where('email', $email)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($resetCode) {
            // Marquer le code comme utilisé
            $resetCode->update(['used' => true]);
            return true;
        }

        // Increment failed attempts
        self::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->increment('attempts');

        // Invalidate codes after 3 failed attempts
        self::where('email', $email)
            ->where('attempts', '>=', 3)
            ->update(['used' => true]);

        return false;
    }

    /**
     * Check if too many attempts (ADD THIS NEW METHOD)
     */
    public static function hasTooManyAttempts(string $email): bool
    {
        return self::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->where('attempts', '>=', 3)
            ->exists();
    }

    /**
     * Clean expired codes
     */
    public static function cleanExpired(): void
    {
        self::where('expires_at', '<', Carbon::now())
            ->orWhere('used', true)
            ->delete();
    }
}