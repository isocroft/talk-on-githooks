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
$js_output = array();
$rc     = 0;

// trying to make sure that the Git Tree isn't empty (working directory inclusive)
exec('git rev-parse --verify HEAD 2> /dev/null', $output, $rc);
if ($rc == 0)  $against = 'HEAD';
else           $against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

// This Git command executed below, grabs all files that have been staged for commit as a result of { git add . }
// command or {git rm [--cached] <file> } command executed by the repo maintainer/owner

// exec("git diff-index --cached --name-only {$against}", $output, $rc);

exec("git diff-index --cached {$against} | grep '.php' | cut -f2", $output, $rc);

exec("git diff-index --cached {$against} | grep '.js' | cut -f2", $js_output, $rc);

$needle            = '/\.ph(tml|p)$/'; // we only need to grab php files for PHP linting (by extension)
$exit_status = 0; // if linting is successful, exit with status 0, else exit with status 1
$files_with_issues = array(); // stores all file that have issues with liniting or static analysis

fwrite(STDOUT, "Now Running Pre-Commit Hook" . PHP_EOL);
fwrite(STDOUT, "Please Wait..." . PHP_EOL. PHP_EOL .PHP_EOL);

# Use composer to install like so: [composer require --dev phpstan/phpstan]

function statically_analyse_php($bootstrap_file_path = NULL, $level = 2){

    fwrite(STDOUT, '> Running PHP Static Ananlysis Routine...' . PHP_EOL);
	
	if(!is_null($bootstrap_file_path)){
		$arg = "--autoload-file={$bootstrap_file_path} ";
	}else{
		$arg = "";
	}

    global $output;

    global $exit_status;

    global $rc;

    global $files_with_issues;

    $has_errors = array();

	foreach ($output as $file) {

        	if (!preg_match($needle, $file)) {
            		// only check php files
                    continue;
        	}

            $reset_output = array();
        	$analysis_output = array();
        	$rc              = 0;
            $_rc             = 0;

        	exec('phpstan analyse '. escapeshellarg("{$arg}-l {$level} {$file}"), $analysis_output, $rc);
        	
            if ($rc == 0) {
            		continue;
        	}

            if($rc == 1){
                $has_errors[] = array('file' => $file, 'details' => $analysis_output);
                if(array_search($file, $files_with_issues, TRUE) === FALSE){
                    exec('git reset '. escapeshellarg($file), $reset_output, $_rc);
                    fwrite(STDOUT, "GIT: Unstaging File: ". basename($file, ".php") . "... " . PHP_EOL);
                }else{
                    $files_with_issues[] = $file;
                    fwrite(STDOUT, "GIT: File: ". basename($file, ".php") . " Prevoiusly Unstaged..." . PHP_EOL);
                }

                fwrite(STDOUT, PHP_EOL . "PHP: File Contains Syntax Errors!" . PHP_EOL);
            }

	}

    if(count($has_errors) > 0){
            $trace = "";
            foreach ($has_errors as $error) {
                $trace .= (implode(PHP_EOL . PHP_EOL, $error));
            }
            fwrite(STDOUT,  $trace . PHP_EOL . PHP_EOL); // writing to standard output (STDOUT)
            fwrite(STDOUT, '> Ended PHP Static Ananlysis Routine - Failure' . PHP_EOL);
            $exit_status = 1;
            exit($exit_status);
    }

    fwrite(STDOUT, '> Ended PHP Static Ananlysis Routine - Success' . PHP_EOL);

    exit($exit_status);
}

## {lint_js} is calling Grunt directly, No proxies

function lint_js($arg = 'jshint'){
    
    $lint_output = array();

    global $exit_status;

    global $rc;

    exec('grunt '. escapeshellarg($arg), $lint_output, $rc);

    if($rc == 1){
        fwrite(STDOUT, (implode(PHP_EOL, $lint_output)) . PHP_EOL);
        $exit_status = 1;
        exit($exit_status);
    }

    exit($exit_status);
}


## {test_php} is calling the PHPUnit logic to run unit tests

function test_php(){

    fwrite(STDOUT, '> Running PHP Unit Tests...' . PHP_EOL);

    $test_output = array();

    global $exit_status;

    global $rc;

    exec('phpunit', $test_output, $rc);

    if($rc == 1){
        fwrite(STDOUT, (implode(PHP_EOL, $test_output)) . PHP_EOL);
        fwrite(STDOUT, '> Ended PHP Unit Tests - Failure' . PHP_EOL);
        $exit_status = 1;
        exit($exit_status);
    }

    fwrite(STDOUT, '> Ended PHP Unit Tests - Success' . PHP_EOL);

    exit($exit_status);
}


## {lint_php} is calling PHP Interpreter linter directly

function lint_php(){

    fwrite(STDOUT, '> Running PHP Linting Routine...' . PHP_EOL);

    global $output;

    global $exit_status;

    global $rc;

    global $files_with_issues;

    global $needle;

    $has_errors = array();

    foreach ($output as $file) {

        // $fileName = trim(substr($file, 1));

        if (!preg_match($needle, $file)) {
            // only check php files
            continue;
        }

        $reset_output = array();
        $lint_output = array();
        $rc              = 0;
        $_rc             = 0;
        exec('php -l '. escapeshellarg($file), $lint_output, $rc);
        
        if ($rc == 0) {
            continue;
        }
        
        if($rc == 1){
            $has_errors[] = array('file' => $file, 'details' => $lint_output);
            if(array_search($file, $files_with_issues, TRUE) === FALSE){
                    exec('git reset '. escapeshellarg($file), $reset_output, $_rc);
                    fwrite(STDOUT, "GIT: Unstaging File:". basename($file, ".php") . "... " . PHP_EOL);
            }else{
                $files_with_issues[] = $file;
                fwrite(STDOUT, "GIT: File: ". basename($file, ".php") . " Prevoiusly Unstaged..." . PHP_EOL);
            }

            fwrite(STDOUT, PHP_EOL . "PHP: File Contains Syntax Errors!" . PHP_EOL);
        }
    }

    if(count($has_errors) > 0){
            $trace = "";
            foreach ($has_errors as $error) {
                $trace .= (implode(PHP_EOL . PHP_EOL, $error));
            }
            fwrite(STDOUT,  $trace . PHP_EOL . PHP_EOL); // writing to standard output (STDOUT)
            fwrite(STDOUT, '> Ended PHP Linting Routine - Failure' . PHP_EOL);
            $exit_status = 1;
            exit($exit_status);
    }

    fwrite(STDOUT, '> Ended PHP Linting Routine - Success' . PHP_EOL);

    exit($exit_status);
}

// lint_js();
lint_php();
// statically_analyse_php('./vendor/autoload.php'); -  [ composer require --dev phpstan/phpstan ]
test_php();

?>
