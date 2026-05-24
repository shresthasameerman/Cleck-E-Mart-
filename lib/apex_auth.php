<?php
// This file handles authentication via the remote APEX system as a fallback or alternative login method.

/**
 * Oracle APEX User Authentication Integration
 * Handles login via Oracle APEX REST endpoints
 */

/**
 * Authenticate user via Oracle APEX API
 *
 * @param string $email User email address
 * @param string $password User password
 * @return array|null User data on success (user_id, first_name, last_name, role, email), null on failure
 * @throws RuntimeException If API call fails
 */
function apex_login_user(string $email, string $password): ?array
{
    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/user/login/";
    $timeout = 10;

    try {
        $postData = [
            "email" => $email,
            "password" => $password,
        ];

        // Initialize cURL
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200) {
            error_log("APEX login API returned HTTP {$httpCode}");
            return null;
        }

        // Decode JSON response
        $result = json_decode($response, true);
        if ($result === null) {
            throw new RuntimeException('Invalid JSON response from API: ' . json_last_error_msg());
        }

        // Check API response status
        if (($result['status'] ?? '') !== 'success') {
            error_log('APEX login failed: ' . ($result['message'] ?? 'Unknown error'));
            return null;
        }

        // Validate required fields from API response
        if (!isset($result['user_id'], $result['first_name'], $result['role'])) {
            throw new RuntimeException('APEX API response missing required fields');
        }

        // Build normalized user data for session
        return [
            'USER_ID' => (int) $result['user_id'],
            'FIRST_NAME' => (string) $result['first_name'],
            'LAST_NAME' => (string) ($result['last_name'] ?? ''),
            'EMAIL' => $email,
            'ROLE' => strtoupper((string) $result['role']),
        ];
    } catch (Throwable $e) {
        error_log('APEX login error: ' . $e->getMessage());
        throw new RuntimeException('Authentication service unavailable: ' . $e->getMessage());
    }
}

/**
 * Check if APEX authentication is enabled
 *
 * @return bool True if APEX authentication should be used
 */
function apex_auth_enabled(): bool
{
    $enabled = strtolower((string) (getenv('APEX_AUTH_ENABLED') ?: 'false'));
    return $enabled === 'true' || $enabled === '1';
}

/**
 * Register user via Oracle APEX API (optional - if you want to use APEX for signup too)
 *
 * @param string $email User email
 * @param string $firstName User first name
 * @param string $lastName User last name
 * @param string $password User password
 * @param string $role User role (CUSTOMER or TRADER)
 * @return array|null User data on success, null on failure
 * @throws RuntimeException If API call fails
 */
function apex_register_user(string $email, string $firstName, string $lastName, string $password, string $role): ?array
{
    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/user/register/";
    $timeout = 10;

    try {
        $postData = [
            "email" => $email,
            "first_name" => $firstName,
            "last_name" => $lastName,
            "password" => $password,
            "role" => strtoupper($role),
        ];

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLOPT_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log("APEX register API returned HTTP {$httpCode}");
            return null;
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new RuntimeException('Invalid JSON response from API: ' . json_last_error_msg());
        }

        if (($result['status'] ?? '') !== 'success') {
            error_log('APEX registration failed: ' . ($result['message'] ?? 'Unknown error'));
            return null;
        }

        if (!isset($result['user_id'])) {
            throw new RuntimeException('APEX API response missing user_id');
        }

        return [
            'USER_ID' => (int) $result['user_id'],
            'FIRST_NAME' => $firstName,
            'LAST_NAME' => $lastName,
            'EMAIL' => $email,
            'ROLE' => strtoupper($role),
        ];
    } catch (Throwable $e) {
        error_log('APEX registration error: ' . $e->getMessage());
        throw new RuntimeException('Registration service unavailable: ' . $e->getMessage());
    }
}
