# Passhole
It's a hole... for passwords!

If you're like me, and you probably aren't, the idea of using a closed source password safe scares you.  Also, you have a terrible memory.  So I wrote this little PHP application.  Instead of remembering dozens of strong passwords for many different sites, you only remember one.  Easy, right?



# Install

0. Look through the code and make sure your passwords aren't getting flung across the Internet to some gangster's house.
1. Install PHP
  - Copy php.ini-production to php.ini
  - Uncomment ";extension-php_sqlite3.dll", ie. Remove the semi-colon.
2. Copy this repository to your machine somewhere. eg. 'c:\Users\richard\Documents\passhole'
2. Pick a location for your Sqlite3 database file outside the path in #2. eg. 'c:/Users/richard/Documents/passhole.sqlite3'
3. Change $TEST_STRING to something unique.  eg. 'fadsfadsfdv45097gfhj$#@@'
4. Start the PHP development server. eg. Open a cmd window, type "cd Documents\passhole", 
   then type "c:\php\php.exe -S localhost:8000"
   
 Every time you start your machine you'll have to do #4 again, or you can script it to run on startup.
