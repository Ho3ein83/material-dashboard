- What is this directory?
This directory is an autoloader folder to execute every PHP file
that starts with "al_", please be careful using this directory,
you may crash the whole plugin!

- How it does execute the PHP scripts?
It uses "amd_hooks_load_all" function located in "index.php" file to
execute every PHP files that starts with "al_" (stands for Auto-Load).
The other files can be included inside autoload scripts by using "require" and
"include" in your PHP codes.

- Is it safe to use?
YES and NO!
It is safe to use as it is only editable by the plugin author, and it only
contains hooks and functions (which doesn't execute automatically) and if
there is an error in specific place it only breaks specific part and doesn't
break your site.
It is not safe because it executes PHP files automatically, and it can be
a bad way for security reasons, but as long as you don't run your codes
directly and use them in a new scope (like hooks and functions), it is safe to use.