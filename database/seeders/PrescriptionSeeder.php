<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $products = Product::where('prescription_required', true)->get();
        $users = User::all();
        
        if ($clients->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders pour les clients, produits et utilisateurs.');
            return;
        }

        $prescriptions = [
            [
                'client_id' => $clients->where('first_name', 'Martin')->where('last_name', 'Dupont')->first()?->id,
                'doctor_name' => 'Dr. Jean Martin',
                'doctor_phone' => '01 23 45 67 88',
                'doctor_speciality' => 'Médecin généraliste',
                'prescription_date' => Carbon::now()->subDays(5),
                'expiry_date' => Carbon::now()->addMonths(3),
                'status' => 'pending',
                'medical_notes' => 'Patient diabétique, surveiller la glycémie',
                'pharmacist_notes' => null,
                'items' => [
                    ['product_name' => 'Amoxicilline', 'quantity' => 2, 'dosage' => '1 comprimé matin et soir', 'duration' => 7, 'instructions' => 'Prendre au cours du repas'],
                    ['product_name' => 'Paracétamol', 'quantity' => 1, 'dosage' => '1 comprimé si douleur', 'duration' => null, 'instructions' => 'Maximum 4 par jour'],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Sophie')->where('last_name', 'Laurent')->first()?->id,
                'doctor_name' => 'Dr. Marie Dubois',
                'doctor_phone' => '01 23 45 67 89',
                'doctor_speciality' => 'Cardiologue',
                'prescription_date' => Carbon::now()->subDays(3),
                'expiry_date' => Carbon::now()->addMonths(3),
                'status' => 'partially_delivered',
                'medical_notes' => 'Hypertension artérielle, contrôler la tension',
                'pharmacist_notes' => 'Première délivrance effectuée',
                'items' => [
                    ['product_name' => 'Amoxicilline', 'quantity' => 3, 'dosage' => '1 comprimé le matin', 'duration' => 30, 'instructions' => 'À jeun'],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Marie')->where('last_name', 'Leclerc')->first()?->id,
                'doctor_name' => 'Dr. Pierre Moreau',
                'doctor_phone' => '01 23 45 67 90',
                'doctor_speciality' => 'Rhumatologue',
                'prescription_date' => Carbon::now()->subDays(1),
                'expiry_date' => Carbon::now()->addMonths(3),
                'status' => 'pending',
                'medical_notes' => 'Arthrose du genou, traitement anti-inflammatoire',
                'pharmacist_notes' => null,
                'items' => [
                    ['product_name' => 'Paracétamol', 'quantity' => 2, 'dosage' => '1 comprimé matin, midi et soir', 'duration' => 14, 'instructions' => 'Prendre pendant les repas'],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Claire')->where('last_name', 'Dubois')->first()?->id,
                'doctor_name' => 'Dr. Anne Petit',
                'doctor_phone' => '01 23 45 67 91',
                'doctor_speciality' => 'Gynécologue',
                'prescription_date' => Carbon::now()->subDays(7),
                'expiry_date' => Carbon::now()->addMonths(3),
                'status' => 'completed',
                'medical_notes' => 'Grossesse - 6ème mois, supplémentation recommandée',
                'pharmacist_notes' => 'Conseils donnés sur la prise pendant la grossesse',
                'items' => [
                    ['product_name' => 'Paracétamol', 'quantity' => 1, 'dosage' => '1 comprimé si besoin', 'duration' => null, 'instructions' => 'Maximum 3g par jour'],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Antoine')->where('last_name', 'Rousseau')->first()?->id,
                'doctor_name' => 'Dr. Laurent Blanc',
                'doctor_phone' => '01 23 45 67 92',
                'doctor_speciality' => 'Pneumologue',
                'prescription_date' => Carbon::now()->subMonths(4),
                'expiry_date' => Carbon::now()->subMonth(1),
                'status' => 'expired',
                'medical_notes' => 'Asthme, traitement de fond',
                'pharmacist_notes' => 'Ordonnance expirée, renouvellement nécessaire',
                'items' => [
                    ['product_name' => 'Amoxicilline', 'quantity' => 1, 'dosage' => '2 pulvérisations matin et soir', 'duration' => 30, 'instructions' => 'Bien agiter avant usage'],
                ]
            ],
            // Ordonnance qui expire bientôt
            [
                'client_id' => $clients->where('first_name', 'Émilie')->where('last_name', 'Bernard')->first()?->id,
                'doctor_name' => 'Dr. François Leroy',
                'doctor_phone' => '01 23 45 67 93',
                'doctor_speciality' => 'Allergologue',
                'prescription_date' => Carbon::now()->subMonths(2)->subWeeks(3),
                'expiry_date' => Carbon::now()->addDays(5), // Expire dans 5 jours
                'status' => 'pending',
                'medical_notes' => 'Allergie saisonnière, traitement préventif',
                'pharmacist_notes' => null,
                'items' => [
                    ['product_name' => 'Paracétamol', 'quantity' => 2, 'dosage' => '1 comprimé le matin', 'duration' => 30, 'instructions' => 'Commencer 15 jours avant la saison'],
                ]
            ],
        ];

        foreach ($prescriptions as $prescriptionData) {
            if (!$prescriptionData['client_id']) continue;

            // Créer la prescription
            $prescription = Prescription::create([
                'client_id' => $prescriptionData['client_id'],
                'doctor_name' => $prescriptionData['doctor_name'],
                'doctor_phone' => $prescriptionData['doctor_phone'],
                'doctor_speciality' => $prescriptionData['doctor_speciality'],
                'prescription_date' => $prescriptionData['prescription_date'],
                'expiry_date' => $prescriptionData['expiry_date'],
                'status' => $prescriptionData['status'],
                'medical_notes' => $prescriptionData['medical_notes'],
                'pharmacist_notes' => $prescriptionData['pharmacist_notes'],
                'created_by' => $users->random()->id,
                'delivered_by' => in_array($prescriptionData['status'], ['completed', 'partially_delivered']) ? $users->random()->id : null,
                'delivered_at' => $prescriptionData['status'] === 'completed' ? $prescriptionData['prescription_date']->addDays(1) : null,
            ]);

            // Ajouter les médicaments à la prescription
            foreach ($prescriptionData['items'] as $itemData) {
                $product = $products->where('name', $itemData['product_name'])->first();
                
                if ($product) {
                    $quantityDelivered = 0;
                    
                    // Simuler les délivrances selon le statut
                    if ($prescriptionData['status'] === 'completed') {
                        $quantityDelivered = $itemData['quantity'];
                    } elseif ($prescriptionData['status'] === 'partially_delivered') {
                        $quantityDelivered = max(1, intval($itemData['quantity'] / 2));
                    }

                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'product_id' => $product->id,
                        'quantity_prescribed' => $itemData['quantity'],
                        'quantity_delivered' => $quantityDelivered,
                        'dosage_instructions' => $itemData['dosage'],
                        'duration_days' => $itemData['duration'],
                        'instructions' => $itemData['instructions'],
                        'is_substitutable' => true,
                    ]);

                    // Mettre à jour le stock si délivré
                    if ($quantityDelivered > 0) {
                        $product->decrement('stock_quantity', $quantityDelivered);
                    }
                }
            }
        }

        $this->command->info('PrescriptionSeeder: ' . count($prescriptions) . ' ordonnances créées avec succès.');
    }
}