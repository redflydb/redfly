<?php
// ================================================================================
// Abstraction for user level information including username, roles, and
// authorization.
// ================================================================================
class User
{
    // Username
    private $username = null;
    // Actual name of the user
    private $firstName = null;
    private $lastName = null;
    // Id of the user
    private $userId = null;
    // Email address of the user
    private $email = null;
    // One or more roles that the user is a member of
    private $roles = array();
    // --------------------------------------------------------------------------------
    // Generate a user object based on the username, or if an object was already
    // placed into the session return that object.
    // @param $roleList A single role or an array of roles that are required for
    //   access
    // @returns The User object
    // @throws Exception If the username was empty or the user was not found in
    // the database.
    // --------------------------------------------------------------------------------

    public static function factory($username)
    {
        if ( empty($username) ) {
            throw new Exception("Username not provided");
        }
        $user = null;
        $dbObj = DbService::factory();
        $sql = "SELECT *
                FROM Users
                WHERE username = " . $dbObj->escape($username, true);
        $result = $dbObj->query($sql);
        if ( $result->num_rows === 0 ) {
            throw new Exception("Unknown user \"$username\"");
        }
        $row = $result->fetch_assoc();
        $user = new User(
            $username,
            $row["role"],
            $row["first_name"],
            $row["last_name"],
            $row["email"],
            $row["user_id"]
        );

        return $user;
    }
    private function __construct(
        $username,
        $role,
        $firstName,
        $lastName,
        $email,
        $userId
    ) {
        $this->username = $username;
        $this->roles = array($role);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->userId = $userId;
    }
    // --------------------------------------------------------------------------------
    // Return TRUE iAuthorize the user.
    // Return true if the user has at least one role that matches the role or list of
    // roles provided.
    // @param $roles A single role or array of roles
    // @returns TRUE if the user has at least one of the specified roles, FALSE
    //  otherwise.
    // --------------------------------------------------------------------------------
    public function hasRole($targetRoles)
    {
        $targetRoles = ( (! is_array($targetRoles))
            ? array($targetRoles)
            : $targetRoles );
        foreach ( $this->roles as $role ) {
            if ( in_array($role, $targetRoles) ) {
                return true;
            }
        }

        return false;
    }
    public function username()
    {
        return $this->username;
    }
    public function firstName()
    {
        return $this->firstName;
    }
    public function lastName()
    {
        return $this->lastName;
    }
    public function fullName()
    {
        return $this->firstName . " " . $this->lastName;
    }
    public function email()
    {
        return $this->email;
    }
    public function userId()
    {
        return $this->userId;
    }
    public function roles()
    {
        return $this->roles;
    }
}
