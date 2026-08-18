<?php

namespace Database\Seeders;

use App\Models\Tooth;
use Illuminate\Database\Seeder;

class ToothCatalogSeeder extends Seeder
{
    /**
     * The 52 FDI two-digit tooth codes + names that the Flutter
     * teeth_selector chart renders. Idempotent (upsert by tooth_code).
     */
    public function run(): void
    {
        $teeth = [
            // ---- Permanent upper right (11-18) ----
            ['11', 'Upper right central incisor'],
            ['12', 'Upper right lateral incisor'],
            ['13', 'Upper right canine'],
            ['14', 'Upper right first premolar'],
            ['15', 'Upper right second premolar'],
            ['16', 'Upper right first molar'],
            ['17', 'Upper right second molar'],
            ['18', 'Upper right third molar'],
            // ---- Permanent upper left (21-28) ----
            ['21', 'Upper left central incisor'],
            ['22', 'Upper left lateral incisor'],
            ['23', 'Upper left canine'],
            ['24', 'Upper left first premolar'],
            ['25', 'Upper left second premolar'],
            ['26', 'Upper left first molar'],
            ['27', 'Upper left second molar'],
            ['28', 'Upper left third molar'],
            // ---- Permanent lower left (31-38) ----
            ['31', 'Lower left central incisor'],
            ['32', 'Lower left lateral incisor'],
            ['33', 'Lower left canine'],
            ['34', 'Lower left first premolar'],
            ['35', 'Lower left second premolar'],
            ['36', 'Lower left first molar'],
            ['37', 'Lower left second molar'],
            ['38', 'Lower left third molar'],
            // ---- Permanent lower right (41-48) ----
            ['41', 'Lower right central incisor'],
            ['42', 'Lower right lateral incisor'],
            ['43', 'Lower right canine'],
            ['44', 'Lower right first premolar'],
            ['45', 'Lower right second premolar'],
            ['46', 'Lower right first molar'],
            ['47', 'Lower right second molar'],
            ['48', 'Lower right third molar'],
            // ---- Deciduous upper right (51-55) ----
            ['51', 'Upper right deciduous central incisor'],
            ['52', 'Upper right deciduous lateral incisor'],
            ['53', 'Upper right deciduous canine'],
            ['54', 'Upper right deciduous first molar'],
            ['55', 'Upper right deciduous second molar'],
            // ---- Deciduous upper left (61-65) ----
            ['61', 'Upper left deciduous central incisor'],
            ['62', 'Upper left deciduous lateral incisor'],
            ['63', 'Upper left deciduous canine'],
            ['64', 'Upper left deciduous first molar'],
            ['65', 'Upper left deciduous second molar'],
            // ---- Deciduous lower left (71-75) ----
            ['71', 'Lower left deciduous central incisor'],
            ['72', 'Lower left deciduous lateral incisor'],
            ['73', 'Lower left deciduous canine'],
            ['74', 'Lower left deciduous first molar'],
            ['75', 'Lower left deciduous second molar'],
            // ---- Deciduous lower right (81-85) ----
            ['81', 'Lower right deciduous central incisor'],
            ['82', 'Lower right deciduous lateral incisor'],
            ['83', 'Lower right deciduous canine'],
            ['84', 'Lower right deciduous first molar'],
            ['85', 'Lower right deciduous second molar'],
        ];

        foreach ($teeth as [$code, $name]) {
            Tooth::query()->updateOrCreate(
                ['tooth_code' => $code],
                ['tooth_name' => $name],
            );
        }
    }
}
