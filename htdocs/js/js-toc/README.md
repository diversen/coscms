### About

Simple js plugin for making it easy to add TOC headings to your markup 
for more complex articles or blog entries. 

### Usage

Template is installed. 

Add to page where you want Table of conetent to be displayed: 

    <div id ="toc"></div>

Example: 
    
    
    if (template::templateCommonExists('js-toc')){
        include_template_inc('js-toc');
        // content is the part of the page for which we generate the 
        // table of content
        jstoc_set_toc(array ('context' => '#content_article'));
    }
