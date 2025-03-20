<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('mnt_clientes')->insert([
            [
                'nombre' => 'Juan',
                'apellido' => 'Rauda',
                'email' => 'juan@gmail.com',
                'direccion_envio' => 'San Salvador',
                'direccion_facturacion' => 'San Salvador',
                'telefono' => '12345678',
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
