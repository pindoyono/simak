<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shield:super-admin {--email=admin@simak.local} {--password=admin123} {--name=Super Administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update super admin user with all Filament Shield permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up Super Admin with Filament Shield...');
        
        // Get options
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');
        
        try {
            // 1. Create or get super_admin role
            $this->info('📋 Creating super_admin role...');
            $superAdminRole = Role::firstOrCreate(
                ['name' => 'super_admin'],
                ['guard_name' => 'web']
            );
            
            // 2. Get all permissions and assign to super admin role
            $this->info('🔑 Assigning all permissions to super_admin role...');
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
            
            $this->info("✅ Assigned {$allPermissions->count()} permissions to super_admin role");
            
            // 3. Create or update super admin user
            $this->info("👤 Creating super admin user: {$email}...");
            $superAdmin = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
            
            // 4. Assign super admin role
            $this->info('🛡️ Assigning super_admin role to user...');
            $superAdmin->syncRoles([$superAdminRole]);
            
            // 5. Verify setup
            $userPermissions = $superAdmin->getAllPermissions();
            
            $this->newLine();
            $this->info('🎉 Super Admin setup completed successfully!');
            $this->newLine();
            
            // Display summary
            $this->table([
                'Property', 'Value'
            ], [
                ['Name', $superAdmin->name],
                ['Email', $superAdmin->email],
                ['Password', $password],
                ['Role', 'super_admin'],
                ['Permissions', $userPermissions->count() . ' permissions'],
                ['Status', $superAdmin->email_verified_at ? 'Verified' : 'Not Verified'],
            ]);
            
            $this->newLine();
            $this->info('🔗 Login URL: http://localhost:8000/admin/login');
            $this->newLine();
            
            // Show some key permissions
            $keyPermissions = $userPermissions->whereIn('name', [
                'view_any_role',
                'create_role', 
                'view_any_school',
                'create_school',
                'page_AssessmentWizard',
                'page_AssessmentReport'
            ])->pluck('name');
            
            if ($keyPermissions->count() > 0) {
                $this->info('🔐 Key permissions verified:');
                foreach ($keyPermissions as $permission) {
                    $this->line("   ✅ {$permission}");
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Error setting up super admin: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
