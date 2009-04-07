<?php

error_reporting(E_ALL);

include('XMLegant.php');

class XMLegant_Tests extends XMLegant
{
    function test_simple()
    {
        $x = new XMLegant();
        
        $x->a->b->c = 'd';    
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b></a>';//{$x->toXML(FALSE)}");
        
        $x->a->b->c = 'e';    
        assert("\$x->toXML(FALSE) == '<a><b><c>e</c></b></a>';//{$x->toXML(FALSE)}");
        
        $x->a->b = 'c';    
        assert("\$x->toXML(FALSE) == '<a><b>c</b></a>';//{$x->toXML(FALSE)}");
        
        $x->a = 'b';
        assert("\$x->toXML(FALSE) == '<a>b</a>';//{$x->toXML(FALSE)}");

        $x->a->b = 'c';
        assert("\$x->toXML(FALSE) == '<a><b>c</b></a>';//{$x->toXML(FALSE)}");
    }
    
    function test_multipleChildren()
    {
        $x = new XMLegant();
        
        $x->a->b = 'c';
        assert("\$x->toXML(FALSE) == '<a><b>c</b></a>';//{$x->toXML(FALSE)}");

        $x->a->b = 'd';
        assert("\$x->toXML(FALSE) == '<a><b>d</b></a>';//{$x->toXML(FALSE)}");

        $x->a->b[] = 'e';
        assert("\$x->toXML(FALSE) == '<a><b>d</b><b>e</b></a>';//{$x->toXML(FALSE)}");

        $x->a->b = 'c';
        assert("\$x->toXML(FALSE) == '<a><b>d</b><b>c</b></a>';//{$x->toXML(FALSE)}");
        
        $x->a->deleteChildren();
        assert("\$x->toXML(FALSE) == '<a/>';//{$x->toXML(FALSE)}");

        $x->a->b[]->c = 'd';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b></a>';//{$x->toXML(FALSE)}");

        $x->a->b[]->c = 'e';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b><b><c>e</c></b></a>';//{$x->toXML(FALSE)}");

        $x->a->deleteChildren();
        assert("\$x->toXML(FALSE) == '<a/>';//{$x->toXML(FALSE)}");

        $x->a->b->c = 'd';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b></a>';//{$x->toXML(FALSE)}");

        $x->a->b[]->c = 'e';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b><b><c>e</c></b></a>';//{$x->toXML(FALSE)}");

    }
    
    function test_attributes()
    {
        $x = new XMLegant();
        
        $x->a = array('b'=>'c', 'd'=>'e');
        assert("\$x->toXML(FALSE) == '<a b=\"c\" d=\"e\"/>';//{$x->toXML(FALSE)}");

        $x->a['f'] = 'g';
        assert("\$x->toXML(FALSE) == '<a b=\"c\" d=\"e\" f=\"g\"/>';//{$x->toXML(FALSE)}");
        
        assert("\$x->a['f'] == 'g';//{$x->a['f']}");
    }
    
