<?php

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\User;
use Database\Seeders\EquipmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds equipments with generated codes for every user', function () {
    $users = User::factory()->count(3)->create();

    $this->seed(EquipmentSeeder::class);

    expect(Equipment::query()->count())->toBe(24)
        ->and(Equipment::query()->whereNull('code')->count())->toBe(0);

    $users->each(function (User $user): void {
        expect($user->equipments()->count())->toBe(8);

        expect($user->equipments()->pluck('code')->unique())->toHaveCount(8);
    });
});

it('seeds equipments with a mix of statuses', function () {
    User::factory()->create();

    $this->seed(EquipmentSeeder::class);

    expect(Equipment::query()->where('status', EquipmentStatus::Active->value)->count())->toBeGreaterThan(0)
        ->and(Equipment::query()->where('status', EquipmentStatus::Inactive->value)->count())->toBeGreaterThan(0)
        ->and(Equipment::query()->where('status', EquipmentStatus::Maintenance->value)->count())->toBeGreaterThan(0);
});

it('does not duplicate equipments when seeded twice', function () {
    User::factory()->create();

    $this->seed(EquipmentSeeder::class);
    $count = Equipment::query()->count();

    $this->seed(EquipmentSeeder::class);

    expect(Equipment::query()->count())->toBe($count);
});
