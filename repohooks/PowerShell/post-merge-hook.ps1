# ------------ POST-MERGE HOOK FOR LARAVEL 5.3 ------------------ #

function rerun_migrations () {
	 
	 write-host "Running Merge - Laravel 5 Migrations and Seeding..." -foregroundcolor "blue"
	 write-host "Please wait... this might take a while" -foregroundcolor "white"

	 $errors = & php artisan migrate:refresh --seed

	 if($errors -match "^.*(Error|Invalid)$"){
	      write-host "Error: Migrations not Successful! " -foregroundcolor red
          exit 1
	 }else{
	      write-host "OK: Migrations Run Successful! " -foregroundcolor green
          exit 0
	 }
	 
}

rerun_migrations