    function test_vars()
    {
        $x = new XMLegant();
        
        $x->a->b->c = 'z';
        $c_obj = $x->a->b->c;
        assert("\$x->toXML(FALSE) == '<a><b><c>z</c></b></a>';//{$x->toXML(FALSE)}");

        assert("\$c_obj->toXML(FALSE) == '<c>z</c>';//{$c_obj->toXML(FALSE)}");

        // Note: This adds an additional 'c' child to $c_obj's parent
        $c_obj[] = 'd';
        assert("\$x->toXML(FALSE) == '<a><b><c>z</c><c>d</c></b></a>';//{$x->toXML(FALSE)}");
        assert("\$c_obj->toXML(FALSE) == '<c>z</c>';//{$c_obj->toXML(FALSE)}");
        
        $c_obj->e = 'f';
        assert("\$x->toXML(FALSE) == '<a><b><c><e>f</e></c><c>d</c></b></a>';//{$x->toXML(FALSE)}");
        assert("\$c_obj->toXML(FALSE) == '<c><e>f</e></c>';//{$c_obj->toXML(FALSE)}");
        
        /*
            Note: The following may not work as expected. The value is:
                <a><b><c>x</c><c>y</c><c>z</c></b></a>
            instead of:
                <a><b><c></c><c>x</c><c>y</c><c>z</c></b></a>
            (missing the fisrt <c></c> element)
            
            because [] will overwrite an initial element if is it empty. This is
            done so:
                $x = new XMLegant();
                $x->a->b[] = 'c';
            works correctly. Without this adjustment, the above would equal:
                <a><b><b><b>c</b></a>
            ...because the call to '$x->a->b' would create one element
            and the call to '[]' would create an additional one.
        */
        $x = new XMLegant();
        $c_obj = $x->a->b->c;
        $c_obj[] = 'x';
        $c_obj[] = 'y';
        $c_obj[] = 'z';
        assert("\$x->toXML(FALSE) == '<a><b><c>x</c><c>y</c><c>z</c></b></a>';//{$x->toXML(FALSE)}");
        
        /*
            If you need to get around this, simply initialize the element.
        */
        
        $x = new XMLegant();
        $x->a->b->c = '';
        $c_obj = $x->a->b->c;
        $c_obj[] = 'x';
        $c_obj[] = 'y';
        $c_obj[] = 'z';
        assert("\$x->toXML(FALSE) == '<a><b><c></c><c>x</c><c>y</c><c>z</c></b></a>';//{$x->toXML(FALSE)}");
        
    }
    
    function test_childObjs()
    {
        $x1 = new XMLegant();
        
        $x1->d->e = 'f';        
        assert("\$x1->toXML(FALSE) == '<d><e>f</e></d>';//{$x1->toXML(FALSE)}");
        
        $x2 = new XMLegant();
        
        $x2->a->b;
        assert("\$x2->toXML(FALSE) == '<a><b/></a>';//{$x2->toXML(FALSE)}");
        
        $x2->a->b = $x1;
        assert("\$x2->toXML(FALSE) == '<a><b><d><e>f</e></d></b></a>';//{$x2->toXML(FALSE)}");
        
        $x2->a->b[] = $x1;
        assert("\$x2->toXML(FALSE) == '<a><b><d><e>f</e></d></b><b><d><e>f</e></d></b></a>';//{$x2->toXML(FALSE)}");
    }

    function test_childObjRef()
    {
        $x = new XMLegant();
        
        $x->a->b->c = 'd';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b></a>';//{$x->toXML(FALSE)}");

        $x->a->b[]->e = 'f';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b><b><e>f</e></b></a>';//{$x->toXML(FALSE)}");

        $x->a->b[]->g = 'h';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b><b><e>f</e></b><b><g>h</g></b></a>';//{$x->toXML(FALSE)}");
        
        $x->a->b[1] = 'i';
        assert("\$x->toXML(FALSE) == '<a><b><c>d</c></b><b>i</b><b><g>h</g></b></a>';//{$x->toXML(FALSE)}");
        
       
    }    
    
    function test_addingBulkChildren()
    {
        /*
            Suppose you want to easily create the structure
                <books>
                    <book>
                        <title>Title 1</title>
                        <author>Author 1</author>
                        <isbn>isbn1</isbn>
                    </book>
                    <book>
                        <title>Title 2</title>
                        <author>Author 2</author>
                        <isbn>isbn2</isbn>
                    </book>
                    <book>
                        <title>Title 2</title>
                        <author>Author 2</author>
                        <isbn>isbn2</isbn>
                    </book>
                    ...
                </books>
        
        */
        $book_data = new XMLegant();
        
        $x = new XMLegant();
        
        for($i=0;$i<5;$i++)
        {
            $book_data->title = "Title $i";
            $book_data->author = "Author $i";
            $book_data->isbn = "isbn $i";
            $x->books->book[] = $book_data;
        }
        
        assert("\$x->toXML(FALSE) == '<books><book><title>Title 0</title><author>Author 0</author><isbn>isbn 0</isbn></book><book><title>Title 1</title><author>Author 1</author><isbn>isbn 1</isbn></book><book><title>Title 2</title><author>Author 2</author><isbn>isbn 2</isbn></book><book><title>Title 3</title><author>Author 3</author><isbn>isbn 3</isbn></book><book><title>Title 4</title><author>Author 4</author><isbn>isbn 4</isbn></book></books>';//{$x->toXML(FALSE)}");
        
    }
    
