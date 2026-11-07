<?php

use App\Domain\Kyc\Actions\UpdateKycReviewStatusAction;
use App\Domain\Kyc\KycDocumentSource;
use App\Domain\Kyc\KycDocumentStore;
use App\Models\OrganizerKyc;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\UserWalletKyc;
use App\Services\KycService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('kyc');
    Storage::fake('public');
    $GLOBALS['__harness_roles'] = [];
    if (class_exists(\Spatie\Permission\Models\Role::class)) {
        foreach (['user', 'organizer', 'admin'] as $role) {
            \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
        }
    }
});

// Roles as the app sees them (spatie in the project, a recorder in the test harness).
function kycRoles(User $user): array
{
    if (method_exists($user, 'getRoleNames')) {
        return $user->fresh()->getRoleNames()->all();
    }
    return $GLOBALS['__harness_roles'][$user->id] ?? [];
}

function kycUser(array $extra = []): User
{
    static $n = 0; $n++;
    return User::create($extra + [
        'name' => "KYC $n", 'email' => "kyc$n@example.test", 'password' => 'secret-pass', 'type' => 'user', 'is_wizard_completed' => true,
    ]);
}

// ---------- K1: where documents live ----------

it('stores new KYC uploads on the private disk, never the public one', function () {
    $user = kycUser();
    $path = app(KycDocumentStore::class)->store(UploadedFile::fake()->image('id.jpg'), 'wallet/' . $user->id);

    expect($path)->toStartWith('wallet/' . $user->id . '/');
    Storage::disk('kyc')->assertExists($path);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('profile KYC (KycService) saves to the private disk', function () {
    $user = kycUser();
    Auth::login($user);

    app(KycService::class)->submitKyc([], [
        'front_side' => UploadedFile::fake()->image('f.jpg'),
        'back_side' => UploadedFile::fake()->image('b.jpg'),
        'address_proof' => UploadedFile::fake()->image('a.jpg'),
    ]);

    $user->refresh();
    foreach (['front_side', 'back_side', 'address_proof'] as $col) {
        Storage::disk('kyc')->assertExists($user->$col);
    }
    expect($user->selfie)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('never serialises ID document paths on the user model', function () {
    $user = kycUser(['front_side' => '/storage/kyc/front/a.jpg', 'back_side' => 'x', 'address_proof' => 'y', 'selfie' => 'z']);
    $json = $user->fresh()->toArray();

    foreach (['front_side', 'back_side', 'address_proof', 'selfie', 'front_side_url', 'back_side_url', 'address_proof_url'] as $key) {
        expect($json)->not->toHaveKey($key);
    }
});

it('locates legacy public paths in every stored format', function () {
    Storage::disk('public')->put('kyc-documents/old.jpg', 'img');
    Storage::disk('public')->put('kyc/front/old.jpg', 'img');
    $store = app(KycDocumentStore::class);

    expect($store->locate('kyc-documents/old.jpg'))->toBe(['disk' => 'public', 'path' => 'kyc-documents/old.jpg'])
        ->and($store->locate('/storage/kyc/front/old.jpg'))->toBe(['disk' => 'public', 'path' => 'kyc/front/old.jpg'])
        ->and($store->locate('http://localhost/storage/kyc/front/old.jpg'))->toBe(['disk' => 'public', 'path' => 'kyc/front/old.jpg'])
        ->and($store->locate('../../.env'))->toBeNull()
        ->and($store->locate('kyc/../../.env'))->toBeNull()
        ->and($store->locate(null))->toBeNull();
});

// ---------- K1: admin-only signed file route ----------

function walletKycWithDoc(): array
{
    $owner = kycUser();
    $path = app(KycDocumentStore::class)->store(UploadedFile::fake()->image('id.jpg'), 'wallet/' . $owner->id);
    $kyc = UserWalletKyc::create(['user_id' => $owner->id, 'kyc_documents' => [$path], 'status' => 'pending']);
    return [$owner, $kyc];
}

it('lets an admin open a document through a signed link', function () {
    [, $kyc] = walletKycWithDoc();
    $admin = kycUser(['type' => 'admin']);
    $url = KycDocumentSource::adminUrl('wallet', $kyc->id, 'document_1', $kyc->kyc_documents[0]);

    expect($url)->toStartWith('/admin/trust/kyc/wallet/' . $kyc->id . '/documents/document_1/file?');

    $res = $this->actingAs($admin)->get($url);
    $res->assertOk();
    expect($res->headers->get('Cache-Control'))->toContain('no-store');
    expect($res->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

it('blocks guests, non-admins, unsigned and expired links', function () {
    [$owner, $kyc] = walletKycWithDoc();
    $admin = kycUser(['type' => 'admin']);
    $url = KycDocumentSource::adminUrl('wallet', $kyc->id, 'document_1', $kyc->kyc_documents[0]);

    $this->get($url)->assertRedirect();                       // guest → login
    $this->actingAs($owner)->get($url)->assertForbidden();    // even the owner
    $this->actingAs($admin)->get('/admin/trust/kyc/wallet/' . $kyc->id . '/documents/document_1/file')->assertForbidden(); // unsigned

    $this->travel(KycDocumentSource::LINK_MINUTES + 1)->minutes();
    $this->actingAs($admin)->get($url)->assertForbidden();    // expired
});

it('does not let a signed link be re-pointed at another record or file', function () {
    [, $kyc] = walletKycWithDoc();
    [, $other] = walletKycWithDoc();
    $admin = kycUser(['type' => 'admin']);
    $url = KycDocumentSource::adminUrl('wallet', $kyc->id, 'document_1', $kyc->kyc_documents[0]);

    $this->actingAs($admin)->get(str_replace('/wallet/' . $kyc->id . '/', '/wallet/' . $other->id . '/', $url))->assertForbidden();
    $this->actingAs($admin)->get(str_replace('document_1', 'document_2', $url))->assertForbidden();
});

it('serves organizer and profile documents, and 404s unknown keys', function () {
    $admin = kycUser(['type' => 'admin']);
    $orgUser = kycUser();
    $profile = OrganizerProfile::create(['user_id' => $orgUser->id]);
    Storage::disk('public')->put('organizer/kyc/p.jpg', 'img');            // legacy public file
    $org = OrganizerKyc::create(['organizer_id' => $profile->id, 'passport_front' => 'organizer/kyc/p.jpg']);
    $u = kycUser(['front_side' => app(KycDocumentStore::class)->store(UploadedFile::fake()->image('f.jpg'), 'user/1')]);

    $this->actingAs($admin)->get(KycDocumentSource::adminUrl('organizer', $org->id, 'passport_front', $org->passport_front))->assertOk();
    $this->actingAs($admin)->get(KycDocumentSource::adminUrl('user', $u->id, 'front_side', $u->front_side))->assertOk();
    $this->actingAs($admin)->get(KycDocumentSource::adminUrl('user', $u->id, 'password', 'x'))->assertNotFound();
    $this->actingAs($admin)->get(KycDocumentSource::adminUrl('organizer', $org->id, 'passport_back', 'nothing'))->assertNotFound();
});

// ---------- K1: moving old files ----------

it('kyc:secure-documents moves public files to the private disk and updates paths', function () {
    $owner = kycUser(['front_side' => '/storage/kyc/front/f.jpg']);
    Storage::disk('public')->put('kyc/front/f.jpg', 'front');
    Storage::disk('public')->put('kyc-documents/w.jpg', 'wallet');
    $kyc = UserWalletKyc::create(['user_id' => $owner->id, 'kyc_documents' => ['kyc-documents/w.jpg', 'kyc-documents/missing.jpg'], 'status' => 'approved']);

    $this->artisan('kyc:secure-documents', ['--dry-run' => true])->assertSuccessful();
    Storage::disk('public')->assertExists('kyc-documents/w.jpg');               // dry run changes nothing

    $this->artisan('kyc:secure-documents')->assertSuccessful();

    $docs = $kyc->fresh()->kyc_documents;
    expect($docs[0])->toStartWith('wallet/' . $owner->id . '/')->and($docs[1])->toBe('kyc-documents/missing.jpg');
    expect(Storage::disk('kyc')->get($docs[0]))->toBe('wallet');
    Storage::disk('public')->assertMissing('kyc-documents/w.jpg');

    $front = User::find($owner->id)->front_side;
    expect($front)->toStartWith('user/' . $owner->id . '/');
    Storage::disk('public')->assertMissing('kyc/front/f.jpg');

    $this->artisan('kyc:secure-documents')->assertSuccessful();                   // second run is a no-op
    expect($kyc->fresh()->kyc_documents[0])->toBe($docs[0]);
});

// ---------- K2: wallet approval keeps the user's type and roles ----------

it('approving a wallet KYC does not make the user an organizer', function () {
    $user = kycUser();
    $kyc = UserWalletKyc::create(['user_id' => $user->id, 'status' => 'pending']);

    app(UpdateKycReviewStatusAction::class)->execute('wallet', $kyc->id, 'approved');

    expect($kyc->fresh()->status)->toBe('approved')
        ->and($user->fresh()->type)->toBe('user')
        ->and(kycRoles($user))->not->toContain('organizer');
});

it('approving an organizer KYC still upgrades the organizer', function () {
    $user = kycUser();
    $profile = OrganizerProfile::create(['user_id' => $user->id]);
    $org = OrganizerKyc::create(['organizer_id' => $profile->id]);

    app(UpdateKycReviewStatusAction::class)->execute('organizer', $org->id, 'approved');

    expect($org->fresh()->status)->toBe('approved')
        ->and($org->fresh()->p_front_status)->toBe('approved')
        ->and($user->fresh()->type)->toBe('organizer')
        ->and(kycRoles($user))->toBe(['organizer']);
});

it('rejecting an organizer KYC stores canceled and never touches admins', function () {
    $admin = kycUser(['type' => 'admin']);
    $profile = OrganizerProfile::create(['user_id' => $admin->id]);
    $org = OrganizerKyc::create(['organizer_id' => $profile->id]);

    app(UpdateKycReviewStatusAction::class)->execute('organizer', $org->id, 'rejected');
    expect($org->fresh()->status)->toBe('canceled');

    app(UpdateKycReviewStatusAction::class)->execute('organizer', $org->id, 'approved');
    expect($admin->fresh()->type)->toBe('admin')->and(kycRoles($admin))->not->toContain('organizer');
});

it('kyc:wallet-upgrades lists organizers whose only approval is wallet KYC', function () {
    $suspect = kycUser(['type' => 'organizer', 'email' => 'suspect@example.test']);
    UserWalletKyc::create(['user_id' => $suspect->id, 'status' => 'approved']);

    $real = kycUser(['type' => 'organizer', 'email' => 'real@example.test']);
    UserWalletKyc::create(['user_id' => $real->id, 'status' => 'approved']);
    OrganizerKyc::create(['organizer_id' => OrganizerProfile::create(['user_id' => $real->id])->id, 'status' => 'approved']);

    $this->artisan('kyc:wallet-upgrades')
        ->expectsOutputToContain('suspect@example.test')
        ->doesntExpectOutputToContain('real@example.test')
        ->assertSuccessful();

    expect($suspect->fresh()->type)->toBe('organizer'); // read-only
});
