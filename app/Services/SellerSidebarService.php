<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\SidebarManager\Entities\Backendmenu;
use Modules\SidebarManager\Entities\BackendmenuUser;

class SellerSidebarService
{
    public function setupForSeller(User $user): void
    {
        if (!$user || $user->role?->type !== 'seller') return;

        // Fetch all seller menus
        $menus = Backendmenu::where('is_seller', 1)
            ->orderBy('parent_id')
            ->orderBy('position')
            ->get();

        if ($menus->isEmpty()) return;

        DB::transaction(function () use ($menus, $user) {
            $createdMap = []; // backendmenu_id => backendmenu_user_id

            foreach ($menus as $menu) {
                // Skip if already assigned
                $existing = BackendmenuUser::where('user_id', $user->id)
                    ->where('backendmenu_id', $menu->id)->first();
                if ($existing) {
                    $createdMap[$menu->id] = $existing->id;
                    continue;
                }

                $parentBackendUserId = null;
                if (!empty($menu->parent_id) && isset($createdMap[$menu->parent_id])) {
                    $parentBackendUserId = $createdMap[$menu->parent_id];
                }

                $position = BackendmenuUser::where('user_id', $user->id)
                    ->where('parent_id', $parentBackendUserId)->count() + 1;

                $bu = BackendmenuUser::create([
                    'parent_id' => $parentBackendUserId,
                    'user_id' => $user->id,
                    'backendmenu_id' => $menu->id,
                    'position' => $position,
                ]);
                $createdMap[$menu->id] = $bu->id;
            }
        });
    }
}

