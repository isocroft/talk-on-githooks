# -------------- PRE-COMMIT FOR A LARAVEL 5.2 PROJECT -------------- #

$php_ext = "php|engine|theme|inc|test"

$grunt = "grunt"

$args = @("")

[array]$all_php_files = @("./resources/views/sso/login.blade.php","./app/Http/Middleware/VerifyCsrfToken.php","./app/Http/Middleware/RedirectIfAuthenticated.php","./resources/views/posts.balde.php","./app/Http/Controllers/LoginController.php","./app/Http/Controllers/RegisterController.php","./app/Http/Controllers/PostsController.php","./app/Posts.php","./app/Login.php","./app/Http/routes.php")

[int]$unstage_on_error = 0

function run_task {

		param(
		  [string] $command_path,
		  [array] $argument_list
		)

          $err_counter = 0
		
		write-host "Running Pre-commit (Hook) - Grunt Build Tasks" -foregroundcolor "white"

		# THIS IS A CUSTOM ARTISAN COMMAND THAT PROXIES TO NPM GRUNT

		$errors = & php artisan $command_path 
		if($errors -match "Build Successful"){
           write-host  ":OK => Successful" -foregroundcolor "green"
		}else{
           write-host  ":NOT-OK => Not Successful" -foregroundcolor "red"
           $err_counter++
		}

		if($err_counter -gt 0){
            exit 1
		}
}

function php_syntax_check { 
     
	 param(
	    [array]$php_source, 
		[string]$extensions
		)
	 
	    $err_counter = 0
		write-host "Running Pre-commit (Hook) - PHP Syntax Check" -foregroundcolor "white"
		write-host "Please Wait... this will only take a few minutes" -foregroundcolor "magenta"
		  
		$php_source | foreach { 
	           if($_ -match ".*\.($extensions)$"){
			        $file = $matches[0]
					$errors = & php -l $file
					if($errors -match "No syntax errors detected in $file"){
					   write-host $file ":OK => No syntax errors" -foregroundcolor "green"
					}else{
					   write-host $file ":NOT-OK => " $errors -foregroundcolor "red"
					  
					        git reset $file
							write-host "GIT:Unstaging $file..." -foregroundcolor "cyan"
					   
					   $err_counter++
					}
			   }
	    }
	    
		if($err_counter -gt 0){
	          exit 1
	    }
		
}

php_syntax_check $all_php_files $php_ext $unstage_on_error
run_task $grunt $args 