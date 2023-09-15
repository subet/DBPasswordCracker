<?php

/**
 * Password cracker class
 * 
 * 
 * @author     MURAT DIKICI <mdikici@gmail.com>
 */

 include_once "../config.php";

class Cracker {

    private $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private $passwordLengths = [1];
    private $found = [];
    private $recordsArray;
    private $recordsToIDs;
    private $length;

    /**
     * Salter function to create password hashes
     * @param string $string the string to be created a password hash
     * @global string this function uses constant SALT
     * @return string
     */
    private function salter($string) {
        return md5($string . SALT);
    }

    /**
     * Initializes variables and calls generateAndTest function to create passwords and test with hashed passwords which are read from database
     * @global array this function uses global variable $recordsArray
     * @global array this function uses global variable $recordsToIDs
     * @global string this function uses global variable $charset
     * @global array this function uses global variable $passwordLengths
     * @global integer this function uses global variable $length
     * @param string $charset the list of characters that a password can include
     * @param array $passwordLengths the lengths of the passwords that will be created by this function
     * @param array $recordsArray the password hashes from db
     * @param array $recordsToIDs user_id field that matches to the password hash from db
     * @return array The return value may be an empty array if no passwords found
     */
    public function generateAndTestPasswords($charset, $passwordLengths, $recordsArray, $recordsToIDs) {
        
        $this->recordsArray = $recordsArray;
        $this->recordsToIDs = $recordsToIDs;
        $this->charset = $charset;
        $this->passwordLengths = $passwordLengths;
        $this->length = strlen($this->charset);
        $password = '';

        // Start generating passwords of different lengths
        foreach ($this->passwordLengths as $passwordLength) {
            $this->generateAndTest($passwordLength, '');
        }

        // Return found passwords
        return $this->found;

    }

    /**
     * Creates passwords and test with hashed passwords which are read from database
     * This function runs recursively by calling itself
     * @global array this function uses global variable $recordsArray
     * @global array this function uses global variable $recordsToIDs
     * @global string this function uses global variable $charset
     * @global integer this function uses global variable $length
     * @param integer $depth the length of password to create
     * @param string $password currently created password
     */
    private function generateAndTest($depth, $password) {

        if ($depth === 0) {
            // Test the generated password
            $hashed = $this->salter($password);
            if (in_array($hashed, $this->recordsArray)) {
                $this->found[] = [
                    'user_id' => $this->recordsToIDs[$hashed],
                    'password' => $password,
                    'hash' => $hashed,
                ];
            }
            return;
        }

        for ($i = 0; $i < $this->length; $i++) {
            $this->generateAndTest(
                    $depth - 1, 
                    $password . $this->charset[$i]
                );
        }
    }

}