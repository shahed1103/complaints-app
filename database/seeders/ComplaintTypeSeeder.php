<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ComplaintType;

class ComplaintTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['طلب تسجيل' , 'خطأ فاتورة' , 'عطل فني'];

        for ($i=0; $i < 3 ; $i++) {
            ComplaintType::query()->create([
           'type' => $status[$i] ,
            ]); }
    }
}
