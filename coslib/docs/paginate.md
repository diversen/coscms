Paginate
========

You can use the `paginate` class for pagination 
The paginate class is just an empty extension of the 
pearPager class which again is just a wrapper around PEAR::Pager

It works by knowing the the `$_GET['from']` param and from this it know what
will be the next page in the set. 

### Usage

     // get a count of rows from a database
     $num_rows = db_q::setSelectNumRows('mailer_archive')->fetch();
     $per_page = 50;   
     
     // initialize the paginate class
     $pager = new paginate($num_rows, $per_page);

     // the rows to display
     $rows = db_q::setSelect('mailer_archive')->
            order('send_date', 'DESC')->
            order('send_time', 'DESC')->
            limit($pager->from, $per_page)->
            fetch();

    // display rows in some way, and then print the paginator
    echo $pager->getPagerHTML();
