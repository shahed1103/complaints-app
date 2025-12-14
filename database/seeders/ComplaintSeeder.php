<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Complaint;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $complaint_type_id = ['1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3'];
        $complaint_department_id = ['1' ,'2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3' , '1' , '2' , '3'];
        $complaint_status_id = ['1' ,'2' , '1' , '1' ,'2' , '1' , '1' ,'2' , '1' , '1' ,'2' , '1' , '1' ,'2' , '1' , '1' ,'2' , '1' , '1' ,'2' , '1'];

        for ($i=0; $i < 21 ; $i++) {
            Complaint::query()->create([
           'user_id' => 2 ,
           'complaint_type_id' => $complaint_type_id[$i],
           'complaint_department_id' => $complaint_department_id[$i],
           'complaint_status_id' => $complaint_status_id[$i],
           'problem_description' => "Easily generate Lorem Ipsum placeholder text in any number of characters, words sentences or paragraphs. Learn about the origins of the passage and its history, from the Roman era to today.",
           'location' => "Syria-Damascus"
            ]);
        }
    }
}
