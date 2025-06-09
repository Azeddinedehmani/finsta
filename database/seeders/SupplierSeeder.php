<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Pharma Distrib', 
                'contact_person' => 'Jean Dupont',
                'phone_number' => '01 23 45 67 89',
                'email' => 'contact@pharmadistrib.com',
                'address' => '123 Avenue de la Pharmacie, 75001 Paris',
                'notes' => 'Fournisseur principal de médicaments génériques',
                'active' => true
            ],
            [
                'name' => 'MediStock', 
                'contact_person' => 'Marie Laurent',
                'phone_number' => '01 98 76 54 32',
                'email' => 'info@medistock.fr',
                'address' => '45 Rue des Laboratoires, 69002 Lyon',
                'notes' => 'Fournisseur spécialisé en médicaments de marque',
                'active' => true
            ],
            [
                'name' => 'BioPharm', 
                'contact_person' => 'Paul Mercier',
                'phone_number' => '01 34 56 78 90',
                'email' => 'commandes@biopharm.fr',
                'address' => '789 Boulevard Médical, 33000 Bordeaux',
                'notes' => 'Produits biologiques et naturels',
                'active' => true
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}