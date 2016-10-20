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
$rc     = 0;

// trying to make sure that the Git Tree isn't empty (working directory inclusive)
exec('git rev-parse --verify HEAD 2> /dev/null', $output, $rc);
if ($rc == 0)  $against = 'HEAD';
else           $against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

// This Git command executed below, grabs all files that have been staged for commit as a result of { git add . }
// command or {git rm [--cached] <file> } command executed by the repo maintainer/owner
exec("git diff-index --cached --name-status $against | egrep '^(A|M)' | awk '{print $2;}'", $output);

$needle            = '/(\.php|\.phtml)$/'; // we only need to grab php files for PHP linting (by extension)
$exit_status = 0; // if linting is successful, exit with status 0, else exit with status 1

## {lint_js} is calling Grunt directly, No proxies

function lint_js($arg = 'jshint'){
    $lint_output = array();

    exec('grunt '. escapeshellarg($arg), $lint_output, $rc);

    if($rc == 1){
        fwrite(STDOUT, (implode(PHP_EOL, $lint_output)) . PHP_EOL);
        $exit_status = 1;
        exit($exit_status);
    }
}


## {lint_php} is calling PHP Interpreter linter directly

function lint_php(){
    foreach ($output as $file) {
        if (!preg_match($needle, $file)) {
            // only check php files
            continue;
        }

        $lint_output = array();
        $rc              = 0;
        exec('php -l '. escapeshellarg($file), $lint_output, $rc);
        if ($rc == 0) {
            continue;
        }
        if($rc == 1){
            fwrite(STDOUT, (implode(PHP_EOL, $lint_output)) . PHP_EOL); // writing to standard output (STDOUT)
            $exit_status = 1;
            exit($exit_status);
        }
    }
}

lint_js();
lint_php();

?>