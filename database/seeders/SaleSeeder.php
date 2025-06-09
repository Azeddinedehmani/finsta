<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $products = Product::all();
        $users = User::all();
        
        if ($clients->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders pour les clients, produits et utilisateurs.');
            return;
        }

        $sales = [
            [
                'client_id' => $clients->where('first_name', 'Martin')->where('last_name', 'Dupont')->first()?->id,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'has_prescription' => false,
                'prescription_number' => null,
                'discount_amount' => 0,
                'notes' => 'Client régulier',
                'sale_date' => Carbon::now()->subDays(2),
                'products' => [
                    ['name' => 'Doliprane', 'quantity' => 2],
                    ['name' => 'Vitamine C', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Sophie')->where('last_name', 'Laurent')->first()?->id,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'has_prescription' => true,
                'prescription_number' => 'ORD-20250523-001',
                'discount_amount' => 5.00,
                'notes' => 'Ordonnance Dr. Martin',
                'sale_date' => Carbon::now()->subDays(1),
                'products' => [
                    ['name' => 'Amoxicilline', 'quantity' => 1],
                    ['name' => 'Doliprane sirop enfant', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Jean')->where('last_name', 'Petit')->first()?->id,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'has_prescription' => false,
                'prescription_number' => null,
                'discount_amount' => 0,
                'notes' => null,
                'sale_date' => Carbon::now()->subHours(5),
                'products' => [
                    ['name' => 'Crème hydratante', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Marie')->where('last_name', 'Leclerc')->first()?->id,
                'payment_method' => 'insurance',
                'payment_status' => 'paid',
                'has_prescription' => true,
                'prescription_number' => 'ORD-20250523-002',
                'discount_amount' => 0,
                'notes' => 'Remboursement sécurité sociale',
                'sale_date' => Carbon::now()->subHours(3),
                'products' => [
                    ['name' => 'Paracétamol', 'quantity' => 3],
                    ['name' => 'Ibuprofène', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Lucas')->where('last_name', 'Moreau')->first()?->id,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'has_prescription' => false,
                'prescription_number' => null,
                'discount_amount' => 2.50,
                'notes' => 'Remise fidélité',
                'sale_date' => Carbon::now()->subHours(1),
                'products' => [
                    ['name' => 'Pansements adhésifs', 'quantity' => 2],
                    ['name' => 'Bétadine', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => null, // Vente anonyme
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'has_prescription' => false,
                'prescription_number' => null,
                'discount_amount' => 0,
                'notes' => 'Vente sans ordonnance',
                'sale_date' => Carbon::now()->subMinutes(30),
                'products' => [
                    ['name' => 'Cétirizine', 'quantity' => 1],
                    ['name' => 'Thermomètre digital', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Émilie')->where('last_name', 'Bernard')->first()?->id,
                'payment_method' => 'card',
                'payment_status' => 'pending',
                'has_prescription' => false,
                'prescription_number' => null,
                'discount_amount' => 0,
                'notes' => 'Paiement en attente de validation',
                'sale_date' => Carbon::now()->subMinutes(15),
                'products' => [
                    ['name' => 'Loratadine', 'quantity' => 2],
                    ['name' => 'Multivitamines', 'quantity' => 1],
                ]
            ],
            [
                'client_id' => $clients->where('first_name', 'Claire')->where('last_name', 'Dubois')->first()?->id,
                'payment_method' => 'insurance',
                'payment_status' => 'paid',
                'has_prescription' => true,
                'prescription_number' => 'ORD-20250523-003',
                'discount_amount' => 0,
                'notes' => 'Femme enceinte - Vérification posologie effectuée',
                'sale_date' => Carbon::now(),
                'products' => [
                    ['name' => 'Paracétamol', 'quantity' => 1],
                    ['name' => 'Vitamine C', 'quantity' => 2],
                ]
            ],
        ];

        foreach ($sales as $saleData) {
            // Créer la vente
            $sale = Sale::create([
                'client_id' => $saleData['client_id'],
                'user_id' => $users->random()->id,
                'payment_method' => $saleData['payment_method'],
                'payment_status' => $saleData['payment_status'],
                'has_prescription' => $saleData['has_prescription'],
                'prescription_number' => $saleData['prescription_number'],
                'discount_amount' => $saleData['discount_amount'],
                'notes' => $saleData['notes'],
                'sale_date' => $saleData['sale_date'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            // Ajouter les produits à la vente
            foreach ($saleData['products'] as $productData) {
                $product = $products->where('name', $productData['name'])->first();
                
                if ($product && $product->stock_quantity >= $productData['quantity']) {
                    // Créer l'item de vente
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $product->selling_price,
                    ]);

                    // Mettre à jour le stock
                    $product->decrement('stock_quantity', $productData['quantity']);
                }
            }

            // Recalculer les totaux
            $sale->calculateTotals();
        }
    }
}