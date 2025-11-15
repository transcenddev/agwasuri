#!/usr/bin/env pwsh
# Idempotent script to create or update the admin user used for local development.
# Usage: pwsh ./scripts/create-admin.ps1

php artisan tinker --execute='use Illuminate\\Support\\Str; $u=\\App\\Models\\User::updateOrCreate(["email"=>"admin@admin.com"],["first_name"=>"Admin","last_name"=>"User","account_type"=>"admin","password"=>bcrypt("admin1234"),"api_key"=>Str::random(40)]); echo "OK: {$u->email} user_id: {$u->user_id}\n";'
