<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use DateTimeImmutable;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

use function bin2hex;
use function random_bytes;
use function random_int;

/**
 * @internal
 */
final class UpdateUserTest extends IntegrationTestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function removePhotoUrl(): void
    {
        $photoUrl = 'http://example.com/a_photo.jpg';
        $user = $this->auth->createUser(CreateUser::new()->withPhotoUrl($photoUrl));
        $this->assertSame($user->photoUrl, $photoUrl);
        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedPhotoUrl());
        $this->assertNull($updatedUser->photoUrl);
        $this->auth->deleteUser($user->uid);
    }

    public function removeDisplayName(): void
    {
        $displayName = 'A display name';
        $user = $this->auth->createUser(CreateUser::new()->withDisplayName($displayName));
        $this->assertSame($user->displayName, $displayName);
        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedDisplayName());
        $this->assertNull($updatedUser->displayName);
        $this->auth->deleteUser($user->uid);
    }

    public function markNonExistingEmailAsVerified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5))),
        );
        $this->assertNotTrue($user->emailVerified);
        $this->assertNull($user->email);
        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());
        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertNull($updatedUser->email);
        $this->assertTrue($updatedUser->emailVerified);
        $this->auth->deleteUser($updatedUser->uid);
    }

    public function markExistingUnverifiedEmailAsVerified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withUnverifiedEmail($uid.'@example.org'),
        );
        $this->assertFalse($user->emailVerified);
        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->markEmailAsVerified());
        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertTrue($updatedUser->emailVerified);
        $this->auth->deleteUser($updatedUser->uid);
    }

    public function markExistingVerifiedEmailAsUnverified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withVerifiedEmail($uid.'@example.org'),
        );
        $this->assertTrue($user->emailVerified);
        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsUnverified());
        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertFalse($updatedUser->emailVerified);
        $this->auth->deleteUser($updatedUser->uid);
    }

    public function updateUserWithCustomAttributes(): void
    {
        $request = CreateUser::new()
            ->withUid($uid = bin2hex(random_bytes(5)))
        ;
        $this->auth->createUser($request);
        $request = UpdateUser::new()
            ->withCustomAttributes($claims = [
                'admin' => true,
                'groupId' => '1234',
            ])
        ;
        $user = $this->auth->updateUser($uid, $request);
        $this->assertEqualsCanonicalizing($claims, $user->customClaims);
        $idToken = $this->auth->signInAsUser($user)->idToken();
        $this->assertNotNull($idToken);
        $verifiedToken = $this->auth->verifyIdToken($idToken);
        $this->assertTrue($verifiedToken->claims()->get('admin'));
        $this->assertSame('1234', $verifiedToken->claims()->get('groupId'));
        $this->auth->deleteUser($uid);
    }

    public function removePhoneNumber(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withVerifiedEmail($uid.'@example.org')
                ->withPhoneNumber($phoneNumber = '+1234567'.random_int(1000, 9999)),
        );
        $this->assertSame($phoneNumber, $user->phoneNumber);
        $updatedUser = $this->auth->updateUser(
            $user->uid,
            UpdateUser::new()->withRemovedPhoneNumber(),
        );
        $this->assertNull($updatedUser->phoneNumber);
        $this->auth->deleteUser($user->uid);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/196
     */
    public function reEnable(): void
    {
        $user = $this->auth->createUser([
            'disabled' => true,
        ]);
        $check = $this->auth->updateUser($user->uid, [
            'disabled' => false,
        ]);
        $this->assertFalse($check->disabled);
        $this->auth->deleteUser($user->uid);
    }

    public function timeOfLastPasswordUpdateIsIncluded(): void
    {
        $user = $this->auth->createAnonymousUser();
        try {
            $this->assertNull($user->metadata->passwordUpdatedAt);

            $updatedUser = $this->auth->updateUser($user->uid, ['password' => 'new-password']);

            $this->assertInstanceOf(DateTimeImmutable::class, $updatedUser->metadata->passwordUpdatedAt);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }
}
