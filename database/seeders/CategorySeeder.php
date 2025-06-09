<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Analgésiques', 'description' => 'Médicaments contre la douleur'],
            ['name' => 'Antibiotiques', 'description' => 'Médicaments pour traiter les infections bactériennes'],
            ['name' => 'Anti-inflammatoires', 'description' => 'Médicaments pour réduire l\'inflammation'],
            ['name' => 'Antihistaminiques', 'description' => 'Médicaments contre les allergies'],
            ['name' => 'Vitamines et suppléments', 'description' => 'Compléments alimentaires'],
            ['name' => 'Soins de la peau', 'description' => 'Produits pour la peau'],
            ['name' => 'Matériel médical', 'description' => 'Équipements et fournitures médicales'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}