<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::where('active', true)->get();
        $products = Product::all();
        $users = User::where('role', 'responsable')->get();
        
        if ($suppliers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders pour les fournisseurs, produits et utilisateurs.');
            return;
        }

        $purchases = [
            [
                'supplier_id' => $suppliers->where('name', 'Pharma Distrib')->first()?->id,
                'status' => 'received',
                'order_date' => Carbon::now()->subDays(10),
                'expected_date' => Carbon::now()->subDays(3),
                'received_date' => Carbon::now()->subDays(2),
                'notes' => 'Livraison conforme, tous les produits reçus en bon état',
                'items' => [
                    ['product_name' => 'Paracétamol', 'quantity_ordered' => 50, 'quantity_received' => 50, 'unit_price' => 1.50],
                    ['product_name' => 'Doliprane', 'quantity_ordered' => 30, 'quantity_received' => 30, 'unit_price' => 2.20],
                    ['product_name' => 'Vitamine C', 'quantity_ordered' => 25, 'quantity_received' => 25, 'unit_price' => 3.20],
                ]
            ],
            [
                'supplier_id' => $suppliers->where('name', 'MediStock')->first()?->id,
                'status' => 'partially_received',
                'order_date' => Carbon::now()->subDays(7),
                'expected_date' => Carbon::now()->subDays(1),
                'received_date' => null,
                'notes' => 'Livraison partielle reçue, reste à venir',
                'items' => [
                    ['product_name' => 'Ibuprofène', 'quantity_ordered' => 40, 'quantity_received' => 25, 'unit_price' => 2.80],
                    ['product_name' => 'Cétirizine', 'quantity_ordered' => 20, 'quantity_received' => 20, 'unit_price' => 2.90],
                    ['product_name' => 'Loratadine', 'quantity_ordered' => 15, 'quantity_received' => 0, 'unit_price' => 3.40],
                ]
            ],
            [
                'supplier_id' => $suppliers->where('name', 'BioPharm')->first()?->id,
                'status' => 'pending',
                'order_date' => Carbon::now()->subDays(5),
                'expected_date' => Carbon::now()->addDays(2),
                'received_date' => null,
                'notes' => 'Commande urgente pour réapprovisionner les stocks',
                'items' => [
                    ['product_name' => 'Amoxicilline', 'quantity_ordered' => 20, 'quantity_received' => 0, 'unit_price' => 5.40],
                    ['product_name' => 'Augmentin', 'quantity_ordered' => 15, 'quantity_received' => 0, 'unit_price' => 8.90],
                    ['product_name' => 'Multivitamines', 'quantity_ordered' => 12, 'quantity_received' => 0, 'unit_price' => 8.50],
                ]
            ],
            [
                'supplier_id' => $suppliers->where('name', 'Pharma Distrib')->first()?->id,
                'status' => 'pending',
                'order_date' => Carbon::now()->subDays(3),
                'expected_date' => Carbon::now()->addDays(5),
                'received_date' => null,
                'notes' => 'Commande pour réapprovisionner les produits de parapharmacie',
                'items' => [
                    ['product_name' => 'Crème hydratante', 'quantity_ordered' => 18, 'quantity_received' => 0, 'unit_price' => 4.80],
                    ['product_name' => 'Pansements adhésifs', 'quantity_ordered' => 30, 'quantity_received' => 0, 'unit_price' => 2.40],
                ]
            ],
            [
                'supplier_id' => $suppliers->where('name', 'MediStock')->first()?->id,
                'status' => 'pending',
                'order_date' => Carbon::now()->subDays(8),
                'expected_date' => Carbon::now()->subDays(1), // En retard
                'received_date' => null,
                'notes' => 'Commande en retard, contacter le fournisseur',
                'items' => [
                    ['product_name' => 'Bétadine', 'quantity_ordered' => 25, 'quantity_received' => 0, 'unit_price' => 3.60],
                    ['product_name' => 'Thermomètre digital', 'quantity_ordered' => 8, 'quantity_received' => 0, 'unit_price' => 8.90],
                    ['product_name' => 'Doliprane sirop enfant', 'quantity_ordered' => 20, 'quantity_received' => 0, 'unit_price' => 3.80],
                ]
            ],
            [
                'supplier_id' => $suppliers->where('name', 'BioPharm')->first()?->id,
                'status' => 'cancelled',
                'order_date' => Carbon::now()->subDays(15),
                'expected_date' => Carbon::now()->subDays(10),
                'received_date' => null,
                'notes' => 'Commande annulée - produit non disponible chez le fournisseur',
                'items' => [
                    ['product_name' => 'Ventoline', 'quantity_ordered' => 10, 'quantity_received' => 0, 'unit_price' => 4.20],
                ]
            ],
        ];

        foreach ($purchases as $purchaseData) {
            if (!$purchaseData['supplier_id']) continue;

            // Créer la commande d'achat
            $purchase = Purchase::create([
                'supplier_id' => $purchaseData['supplier_id'],
                'user_id' => $users->random()->id,
                'status' => $purchaseData['status'],
                'order_date' => $purchaseData['order_date'],
                'expected_date' => $purchaseData['expected_date'],
                'received_date' => $purchaseData['received_date'],
                'notes' => $purchaseData['notes'],
                'subtotal' => 0, // Sera calculé après
                'tax_amount' => 0, // Sera calculé après
                'total_amount' => 0, // Sera calculé après
            ]);

            // Ajouter les produits à la commande
            foreach ($purchaseData['items'] as $itemData) {
                $product = $products->where('name', $itemData['product_name'])->first();
                
                if ($product) {
                    $totalPrice = $itemData['quantity_ordered'] * $itemData['unit_price'];
                    
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity_ordered' => $itemData['quantity_ordered'],
                        'quantity_received' => $itemData['quantity_received'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $totalPrice,
                    ]);

                    // Mettre à jour le stock si des produits ont été reçus
                    if ($itemData['quantity_received'] > 0) {
                        $product->increment('stock_quantity', $itemData['quantity_received']);
                    }
                }
            }

            // Recalculer les totaux
            $purchase->calculateTotals();
        }

        $this->command->info('PurchaseSeeder: ' . count($purchases) . ' commandes d\'achat créées avec succès.');
    }
}