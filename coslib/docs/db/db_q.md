db_q
====

Simple ORM for doing basic CRUD

### Connect

Connection is made in the db class

    $db = new db($options);
    $db->connect();

### Read (Select)

Select all rows: 

    //$q = new dbQ;
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

    $res = db_q->delete('account')->
            filter('id =', 21)->
            exec();

### Update

    $values['username'] = 'dennis';
    $res = db_q->setUpdate('account')->
            setUpdateValues($values)->
            filter('id =', 22)->
            exec();

