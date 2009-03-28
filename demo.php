<?php

error_reporting(E_ALL);

include('XMLegant.php');

/*
    Create the document:
        <a>
            <b>
                <c>
                    <d>e</d>
                </c>
            </b>
        <a>
*/
$x = new XMLegant();
$x->a->b->c->d = 'e';


/*
    Create the document:
        <a>
            <b>c</b>
            <b>d</b>
            <e>f</e>
            <b>g</b>
            <h>
                <i j="k"/>
                <l>
                    <m/>
                </l>
                <n>o</n>
            </h>
        <a>
*/
$x = new XMLegant();

$x->a->b = 'c'; 
$x->a->b[] = 'd';
$x->a->e = 'f';
$x->a->b[] = 'g';
$x->a->h->i['j'] = 'k';
$x->a->h->l->m;


/*
    The same document using chaining
    (The function getParent() is used to "move up" the tree)
*/

$x = new XMLegant();

$x->a()
    ->b('c')
    ->b('d')
    ->e('f')
    ->b('g')
    ->h()
      ->i('j', 'k')
      ->l()
        ->m('')
        ->getParent()
      ->n('o');

/*
    Composing two XMLegant objects to create the following document:
    
        <a>
            <b>
                <d>
                    <e>f</e>
                </d>
            </b>
            <b>
                <d>
                    <e>f</e>
                </d>
            </b>
        </a>              
*/

$x1 = new XMLegant();

$x1->d->e = 'f';        

$x2 = new XMLegant();

$x2->a->b = $x1;
$x2->a->b[] = $x1;

/*
    Creating a document using a loop
        <books>
            <book>
                <title>Title 1</title>
                <author>Author 1</author>
                <isbn>isbn 1</isbn>
            </book>
            <book>
                <title>Title 2</title>
                <author>Author 2</author>
                <isbn>isbn 2</isbn>
            </book>
            <book>
                <title>Title 3</title>
                <author>Author 3</author>
                <isbn>isbn 3</isbn>
            </book>
            ...
        </books>    
*/

$x = new XMLegant();
$book_data = new XMLegant();

for($i=0;$i<5;$i++)
{
    $book_data->title = "Title $i";
    $book_data->author = "Author $i";
    $book_data->isbn = "isbn $i";
    $x->books->book[] = $book_data;
}


/*
    Or using chaining
*/

$x = new XMLegant();

for($i=0;$i<5;$i++)
    $x->books->book[] = XMLegant::Create()->title("Title $i")
                                          ->author("Author $i")
                                          ->isbn("isbn $i");


/*
    Or, using even more chaining...
*/

$x = new XMLegant();

for($i=0;$i<5;$i++)
    $x->books->book()->title("Title $i")
                     ->author("Author $i")
                     ->isbn("isbn $i");


/*
    Create one element. This creates:
    
        <a>
            <b>f</b>
        </a>   
    
    It does not create: 
        
        <a>
            <b>c</b>
            <b>d</b>
            <b>e</b>
            <b>f</b>
        </a>   
    
*/

$x = new XMLegant();

$x->a->b = 'c';
$x->a->b = 'd';
$x->a->b = 'e';
$x->a->b = 'f';


/*
    The following will create:
            
        <a>
            <b>c</b>
            <b>d</b>
            <b>e</b>
            <b>f</b>
        </a>       
*/


$x = new XMLegant();

$x->a->b[] = 'c';
$x->a->b[] = 'd';
$x->a->b[] = 'e';
$x->a->b[] = 'f';


/*
    Or, using method chaining...
*/

$x = new XMLegant();

$x->a()->b('c')
       ->b('d')
       ->b('e')
       ->b('f');

