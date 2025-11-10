<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Storage;


class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {


        // 1. Create roles
        $AdminRole = Role::create(['name' => 'Admin']);
        $ClientRole = Role::create(['name' => 'Client']);
        $EmployeeRole = Role::create(['name' => 'Employee']);

        // 2. Create permissions
        $permissions = [ ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

      //assign permissions to roles
        // 3. Assign permissions

        $AdminRole->syncPermissions([]);
        $ClientRole->syncPermissions([]);
        $EmployeeRole->syncPermissions([]);


$sourcePath = public_path('uploads/seeder_photos/defualtProfilePhoto.png');
$targetPath = 'uploads/det/defualtProfilePhoto.png';

Storage::disk('public')->put($targetPath, File::get($sourcePath));


$admin = User::factory()->create([
    'role_id' => $AdminRole->id,
    'gender_id' => 2,
    'phone' => '09544117593',
    'city_id' => 1,
    'age' => '20',
    'name' => 'admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'photo' => url(Storage::url($targetPath))
]);

$admin->assignRole($AdminRole);
//assign permissions with the role to the user
$permissions = $AdminRole->permissions()->pluck('name')->toArray();
$admin->givePermissionTo ($permissions);


$clientUser = User::factory()->create([
    'role_id' => $ClientRole->id,
    'gender_id' => 1,
    'phone' => '0954411753',
    'city_id' => 1,
    'age' => '20',
    'name' => 'Client',
    'email' => 'Client@example.com',
    'password' => bcrypt('password') ,
    'photo' => url(Storage::url($targetPath))
]);


$clientUser->assignRole($ClientRole);
//assign permissions with the role to the user
$permissions = $ClientRole->permissions()->pluck('name')->toArray();
$clientUser->givePermissionTo($permissions);


$employee = User::factory()->create([
    'role_id' => $EmployeeRole->id,
    'gender_id' => 1,
    'phone' => '0954411753',
    'city_id' => 1,
    'age' => '20',
    'name' => 'employee',
    'email' => 'employee@example.com',
    'password' => bcrypt('password') ,
    'photo' => url(Storage::url($targetPath))
]);


$employee->assignRole($EmployeeRole);
//assign permissions with the role to the user
$permissions = $EmployeeRole->permissions()->pluck('name')->toArray();
$employee->givePermissionTo($permissions);


    }
}
