<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'first_name' => 'Martin',
                'last_name' => 'Dupont',
                'email' => 'martin.dupont@email.com',
                'phone' => '01 23 45 67 89',
                'date_of_birth' => '1980-05-15',
                'address' => '15 Rue de la Paix',
                'city' => 'Paris',
                'postal_code' => '75001',
                'emergency_contact_name' => 'Marie Dupont',
                'emergency_contact_phone' => '01 23 45 67 90',
                'allergies' => 'Pénicilline, Aspirine',
                'medical_notes' => 'Diabétique, traitement en cours',
                'insurance_number' => '1234567890123',
                'active' => true,
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Laurent',
                'email' => 'sophie.laurent@email.com',
                'phone' => '01 34 56 78 90',
                'date_of_birth' => '1975-12-03',
                'address' => '42 Avenue des Champs',
                'city' => 'Lyon',
                'postal_code' => '69000',
                'emergency_contact_name' => 'Jean Laurent',
                'emergency_contact_phone' => '01 34 56 78 91',
                'allergies' => null,
                'medical_notes' => 'Hypertension artérielle',
                'insurance_number' => '2345678901234',
                'active' => true,
            ],
            [
                'first_name' => 'Jean',
                'last_name' => 'Petit',
                'email' => 'jean.petit@email.com',
                'phone' => '01 45 67 89 01',
                'date_of_birth' => '1990-08-22',
                'address' => '8 Rue du Commerce',
                'city' => 'Marseille',
                'postal_code' => '13000',
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
                'allergies' => 'Lactose',
                'medical_notes' => null,
                'insurance_number' => '3456789012345',
                'active' => true,
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Leclerc',
                'email' => 'marie.leclerc@email.com',
                'phone' => '01 56 78 90 12',
                'date_of_birth' => '1965-02-14',
                'address' => '25 Boulevard Saint-Michel',
                'city' => 'Toulouse',
                'postal_code' => '31000',
                'emergency_contact_name' => 'Pierre Leclerc',
                'emergency_contact_phone' => '01 56 78 90 13',
                'allergies' => 'Iode, Fruits de mer',
                'medical_notes' => 'Arthrose, traitement anti-inflammatoire',
                'insurance_number' => '4567890123456',
                'active' => true,
            ],
            [
                'first_name' => 'Lucas',
                'last_name' => 'Moreau',
                'email' => 'lucas.moreau@email.com',
                'phone' => '01 67 89 01 23',
                'date_of_birth' => '1995-11-07',
                'address' => '12 Rue de la République',
                'city' => 'Nice',
                'postal_code' => '06000',
                'emergency_contact_name' => 'Anne Moreau',
                'emergency_contact_phone' => '01 67 89 01 24',
                'allergies' => null,
                'medical_notes' => 'Asthme léger',
                'insurance_number' => '5678901234567',
                'active' => true,
            ],
            [
                'first_name' => 'Émilie',
                'last_name' => 'Bernard',
                'email' => 'emilie.bernard@email.com',
                'phone' => '01 78 90 12 34',
                'date_of_birth' => '1988-07-19',
                'address' => '33 Rue Victor Hugo',
                'city' => 'Nantes',
                'postal_code' => '44000',
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
                'allergies' => 'Pollen, Acariens',
                'medical_notes' => 'Allergies saisonnières',
                'insurance_number' => '6789012345678',
                'active' => true,
            ],
            [
                'first_name' => 'Antoine',
                'last_name' => 'Rousseau',
                'email' => null,
                'phone' => '01 89 01 23 45',
                'date_of_birth' => '1972-04-30',
                'address' => '7 Place de la Mairie',
                'city' => 'Strasbourg',
                'postal_code' => '67000',
                'emergency_contact_name' => 'Sylvie Rousseau',
                'emergency_contact_phone' => '01 89 01 23 46',
                'allergies' => null,
                'medical_notes' => 'Cholestérol élevé',
                'insurance_number' => '7890123456789',
                'active' => true,
            ],
            [
                'first_name' => 'Claire',
                'last_name' => 'Dubois',
                'email' => 'claire.dubois@email.com',
                'phone' => null,
                'date_of_birth' => '1983-09-12',
                'address' => '18 Rue des Lilas',
                'city' => 'Bordeaux',
                'postal_code' => '33000',
                'emergency_contact_name' => 'Paul Dubois',
                'emergency_contact_phone' => '01 90 12 34 56',
                'allergies' => 'Antibiotiques (Amoxicilline)',
                'medical_notes' => 'Enceinte - 6 mois',
                'insurance_number' => '8901234567890',
                'active' => true,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}