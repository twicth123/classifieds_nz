<?php
/**
 * POST /api/auth.php
 * Body: { "action": "register|login|forgot|reset", ...fields }
 */
require_once __DIR__ . '/_bootstrap.php';

$in = input();
$action = $in['action'] ?? '';

switch ($action) {

    case 'register': {
        $name = trim($in['name'] ?? '');
        $email = trim($in['email'] ?? '');
        $mobile = trim($in['mobile'] ?? '');
        $password = (string)($in['password'] ?? '');
        $city = trim($in['city'] ?? '') ?: null;
        $state = trim($in['state'] ?? '') ?: null;

        if (!$name || !isValidEmail($email) || !isValidMobile($mobile) || strlen($password) < 6) {
            fail('Please fill all fields correctly (mobile must be 10 digits, password min 6 characters).');
        }

        $stmt = $pdo->prepare('SELECT UserID FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) fail('An account with this email already exists.');

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (Name, Email, Mobile, PasswordHash, City, State, Role, Status)
             VALUES (?, ?, ?, ?, ?, ?, \'user\', \'active\')'
        );
        $stmt->execute([$name, $email, $mobile, $hash, $city, $state]);
        $userId = (int)$pdo->lastInsertId();

        $token = issueToken($pdo, $userId);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
        $stmt->execute([$userId]);
        ok(['token' => $token, 'user' => userPublic($stmt->fetch())]);
        break;
    }

    case 'login': {
        $email = trim($in['email'] ?? '');
        $password = (string)($in['password'] ?? '');
        if (!$email || !$password) fail('Email and password are required.');

        $stmt = $pdo->prepare('SELECT * FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['PasswordHash'])) {
            fail('Incorrect email or password.', 401);
        }
        if ($user['Status'] === 'suspended') {
            fail('Your account has been suspended. Contact support.', 403);
        }

        $token = issueToken($pdo, (int)$user['UserID']);
        ok(['token' => $token, 'user' => userPublic($user)]);
        break;
    }

    case 'logout': {
        $token = bearerToken();
        if ($token) {
            $pdo->prepare('DELETE FROM api_tokens WHERE Token = ?')->execute([$token]);
        }
        ok();
        break;
    }

    case 'forgot': {
        $email = trim($in['email'] ?? '');
        $stmt = $pdo->prepare('SELECT UserID FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) fail('No account found with that email.', 404);
        // Website has no SMTP configured either - this simply confirms the
        // account exists so the app can move to the reset step.
        ok(['message' => 'You can now set a new password for ' . $email]);
        break;
    }

    case 'reset': {
        $email = trim($in['email'] ?? '');
        $newPassword = (string)($in['newPassword'] ?? '');
        if (strlen($newPassword) < 6) fail('Password must be at least 6 characters.');

        $stmt = $pdo->prepare('SELECT UserID FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) fail('No account found with that email.', 404);

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET PasswordHash = ? WHERE UserID = ?')->execute([$hash, $user['UserID']]);
        ok();
        break;
    }

    default:
        fail('Unknown action.');
}
