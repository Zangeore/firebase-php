<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use DateInterval;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\RevokedSessionCookie;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util;

use function assert;
use function bin2hex;
use function is_string;
use function parse_str;
use function parse_url;
use function random_bytes;
use function random_int;
use function sleep;

use const PHP_URL_QUERY;

/**
 * @internal
 */
abstract class AuthTestCase extends IntegrationTestCase
{
    protected Auth $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = self::$factory->createAuth();
    }

    public function createAnonymousUser(): void
    {
        $user = $this->auth->createAnonymousUser();
        try {
            $this->assertNull($user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function changeUserPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');
        $this->auth->changeUserPassword($user->uid, 'new password');
        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function changeUserEmail(): void
    {
        $email = self::randomEmail(__FUNCTION__.'_1');
        $newEmail = self::randomEmail(__FUNCTION__.'_2');
        $password = 'my password';
        $user = $this->auth->createUserWithEmailAndPassword($email, $password);
        $check = $this->auth->changeUserEmail($user->uid, $newEmail);
        $this->assertSame($newEmail, $check->email);
        $userWithNewEmail = $this->auth->getUserByEmail($newEmail);
        $this->assertSame($newEmail, $userWithNewEmail->email);
        $this->auth->deleteUser($user->uid);
    }

    public function getEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->getEmailVerificationLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function sendEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->sendEmailVerificationLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function sendEmailVerificationLinkToUnknownUser(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailVerificationLink(self::randomEmail(__FUNCTION__));
    }

    public function sendEmailVerificationLinkToDisabledUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->disableUser($user->uid);

            $this->expectException(FailedToSendActionLink::class);
            $this->auth->sendEmailVerificationLink((string) $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function getPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->getPasswordResetLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function sendPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->sendPasswordResetLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function getSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert($user->email !== null);
        try {
            $this->auth->getSignInWithEmailLink($user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function sendSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->auth->sendSignInWithEmailLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function getUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToCreateActionLink::class);
        $this->auth->getEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    public function getLocalizedEmailActionLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        $this->assertIsString($user->email);
        $link = $this->auth->getEmailVerificationLink($user->email, null, 'fr');
        if (Util::authEmulatorHost() !== null) {
            $this->assertStringNotContainsString('lang=fr', $link);
        } else {
            $this->assertStringContainsString('lang=fr', $link);
        }
    }

    public function sendUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    public function listUsers(): void
    {
        // We already should have a list of users, but let's add another one,
        // just to be sure
        $createdUsers = [
            $this->auth->createUser([]),
            $this->auth->createUser([]),
        ];
        try {
            $userRecords = array_merge(iterator_to_array($this->auth->listUsers($maxResults = 2, 1)));
            $this->assertCount($maxResults, $userRecords);
        } finally {
            foreach ($createdUsers as $createdUser) {
                $this->auth->deleteUser($createdUser->uid);
            }
        }
    }

    public function verifyIdToken(): void
    {
        $result = $this->auth->signInAnonymously();
        $uid = $result->firebaseUserId();
        $this->assertIsString($uid);
        try {
            $idToken = $result->idToken();
            $this->assertIsString($result->firebaseUserId());
            $this->assertIsString($idToken);

            $verifiedToken = $this->auth->verifyIdToken($idToken);

            $this->assertSame($uid, $verifiedToken->claims()->get('sub'));

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function revokeRefreshTokensAfterIdTokenVerification(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        $this->assertIsString($idToken);
        $uid = $this->auth
            ->verifyIdToken($idToken)
            ->claims()
            ->get('sub')
        ;
        sleep(1);
        $this->auth->revokeRefreshTokens($uid);
        $this->expectException(RevokedIdToken::class);
        try {
            $this->auth->verifyIdToken($idToken, true);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function verifyIdTokenString(): void
    {
        $result = $this->auth->signInAnonymously();
        $uid = $result->firebaseUserId();
        $this->assertIsString($uid);
        $idToken = $result->idToken();
        $this->assertIsString($idToken);
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $this->assertSame($uid, $verifiedToken->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function createSessionCookie(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();
        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $sessionCookie = $this->auth->createSessionCookie($idToken, 3600);

            $parsed = $this->auth->parseToken($sessionCookie);

            $this->assertSame($uid, $parsed->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function createSessionCookieWithInvalidTTL(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();
        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $this->expectException(InvalidArgumentException::class);
            $this->auth->createSessionCookie($idToken, 5);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function createSessionCookieWithInvalidIdToken(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/INVALID_ID_TOKEN/');
        $this->auth->createSessionCookie('invalid', 3600);
    }

    public function verifySessionCookie(): void
    {
        $result = $this->auth->signInAnonymously();
        $uid = $result->firebaseUserId();
        assert(is_string($uid));
        $idToken = $result->idToken();
        assert(is_string($idToken));
        $sessionCookie = $this->auth->createSessionCookie($idToken, new DateInterval('PT5M'));
        try {
            $verifiedCookie = $this->auth->verifySessionCookie($sessionCookie);
            $this->assertSame($uid, $verifiedCookie->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function verifySessionCookieAfterTokenRevocation(): void
    {
        $result = $this->auth->signInAnonymously();
        $uid = $result->firebaseUserId();
        assert(is_string($uid));
        $idToken = $result->idToken();
        assert(is_string($idToken));
        $sessionCookie = $this->auth->createSessionCookie($idToken, new DateInterval('PT5M'));
        $verifiedCookie = $this->auth->verifySessionCookie($sessionCookie, $checkIfRevoked = false);
        $uid = $verifiedCookie->claims()->get('sub');
        sleep(1);
        $this->auth->revokeRefreshTokens($uid);
        $this->expectException(RevokedSessionCookie::class);
        try {
            $this->auth->verifySessionCookie($sessionCookie, $checkIfRevoked = true);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function disableAndEnableUser(): void
    {
        $user = $this->auth->createUser([]);
        $check = $this->auth->disableUser($user->uid);
        $this->assertTrue($check->disabled);
        $check = $this->auth->enableUser($user->uid);
        $this->assertFalse($check->disabled);
        $this->auth->deleteUser($user->uid);
    }

    public function getUser(): void
    {
        $user = $this->auth->createUser([]);
        $check = $this->auth->getUser($user->uid);
        $this->assertSame($user->uid, $check->uid);
        $this->auth->deleteUser($user->uid);
    }

    public function getUsers(): void
    {
        $one = $this->auth->createAnonymousUser();
        $two = $this->auth->createAnonymousUser();
        $check = $this->auth->getUsers([$one->uid, $two->uid, 'non_existing']);
        try {
            $this->assertInstanceOf(UserRecord::class, $check[$one->uid]);
            $this->assertInstanceOf(UserRecord::class, $check[$two->uid]);
            $this->assertNull($check['non_existing']);
        } finally {
            $this->auth->deleteUser($one->uid);
            $this->auth->deleteUser($two->uid);
        }
    }

    public function getNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->deleteUser($user->uid);
        $this->expectException(UserNotFound::class);
        $this->auth->getUser($user->uid);
    }

    public function getUserByNonExistingEmail(): void
    {
        $user = $this->auth->createUser([
            'email' => $email = self::randomEmail(__FUNCTION__),
        ]);
        $this->auth->deleteUser($user->uid);
        $this->expectException(UserNotFound::class);
        $this->auth->getUserByEmail($email);
    }

    public function getUserByPhoneNumber(): void
    {
        $phoneNumber = '+1234567'.random_int(1000, 9999);
        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);
        $check = $this->auth->getUserByPhoneNumber($phoneNumber);
        $this->assertSame($user->uid, $check->uid);
        $this->auth->deleteUser($user->uid);
    }

    public function getUserByNonExistingPhoneNumber(): void
    {
        $phoneNumber = '+1234567'.random_int(1000, 9999);
        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);
        $this->auth->deleteUser($user->uid);
        $this->expectException(UserNotFound::class);
        $this->auth->getUserByPhoneNumber($phoneNumber);
    }

    public function createUser(): void
    {
        $uid = bin2hex(random_bytes(5));
        $userRecord = $this->auth->createUser([
            'uid' => $uid,
            'displayName' => $displayName = self::randomString(__FUNCTION__),
            'verifiedEmail' => $email = self::randomEmail(__FUNCTION__),
        ]);
        $this->assertSame($uid, $userRecord->uid);
        $this->assertSame($displayName, $userRecord->displayName);
        $this->assertTrue($userRecord->emailVerified);
        $this->assertSame($email, $userRecord->email);
        $this->auth->deleteUser($uid);
    }

    public function updateUserWithUidAsAdditionalArgument(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->updateUser($user->uid, []);
        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function deleteNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->deleteUser($user->uid);
        $this->expectException(UserNotFound::class);
        $this->auth->deleteUser($user->uid);
    }

    public function batchDeleteDisabledUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();
        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);
        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];
        $result = $this->auth->deleteUsers($uids, false);
        $this->assertSame(1, $result->successCount());
        $this->assertSame(2, $result->failureCount());
        $this->assertCount(2, $result->rawErrors());
    }

    public function batchForceDeleteUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();
        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);
        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];
        $result = $this->auth->deleteUsers($uids, true);
        $this->assertSame(3, $result->successCount());
        $this->assertSame(0, $result->failureCount());
        $this->assertCount(0, $result->rawErrors());
    }

    public function setCustomUserClaims(): void
    {
        $user = $this->auth->createAnonymousUser();
        try {
            $this->auth->setCustomUserClaims($user->uid, $claims = ['a' => 'b']);

            $this->assertSame($claims, $this->auth->getUser($user->uid)->customClaims);

            $this->auth->setCustomUserClaims($user->uid, null);

            $this->assertSame([], $this->auth->getUser($user->uid)->customClaims);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function unlinkProvider(): void
    {
        $uid = self::randomString(__FUNCTION__);
        $user = $this->auth->createUser([
            'uid' => $uid,
            'verifiedEmail' => self::randomEmail($uid),
            'phone' => '+1234567'.random_int(1000, 9999),
        ]);
        $updatedUser = $this->auth->unlinkProvider($user->uid, 'phone');
        $this->assertNull($updatedUser->phoneNumber);
        $this->auth->deleteUser($user->uid);
    }

    public function verifyPasswordResetCode(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert(is_string($user->email));
        try {
            $url = $this->auth->getPasswordResetLink($user->email);

            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            $email = $this->auth->verifyPasswordResetCode($query['oobCode']);
            $this->assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function verifyPasswordWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->verifyPasswordResetCode('invalid');
    }

    public function confirmPasswordReset(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        $url = $this->auth->getPasswordResetLink($user->email);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123');
        try {
            $this->assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function confirmPasswordResetAndInvalidateRefreshTokens(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert(is_string($user->email));
        $url = $this->auth->getPasswordResetLink($user->email);
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str((string) $queryString, $query);
        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123', true);
        sleep(1);
        // wait for a second
        try {
            $this->assertSame($email, $user->email);
            $this->assertGreaterThanOrEqual($user->tokensValidAfterTime, $this->auth->getUser($user->uid)->tokensValidAfterTime);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function confirmPasswordResetWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->confirmPasswordReset('invalid', 'newPassword123');
    }

    public function signInAsUser(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);
        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $this->auth->deleteUser($user->uid);
    }

    public function signInWithCustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();
        $customToken = $this->auth->createCustomToken($user->uid);
        $result = $this->auth->signInWithCustomToken($customToken);
        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $this->auth->deleteUser($user->uid);
    }

    public function signInWithRefreshToken(): void
    {
        $user = $this->auth->createAnonymousUser();
        // We need to sign in once to get a refresh token
        $firstRefreshToken = $this->auth->signInAsUser($user)->refreshToken();
        $this->assertIsString($firstRefreshToken);
        $result = $this->auth->signInWithRefreshToken($firstRefreshToken);
        $this->assertIsString($result->idToken());
        $this->assertIsString($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $this->auth->deleteUser($user->uid);
    }

    public function signInWithEmailAndPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';
        $user = $this->createUserWithEmailAndPassword($email, $password);
        $result = $this->auth->signInWithEmailAndPassword($email, $password);
        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $this->auth->deleteUser($user->uid);
    }

    public function signInWithEmailAndOobCode(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';
        $user = $this->createUserWithEmailAndPassword($email, $password);
        $signInLink = $this->auth->getSignInWithEmailLink($email);
        $query = (string) parse_url($signInLink, PHP_URL_QUERY);
        $oobCode = Query::parse($query)['oobCode'] ?? '';
        $result = $this->auth->signInWithEmailAndOobCode($email, $oobCode);
        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $this->auth->deleteUser($user->uid);
    }

    public function signInAnonymously(): void
    {
        $result = $this->auth->signInAnonymously();
        $idToken = $result->idToken();
        $this->assertIsString($idToken);
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());
        $token = $this->auth->parseToken($idToken);
        $this->assertIsString($uid = $token->claims()->get('sub'));
        $user = $this->auth->getUser($uid);
        $this->addToAssertionCount(1);
        $this->auth->deleteUser($user->uid);
    }

    public function signInWithIdpAccessToken(): void
    {
        // I don't know how to retrieve a current user access token programmatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpAccessToken('google.com', 'invalid', Utils::uriFor('http://localhost'));
    }

    public function signInWithIdpIdToken(): void
    {
        // I don't know how to retrieve a current user access token programmatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpIdToken('google.com', 'invalid', 'http://localhost');
    }

    public function removeEmailFromUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        try {
            $this->assertNotNull($user->email);

            $userWithoutEmail = $this->auth->updateUser($user->uid, [
                'deleteEmail' => true,
            ]);

            $this->assertNull($userWithoutEmail->email);
            $this->assertFalse($userWithoutEmail->emailVerified);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function verifyIdTokenAcceptsResultFromParseToken(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        $uid = $signInResult->firebaseUserId();
        assert(is_string($uid));
        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $parsedToken = $this->auth->parseToken($idToken);
            $this->auth->verifyIdToken($parsedToken);

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function itDownloadsOnlyAsManyAccountsAsItIsSupposedTo(): void
    {
        // Make sure we have at least two users present
        $first = $this->auth->createAnonymousUser();
        $second = $this->auth->createAnonymousUser();
        try {
            $users = $this->auth->listUsers(2, 99);
            $this->assertCount(2, iterator_to_array($users));
        } finally {
            $this->auth->deleteUser($first->uid);
            $this->auth->deleteUser($second->uid);
        }
    }

    protected function createUserWithEmailAndPassword(?string $email = null, ?string $password = null): UserRecord
    {
        $email ??= self::randomEmail();
        $password ??= self::randomString();

        return $this->auth->createUser([
            'email' => $email,
            'clear_text_password' => $password,
        ]);
    }
}
