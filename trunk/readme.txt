This is a example of master dialog that imports CSV file to database table with visual mapping of CSV columns to table columns.

Your PHP server must support sessions and mysql.

Script auto-detects CSV separator as most frequent separator symbol in the first line.

How to install:
1. Create a database and a table
2. Define database settings in config.inc
3. Turn ON 'file_uploads' option in php.ini
4. (optional) Check that 'upload_max_filesize' option in php.ini is big enough (for example, 100M - 100 megabytes)

Ask you questions here - http://i1t2b3.com/2009/01/14/quick-csv-import-with-mapping/