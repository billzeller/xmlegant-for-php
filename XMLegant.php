<?php

/*
    XMLegant
    (c) 2009 Bill Zeller
    http://from.bz/
    Version: .6   
    
    This source code is licensed under the BSD License
    The license is available here:
        http://creativecommons.org/licenses/BSD/
*/

class XMLegant implements ArrayAccess
{
    protected $name = NULL;
    protected $text = FALSE;
    protected $attrs = array();
    protected $children = array();
    protected $parent = NULL;
    
    /*
        map of child names to a list of objects
        ie, 
            array('a' => array(a1, a2, a2),
                  'b' => array(b1,b2))
                  
        Represents three 'a' children and two 'b' children
        
    */
    protected $child_names = array();
    
    // if this is set to TRUE, then elements named
    // foo_bar are replaced by foo:bar
    protected $replace_underscores = TRUE;
    
    function __construct($parent = NULL, $name = NULL, $text = FALSE, $attrs = array())
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->text = $text;
        $this->attrs = $attrs;
    }
    
    function Create()
    {
        return new XMLegant();
    }
    
    
    function __set($name, $value)
    {
        $this->SetChild($this->lastChildByName($name), $value);
    }
    
    function __get($name)
    {
        return $this->lastChildByName($name);
    }
    
    /*
        $x = new XMLegant()
        
        $x->a->b() returns the b() object
        $x->a->b($c) is the same as calling $x->a->b = $c 
            'a' is returned
        $x->a->b($name, $val) sets the attribute $name to $val in the
            'b' attribute and returns 'a'
    
    */   
    function __call($name, $args)
    {
        $child = $this->addChild($name);
        switch(count($args)){
            case 0:
                return $child;
            case 1:
                XMLegant::SetChild($child, $args[0]);
                break;
            case 2:
                $child->offsetSet($args[0], $args[1]);
                break;
        }

        return $this;
    }
    
    function getParent()
    {
        return $this->parent;
    }
    
    function SetReplaceUnderscores($replace = TRUE)
    {
        $this->replace_underscores = $replace;
        foreach($this->children as $child){
            $child->SetReplaceUnderscores($replace);
        }
    }
    
    function hasAttrs()
    {
        return !empty($this->attrs);
    }
    
    // implements ArrayAccess
    function offsetExists($offset)
    {
        if(empty($offset)){
            return FALSE;
        }elseif(is_int($offset) || ctype_digit($offset)){
            isset($this->parent->child_names[$this->name][$offset]);
        }else{
            return isset($this->attrs[$offset]);
        }        
        
    }
    
    function offsetGet($offset)
    {
        /* If offset is empty, create new node
                e.g., $x->a[] = "blah"
                
           If offset not empty, assume key is an attribute name
        */
        if(empty($offset)){
            /* We need to prevent:
                    $x->a->b[];
                from creating two 'b' nodes. Without this, one 'b' node would
                be created with the call to '$x->a->b' and another one 
                would be created with a call to '[]'. To prevent this, we 
                return the most recently created node if there is only one of 
                them and if that node is empty.                     
            */
            if(count($this->parent->child_names[$this->name]) == 1
                && $this->parent->child_names[$this->name][0]->IsEmpty()){
                return $this->parent->child_names[$this->name][0];
            }else{
                return $this->parent->addChild($this->name);
            }
        }elseif(is_int($offset) || ctype_digit($offset)){
            return isset($this->parent->child_names[$this->name][$offset])?
                      $this->parent->child_names[$this->name][$offset]:NULL;      
        }else{
            return isset($this->attrs[$offset])?$this->attrs[$offset]:NULL;
        }
    }
    
    function offsetSet($offset, $value)
    {
        /* If offset is empty, create new node
                e.g., x[] = "blah"
           If offset not empty, assume key is an attribute name
        */
        if(empty($offset)){
            /* As above, we need to prevent:
                    $x->a->b[] = 'c'
                from creating two 'b' nodes. Without this, one 'b' node would
                be created with the call to '$x->a->b' and another one 
                would be created with a call to '[]'. To prevent this, we 
                return the most recently created node if there is only one of 
                them and if that node is empty.                     
            */            
            if(count($this->parent->child_names[$this->name]) == 1
                && $this->parent->child_names[$this->name][0]->IsEmpty()){
                $this->parent->__set($this->name, $value);
            }else{
                $this->parent->addChild($this->name);
                $this->parent->__set($this->name, $value);
            }            
        }elseif(is_int($offset) || ctype_digit($offset)){
            // if given integer offset, such as:
            //     $x->a->b[1] = 'c';
            // Set the 'b' child at offset 1 equal to c
            $child = $this->parent->child_names[$this->name][$offset];
            $this->SetChild($child, $value);
        }else{
            $this->attrs[$offset] = (string)$value;
        }
    }
    
    function offsetUnset($offset)
    {
        unset($this->attrs[$offset]);
    }
    
    // functions dealing with children
    
    function hasChildren()
    {
        return !empty($this->children);
    }
        
    /*
        Get the last child with name $name
        If one doesn't exist, create it (if $create is TRUE)
    */
    protected function lastChildByName($name, $create = TRUE)
    {
        if(isset($this->child_names[$name])){
            return $this->child_names[$name][count($this->child_names[$name])-1];
        }elseif($create){
            return $this->addChild($name);
        }else{
            return NULL;
        }
    }
    
    /*
        Create a new child
    */
    protected function addChild($name)
    {
        return $this->addChildObj(new XMLegant($this, $name));
    }
    
    protected function addChildObj(XMLegant $child)
    {
        $this->child_names[$child->name][] = $child;
        $this->children[] = $child;
        $this->text = FALSE;
        $child->parent = $this;
        return $child;
    }
    
    protected static function SetChild($child, $value)
    {
        $child->deleteChildren();
        
        // if given an associative array, assume these are attributes
        if(is_array($value) && array_keys($value) != range(0, count($value)-1)){
            $child->attrs = $value;
        }elseif(is_a($value, 'XMLegant')){
            // Each XMLegant object has a "dummy" top node. When adding
            // an XMLegant object as a child node, we reach through this wrapper
            // to obtain the child node.
            if($value->hasChildren()){
                foreach($value->children as $valChild){    
                    $child->addChildObj(clone $valChild);    
                }
            }
        }else{
            if(empty($value)){
                $child->text = FALSE;
            }else{
                $child->text = (string)$value;    
            }
        }        
    }
    
    public function deleteChildren()
    {
        $this->child_names = array();
        $this->children = array();
    }
    
    /* 
        A node is defined as empty if it has no attributes, no children
        and no text
    */
    protected function IsEmpty()
    {
        return empty($this->attrs) 
                && empty($this->children)
                && $this->text === FALSE;
    }
    
    
    function __clone() 
    {
        $this->parent = NULL;
        $children = $this->children;
        $this->deleteChildren();
        foreach($children as $child)
        {
            $this->addChildObj(clone $child);
        }
    }    
    
    // object output
    
    function __toString()
    {
        $s = $this->name.': (';
        if($this->hasChildren()){
            foreach($this->children as $child){
                $s .= $child->name.': ';
                if($child->hasChildren()){
                    $s .= count($child->children);
                }else{
                    $s .= "\"{$child->text}\"";
                }
                
            }
            $s .= ',';
        }else{
            $s .= "\"{$this->text}\"";
        }
        $s .= ")\n";
        return $s;
    }

    function toXML($header = TRUE, XMLWriter $writer = NULL)
    {
        if($writer === NULL)
        {
            $version = '1.0';
            $enc = NULL;
            $standalone = NULL;
            if($this->parent === NULL){
                if(isset($this['version']))
                    $version = $this['version'];
                if(isset($this['encoding']))
                    $enc = $this['encoding'];
                if(isset($this['standalone']))
                    $standalone = $this['standalone'];
            }
            
            $writer = new XMLWriter();
            $writer->openMemory();
            if($header){
                $writer->startDocument($version, $enc, $standalone);
            }else{
                $writer->startDocument();
            }
            
            if($this->parent === NULL){
                if($this->hasChildren()){
                    foreach($this->children as $child){
                        $child->toXML($header, $writer);
                    }
                }    
            }else{
                $this->toXML($header, $writer);
            }
            
            $xml = $writer->outputMemory(true);
            
            if(!$header)
                $xml = str_replace("<?xml version=\"1.0\"?>\n", '', $xml);
            return $xml;
        }else{
            if($this->replace_underscores){
                $writer->startElement(str_replace('_', ':', $this->name));
            }else{
                $writer->startElement($this->name);
            }     
            
            if($this->hasAttrs()){
                foreach($this->attrs as $key=>$val){
                    if($this->replace_underscores){
                        $writer->writeAttribute(str_replace('_', ':', $key), $val);
                    }else{
                        $writer->writeAttribute($key, $val);
                    }
                }    
            }                   
            
            if($this->hasChildren()){
                foreach($this->children as $child){
                    $child->toXML($header, $writer);
                }
            }else{
                if($this->text !== FALSE){
                    $writer->text($this->text);
                }
            }            

            $writer->endElement();            
        }
               
    }
    
    function toSimpleXML()
    {
        return simplexml_load_string($this->toXML());
    }
}
