<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ComplaintStatus;

class ComplaintStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['جديدة' , 'قيد المعالجة' , 'منجزة' , 'مرفوضة'];

        for ($i=0; $i < 4 ; $i++) {
            ComplaintStatus::query()->create([
           'status' => $status[$i] ,
            ]); }
    }
}
