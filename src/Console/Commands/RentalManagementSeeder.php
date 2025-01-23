<?php

namespace Codebright\Rental\Console\Commands;

use Codebright\Rental\Http\Helpers\PermissionList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RentalManagementSeeder extends Command
{
    protected $signature = 'rental-management:seed';

    protected $description = 'Seed the database with rental management data';

    public function handle()
    {
        /*For Permission*/
        $permissions = PermissionList::getConstants();
        foreach ($permissions as $id => $permission_name) {
            $permissionExists = DB::table("permissions")->where([
                "id"    => $id,
                "name"  => $permission_name,
                "guard_name" => "web",
            ])->exists();

            if (!$permissionExists) {
                DB::table("permissions")->insert([
                    "id"          => $id,
                    "name"        => $permission_name,
                    "guard_name"  => "web",
                    "created_at"  => date("Y-m-d H:i:s"),
                    "updated_at"  => date("Y-m-d H:i:s"),
                ]);
            }
        }

        $rentalManagementId = 9000;

        $sideMenuId = DB::table("menus")->where("name", "side_menu")->whereNull("deleted_at")->pluck("id")->first();

        DB::table('menu_items')->updateOrInsert(
            ['id'   => $rentalManagementId],
            [
                "menu_id"   => $sideMenuId,
                "label"     => "Rental Management",
                "position"  => 100,
                "link_type" => "Internal",
            ]
        );

        DB::table("menu_items")->updateOrInsert(
            ['id' => 9001],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-house',
                "label"         => "Rental List",
                "route_name"    => "rentalInfo",
                "position"      => 0,
                "link_type"     => "Internal",
            ]
        );
        DB::table("menu_items")->updateOrInsert(
            ['id' => 9002],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-file-text',
                "label"         => "Rental Reports",
                "route_name"    => "rentalReport",
                "position"      => 1,
                "link_type"     => "Internal",
            ]
        );
        DB::table("menu_items")->updateOrInsert(
            ['id' => 9003],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-calculator',
                "label"         => "Rental Calculation",
                "route_name"    => "rentalCalculation",
                "position"      => 2,
                "link_type"     => "Internal",
            ]
        );
        DB::table("menu_items")->updateOrInsert(
            ['id' => 9004],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-file-text',
                "label"         => "Range Reports",
                "route_name"    => "rangeReport",
                "position"      => 3,
                "link_type"     => "Internal",
            ]
        );
        DB::table("menu_items")->updateOrInsert(
            ['id' => 9005],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-lightning-charge',
                "label"         => "Electricity Dashboard",
                "route_name"    => "billingDashboard",
                "position"      => 4,
                "link_type"     => "Internal",
            ]
        );
        DB::table("menu_items")->updateOrInsert(
            ['id' => 9006],
            [
                "menu_id"       => $sideMenuId,
                "parent_id"     => $rentalManagementId,
                "icon"          => 'bi bi-box-arrow-up-right',
                "label"         => "Export Summary Report",
                "route_name"    => "download.summary.report",
                "position"      => 5,
                "link_type"     => "Internal",
            ]
        );


        return CommandAlias::SUCCESS;
    }
}
