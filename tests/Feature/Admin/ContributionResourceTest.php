<?php

declare(strict_types=1);

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\ContributionResource\Pages\ListContributions;
use App\Mail\ContributionApproved;
use App\Mail\ContributionRejected;
use App\Models\Contribution;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::findOrCreate('admin', 'web');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Mail::fake();
});

it('lists pending contributions by default', function (): void {
    $pending = Contribution::factory()->create();
    $approved = Contribution::factory()->create(['status' => ContributionStatus::Approved->value]);

    Livewire::test(ListContributions::class)
        ->assertCanSeeTableRecords([$pending])
        ->assertCanNotSeeTableRecords([$approved]);
});

it('approves a new_plant contribution and creates the plant', function (): void {
    $submitter = User::factory()->create();
    $family = Family::factory()->create();
    $genus = Genus::factory()->create(['family_id' => $family->id]);

    $contribution = Contribution::factory()->create([
        'type' => ContributionType::NewPlant->value,
        'submitted_by' => $submitter->id,
        'payload' => [
            'scientific_name' => 'Rosa damascena',
            'family_id' => $family->id,
            'genus_id' => $genus->id,
        ],
    ]);

    Livewire::test(ListContributions::class)
        ->callTableAction('approve', $contribution);

    $contribution->refresh();

    expect($contribution->status)->toBe(ContributionStatus::Approved)
        ->and($contribution->reviewed_by)->toBe($this->admin->id)
        ->and($contribution->plant_id)->not->toBeNull();

    $plant = Plant::query()->firstWhere('scientific_name', 'Rosa damascena');
    expect($plant)->not->toBeNull()
        ->and($plant->status)->toBe(PlantStatus::Approved)
        ->and($plant->slug)->toBe('rosa-damascena');

    Mail::assertQueued(ContributionApproved::class);
});

it('approves an update contribution and applies the changes to the plant', function (): void {
    $submitter = User::factory()->create();
    $plant = Plant::factory()->create(['scientific_name' => 'Old name']);

    $contribution = Contribution::factory()->create([
        'type' => ContributionType::Update->value,
        'plant_id' => $plant->id,
        'submitted_by' => $submitter->id,
        'payload' => ['scientific_name' => 'New name'],
    ]);

    Livewire::test(ListContributions::class)
        ->callTableAction('approve', $contribution);

    expect($plant->refresh()->scientific_name)->toBe('New name')
        ->and($contribution->refresh()->status)->toBe(ContributionStatus::Approved);

    Mail::assertQueued(ContributionApproved::class);
});

it('shows a failure notification and keeps contribution pending when required fields are missing', function (): void {
    $contribution = Contribution::factory()->create([
        'type' => ContributionType::NewPlant->value,
        'payload' => ['scientific_name' => 'Rosa incomplete'],
    ]);

    Livewire::test(ListContributions::class)
        ->callTableAction('approve', $contribution)
        ->assertNotified();

    expect($contribution->refresh()->status)->toBe(ContributionStatus::Pending);
    expect(Plant::query()->where('scientific_name', 'Rosa incomplete')->exists())->toBeFalse();

    Mail::assertNothingQueued();
});

it('rejects a contribution with review notes and sends mail', function (): void {
    $submitter = User::factory()->create();
    $contribution = Contribution::factory()->create(['submitted_by' => $submitter->id]);

    Livewire::test(ListContributions::class)
        ->callTableAction('reject', $contribution, data: ['review_notes' => 'Duplicate of existing plant.'])
        ->assertHasNoTableActionErrors();

    $contribution->refresh();

    expect($contribution->status)->toBe(ContributionStatus::Rejected)
        ->and($contribution->reviewed_by)->toBe($this->admin->id)
        ->and($contribution->review_notes)->toBe('Duplicate of existing plant.');

    Mail::assertQueued(ContributionRejected::class);
});

it('requires review_notes when rejecting', function (): void {
    $contribution = Contribution::factory()->create();

    Livewire::test(ListContributions::class)
        ->callTableAction('reject', $contribution, data: ['review_notes' => ''])
        ->assertHasTableActionErrors(['review_notes']);

    expect($contribution->refresh()->status)->toBe(ContributionStatus::Pending);
});

it('hides approve/reject actions for non-pending contributions', function (): void {
    $approved = Contribution::factory()->create(['status' => ContributionStatus::Approved->value]);

    Livewire::test(ListContributions::class)
        ->filterTable('status', ContributionStatus::Approved->value)
        ->assertTableActionHidden('approve', $approved)
        ->assertTableActionHidden('reject', $approved);
});