    function test_multRootChildren()
    {
        $x = new XMLegant();
        
        $x->a = 'b';
        $x->c = 'd';
        $x->e = 'f';
        
        assert("\$x->toXML(FALSE) == '<a>b</a><c>d</c><e>f</e>';//{$x->toXML(FALSE)}");
        
        
    }
    
    function test_functional()
    {
        $x = new XMLegant();

        $x->media('type', 'documentary')
          ->media
            ->movie->title('PHP2: More Parser Stories')
                   ->plot('This is all about the people who make it work.')
                   ->characters()
                      ->character()
                        ->name('Mr. Parser')
                        ->actor('John Doe')
                        ->getParent()
                      ->character()
                        ->name('Mr. Parser2')
                        ->actor('John Doe2')
                        ->getParent()
                      ->getParent()
                   ->rating(5);
    
        //echo $x->toXML();    
    }
    
    function test_underscores()
    {
        $x = new XMLegant();
        
        $x->foo_bar;
        
        assert("\$x->toXML(FALSE) == '<foo:bar/>';//{$x->toXML(FALSE)}");
        
        $x->foo_bar['a_b'] = 'c';
        
        assert("\$x->toXML(FALSE) == '<foo:bar a:b=\"c\"/>';//{$x->toXML(FALSE)}");

        $x->SetReplaceUnderscores(FALSE);
        
        assert("\$x->toXML(FALSE) == '<foo_bar a_b=\"c\"/>';//{$x->toXML(FALSE)}");
      
    }
    
    function test_convertToXML()
    {
        $book_data = new XMLegant();
        
        $x = new XMLegant();
        
        for($i=0;$i<5;$i++)
        {
            $book_data->title = "Title $i";
            $book_data->author = "Author $i";
            $book_data->isbn = "isbn $i";
            $x->books->book[] = $book_data;
            $x->books->book['a'] = "b$i";
        }        
        
        $x['encoding'] = 'UTF-8';
        $x['standalone'] = TRUE;
        unset($x['encoding']);
        assert("\$x->toXML(FALSE) == '<books><book a=\"b0\"><title>Title 0</title><author>Author 0</author><isbn>isbn 0</isbn></book><book a=\"b1\"><title>Title 1</title><author>Author 1</author><isbn>isbn 1</isbn></book><book a=\"b2\"><title>Title 2</title><author>Author 2</author><isbn>isbn 2</isbn></book><book a=\"b3\"><title>Title 3</title><author>Author 3</author><isbn>isbn 3</isbn></book><book a=\"b4\"><title>Title 4</title><author>Author 4</author><isbn>isbn 4</isbn></book></books>';//{$x->toXML(FALSE)}");
        
    }
    
    function test_startTag()
    {
        $books = XMLegant::Create('books');
        $books->book()->author("some author1")->title("some title1");
        $books->book()->author("some author2")->title("some title2");
        
        assert("\$books->toXML(FALSE) == '<books><book><author>some author1</author><title>some title1</title></book><book><author>some author2</author><title>some title2</title></book></books>';//{\$books->toXML(FALSE)}");
        
    }
    
    function test_root()
    {
        $x = new XMLegant();
        assert("\$x->a->b->c->getRoot() == \$x;");
    }
    
}


$class = new ReflectionClass('XMLegant_Tests');
$methods = $class->getMethods();

foreach($methods as $method)
{
    if(strpos($method->name, 'test_') === 0)
        call_user_func(array('XMLegant_Tests', $method->name));
}
