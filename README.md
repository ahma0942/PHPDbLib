# PHPDbLib (Beta-V1.0)
PHPDbLib is a library with an ease of use SQL builder with functionality such as:

- Automatic nested inner joins (any types of complexity)
- Selecting & Unselecting columns (from joined tables too)
- Conditioning with any type of AND & OR complexity
- Data being returned as a multidimensional associative array

All that, while keeping performance on the top, by creating a tree-structure of the database and saved to a file

### Examples:
Consider the following tables:
```
SELECT * FROM test;
+----+----------+----------+
| id | test2_id | test3_id |
+----+----------+----------+
|  1 |        1 |        2 |
|  2 |        2 |        1 |
+----+----------+----------+

SELECT * FROM test2;
+----+------------------+------------------+----------+
| id | test2_reference1 | test2_reference2 | test3_id |
+----+------------------+------------------+----------+
|  1 |                1 |                2 |        2 |
|  2 |                2 |                1 |        1 |
+----+------------------+------------------+----------+

SELECT * FROM test3;
+----+------------------+
| id | name             |
+----+------------------+
|  1 | SomeValue        |
|  2 | Some More Values |
+----+------------------+
```
#### Reading from 1 Table
```
print_r($db->table('test')->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 1
            [test2_id] => 1
            [test3_id] => 2
        )

    [1] => Array
        (
            [id] => 2
            [test2_id] => 2
            [test3_id] => 1
        )

)
```
#### Joining into 1 table
```
print_r($db->table('test')->join('test2_id')->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 1
            [test2_id] => Array
                (
                    [id] => 1
                    [test2_reference1] => 1
                    [test2_reference2] => 2
                    [test3_id] => 2
                )

            [test3_id] => 2
        )

    [1] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                    [test2_reference1] => 2
                    [test2_reference2] => 1
                    [test3_id] => 1
                )

            [test3_id] => 1
        )

)
```
#### Nested joins (table1->table2->table3)
```
print_r($db->table('test')->join(['test2_id','test3_id'])->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                    [test2_reference1] => 2
                    [test2_reference2] => 1
                    [test3_id] => Array
                        (
                            [id] => 1
                            [name] => SomeValue
                        )

                )

            [test3_id] => 1
        )

    [1] => Array
        (
            [id] => 1
            [test2_id] => Array
                (
                    [id] => 1
                    [test2_reference1] => 1
                    [test2_reference2] => 2
                    [test3_id] => Array
                        (
                            [id] => 2
                            [name] => Some More Values
                        )

                )

            [test3_id] => 2
        )

)
```
#### Table joining into multiple tables
```
print_r($db->table('test')->join([['test2_id'],['test3_id']])->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                    [test2_reference1] => 2
                    [test2_reference2] => 1
                    [test3_id] => 1
                )

            [test3_id] => Array
                (
                    [id] => 1
                    [name] => SomeValue
                )

        )

    [1] => Array
        (
            [id] => 1
            [test2_id] => Array
                (
                    [id] => 1
                    [test2_reference1] => 1
                    [test2_reference2] => 2
                    [test3_id] => 2
                )

            [test3_id] => Array
                (
                    [id] => 2
                    [name] => Some More Values
                )

        )

)
```
If we extend our example with the following tables:
```
select * FROM reference1;
+----+------+
| id | name |
+----+------+
|  1 | PHP  |
|  2 | DB   |
|  3 | LIB  |
+----+------+

select * FROM reference2;
+----+--------------------+
| id | name               |
+----+--------------------+
|  1 | ReferenceTestName  |
|  2 | ReferenceTestName2 |
+----+--------------------+
```
We can then do more complex nested joins with the library like this:
```
print_r($db->table('test')->join([['test2_id',['test2_reference1'],['test2_reference2'],['test3_id']],['test3_id']])->read($conn));
```
We get this:
```
Array
(
    [0] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                    [test2_reference1] => Array
                        (
                            [id] => 2
                            [name] => DB
                        )

                    [test2_reference2] => Array
                        (
                            [id] => 1
                            [name] => ReferenceTestName
                        )

                    [test3_id] => Array
                        (
                            [id] => 1
                            [name] => SomeValue
                        )

                )

            [test3_id] => Array
                (
                    [id] => 1
                    [name] => SomeValue
                )

        )

    [1] => Array
        (
            [id] => 1
            [test2_id] => Array
                (
                    [id] => 1
                    [test2_reference1] => Array
                        (
                            [id] => 1
                            [name] => PHP
                        )

                    [test2_reference2] => Array
                        (
                            [id] => 2
                            [name] => ReferenceTestName2
                        )

                    [test3_id] => Array
                        (
                            [id] => 2
                            [name] => Some More Values
                        )

                )

            [test3_id] => Array
                (
                    [id] => 2
                    [name] => Some More Values
                )

        )

)
```
### Selects & Unselects
You can select and unselect columns
```
print_r($db->table('test')->select(['id','test2_id.id'])->join(['test2_id'])->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 1
            [test2_id] => Array
                (
                    [id] => 1
                )

        )

    [1] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                )

        )

)
```
And this
```
print_r($db->table('test')->unselect(['id','test2_id.id'])->join(['test2_id'])->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [test2_id] => Array
                (
                    [test2_reference1] => 1
                    [test2_reference2] => 2
                    [test3_id] => 2
                )

            [test3_id] => 2
        )

    [1] => Array
        (
            [test2_id] => Array
                (
                    [test2_reference1] => 2
                    [test2_reference2] => 1
                    [test3_id] => 1
                )

            [test3_id] => 1
        )

)
```
#### Where
```
print_r($db->table('test')->where([['id','=','2']])->join(['test2_id'])->read($conn));
```
Returns
```
Array
(
    [0] => Array
        (
            [id] => 2
            [test2_id] => Array
                (
                    [id] => 2
                    [test2_reference1] => 2
                    [test2_reference2] => 1
                    [test3_id] => 1
                )

            [test3_id] => 1
        )

)
```
OR of ANDs
```
print_r($db->table('test')->where([
    [['id','>','0'],['id','<','5']],
    [['id','<','0'],['id','>','5']]
])->join(['test2_id'])->read($conn));
```
Equals to `((id>0 AND id<5) OR (id<0 AND id>5))` and returns
```
Array
(
)
```

