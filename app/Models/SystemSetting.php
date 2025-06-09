<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the value with proper type casting
     */
    public function getTypedValueAttribute()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value
        };
    }

    /**
     * Set value with proper type handling
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };
    }

    /**
     * Get setting by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    /**
     * Set setting by key
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        $setting->group = $group;
        
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->save();
        
        return $setting;
    }

    /**
     * Get settings by group
     */
    public static function getGroup($group)
    {
        return static::where('group', $group)->get()->pluck('typed_value', 'key');
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings()
    {
        return static::all()->pluck('typed_value', 'key')->toArray();
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults()
    {
        $defaults = [
            // Application settings
            'app_name' => ['value' => 'Pharmacia', 'type' => 'string', 'group' => 'app', 'description' => 'Nom de l\'application'],
            'app_version' => ['value' => '1.0.0', 'type' => 'string', 'group' => 'app', 'description' => 'Version de l\'application'],
            'app_logo' => ['value' => null, 'type' => 'string', 'group' => 'app', 'description' => 'Logo de l\'application'],
            
            // Pharmacy settings
            'pharmacy_name' => ['value' => 'Pharmacie Centrale', 'type' => 'string', 'group' => 'pharmacy', 'description' => 'Nom de la pharmacie'],
            'pharmacy_address' => ['value' => '123 Avenue de la Santé, 75001 Paris', 'type' => 'string', 'group' => 'pharmacy', 'description' => 'Adresse de la pharmacie'],
            'pharmacy_phone' => ['value' => '01 23 45 67 89', 'type' => 'string', 'group' => 'pharmacy', 'description' => 'Téléphone de la pharmacie'],
            'pharmacy_email' => ['value' => 'contact@pharmacia.com', 'type' => 'string', 'group' => 'pharmacy', 'description' => 'Email de la pharmacie'],
            'pharmacy_siret' => ['value' => '123 456 789 00012', 'type' => 'string', 'group' => 'pharmacy', 'description' => 'Numéro SIRET'],
            
            // Tax settings
            'default_tax_rate' => ['value' => '20', 'type' => 'float', 'group' => 'tax', 'description' => 'Taux de TVA par défaut (%)'],
            'tax_included' => ['value' => false, 'type' => 'boolean', 'group' => 'tax', 'description' => 'Prix TTC par défaut'],
            
            // Stock settings
            'low_stock_alert' => ['value' => true, 'type' => 'boolean', 'group' => 'stock', 'description' => 'Alertes stock faible'],
            'auto_reorder' => ['value' => false, 'type' => 'boolean', 'group' => 'stock', 'description' => 'Réapprovisionnement automatique'],
            'expiry_alert_days' => ['value' => '30', 'type' => 'integer', 'group' => 'stock', 'description' => 'Alertes expiration (jours)'],
            
            // Security settings
            'session_lifetime' => ['value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Durée de session (minutes)'],
            'force_password_change' => ['value' => false, 'type' => 'boolean', 'group' => 'security', 'description' => 'Forcer changement mot de passe'],
            'password_min_length' => ['value' => '6', 'type' => 'integer', 'group' => 'security', 'description' => 'Longueur minimale mot de passe'],
            'login_attempts' => ['value' => '5', 'type' => 'integer', 'group' => 'security', 'description' => 'Tentatives de connexion max'],
            
            // Backup settings
            'auto_backup' => ['value' => true, 'type' => 'boolean', 'group' => 'backup', 'description' => 'Sauvegarde automatique'],
            'backup_frequency' => ['value' => 'daily', 'type' => 'string', 'group' => 'backup', 'description' => 'Fréquence sauvegarde'],
            'backup_retention' => ['value' => '30', 'type' => 'integer', 'group' => 'backup', 'description' => 'Rétention sauvegardes (jours)'],
            
            // Prescription settings
            'prescription_validity_days' => ['value' => '90', 'type' => 'integer', 'group' => 'prescription', 'description' => 'Validité ordonnance (jours)'],
            'prescription_renewal_alert' => ['value' => '7', 'type' => 'integer', 'group' => 'prescription', 'description' => 'Alerte renouvellement (jours)'],
        ];

        foreach ($defaults as $key => $config) {
            if (!static::where('key', $key)->exists()) {
                static::create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'description' => $config['description'],
                    'is_public' => in_array($config['group'], ['app', 'pharmacy'])
                ]);
            }
        }
    }
}