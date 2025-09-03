<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin role with all permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        
        // Get all permissions and assign to super admin role
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Create Assessor role with limited permissions
        $assessorRole = Role::firstOrCreate(['name' => 'assessor']);
        
        // Assessor permissions - only assessment related
        $assessorPermissions = Permission::whereIn('name', [
            'view_any_assessment::score',
            'view_assessment::score',
            'create_assessment::score',
            'update_assessment::score',
            'view_any_school',
            'view_school',
            'view_any_assessment::indicator',
            'view_assessment::indicator',
            'view_any_assessment::category',
            'view_assessment::category',
            'view_any_assessment::period',
            'view_assessment::period',
            'page_AssessmentWizard',
            'page_AssessmentReport',
            'widget_AssessmentStatsOverview',
            'widget_AssessmentStatsWidget',
        ])->get();
        
        $assessorRole->syncPermissions($assessorPermissions);

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@simak.local'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@simak.local',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Assign super admin role
        $superAdmin->assignRole($superAdminRole);

        // Create assessor user
        $assessor = User::firstOrCreate(
            ['email' => 'assessor@simak.local'],
            [
                'name' => 'Assessor User',
                'email' => 'assessor@simak.local',
                'password' => Hash::make('assessor123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Assign assessor role
        $assessor->assignRole($assessorRole);

        $this->command->info('✅ Super Admin created: admin@simak.local / admin123');
        $this->command->info('✅ Assessor created: assessor@simak.local / assessor123');
    }
}
