<?php
// database/migrations/2025_06_08_HHMMSS_add_client_tracking_to_sales_table.php
// Créez ce fichier avec: php artisan make:migration add_client_tracking_to_sales_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà avant de les ajouter
            if (!Schema::hasColumn('sales', 'client_name_at_deletion')) {
                $table->string('client_name_at_deletion')->nullable()->after('client_id');
            }
            
            if (!Schema::hasColumn('sales', 'deleted_client_data')) {
                $table->json('deleted_client_data')->nullable()->after('client_name_at_deletion');
            }
        });

        // Modifier la contrainte foreign key pour permettre SET NULL si elle existe
        try {
            Schema::table('sales', function (Blueprint $table) {
                // Supprimer l'ancienne contrainte si elle existe
                $table->dropForeign(['client_id']);
            });
        } catch (\Exception $e) {
            // Si la contrainte n'existe pas, continuer
        }

        // Ajouter la nouvelle contrainte
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Supprimer la contrainte foreign key
            $table->dropForeign(['client_id']);
            
            // Supprimer les colonnes si elles existent
            if (Schema::hasColumn('sales', 'deleted_client_data')) {
                $table->dropColumn('deleted_client_data');
            }
            
            if (Schema::hasColumn('sales', 'client_name_at_deletion')) {
                $table->dropColumn('client_name_at_deletion');
            }
        });

        // Remettre l'ancienne contrainte foreign key
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }
};