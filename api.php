<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'This endpoint accepts POST requests only.']);
}

try {
    $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    respond(400, ['error' => 'The request body must be valid JSON.']);
}

if (!is_array($input)) {
    respond(400, ['error' => 'The request body must be a JSON object.']);
}

$action = $_GET['action'] ?? '';
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    respond(422, ['error' => 'Enter a valid email address.']);
}

if ($action === 'register') {
    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '' || strlen($name) > 120) {
        respond(422, ['error' => 'Enter your name (up to 120 characters).']);
    }
    if (strlen($password) < 8) {
        respond(422, ['error' => 'Use a password with at least 8 characters.']);
    }

    try {
        require __DIR__ . '/db.php';
        $insert = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );
        $insert->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        respond(201, [
            'user' => [
                'id' => (int) $pdo->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
            ],
        ]);
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            respond(409, ['error' => 'That email already has an account. Sign in instead.']);
        }
        error_log('THREADLINE registration database error: ' . $error->getMessage());
        respond(500, ['error' => 'Could not save your account. Check that MySQL is running and try again.']);
    }
}

if ($action === 'login') {
    if ($password === '') {
        respond(422, ['error' => 'Enter your password.']);
    }

    try {
        require __DIR__ . '/db.php';
        $query = $pdo->prepare(
            'SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1'
        );
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            respond(401, ['error' => 'Account not found or password is incorrect.']);
        }
        respond(200, [
            'user' => [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => 'customer',
            ],
        ]);
    } catch (PDOException $error) {
        error_log('THREADLINE login database error: ' . $error->getMessage());
        respond(500, ['error' => 'Could not sign in. Check that MySQL is running and try again.']);
    }
}

respond(404, ['error' => 'Unknown account action.']);
