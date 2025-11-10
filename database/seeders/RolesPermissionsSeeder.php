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
        $superAdminRole = Role::create(['name' => 'superAdmin']);
        $clientRole = Role::create(['name' => 'Client']);
        $adminRole = Role::create(['name' => 'Admin']);
        $leaderRole = Role::create(['name' => 'Leader']);

        // 2. Create permissions
        $permissions = [
            'countAssociations', 'getTotalCampaignsCountByYear', 'getUserCountsByRoleByYear',
            'getClassification', 'getUnderReviewIndiviCampaignDetails', 'showRejectedIndiviCampaignDetails', 'giftAdonation',
            'getUserCountsLastFiveYears', 'getTotalCampaignsCount', 'countAssociations', 'lastNewUsers' , 'countAssociationsMob',
            'myVoluntings' , 'updateVoluntingProfile' , 'showVoluntingProfile' , 'showVoluntingProfileDetails', 'getUnderReviewIndiviCampaignDetailsMob',
            'voluntingRequest' , 'upComingTasks' , 'editTaskStatus' ,'viewAssociationsCompaingsActive' , 'viewAssociationCompaingsComplete' , 'showAssociationDetails',
            'showCampaignDetails' , 'donateWithPoints' , 'donateWithWallet' , 'quickDonateWithWallet' , 'createIndiviCompa' , 'getEndedCampaignsCountByYearMob',
            'viewMyIndiviCompa' , 'viewIndiviCompa' , 'showIndiviCampaignDetails' , 'searchCampaigns' , 'emergencyCompaings' , 'totalDonationsByYearMob',
            'miniIfo' , 'mySummryAchievements' , 'mydonations' , 'mostDonationFor' , 'createVoluntingProfile' , 'showAllInfo' , 'showAllInkindDonations',
            'editPersonalInfo' , 'createWallet' , 'showWallet' , 'editWallet' , 'getAllVoluntingCampigns' , 'getVoluntingCampigndetails' , 'getTaskDetails' , 'showInkindDonationDetails',
            'searchForNearestInkindDonation' , 'addInkindDonation' , 'reserveInkindDonation' , 'getCenter' , 'getInkindDonationTypes' , 'getStatusOfDonation' , 'totalInkindDonationsByYearMob',
            'reciveInkindDonation' , 'updateInkindDonationAcceptence' , 'requestToHaveInkindDonation' , 'updateRequestToHaveInkindDonation',
            'editPersonalInfo' , 'createWallet' , 'showWallet' , 'getAllVoluntingCampigns' , 'getVoluntingCampigndetails' , 'getTaskDetails' , 'showInkindDonationDetails',
            'searchForNearestInkindDonation' , 'addInkindDonation' , 'reserveInkindDonation' , 'getCenter' , 'getInkindDonationTypes' , 'getStatusOfDonation' , 'totalInkindDonationsByYearMob',
            'totalAssociationDonationsByYear' , 'getMonthlyDonationsByYear' , 'getActiveCampaignsCount' ,
    'getCompleteCampaignsCount' , 'getDonationCountsByClassByYear' , 'AssociationDetails' ,
'getCampaignsStatus' , 'HealthyAssociationsCampaigns' , 'EducationalAssociationsCampaigns' , 'CleanlinessAssociationsCampaigns'
, 'EnvironmentalAssociationsCampaigns' , 'getAdminCampaignDetails' , 'AssociationAdmin' ,
'getVoluntingCampigns' , 'getVoluntingCompDetails' , 'createAssociationCampaign' , 'getVolunteersByTask' ,
'updateAcceptanceVolunteerStatus' , 'deleteVoluntingRequest' , 'UnderReviewIndiviCompaign' , 'addLeaderForm'

        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

      //assign permissions to roles
        $clientRole->syncPermissions(['viewAssociationsCompaingsActive' , 'viewAssociationCompaingsComplete' , 'showAssociationDetails',
        'showCampaignDetails' , 'donateWithPoints' , 'donateWithWallet' , 'quickDonateWithWallet' , 'createIndiviCompa' , 'getEndedCampaignsCountByYearMob',
        'viewMyIndiviCompa' , 'viewIndiviCompa' , 'showIndiviCampaignDetails' , 'searchCampaigns' , 'emergencyCompaings' , 'totalDonationsByYearMob',
        'miniIfo' , 'mySummryAchievements' , 'mydonations' , 'mostDonationFor' , 'createVoluntingProfile' , 'showAllInfo' , 'showAllInkindDonations',
        'editPersonalInfo' , 'createWallet' , 'showWallet' , 'editWallet' , 'getAllVoluntingCampigns' , 'getVoluntingCampigndetails' , 'getTaskDetails',
        'myVoluntings' , 'updateVoluntingProfile' , 'showVoluntingProfile' , 'showVoluntingProfileDetails', 'giftAdonation' , 'countAssociationsMob',
        'voluntingRequest' , 'upComingTasks' , 'editTaskStatus' , 'getUnderReviewIndiviCampaignDetailsMob' , 'showRejectedIndiviCampaignDetails' , 'showInkindDonationDetails',
        'searchForNearestInkindDonation' , 'addInkindDonation' , 'reserveInkindDonation' , 'totalInkindDonationsByYearMob'
        ]);

        // 3. Assign permissions
        $superAdminRole->syncPermissions($permissions);


        $adminRole->syncPermissions(['totalAssociationDonationsByYear' , 'getMonthlyDonationsByYear' , 'getActiveCampaignsCount' ,
    'getCompleteCampaignsCount' , 'getDonationCountsByClassByYear' , 'AssociationDetails' ,
'getCampaignsStatus' , 'HealthyAssociationsCampaigns' , 'EducationalAssociationsCampaigns' , 'CleanlinessAssociationsCampaigns'
, 'EnvironmentalAssociationsCampaigns' , 'getAdminCampaignDetails' , 'AssociationAdmin' ,
'getVoluntingCampigns' , 'getVoluntingCompDetails' , 'createAssociationCampaign' , 'getVolunteersByTask' ,
'updateAcceptanceVolunteerStatus' , 'deleteVoluntingRequest']);

        $leaderRole->syncPermissions(['UnderReviewIndiviCompaign' , 'addLeaderForm' , 'reciveInkindDonation' , 'updateInkindDonationAcceptence' , 'requestToHaveInkindDonation' , 'updateRequestToHaveInkindDonation' ]);

         $sourcePath = public_path('uploads/seeder_photos/defualtProfilePhoto.png');
         $targetPath = 'uploads/det/defualtProfilePhoto.png';

    Storage::disk('public')->put($targetPath, File::get($sourcePath));

        // 4. Create users for each role

        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'gender_id' => 1,
            'phone' => '0954411753',
            'city_id' => 1,
            'age' => '20',
            'name' => 'Super Admin',
            'email' => 'SuperAdmin@example.com',
            'password' => bcrypt('password') ,
            'photo' => url(Storage::url($targetPath))
        ]);

        $superAdmin->assignRole($superAdminRole);

        //assign permissions with the role to the user
        $permissions = $superAdminRole->permissions()->pluck('name')->toArray();
        $superAdmin->givePermissionTo($permissions);
        
        $clientUser = User::factory()->create([
            'role_id' => $clientRole->id,
            'gender_id' => 1,
            'phone' => '0954411753',
            'city_id' => 1,
            'age' => '20',
            'name' => 'Donor',
            'email' => 'Donor@example.com',
            'password' => bcrypt('password') ,
            'photo' => url(Storage::url($targetPath))
        ]);

        $wallet = Wallet::create([
          'user_id' => $clientUser->id,
          'wallet_value' => 1000000,
          'wallet_password' => bcrypt('password') ,
        ]);

        $clientUser->assignRole($clientRole);

        //assign permissions with the role to the user
        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $clientUser->givePermissionTo($permissions);


       $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'gender_id' => 2,
            'phone' => '09544117593',
            'city_id' => 1,
            'age' => '20',
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'photo' => url(Storage::url($targetPath))

        ]);

       $wallet = Wallet::create([
          'user_id' => $admin->id,
          'wallet_value' => 1000000,
          'wallet_password' => bcrypt('password') ,
        ]);
        $admin->assignRole($adminRole);

        //assign permissions with the role to the user
        $permissions = $adminRole->permissions()->pluck('name')->toArray();
        $admin->givePermissionTo ($permissions);

       $leader = User::factory()->create([
            'role_id' => $leaderRole->id,
            'gender_id' => 2,
            'phone' => '09544117593',
            'city_id' => 1,
            'age' => '20',
            'name' => 'leader',
            'email' => 'leader@example.com',
            'password' => bcrypt('password'),
            'photo' => url(Storage::url($targetPath))
        ]);

       $wallet = Wallet::create([
          'user_id' => $leader->id,
          'wallet_value' => 1000000,
          'wallet_password' => bcrypt('password') ,
        ]);
        $leader->assignRole($leaderRole);

        //assign permissions with the role to the user
        $permissions = $leaderRole->permissions()->pluck('name')->toArray();
        $leader->givePermissionTo ($permissions);



        // 5. Create additional client users
        $names = ['shahed', 'dana', 'rama', 'yumna', 'rania', 'lana', 'rayan', 'mohammed', 'marwa', 'sawsan'];
        $nationalities = [1, 2, 3, 4, 5, 6, 7, 8, 3, 1];
        $ages = [20, 30, 25, 19, 21, 35, 29, 18, 29, 37];
        $emails = ['shahed@gmail.com', 'dana@gmail.com', 'rama@gmail.com', 'yumna@gmail.com', 'rania@gmail.com',
                   'lana@gmail.com', 'rayan@gmail.com', 'mohammed@gmail.com', 'marwa@gmail.com', 'sawsan@gmail.com'];
        $phones = ['0977665542', '09777865542', '09790665542', '09887665542', '09776655491',
                   '0977654235', '0966554229', '0977655218', '0929665542', '0973765542'];
        $genders = [1, 1, 1, 1, 1, 1, 2, 2, 1, 1];
        $passwords = ['123456789shahed', '123456789dana', '123456789rama', '123456789yumna',
                      '123456789rania', '123456789lana', '123456789rayan', '123456789mohammed',
                      '123456789marwa', '123456789sawsan'];

        $emailsAdmin = ['shahedAA@gmail.com', 'danaAA@gmail.com', 'ramaAA@gmail.com', 'yumnaAA@gmail.com', 'raniaAA@gmail.com',
        'lanaAA@gmail.com', 'rayanAA@gmail.com', 'mohammedAA@gmail.com', 'marwaAA@gmail.com', 'sawsanAA@gmail.com'];


        $emailsLeader = ['shahedLL@gmail.com', 'danaLL@gmail.com', 'ramaLL@gmail.com', 'yumnaLL@gmail.com', 'raniaLL@gmail.com',
        'lanaLL@gmail.com', 'rayanLL@gmail.com', 'mohammedLL@gmail.com', 'marwaLL@gmail.com', 'sawsanLL@gmail.com'];

        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'role_id' => $clientRole->id,
                'name' => $names[$i],
                'city_id' => $nationalities[$i],
                'age' => $ages[$i],
                'email' => $emails[$i],
                'phone' => $phones[$i],
                'gender_id' => $genders[$i],
                'password' => Hash::make($passwords[$i]),
                'photo' => url(Storage::url($targetPath))

            ]);

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'wallet_value' => 1000000,
                'wallet_password' => Hash::make('password') ,
                ]);


            $user->assignRole($clientRole);

            //assign permissions with the role to the user
            $permissions = $clientRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo ($permissions);
        }

         for ($i = 0; $i < 10; $i++) {
                $admin = User::create([
                'role_id' => $adminRole->id,
                'name' => $names[$i],
                'email' => $emailsAdmin[$i],
                'phone' => $phones[$i],
                'gender_id' => $genders[$i],
                'age' => $ages[$i],
                'city_id' => $nationalities[$i],
                'password' => Hash::make($passwords[$i]),
                'photo' => url(Storage::url($targetPath))

            ]);


            $admin->assignRole($adminRole);
            $permissions = $adminRole->permissions()->pluck('name')->toArray();
            $admin->givePermissionTo ($permissions);

        }

        for ($i = 0; $i < 10; $i++) {
                $leader = User::create([
                'role_id' => $leaderRole->id,
                'name' => $names[$i],
                'email' => $emailsLeader[$i],
                'phone' => $phones[$i],
                'city_id' => $nationalities[$i],
                'phone' => $phones[$i],
                'gender_id' => $genders[$i],
                'password' => Hash::make($passwords[$i]),
                'photo' => url(Storage::url($targetPath))

            ]);

            $leader->assignRole($leaderRole);
            $permissions = $leaderRole->permissions()->pluck('name')->toArray();
            $leader->givePermissionTo ($permissions);
        }
    }
}
