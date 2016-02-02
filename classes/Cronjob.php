<?php

class Cronjob extends Collectable {
    public $command;

    public function __construct($command) {
        $this->command = $command;
    }

    public function run() {
        exec("php " . $this->command . " > /dev/null", $this->return);
        $this->return = "Task " . $this->command . " is done!";
    }
}

?>
