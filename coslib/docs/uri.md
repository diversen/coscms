URI
===

All you need to know from this class is the call `uri::fragment($frag)` which
will give you a part of the url line. If you are on a page called 
`/mymodule/test/1` the `uri::fragment(0)` will return `mymodule` and 
`uri::fragment(1)` will return `test` and `uri::fragment(2)` will return `1`
