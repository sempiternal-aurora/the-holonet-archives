First time setup

When initially installing the server, please follow the directions below

1. Ensure that you have a mysql or mariaDB server setup somewhere, with a holonet database
2. ensure that there is a user for the website, it must have permission to update, insert, delete, use and select on the whole holonet database
3. copy the files to an apache install, and ensure that the website is set up to only display the files in the public_html to the web, for security reasons.
4. use the database dump in the folder mysql_database dump to dump the contents of the file into the holonet database, this will set up the tables as well, so you do not need to worry about them.
5. Ensure that the constants defined in the top of the functions.php document in the php folder are correct for your installation. These include the details of the mysql connection, and the document/website root of the installation.
6. Access the apache server to ensure that it all works.