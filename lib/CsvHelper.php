<?php
// ================================================================================
// Helper class for creating CSV files
// ================================================================================
class CsvHelper
{
    // --------------------------------------------------------------------------------
    // Create a CSV file on disk
    // @param string $path The path to save the CSV file to.  If a
    //   relative path is used it will be saved relative to the site base
    //   directory.
    // @param array $data An array of arrays of strings.  Each array in
    //   the array will be used to create a row in the CSV file.
    // --------------------------------------------------------------------------------
    public static function createFile(
        $path,
        array $data
    ) {
        if ( substr($path, 0, 1) !== "/" ) {
            $path = $GLOBALS["options"]->general->site_base_dir . "/" . $path;
        }
        if ( substr($path, -4) !== ".csv" ) {
            $path = $path . ".csv";
        }
        $handle = @fopen($path, "w");
        if ( $handle === false ) {
            throw new Exception("Failed to open file \"" . $path . "\"");
        }
        foreach ( $data as $line ) {
            foreach ( $line as $field ) {
                fwrite($handle, self::quote($field) . ",");
            }
            fwrite($handle, "\n");
        }
        fclose($handle);
    }
    // --------------------------------------------------------------------------------
    // Quote a string for use in a CSV file
    // @param string $field The string to be quoted
    // @return string The quoted string
    // --------------------------------------------------------------------------------
    public static function quote($field)
    {
        // Any string containing a double quote must be surrounded with
        // double quotes and the double quote characters must be replaced
        // by two double quote characters. If the string contains any
        // whitespace, commas or single quotes it must be surrounded in
        // double quotes. Otherwise, it doesn't need to be quoted.
        if ( strpos($field, "\"") !== false ) {
            $field = "\"" . str_replace("\"", "\"\"", $field) . "\"";
        } elseif ( preg_match("/[\s,']/s", $field) ) {
            $field = "\"" . $field . "\"";
        }

        return $field;
    }
}
