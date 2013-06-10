dbQ
===

Class exists in db.php. Simple ORM for doing basic CRUD

### Connect

Connection is made in the db class

    $db = new db($options);
    $db->connect();

### Read (Select)

Select all rows: 

    $q = new dbQ;
    $rows = $q->setSelect('account')->
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
    $res = $q->setInsert('account')->
        setInsertValues($values)-
        >exec();

### Delete

    $res = $q->setDelete('account')->
            filter('id =', 21)->
            exec();

### Update

    $values['username'] = 'dennis';
    $res = $q->setUpdate('account')->
            setUpdateValues($values)->
            filter('id =', 22)->
            exec();
