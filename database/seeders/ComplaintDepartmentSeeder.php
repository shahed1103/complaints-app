<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ComplaintDepartment;

class ComplaintDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['المؤسسة العامة للكهرباء' , 'المؤسسة العامة لمياه ' , 'المؤسسة العامة للاتصالات'];

        for ($i=0; $i < 3 ; $i++) {
            ComplaintDepartment::query()->create([
           'department_name' => $status[$i] ,
            ]); }    }
}
