#!/usr/bin/php

<?php

/*
 * I, Patrick Ifeora Okechukwu hereby attests that this code is not originally
 * mine. It has been modified for the purposes of this talk and was sourced 
 * from an article on DZONE website.
 *
 * Also feel free to fork this repo, modify this code and PR to the repo if 
 * you feel like sharing your modifications ;)
 */

 $output = array();
 $rc = 0;

 $exit_status = 0;

 fwrite(STDOUT, "Now Running Post-Merge Hook" . PHP_EOL);
 fwrite(STDOUT, "Please Wait..." . PHP_EOL . PHP_EOL . PHP_EOL);

 function migration_refresh(){

    fwrite(STDOUT, '> Running PHP Laravel Migration Routine...' . PHP_EOL);

    global $output;

    global $rc;

    global $exit_status;

    exec('php artisan migrate:refresh --seed', $output, $rc);

    if($rc == 1){
        fwrite(STDOUT, (implode(PHP_EOL, $output)) . PHP_EOL);
        fwrite(STDOUT, '> Ended PHP Laravel Migration Routine - Failure' . PHP_EOL);
        $exit_status = 1;
        exit($exit_status);
    }

    fwrite(STDOUT, '> Ended PHP Laravel Migration Routine - Success' . PHP_EOL);

    exit($exit_status);
 }

 migration_refresh();
?>
