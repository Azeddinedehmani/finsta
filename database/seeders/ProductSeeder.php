<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Analgésiques
            [
                'name' => 'Paracétamol',
                'dosage' => '500mg',
                'description' => 'Antalgique et antipyrétique',
                'barcode' => '3401560250001',
                'purchase_price' => 1.50,
                'selling_price' => 2.90,
                'stock_quantity' => 5,
                'stock_threshold' => 20,
                'category_name' => 'Analgésiques',
                'supplier_name' => 'Pharma Distrib',
                'location' => 'A1-01',
                'prescription_required' => false,
                'expiry_date' => '2025-12-31',
            ],
            [
                'name' => 'Doliprane',
                'dosage' => '1000mg',
                'description' => 'Paracétamol dosage fort',
                'barcode' => '3401560250002',
                'purchase_price' => 2.20,
                'selling_price' => 4.50,
                'stock_quantity' => 15,
                'stock_threshold' => 10,
                'category_name' => 'Analgésiques',
                'supplier_name' => 'Pharma Distrib',
                'location' => 'A1-02',
                'prescription_required' => false,
                'expiry_date' => '2025-11-30',
            ],
            [
                'name' => 'Ibuprofène',
                'dosage' => '400mg',
                'description' => 'Anti-inflammatoire non stéroïdien',
                'barcode' => '3401560250003',
                'purchase_price' => 2.80,
                'selling_price' => 5.20,
                'stock_quantity' => 12,
                'stock_threshold' => 15,
                'category_name' => 'Anti-inflammatoires',
                'supplier_name' => 'MediStock',
                'location' => 'A2-01',
                'prescription_required' => false,
                'expiry_date' => '2025-10-15',
            ],
            
            // Antibiotiques
            [
                'name' => 'Amoxicilline',
                'dosage' => '1g',
                'description' => 'Antibiotique à large spectre',
                'barcode' => '3401560250004',
                'purchase_price' => 5.40,
                'selling_price' => 12.80,
                'stock_quantity' => 15,
                'stock_threshold' => 8,
                'category_name' => 'Antibiotiques',
                'supplier_name' => 'BioPharm',
                'location' => 'B1-01',
                'prescription_required' => true,
                'expiry_date' => '2025-08-30',
            ],
            [
                'name' => 'Augmentin',
                'dosage' => '500mg/125mg',
                'description' => 'Amoxicilline + Acide clavulanique',
                'barcode' => '3401560250005',
                'purchase_price' => 8.90,
                'selling_price' => 18.50,
                'stock_quantity' => 8,
                'stock_threshold' => 5,
                'category_name' => 'Antibiotiques',
                'supplier_name' => 'BioPharm',
                'location' => 'B1-02',
                'prescription_required' => true,
                'expiry_date' => '2025-09-15',
            ],
            
            // Vitamines et suppléments
            [
                'name' => 'Vitamine C',
                'dosage' => '1000mg',
                'description' => 'Complément alimentaire vitamine C',
                'barcode' => '3401560250006',
                'purchase_price' => 3.20,
                'selling_price' => 7.90,
                'stock_quantity' => 25,
                'stock_threshold' => 10,
                'category_name' => 'Vitamines et suppléments',
                'supplier_name' => 'Pharma Distrib',
                'location' => 'C1-01',
                'prescription_required' => false,
                'expiry_date' => '2026-03-30',
            ],
            [
                'name' => 'Multivitamines',
                'dosage' => null,
                'description' => 'Complexe multivitaminé complet',
                'barcode' => '3401560250007',
                'purchase_price' => 8.50,
                'selling_price' => 16.90,
                'stock_quantity' => 18,
                'stock_threshold' => 8,
                'category_name' => 'Vitamines et suppléments',
                'supplier_name' => 'BioPharm',
                'location' => 'C1-02',
                'prescription_required' => false,
                'expiry_date' => '2026-01-15',
            ],
            
            // Antihistaminiques
            [
                'name' => 'Cétirizine',
                'dosage' => '10mg',
                'description' => 'Antihistaminique contre les allergies',
                'barcode' => '3401560250008',
                'purchase_price' => 2.90,
                'selling_price' => 6.80,
                'stock_quantity' => 22,
                'stock_threshold' => 12,
                'category_name' => 'Antihistaminiques',
                'supplier_name' => 'MediStock',
                'location' => 'D1-01',
                'prescription_required' => false,
                'expiry_date' => '2025-12-20',
            ],
            [
                'name' => 'Loratadine',
                'dosage' => '10mg',
                'description' => 'Antihistaminique longue durée',
                'barcode' => '3401560250009',
                'purchase_price' => 3.40,
                'selling_price' => 7.50,
                'stock_quantity' => 16,
                'stock_threshold' => 10,
                'category_name' => 'Antihistaminiques',
                'supplier_name' => 'MediStock',
                'location' => 'D1-02',
                'prescription_required' => false,
                'expiry_date' => '2025-11-10',
            ],
            
            // Soins de la peau
            [
                'name' => 'Crème hydratante',
                'dosage' => '50ml',
                'description' => 'Crème hydratante visage et corps',
                'barcode' => '3401560250010',
                'purchase_price' => 4.80,
                'selling_price' => 12.90,
                'stock_quantity' => 30,
                'stock_threshold' => 15,
                'category_name' => 'Soins de la peau',
                'supplier_name' => 'Pharma Distrib',
                'location' => 'E1-01',
                'prescription_required' => false,
                'expiry_date' => '2026-06-30',
            ],
            [
                'name' => 'Bétadine',
                'dosage' => '10% - 125ml',
                'description' => 'Solution antiseptique',
                'barcode' => '3401560250011',
                'purchase_price' => 3.60,
                'selling_price' => 8.90,
                'stock_quantity' => 20,
                'stock_threshold' => 12,
                'category_name' => 'Soins de la peau',
                'supplier_name' => 'MediStock',
                'location' => 'E1-02',
                'prescription_required' => false,
                'expiry_date' => '2025-07-15',
            ],
            
            // Matériel médical
            [
                'name' => 'Pansements adhésifs',
                'dosage' => 'Boîte de 20',
                'description' => 'Pansements stériles assortis',
                'barcode' => '3401560250012',
                'purchase_price' => 2.40,
                'selling_price' => 5.90,
                'stock_quantity' => 45,
                'stock_threshold' => 20,
                'category_name' => 'Matériel médical',
                'supplier_name' => 'Pharma Distrib',
                'location' => 'F1-01',
                'prescription_required' => false,
                'expiry_date' => '2027-12-31',
            ],
            [
                'name' => 'Thermomètre digital',
                'dosage' => null,
                'description' => 'Thermomètre électronique médical',
                'barcode' => '3401560250013',
                'purchase_price' => 8.90,
                'selling_price' => 18.50,
                'stock_quantity' => 12,
                'stock_threshold' => 5,
                'category_name' => 'Matériel médical',
                'supplier_name' => 'MediStock',
                'location' => 'F1-02',
                'prescription_required' => false,
                'expiry_date' => null,
            ],
            
            // Produits spéciaux
            [
                'name' => 'Doliprane sirop enfant',
                'dosage' => '2,4% - 100ml',
                'description' => 'Paracétamol sirop pour enfants',
                'barcode' => '3401560250014',
                'purchase_price' => 3.80,
                'selling_price' => 8.20,
                'stock_quantity' => 3,
                'stock_threshold' => 10,
                'category_name' => 'Analgésiques',
                'supplier_name' => 'MediStock',
                'location' => 'A1-03',
                'prescription_required' => false,
                'expiry_date' => '2025-05-30',
            ],
            [
                'name' => 'Ventoline',
                'dosage' => '100µg/dose',
                'description' => 'Bronchodilatateur inhalé',
                'barcode' => '3401560250015',
                'purchase_price' => 4.20,
                'selling_price' => 9.80,
                'stock_quantity' => 8,
                'stock_threshold' => 12,
                'category_name' => 'Anti-inflammatoires',
                'supplier_name' => 'BioPharm',
                'location' => 'A2-02',
                'prescription_required' => true,
                'expiry_date' => '2025-09-30',
            ],
        ];

        foreach ($products as $productData) {
            // Trouver la catégorie
            $category = Category::where('name', $productData['category_name'])->first();
            if (!$category) {
                continue;
            }
            
            // Trouver le fournisseur
            $supplier = Supplier::where('name', $productData['supplier_name'])->first();
            
            Product::create([
                'name' => $productData['name'],
                'dosage' => $productData['dosage'],
                'description' => $productData['description'],
                'barcode' => $productData['barcode'],
                'purchase_price' => $productData['purchase_price'],
                'selling_price' => $productData['selling_price'],
                'stock_quantity' => $productData['stock_quantity'],
                'stock_threshold' => $productData['stock_threshold'],
                'category_id' => $category->id,
                'supplier_id' => $supplier ? $supplier->id : null,
                'location' => $productData['location'],
                'prescription_required' => $productData['prescription_required'],
                'expiry_date' => $productData['expiry_date'],
            ]);
        }
    }
}