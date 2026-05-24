<?php
// This script initializes the application environment, starts the session, and loads global configuration.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Escapes HTML characters to prevent XSS attacks.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirects the user to a specific path and terminates the script.
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Sets a flash message in the session to be displayed later.
 */
function set_flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

/**
 * Retrieves and unsets a flash message from the session.
 */
function get_flash(string $key): ?string
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $message = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $message;
}

/**
 * Checks if the current user is logged in.
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Returns the current user's ID or null if not logged in.
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Returns the role of the currently logged-in user.
 */
function current_role(): ?string
{
    return isset($_SESSION['role']) ? strtoupper((string) $_SESSION['role']) : null;
}

/**
 * Returns the customer ID for the current session.
 */
function current_customer_id(): ?int
{
    if (!isset($_SESSION['customer_id'])) {
        return null;
    }

    return (int) $_SESSION['customer_id'];
}
