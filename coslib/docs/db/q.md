db_q
====

Simple CRUD object for doing basic DB operations
in a simplistic way. There is no support for joins, as I think
in most CRUD objects Joins does not become more easy to use and understand. 
Using SQL joins is as easy. 

### Connect

Connection is made in the db class. If writing a module, you will be connected
per auto. 

    $db = new db($options);
    $db->connect();

### Read (Select)
 

    $rows = db_q->select('account')->
            filter('id > ', '10')->
            condition('AND')->
            filter('email LIKE', '%d%')->
            order('email', 'DESC')->limit(0, 10)->
            fetch();
    print_r($rows);

Select one row: 

            fetchSingle()

### Insert

    $values = array ('email' => 'dennisbech@yahoo.dk');
    $res = db_q->insert('account')->
        values($values)->exec();

### Delete

S    $res = db_q->delete('account')->
            filter('id =', 21)->
            exec();

### Update

    $values['username'] = 'dennis';
    $res = db_q->setUpdate('account')->
            setUpdateValues($values)->
            filter('id =', 22)->
            exec();

