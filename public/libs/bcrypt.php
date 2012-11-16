<?php

/*
From: http://phpacademy.org/forum/viewtopic.php?t=15356
*/

class bcrypt {
        private $rounds;
        public function __construct($rounds = 12) {
                if(CRYPT_BLOWFISH != 1) {
                        throw new Exception("Bcrypt is not supported on this server,");
                }
                $this->rounds = $rounds;
        }

        /* Gen Salt */
        public function genSalt() {
                /* openssl_random_pseudo_bytes(16) Fallback */
                $seed = '';
                for($i = 0; $i < 16; $i++) {
                        $seed .= chr(mt_rand(0, 255));
                }
                /* GenSalt */
                $salt = substr(strtr(base64_encode($seed), '+', '.'), 0, 22);
                /* Return */
                return $salt;
        }

        /* Gen Hash */
        public function genHash($password) {
                /* Explain '$2y$' . $this->rounds . '$' */
                        /* 2a selects bcrypt algorithm */
                        /* $this->rounds is the workload factor */
                /* GenHash */
                $hash = crypt($password, '$2y$' . $this->rounds . '$' . $this->genSalt());
                /* Return */
                return $hash;
        }

        /* Verify Password */
        public function verify($password, $existingHash) {
                /* Hash new password with old hash */
                $hash = crypt($password, $existingHash);

                /* Do Hashs match? */
                if($hash === $existingHash) {
                        return true;
                } else {
                        return false;
                }
        }
}
?>
