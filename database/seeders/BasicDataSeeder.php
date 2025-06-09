<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les utilisateurs par défaut
        User::firstOrCreate(
            ['email' => 'admin@pharmacia.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'role' => 'responsable'
            ]
        );

        User::firstOrCreate(
            ['email' => 'pharmacien@pharmacia.com'],
            [
                'name' => 'Jean Dupont',
                'password' => Hash::make('password'),
                'role' => 'pharmacien'
            ]
        );

        // Créer les catégories de base
        $categories = [
            ['name' => 'Médicaments génériques', 'description' => 'Médicaments génériques courants'],
            ['name' => 'Antibiotiques', 'description' => 'Médicaments antibiotiques'],
            ['name' => 'Antalgiques', 'description' => 'Médicaments contre la douleur'],
            ['name' => 'Vitamines', 'description' => 'Compléments vitaminiques'],
            ['name' => 'Parapharmacie', 'description' => 'Produits de parapharmacie'],
            ['name' => 'Matériel médical', 'description' => 'Matériel et dispositifs médicaux'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Créer les fournisseurs de base
        $suppliers = [
            [
                'name' => 'Pharma Distrib',
                'contact_person' => 'Marie Martin',
                'phone_number' => '01 45 67 89 01',
                'email' => 'contact@pharmadistrib.fr',
                'address' => '123 Rue de la Pharmacie, 75001 Paris',
                'notes' => 'Fournisseur principal pour les médicaments génériques',
                'active' => true
            ],
            [
                'name' => 'MediStock',
                'contact_person' => 'Pierre Durand',
                'phone_number' => '01 45 67 89 02',
                'email' => 'commandes@medistock.com',
                'address' => '456 Avenue de la Santé, 69001 Lyon',
                'notes' => 'Spécialisé dans les antibiotiques et médicaments spécialisés',
                'active' => true
            ],
            [
                'name' => 'BioPharm',
                'contact_person' => 'Sophie Bernard',
                'phone_number' => '01 45 67 89 03',
                'email' => 'info@biopharm.fr',
                'address' => '789 Boulevard Médical, 13001 Marseille',
                'notes' => 'Fournisseur de produits biologiques et vitamines',
                'active' => true
            ],
            [
                'name' => 'MedEquip',
                'contact_person' => 'Laurent Rousseau',
                'phone_number' => '01 45 67 89 04',
                'email' => 'ventes@medequip.com',
                'address' => '321 Rue du Matériel, 31000 Toulouse',
                'notes' => 'Matériel médical et dispositifs',
                'active' => true
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }

        // Créer quelques produits de démonstration
        $products = [
            [
                'name' => 'Paracétamol',
                'dosage' => '500mg',
                'description' => 'Antalgique et antipyrétique',
                'barcode' => '3401596048541',
                'purchase_price' => 2.50,
                'selling_price' => 3.75,
                'stock_quantity' => 15,
                'stock_threshold' => 20,
                'category_id' => 3, // Antalgiques
                'supplier_id' => 1, // Pharma Distrib
                'location' => 'A1-01',
                'prescription_required' => false,
            ],
            [
                'name' => 'Amoxicilline',
                'dosage' => '1g',
                'description' => 'Antibiotique à large spectre',
                'barcode' => '3401597014526',
                'purchase_price' => 8.20,
                'selling_price' => 12.30,
                'stock_quantity' => 8,
                'stock_threshold' => 15,
                'category_id' => 2, // Antibiotiques
                'supplier_id' => 2, // MediStock
                'location' => 'B2-03',
                'prescription_required' => true,
            ],
            [
                'name' => 'Ibuprofène',
                'dosage' => '400mg',
                'description' => 'Anti-inflammatoire non stéroïdien',
                'barcode' => '3401598547821',
                'purchase_price' => 1.80,
                'selling_price' => 2.70,
                'stock_quantity' => 25,
                'stock_threshold' => 20,
                'category_id' => 3, // Antalgiques
                'supplier_id' => 1, // Pharma Distrib
                'location' => 'A1-02',
                'prescription_required' => false,
            ],
            [
                'name' => 'Vitamine D3',
                'dosage' => '1000 UI',
                'description' => 'Complément en vitamine D',
                'barcode' => '3401599874523',
                'purchase_price' => 6.50,
                'selling_price' => 9.75,
                'stock_quantity' => 12,
                'stock_threshold' => 10,
                'category_id' => 4, // Vitamines
                'supplier_id' => 3, // BioPharm
                'location' => 'C3-01',
                'prescription_required' => false,
            ],
            [
                'name' => 'Thermomètre digital',
                'dosage' => null,
                'description' => 'Thermomètre médical digital',
                'barcode' => '3401590123456',
                'purchase_price' => 12.00,
                'selling_price' => 18.00,
                'stock_quantity' => 5,
                'stock_threshold' => 8,
                'category_id' => 6, // Matériel médical
                'supplier_id' => 4, // MedEquip
                'location' => 'D4-01',
                'prescription_required' => false,
            ]
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['barcode' => $productData['barcode']],
                $productData
            );
        }
    }
}