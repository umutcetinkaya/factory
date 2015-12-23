Hi , 

This class is made to create all SQL and fast as middleware to connect different databases.

Run Code Example : 

```php
	
	$db = new Database();
	$db->main_table = 'students';
	$db->like_firstname = 'UMUT';
	$db->GetResults();

```
Desc :

```
	$db->like_firstname line allows to search in the 'firstname' field on 'students' table.
```