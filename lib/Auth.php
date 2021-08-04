<?php
// --------------------------------------------------------------------------------
// Singleton authorization class to ensure authorized access to web pages and/or
// REST calls.
// --------------------------------------------------------------------------------
class Auth
{
    // Singleton user object
    private static $user = null;
    // Any error message pertaining to the latest operation
    private static $errorMsg = null;
    // --------------------------------------------------------------------------------
    // Authorize the user for an operation requiring a particular role.
    // If no roles are provided then any authenticated user will be authorized.
    // Note that a user must be authenticated in order to be authorized.
    // @param $targetRoles A single role or array of roles
    // @returns TRUE if the user is authorized
    // @throws Exception of an unauthorized user
    // --------------------------------------------------------------------------------
    public static function authorize($targetRoles)
    {
        // If the user object has not been then authorize the user
        if ( self::$user === null ) {
            self::$user = self::authenticate();
        }
        // Let the user in if no roles have been provided
        if ( ($targetRoles === null ) ||
          (count($targetRoles) === 0) ) {
            return true;
        }
        $hasRole = self::$user->hasRole($targetRoles);
        if ( ! $hasRole ) {
            self::$errorMsg = "Access denied";
        }

        return $hasRole;
    }
    // --------------------------------------------------------------------------------
    // Authenticate the user using basic authentication.
    // Upon success store the user object in the session and return it.
    // On failure an exception will be thrown.
    // @returns The User object if authenticated
    // @throws Exception of an unauthenticated user
    // --------------------------------------------------------------------------------
    public static function authenticate()
    {
        // If the user object is not in the session then create it.
        // Otherwise grab it from the session
        if ( ! isset($_SESSION["auth"]["user"]) ) {
            // Authentication is actually handled by the web server using basic http authentication.
            // This will set the $_SERVER["PHP_AUTH_USER"] variable which we will use to create
            // the user object and later authorize the user
            if ( ! isset($_SERVER["PHP_AUTH_USER"]) ) {
                self::$errorMsg = "Basic or MariaDB authentication required prior to using this service";
                throw new Exception(self::$errorMsg);
            }
            // Create the user object and put it into the session
            self::$user = User::factory($_SERVER["PHP_AUTH_USER"]);
            $_SESSION["auth"]["user"] = self::$user;
        } elseif ( $_SESSION["auth"]["user"] instanceof User ) {
            self::$user = $_SESSION["auth"]["user"];
        } else {
            self::$errorMsg = "Could not authenticate user";
            throw new Exception(self::$errorMsg);
        }

        return self::$user;
    }
    // --------------------------------------------------------------------------------
    // Remove the user object from the session.
    // --------------------------------------------------------------------------------
    public static function logout()
    {
        if ( isset($_SESSION["auth"]["user"]) ) {
            unset($_SESSION["auth"]["user"]);
        }
        self::$user = null;
    }
    // --------------------------------------------------------------------------------
    // @returns the User object
    // --------------------------------------------------------------------------------
    public static function getUser()
    {
        return self::$user;
    }
    // --------------------------------------------------------------------------------
    // @returns Any error message generate by the most recent action.
    // --------------------------------------------------------------------------------
    public static function getError()
    {
        return self::$errorMsg;
    }
}
