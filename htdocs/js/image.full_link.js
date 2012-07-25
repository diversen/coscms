
    $(function ()
    {
        //$("#replAll").click(function ()
    //    {
            $('#content_article img').each(function ()
            {
                var currImg = $(this);  // cache the selector
                currImg.wrap("<a href='" + currImg.attr("src") + '?size=file_org' + "' />");
            });
        //});
    }); 
