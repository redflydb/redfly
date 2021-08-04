<?php
class CuratorHandler
{
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new CuratorHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "load" action
    // --------------------------------------------------------------------------------
    public function loadHelp()
    {
        $description = "Load details for the specified curator. Must be authenticated to use " .
            "this action and if the requestor is not an administrator they can only load " .
            "their own data";
        $options = array("id" => "Optional curator ID");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Load the data for one or more entities
    // --------------------------------------------------------------------------------
    public function loadAction(
        array $arguments,
        array $postData = null
    ) {
        try {
            Auth::authorize(array(
                "admin",
                "curator"
            ));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403);
            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $response = null;
        $db = DbService::factory();
        $helper = RestHandlerHelper::factory();
        // Only users with an admin role can view other user profiles
        $hasAdminRole = Auth::getUser()->hasRole("admin");
        if ( ! $hasAdminRole ) {
            $sqlCriteria[] = "u.user_id = " . $db->escape(Auth::getUser()->userId());
        }
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
             // Extract any optional operators from the value
            $sqlOperator = "=";
            $helper->extractOperator(
                $value,
                $sqlOperator
            );
            // If a wildcard was found in the value change the operator to "LIKE"
            if ( $helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $arg ) {
                case "id":
                    if ( $hasAdminRole ) {
                        $sqlCriteria[] = "u.user_id " . $sqlOperator . " " . $db->escape($value);
                    }
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT user_id,
            username,
            first_name,
            last_name,
            email,
            state,
            role
        FROM Users u
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        if ( count($sqlOrderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= " " . $limit;
        $response = $helper->query($db, $sql);

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "save" action
    // --------------------------------------------------------------------------------
    public function saveHelp()
    {
        $description = "Load details for the specified curator. Must be authenticated to use " .
            "this action and if the requestor is not an administrator they can only load " .
            "their own data";
        $options = array("id" => "Optional curator ID");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Save data for a particular entity
    // --------------------------------------------------------------------------------
    public function saveAction(
        array $arguments,
        array $postData = null
    ) {
        try {
            Auth::authorize(array(
                "admin",
                "curator"
            ));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403);

            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $response = null;
        $db = DbService::factory();
        $record = json_decode($postData["results"]);
        // Only users with an admin role can view other user profiles
        $hasAdminRole = Auth::getUser()->hasRole("admin");
        $isNewUser = ( ! isset($record->user_id) );
        if ( (! $hasAdminRole) &&
            ($isNewUser || (Auth::getUser()->userId() !== $record->user_id)) ) {
            return RestResponse::factory(
                false,
                "Administrator required to change another user profile"
            );
        }
        if ( $isNewUser ) {
            $list = array();
            $list["username"] = $db->escape($record->username, true);
            $list["first_name"] = $db->escape($record->first_name, true);
            $list["last_name"] = $db->escape($record->last_name, true);
            $list["email"] = $db->escape($record->email, true);
            $list["password"] = "MD5(" . $db->escape($record->password, true) . ")";
            $list["state"] = $db->escape($record->state, true);
            $list["role"] = $db->escape($record->role, true);
            $list["date_added"] = "NOW()";
            $sql = "INSERT INTO Users (" . implode(",", array_keys($list)) . ") " .
                "VALUES (" . implode(",", $list) . ")";
        } else {
            $list = array();
            $list[] = "first_name = " . $db->escape($record->first_name, true);
            $list[] = "last_name = " . $db->escape($record->last_name, true);
            $list[] = "email = " . $db->escape($record->email, true);
            if ( ! empty($record->password) ) {
                $list[] = "password = MD5(" . $db->escape($record->password, true) . ")";
            }
            if ( $hasAdminRole ) {
                $list[] = "state = " . $db->escape($record->state, true);
                $list[] = "role = " . $db->escape($record->role, true);
            }
            $sql = "UPDATE Users SET " . implode(",", $list) .
                " WHERE user_id = " . $db->escape($record->user_id);
        }
        $queryResult = $db->query($sql);
        if ( $queryResult === false ) {
            $response = RestResponse::factory(
                false,
                $db->error
            );
        } elseif ( $isNewUser &&
            ($db->getHandle()->affected_rows === 0) ) {
            $response = RestResponse::factory(
                false,
                "Failed to create new user"
            );
        } else {
            $response = RestResponse::factory(
                true,
                $sql
            );
        }

        return $response;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "List the curators";
        $options = array(
            "limit"       => "The maximum number of curators to return",
            "limitoffset" => "The offset of the first curator to return. " .
                             "It (requires \"limit\")",
            "name"        => "Return only the curator(s) matching the full name",
            "role"        => "Restrict the list to the specified role",
            "sort"        => "The sort field. The valid option is: name"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // List the curators
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $sqlCriteria = array();
        $sqlOrderBy = array();
        $limit = "";
        $response = null;
        $db = DbService::factory();
        $helper = RestHandlerHelper::factory();
        foreach ( $arguments as $arg => $value ) {
            if ( ($value !== false) &&
                (($value === null) || ($value === "")) ) {
                continue;
            }
             // Extract any optional operators from the value
            $sqlOperator = "=";
            $helper->extractOperator(
                $value,
                $sqlOperator
            );
            // If a wildcard was found in the value change the operator to "LIKE"
            if ( $helper->convertWildcards($value) ) {
                $sqlOperator = "LIKE";
            }
            switch ( $arg ) {
                case "full_name":
                    $sqlCriteria[] = "CONCAT(u.first_name, ' ', u.last_name) " . $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "limit":
                    $limit = $helper->constructLimitStr($arguments);
                    break;
                case "role":
                    $sqlCriteria[] = "u.role ". $sqlOperator . " " . $db->escape($value, true);
                    break;
                case "sort":
                    $sortInformation = $helper->extractSortInformation($value);
                    foreach ( $sortInformation as $sortColumn => $direction ) {
                        switch ( $sortColumn ) {
                            case "full_name":
                                $sqlOrderBy[] = "u.first_name " . $direction . ", u.last_name " . $direction;
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $sql = <<<SQL
        SELECT user_id AS id,
            CONCAT(first_name, ' ' , last_name) AS full_name
        FROM Users u
SQL;
        if ( count($sqlCriteria) !== 0 ) {
            $sql .= " WHERE " . implode(" AND ", $sqlCriteria);
        }
        if ( count($sqlOrderBy) !== 0 ) {
            $sql .= " ORDER BY " . implode(",", $sqlOrderBy);
        }
        $sql .= " " . $limit;
        $response = $helper->query(
            $db,
            $sql
        );

        return $response;
    }
}
