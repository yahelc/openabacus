
<h1>OpenAbacus</h1>

OpenAbacus is the SQL query tool we've built for managing querying and reporting for BSD client databases, and open-sourced for general benefit.


<h2>Scheduled Reports</h2>

Scheduled reports are triggered every minute by two non-overlapping cron jobs on `build1` with the following configuration:

````
*/1 * * * * /home/php/bin/php /path/to/index.php "cron=true" | /home/php/bin/php /path/to/cron.php
*/1 * * * * /home/php/bin/php /home/stats/htdocs/export/index.php "cron=true&hourlycron=1" | /home/php/bin/php /path/to/cron.php
````
Hourly scheduled reports have a different cronjob to avoid collisions with non-hourly reports. 

<h2>TwigSQL</h2>
Queries are saved in a templated format that allows for dynamic SQL generation, using the Twig templating language. 

We've added a few custom functions to better handle the templating.

<h4>tmp</h4>
Generate a table name for the test database, to eliminate the possibility of naming collisions.
````
      tmp('dataset') => test.shpc_1365979007_32 
  ```
  
<h4>date_range</h4>
Templatize the date range inputs for `start_dt` and `end_dt` dynamic inputs. Does not prepend an `AND`. You are responsible for handling fallbacks if need be. 

```
SELECT * FROM foo WHERE {{ date_range("mailing_link.create_dt") | default("1") }}
//GENERATES
  both dates set => SELECT * FROM foo WHERE mailing_link.create_dt BETWEEN {{start_dt}} AND {{end_dt}}
  only start_dt set => SELECT * FROM foo WHERE mailing_link.create_dt >= {{start_dt }}
  only end_dt set => SELECT * FROM foo WHERE mailing_link.create_dt <= {{end_dt}}
  neither set => SELECT * FROM foo WHERE 1
```

<h4>value_range</h4>
Templatize the inputs for `min_value` and `max_value` dynamic inputs. Does not prepend an `AND`. You are responsible for handling fallbacks if need be. In contrast to `date_range`, this will handle (and validate) any numeric input.

```
SELECT * FROM foo WHERE {{ value_range("transaction_amt") | default("1") }}
//GENERATES
  both values set => SELECT * FROM foo WHERE transaction_amt >= {{min_value}} AND transaction_amt <= {{max_value}}
  only min_value set => SELECT * FROM foo WHERE transaction_amt >= {{min_value}}
  only max_value set => SELECT * FROM foo WHERE transaction_amt <= {{max_value}}
  neither set => SELECT * FROM foo WHERE 1
```

<h4>create_dt <em>(DEPRECATED)</em></h4>
Templatize the date range inputs for `create_dt`, with `start_dt` and `end_dt` dynamic inputs. Prepends an `AND`.

```
 SELECT * FROM foo WHERE 1 {{create_dt("mailing_link.create_dt") }}
 //GENERATES:
   both dates set =>  SELECT * FROM foo WHERE 1 AND mailing_link.create_dt BETWEEN {{start_dt}} AND {{end_dt}}
   only start_dt set=> SELECT * FROM foo WHERE 1 AND mailing_link.create_dt >= {{start_dt }}
   only end_dt set => mailing_link.create_dt <= {{end_dt}}
   neither set => SELECT * FROM foo WHERE 1 ;
```

<h4>eval_sql</h4>
Pre-evaluate a line of SQL to dynamically generate information for the query, returning an array of key-val objects, where the key is the column name and the value is the row's value. Helpful for situations requiring logic to execute before the query can get templated.

```
{% set forms = eval_sql("SELECT signup_form_id, signup_form_name FROM signup_form LIMIT 2") %}
// forms = [{"signup_form_id"=>"1", "signup_form_name"=>"Default" }, {"signup_form_id"=>"2", "signup_form_name"=>"Petition" }]
{%for form in forms%} 
SELECT "{{form.signup_form_id}}", "{{form.signup_form_name}}"; 
{%endfor%}
```

<h4>sql</h4>

Escape and handle numbers, arrays, and strings (as well as quoting) for usage within a SQL statement:

```
SELECT * FROM foo WHERE id={{ string_id_variable | sql }}
--becomes
SELECT * FROM foo where id="Angela\'s So-Called \"Life\"";
```

<h5>Arrays for "IN" clauses:</h5>
```
SELECT * FROM foo WHERE id IN {{ array_of_items | sql }} 
--becomes
SELECT * FROM foo WHERE id IN (1, "foo", "bar", "Angela\'s So-Called \"Life\""); 
```

`sql` also works as both a function, in addition to a filter:

```
SELECT *  FROM foo WHERE id IN {{  sql(array_of_items) }} 
```
<h4>pluck</h4>

Extracts a list of property values from an associative array. Useful when taking data from `eval_sql` and using it in `sql`. 
```
{% set data = [{"mailing_id":4}, {"mailing_id":5}] %}
pluck(data, "mailing_id")  -->  [4,5]
```

<h5>Example usage</h5>

```
{% set target_mailings= eval_sql("select mailing_id, mailing_name, mailing_send_id, ms.sent_dt from mailing m join mailing_send ms using (mailing_id);") %}
select mailing_id, mailing_name from mailing where mailing_id IN {{ sql(pluck(target_mailings, 'mailing_id')) }};
```
`pluck` is available as both a function and a filter. 


<h4>use</h4>
Allows you to switch to use a supported  database.


<h4>killall</h4>
Finds all the queries your OpenAbacus user is currently running on the current client and outputs KILL statements, followed by a SHOW FULL PROCESSLIST so you can view the current state of processes. This will not kill queries run by other OpenAbacus users. 

```
{{ killall() }}
```
Will output something like: 

```
KILL 249249;
KILL 249999;
SHOW FULL PROCESSLIST;
```

<h4>load_csv</h4>
Load a CSV URL into a table. This can be a publicly accessible HTTP(s) URL, or an s3:// URL in the `$config["s3_bucket"]` s3 repo.
```
{{ load_csv( "data","https://www.dropbox.com/s/v24rk3drjydzap6/type%20by%20postcode.csv?dl=1") }}
//generates:
LOAD DATA LOCAL INFILE '/tmp/type%20by%20postcode.csv?dl=1' INTO TABLE data FIELDS TERMINATED BY ','  OPTIONALLY ENCLOSED BY '"'  LINES TERMINATED BY '\n' IGNORE 1 LINES;
```
