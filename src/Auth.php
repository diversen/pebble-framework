<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\DB;
use Pebble\Cookie;

/**
 * A simple authentication class based on a single database table
 */
class Auth
{
    /**
     * @var \Pebble\DB
     */
    public $db;

    /**
     * @var array<mixed>
     */
    public $auth_cookie_settings;

    /**
     * @param array<mixed> $auth_cookie_settings
     */
    public function __construct(\Pebble\DB $db, array $auth_cookie_settings)
    {
        $this->auth_cookie_settings = $auth_cookie_settings;
        $this->db = $db;
    }

    /**
     * Authenticate a against database auth table
     * @return array<mixed>
     */
    public function authenticate(string $email, string $password): array
    {
        $sql = 'SELECT * FROM auth WHERE email = ? AND verified = 1 AND locked = 0';
        $row = $this->db->prepareFetch($sql, [$email]);

        if (!empty($row) && password_verify($password, $row['password_hash'])) {
            return $row;
        }

        return [];
    }

    /**
     * @param int<1, max> $len_bytes
     */
    private function getRandom(int $len_bytes): string
    {
        $random = bin2hex(random_bytes($len_bytes));
        return $random;
    }

    /**
     * Create a user using an email and a password
     */
    public function create(string $email, string $password): bool
    {
        $random = $this->getRandom(32);
        $options = ['cost' => 12];
        $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

        $sql = "INSERT INTO auth (`email`, `password_hash`, `random`) VALUES(?, ?, ?)";
        return $this->db->prepareExecute($sql, [$email, $password_hash, $random]);
    }

    /**
     * Verify a auth row by a key. Set verified = 1 and generate a new key
     * if there is a match
     */
    public function verifyKey(string $key): bool
    {
        $row = $this->getByWhere(['random' => $key]);

        if (!empty($row)) {
            $new_key = $this->getRandom(32);
            $sql = "UPDATE auth SET `verified` = 1, `random` = ? WHERE id= ? ";
            return $this->db->prepareExecute($sql, [$new_key, $row['id']]);
        } else {
            return false;
        }
    }

    /**
     * Check if an email is verified
     */
    public function isVerified(string $email): bool
    {
        $auth_row = $this->getByWhere(['verified' => 1, 'email' => $email]);
        if (empty($auth_row)) {
            return false;
        }
        return true;
    }

    /**
     * Get auth row by 'where' condition ['id' => 10, 'random' => 'random key of sorts']
     * @param array<mixed> $where
     * @return array<mixed>
     */
    public function getByWhere(array $where): array
    {
        return $this->db->getOne('auth', $where);
    }

    /**
     * Update 'password', actually the 'password_hash', and the random key by auth 'id'
     */
    public function updatePassword(string $id, string $password): bool
    {
        $random = $this->getRandom(32);
        $options = ['cost' => 12];
        $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

        $sql = "UPDATE auth SET `password_hash` = ?, `random` = ? WHERE id = ?";
        return $this->db->prepareExecute($sql, [$password_hash, $random, $id]);
    }

    /**
     * Get current users auth row from $_COOKIE['auth']
     * @return array<mixed>
     */
    private function getValidCookieRow(): array
    {
        if (isset($_COOKIE['auth'])) {
            $sql = "SELECT * FROM auth_cookie WHERE cookie_id = ?";
            $row = $this->db->prepareFetch($sql, [$_COOKIE['auth']]);
            return $row;
        }

        return [];
    }

    /**
     * Check if a user has a valid auth cookie by searching the auth_cookie table
     * by $_COOKIE['auth']
     * @return boolean
     */
    public function isAuthenticated(): bool
    {
        $auth = $this->getValidCookieRow();
        if (empty($auth)) {
            return false;
        }
        return true;
    }

    /**
     * Get an auth id by checking the auth_cookie table
     * for a $_COOKIE['auth'] match. If no row is found
     * return "0"
     */
    public function getAuthId(): string
    {
        $auth_cookie_row = $this->getValidCookieRow();
        if (empty($auth_cookie_row)) {
            return "0";
        }
        return $auth_cookie_row['auth_id'];
    }

    /**
     * Unsets current auth cookie. This will log out the user
     */
    public function unlinkCurrentCookie(): void
    {
        if (isset($_COOKIE['auth'])) {

            // Delete current cookie
            $sql = "DELETE FROM auth_cookie WHERE cookie_id = ?";
            $this->db->prepareExecute($sql, [$_COOKIE['auth']]);
        }
    }

    /**
     * Unset all 'auth_cookies' across different devices
     */
    public function unlinkAllCookies(string $auth_id): bool
    {
        $sql = "DELETE FROM auth_cookie WHERE auth_id = ?";
        return $this->db->prepareExecute($sql, [$auth_id]);
    }

    /**
     * Set the browser cookie and store store the random cookie value in `auth_cookie`
     * @param array<mixed> $auth
     */
    public function setCookie(array $auth, int $expires = 0): bool
    {
        $random = $this->getRandom(32);

        $cookie = new Cookie($this->auth_cookie_settings);
        $bool = $cookie->setCookie('auth', $random, $expires);

        if ($bool) {
            return $this->setCookieDB($random, $auth['id'], $expires);
        }

        return false;
    }

    private function setCookieDB(string $random, string $auth_id, int $expires = 0): bool
    {
        $sql = "INSERT INTO auth_cookie (`cookie_id`, `auth_id`, `expires`) VALUES (?, ?, ?) ";
        return $this->db->prepareExecute($sql, [$random, $auth_id, $expires]);
    }
}
