<?php
/**
 * User: umut
 * Date: 5.11.15
 * Time: 12:19
 */

class Factory {

    // Prior Veriables
    public $primary_key;

    // Table AND Joins
    public $main_table;
    public $join = array();
    public $inner_join = array();
    public $left_join = array();
    public $right_join = array();
    public $full_join = array();

    // Fields types
    public $fields = array();
    public $rfields = array();
    public $md5_fields = array();
    public $avg_fields = array();
    public $count_fields = array();
    public $max_fields = array();
    public $min_fields = array();
    public $sum_fields = array();
    public $ucase_fields = array();
    public $lcase_fields = array();
    public $len_fields = array();
    public $distinct_fields = array();

    // Md5
    public $md5_false = true;

    // Geometri
    public $geometryfields = array();
    public $lat,$lng;

    // Date Formats
    public $dateformat = '%d.%m.%Y %H:%i:%s';
    public $datefields = array();
    public $dateformats = array();
    public $noformatter = array();

    // Where
    public $where = array();
    public $where_and = array();
    public $where_or = array();

    // Having
    public $having = array();
    public $having_and = array();
    public $having_or = array();

    // Like
    public $like = array();
    public $like_and = array();
    public $like_or = array();
    public $like_start = array();
    public $like_start_and = array();
    public $like_start_or = array();
    public $like_end = array();
    public $like_end_and = array();
    public $like_end_or = array();

    // In
    public $in = array();

    // Between
    public $between = array();

    // Group By
    public $groupfields = array();

    // Order
    public $order_field;
    public $order = 'ASC';
    public $order_rand = false;

    // Limit
    public $start = 0;
    public $limit = 999999;

    // İsset
    public $isset = array();
    public $isset_true = array();
    public $isset_false = array();

    // Data
    public $data = array();
    public $last_insert_id;

    // Last Query
    public $last_query_string;

    // Query HARD String
    public $sql_query;

    // Query String
    public $querystring = array();


    function __set($key,$value)
    {

        $constants = array(
            'where_and_'=>'where_and',
            'where_or_'=>'where_or',
            'where_'=>'where',
            'having_and_'=>'having_and',
            'having_or_'=>'having_or',
            'having_'=>'having',
            'like_start_and_'=>'like_start_and',
            'like_start_or_'=>'like_start_or',
            'like_start_'=>'like_start',
            'like_end_and_'=>'like_end_and',
            'like_end_or_'=>'like_end_or',
            'like_end_'=>'like_end',
            'like_and_'=>'like_and',
            'like_or_'=>'like_or',
            'like_'=>'like',
            'isset_'=>'isset',
            'isset_true_'=>'isset_true',
            'isset_false_'=>'isset_false'
        );

        foreach($constants AS $cons=>$cons_arr)
        {
            if(strstr($key,$cons) !== false)
            {
                if(is_array($this->{$cons_arr}))
                {
                    $key = trim(str_replace($cons,'',$key));
                    $this->{$cons_arr}[$key] = $value;
                }
                else if(!is_array($this->{$cons_arr}) && isset($this->{$cons_arr}))
                {
                    $this->{$cons_arr} = $value;
                }
            }
        }

        $value = ((in_array($key,$this->md5_fields) && trim($value)!='')?(md5($value)):($value));
        $value = ((in_array($key,$this->geometryfields) && $value!='')?('IFNULL(GeomFromText('."'".$value."'".'),\'\')'):($value));
        $value = ((in_array($key,$this->datefields) && $value!='')?("STR_TO_DATE('".$value."','".((isset($this->dateformats[$key]))?($this->dateformats[$key]):($this->dateformat))."')"):($value));
        $value = ((is_string($value) && (!in_array($key,$this->geometryfields) && !in_array($key,$this->datefields)))?('"'.$value.'"'):($value));
        $value = ((trim($value)=='')?('""'):($value));
        if(trim($key)==trim($this->primary_key)){ $this->primary_data = $value; }
        if(!(in_array($key,$this->geometryfields) && trim($value)==''))
        {
            if(!(in_array($key,$this->md5_fields) && trim($value)==''))
            {
                $this->data[$key] = $value;
            }

        }

    }

    function PrepareQuery()
    {

        $querystring = array();
        $querystring['alias'] = '';
        $querystring['table'] = array();
        $querystring['join'] = array();
        $querystring['inner_join'] = array();
        $querystring['left_join'] = array();
        $querystring['right_join']  = array();
        $querystring['full_join'] = array();
        $querystring['fields'] = array();
        $querystring['where'] = array();
        $querystring['having'] = array();
        $querystring['like'] = array();
        $querystring['in'] = array();
        $querystring['between'] = array();
        $querystring['groupby'] = '';
        $querystring['orderby'] = '';
        $querystring['limit'] = '';

        // Table
        if(strstr(" AS ",$this->main_table))
        {
            $exp = explode(" AS ",$this->main_table);
            $querystring['alias'] = trim($exp[1]);
            $querystring['table'] = trim($exp[0]);
        }
        else
        {
            $querystring['alias'] = '';
            $querystring['table'] = $this->main_table;
        }

        // Join Table
        $arr = array();
        $arr['join'] = $this->join;
        $arr['inner_join'] = $this->inner_join;
        $arr['left_join'] = $this->left_join;
        $arr['right_join'] = $this->right_join;
        $arr['full_join'] = $this->full_join;

        $jarr_keys = array(
            "join"=>"JOIN",
            "inner_join"=>"INNER JOIN",
            "left_join"=>"LEFT JOIN",
            "right_join"=>"RIGHT JOIN",
            "full_join"=>"FULL JOIN"
        );

        if(is_array($arr) && count($arr)>0)
        {
            foreach($arr AS $join_key=>$join_arr)
            {
                if(is_array($join_arr) && count($join_arr)>0)
                {
                    foreach($join_arr AS $jtable=>$jon)
                    {

                        if(is_string($jon))
                        {
                            array_push($querystring[$join_key],$jarr_keys[$join_key].' '.$jtable.' ON '.$jon);
                        }
                        else if(is_array($jon) && is_array($jon[0]) && count($jon[0])>0)
                        {
                            $jarr = array();
                            foreach($jon AS $j)
                            {
                                if(in_array(trim($j[0]),array('AND','OR')))
                                {
                                    array_push($jarr,' '.trim($j[0]).' ');
                                    continue;
                                }

                                $ss = implode("",$j);
                                preg_match("/%SESSION\[(.*?)\]/",$ss,$match);

                                if(isset($match[1]) && $match[1]!='')
                                {
                                    $ss = preg_replace("/%SESSION\[(.*?)\]/",$_SESSION[$match[1]],$ss);
                                }

                                array_push($jarr,trim($ss));
                            }

                            array_push($querystring[$join_key],' '.$jarr_keys[$join_key].' '.$jtable.' ON '.implode(" ",$jarr));

                        }
                        else if(is_array($jon) && count($jon)==3)
                        {
                            array_push($querystring[$join_key],' '.$jarr_keys[$join_key].' '.$jtable.' ON '.implode(" ",$jon));
                        }
                        else if(is_array($jon) && count($jon)>3)
                        {
                            $jarr = array();
                            $jar = array();
                            foreach($jon AS $j)
                            {
                                if(in_array(trim($j),array('AND','OR')))
                                {
                                    array_push($jarr,implode(" ",$jar));
                                    array_push($jarr,' '.trim($j).' ');
                                    $jar = array();
                                    continue;
                                }
                                array_push($jarr,trim($j));
                            }

                            array_push($querystring[$join_key],' '.$jarr_keys[$join_key].' '.$jtable.' ON '.implode(" ",$jarr));
                        }

                    }
                }
            }
        }


        // Fields
        if(is_array($this->fields) && count($this->fields)>0)
        {
            foreach($this->fields AS $field)
            {
                if(in_array($field,$this->md5_fields) && $this->md5_false){ continue; } // MD5 alanları getirmeyecek.
                if(in_array($field,$this->geometryfields) && @trim($this->data[$field])==''){ continue;  } // Geometri alanlarını getirmeyecek
                array_push($querystring['fields'],(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$field);
            }
        }

        // Format Fields
        if(is_array($this->rfields) && count($this->rfields)>0)
        {
            foreach($this->rfields AS $rfield=>$rfieldval)
            {
                preg_match("/%SESSION\[(.*?)\]/",$rfield,$match);
                if(isset($match[1]) && $match[1]!='')
                {
                    $rfield = preg_replace("/%SESSION\[(.*?)\]/",$_SESSION[$match[1]],$rfield);
                }
                array_push($querystring['fields'],$rfield.' AS '.$rfieldval);

            }
        }

        // Avg Fields
        if(is_array($this->avg_fields) && count($this->avg_fields)>0)
        {
            foreach($this->avg_fields AS $avgfield)
            {
                array_push($querystring['fields'],'AVG('.$avgfield.') AS AVG'.$avgfield);
            }
        }

        // Count Fields
        if(is_array($this->count_fields) && count($this->count_fields)>0)
        {
            foreach($this->count_fields AS $countfield)
            {
                array_push($querystring['fields'],'COUNT('.$countfield.') AS COUNT'.$countfield);
            }
        }

        // Max Fields
        if(is_array($this->max_fields) && count($this->max_fields)>0)
        {
            foreach($this->max_fields AS $maxfield)
            {
                array_push($querystring['fields'],'MAX('.$maxfield.') AS MAX'.$maxfield);
            }
        }

        // Min Fields
        if(is_array($this->min_fields) && count($this->min_fields)>0)
        {
            foreach($this->min_fields AS $minfield)
            {
                array_push($querystring['fields'],'MIN('.$minfield.') AS MIN'.$minfield);
            }
        }

        // Sum Fields
        if(is_array($this->sum_fields) && count($this->sum_fields)>0)
        {
            foreach($this->sum_fields AS $sumfield)
            {
                array_push($querystring['fields'],'SUM('.$sumfield.') AS SUM'.$sumfield);
            }
        }

        // Ucase Fields
        if(is_array($this->ucase_fields) && count($this->ucase_fields)>0)
        {
            foreach($this->ucase_fields AS $ucasefield)
            {
                array_push($querystring['fields'],'UCASE('.$ucasefield.') AS UCASE'.$ucasefield);
            }
        }

        // Lcase Fields
        if(is_array($this->lcase_fields) && count($this->lcase_fields)>0)
        {
            foreach($this->lcase_fields AS $lcasefield)
            {
                array_push($querystring['fields'],'LCASE('.$lcasefield.') AS LCASE'.$lcasefield);
            }
        }

        // Len Fields
        if(is_array($this->len_fields) && count($this->len_fields)>0)
        {
            foreach($this->len_fields AS $lenfield)
            {
                array_push($querystring['fields'],'LEN('.$lenfield.') AS LEN'.$lenfield);
            }
        }

        // DISTINCT Fields
        if(is_array($this->distinct_fields) && count($this->distinct_fields)>0)
        {
            array_push($querystring['fields'],'DISTINCT ('.implode(",",$this->distinct_fields).')');
        }

        // Date Fields
        if(is_array($this->datefields) && count($this->datefields)>0)
        {
            foreach($this->datefields AS $datefield)
            {
                if(is_array($this->dateformats) && count($this->dateformats)>0 && isset($this->dateformats[$datefield]) && $this->dateformats[$datefield]!='')
                {
                    $str = "if($datefield='0000-00-00','',DATE_FORMAT($datefield,'".$this->dateformats[$datefield]."')) AS ".((in_array($datefield,$this->noformatter))?(''):('F')).$datefield;
                }
                else
                {
                    $str = "if($datefield='0000-00-00','',DATE_FORMAT($datefield,'".$this->dateformat."')) AS ".((in_array($datefield,$this->noformatter))?(''):('F')).$datefield;
                }
                array_push($querystring['fields'],$str);
            }
        }

        // Geometry Distance Fields
        if($this->lat!='' && $this->lng!='' && is_array($this->geometryfields) && count($this->geometryfields)>0)
        {
            array_push($querystring['fields'],"( 6371 * acos( cos( radians(".$this->lat.") ) * cos( radians( IFNULL(X(".current($this->geometryfields)."),'') ) ) * cos( radians( IFNULL(Y(".current($this->geometryfields)."),'') ) - radians(".$this->lng.") ) + sin( radians(".$this->lat.") ) * sin( radians( IFNULL(X(".current($this->geometryfields)."),'') ) ) ) ) AS KMDISTANCE");
        }


        // Where, Having, Like, Like Start, Like End, In, Between
        $arr = array();
        $arr['where'] = $this->where;
        $arr['where_and'] = $this->where_and;
        $arr['where_or'] = $this->where_or;
        $arr['having'] = $this->having;
        $arr['having_and'] = $this->having_and;
        $arr['having_or'] = $this->having_or;
        $arr['like'] = $this->like;
        $arr['like_and'] = $this->like_and;
        $arr['like_or'] = $this->like_or;
        $arr['like_start'] = $this->like_start;
        $arr['like_start_and'] = $this->like_start_and;
        $arr['like_start_or'] = $this->like_start_or;
        $arr['like_end'] = $this->like_end;
        $arr['like_end_and'] = $this->like_end_and;
        $arr['like_end_or'] = $this->like_end_or;

        foreach($arr AS $key=>$a)
        {
            if(is_array($a) && count($a)>0)
            {
                $whrv = array();
                foreach($a AS $akey=>$aval)
                {

                    if(is_array($aval) && count($aval)>0)
                    {
                        $twhrv = array();
                        foreach($aval AS $wv)
                        {
                            // SESSION
                            preg_match("/%SESSION\[(.*?)\]/",$wv,$match);
                            if(isset($match[1]) && $match[1]!='')
                            {
                                $wv = preg_replace("/%SESSION\[(.*?)\]/",$_SESSION[$match[1]],$wv);
                            }

                            // TYPES ..
                            if($key == 'like')
                            {
                                array_push($twhrv,'('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'%") ');
                            }
                            else  if($key == 'like_and')
                            {
                                array_push($twhrv,'AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'%") ');
                            }
                            else  if($key == 'like_or')
                            {
                                array_push($twhrv,'OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'%") ');
                            }
                            else if($key == 'like_start')
                            {
                                array_push($twhrv,'('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$wv.'%") ');
                            }
                            else if($key == 'like_start_and')
                            {
                                array_push($twhrv,'AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$wv.'%") ');
                            }
                            else if($key == 'like_start_or')
                            {
                                array_push($twhrv,'OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$wv.'%") ');
                            }
                            else if($key == 'like_end')
                            {
                                array_push($twhrv,'('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'") ');
                            }
                            else if($key == 'like_end_and')
                            {
                                array_push($twhrv,'AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'") ');
                            }
                            else if($key == 'like_end_or')
                            {
                                array_push($twhrv,'OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$wv.'") ');
                            }
                            else if($key == 'in')
                            {
                                array_push($twhrv,"(".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey." IN (".implode(",",$wv)."))");
                            }
                            else if($key == 'between')
                            {
                                if(is_array($aval) && count($aval)==2)
                                {
                                    array_push($whrv,"(".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey." BETWEEN ".$aval[0]." AND ".$aval[1].")");
                                }
                            }
                            else if($key == 'where')
                            {
                                array_push($twhrv, '('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv.')');
                            }
                            else if($key == 'where_and')
                            {
                                array_push($twhrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv .')');
                            }
                            else if($key == 'where_or')
                            {
                                array_push($twhrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv .')');
                            }
                            else if($key == 'having')
                            {
                                array_push($twhrv, '('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv.')');
                            }
                            else if($key == 'having_and')
                            {
                                array_push($twhrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv .')');
                            }
                            else if($key == 'having_or')
                            {
                                array_push($twhrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $wv .')');
                            }
                        }
                        if(is_array($twhrv) && count($twhrv)>0)
                        {
                            array_push($whrv,implode(" ",$twhrv));
                        }
                    }
                    else
                    {
                        // SESSION ...
                        preg_match("/%SESSION\[(.*?)\]/",$aval,$match);
                        if(isset($match[1]) && $match[1]!='')
                        {
                            $aval = preg_replace("/%SESSION\[(.*?)\]/",$_SESSION[$match[1]],$aval);
                        }

                        // TYPES ...
                        if($key == 'like')
                        {
                            array_push($whrv,' ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'%")');
                        }
                        else if($key == 'like_and')
                        {
                            array_push($whrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'%")');
                        }
                        else if($key == 'like_or')
                        {
                            array_push($whrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'%")');
                        }
                        else if($key == 'like_start')
                        {
                            array_push($whrv,'('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$aval.'%")');
                        }
                        else if($key == 'like_start_and')
                        {
                            array_push($whrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$aval.'%")');
                        }
                        else if($key == 'like_start_or')
                        {
                            array_push($whrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "'.$aval.'%")');
                        }
                        else if($key == 'like_end')
                        {
                            array_push($whrv,'('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'")');
                        }
                        else if($key == 'like_end_and')
                        {
                            array_push($whrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'")');
                        }
                        else if($key == 'like_end_or')
                        {
                            array_push($whrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey.' LIKE "%'.$aval.'")');
                        }
                        else if($key == 'in')
                        {
                            array_push($whrv,"(".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey." IN (".implode(",",$aval)."))");
                        }
                        else if($key == 'between')
                        {
                            if(is_array($aval) && count($aval)==2)
                            {
                                array_push($whrv,"(".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey." BETWEEN ".$aval[0]." AND ".$aval[1].")");
                            }
                        }
                        else if($key == 'where')
                        {
                            array_push($whrv, '('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval.')');
                        }
                        else if($key == 'where_and')
                        {
                            array_push($whrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval .')');
                        }
                        else if($key == 'where_or')
                        {
                            array_push($whrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval .')');
                        }
                        else if($key == 'having')
                        {
                            array_push($whrv, '('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval.')');
                        }
                        else if($key == 'having_and')
                        {
                            array_push($whrv,' AND ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval .')');
                        }
                        else if($key == 'having_or')
                        {
                            array_push($whrv,' OR ('.(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$akey . "=" . $aval .')');
                        }

                    }
                }
                $key = ((strstr($key,"_"))?(current(explode("_",$key))):($key));
                if(is_array($whrv) && count($whrv)>0)
                {
                    array_push($querystring[$key],implode(" ",$whrv));
                }
            }
        }

        // Group By
        if(is_array($this->groupfields) && count($this->groupfields)>0)
        {
            $querystring['groupby'] = "GROUP BY ".implode(",",$this->groupfields);
        }

        // Order by
        if($this->order_rand)
        {
            $querystring['orderby'] = "ORDER BY RAND()";
        }
        else if($this->order_field!='')
        {
            $querystring['orderby'] = "ORDER BY ".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$this->order_field." ".$this->order;
        }
        else if($this->primary_key!='')
        {
            $querystring['orderby'] = "ORDER BY ".(($querystring['alias']!='')?($querystring['alias'].'.'):('')).$this->primary_key." ".$this->order;
        }

        // Limit
        if(intval($this->start)>0 && intval($this->limit)>0)
        {
            $querystring['limit'] = "LIMIT ".intval($this->start).",".intval($this->limit);
        }

        return $querystring;

    }

    function GetQueryString($type='',$data=array())
    {

        $this->querystring = $this->PrepareQuery(); // Prepare Query ...
        $str = $this->sql_query; // HARD QUERY...

        if($type=='SELECT')
        {

            $str  = 'SELECT ';
            $str .= ((is_array($this->querystring['fields']) && count($this->querystring['fields'])>0 )?('STRAIGHT_JOIN '.implode(',',$this->querystring['fields'])):('*'));
            $str .= ' FROM ';
            $str .= (($this->querystring['alias']!='')?(' '.$this->querystring['table'].' AS '.$this->querystring['alias'].' '):(' '.$this->querystring['table'].' '));
            $str .= ((is_array($this->querystring['join']) && count($this->querystring['join'])>0)?(implode(" ",$this->querystring['join'])):(''));
            $str .= ((is_array($this->querystring['inner_join']) && count($this->querystring['inner_join'])>0)?(implode(" ",$this->querystring['inner_join'])):(''));
            $str .= ((is_array($this->querystring['left_join']) && count($this->querystring['left_join'])>0)?(implode(" ",$this->querystring['left_join'])):(''));
            $str .= ((is_array($this->querystring['right_join']) && count($this->querystring['right_join'])>0)?(implode(" ",$this->querystring['right_join'])):(''));
            $str .= ((is_array($this->querystring['full_join']) && count($this->querystring['full_join'])>0)?(implode(" ",$this->querystring['full_join'])):(''));
            $str .= (((count($this->querystring['where'])>0) || (count($this->querystring['like'])>0) || (count($this->querystring['in'])>0)  || (count($this->querystring['between'])>0))?(' WHERE '):(''));
            $str .= ((is_array($this->querystring['where']) && count($this->querystring['where'])>0)?(implode(" ",$this->querystring['where'])):(''));
            $str .= ((is_array($this->querystring['like']) && count($this->querystring['like'])>0)?('('.implode(" ",$this->querystring['like']).')'):(''));
            $str .= ((is_array($this->querystring['in']) && count($this->querystring['in'])>0)?('('.implode(" OR ",$this->querystring['in']).')'):(''));
            $str .= ((is_array($this->querystring['between']) && count($this->querystring['between'])>0)?('('.implode(" OR ",$this->querystring['between']).')'):(''));
            $str .= ((count($this->querystring['having'])>0)?('HAVING '):(''));
            $str .= ((is_array($this->querystring['having']) && count($this->querystring['having'])>0)?(implode(" ",$this->querystring['having'])):(''));
            $str .= ((isset($this->querystring['groupby']) && $this->querystring['groupby']!='')?(' '.$this->querystring['groupby'].' '):(''));
            $str .= ((isset($this->querystring['orderby']) && $this->querystring['orderby']!='')?(' '.$this->querystring['orderby'].' '):(''));
            $str .= ((isset($this->querystring['limit']) && $this->querystring['limit']!='')?(' '.$this->querystring['limit'].' '):(''));

        }
        else if($type=='INSERT')
        {
            $str  = 'INSERT INTO ';
            $str .=  $this->querystring['table'].' ';
            $str .= '('.implode(",",array_keys($data)).')';
            $str .= ' VALUES ';
            $str .= '('.implode(",",array_values($data)).')';
        }
        else if($type=='UPDATE')
        {
            $str  = 'UPDATE ';
            $str .=  $this->querystring['table'].' ';
            $str .= ' SET ';
            $str .= implode(', ', array_map(function ($v, $k) { return $k . '=' . $v;  }, $data, array_keys($data) ) );
            $str .= (((count($this->querystring['where'])>0) || (count($this->querystring['like'])>0) || (count($this->querystring['in'])>0)  || (count($this->querystring['between'])>0))?(' WHERE '):(''));
            $str .= ((is_array($this->querystring['where']) && count($this->querystring['where'])>0)?('('.implode("  ",$this->querystring['where']).')'):(''));
            $str .= ((is_array($this->querystring['like']) && count($this->querystring['like'])>0)?('('.implode("  ",$this->querystring['like']).')'):(''));
            $str .= ((is_array($this->querystring['in']) && count($this->querystring['in'])>0)?('('.implode(" OR ",$this->querystring['in']).')'):(''));
            $str .= ((is_array($this->querystring['between']) && count($this->querystring['between'])>0)?('('.implode(" OR ",$this->querystring['between']).')'):(''));
            $str .= ((count($this->querystring['having'])>0)?('HAVING '):(''));
            $str .= ((is_array($this->querystring['having']) && count($this->querystring['having'])>0)?(implode(" ",$this->querystring['having'])):(''));

        }
        else if($type=='DELETE')
        {
            $str  = 'DELETE FROM ';
            $str .=  $this->querystring['table'].' ';
            $str .= (((count($this->querystring['where'])>0) || (count($this->querystring['like'])>0) || (count($this->querystring['in'])>0)  || (count($this->querystring['between'])>0))?(' WHERE '):(''));
            $str .= ((is_array($this->querystring['where']) && count($this->querystring['where'])>0)?('('.implode(" ",$this->querystring['where']).')'):(''));
            $str .= ((is_array($this->querystring['like']) && count($this->querystring['like'])>0)?('('.implode(" ",$this->querystring['like']).')'):(''));
            $str .= ((is_array($this->querystring['in']) && count($this->querystring['in'])>0)?('('.implode(" OR ",$this->querystring['in']).')'):(''));
            $str .= ((is_array($this->querystring['between']) && count($this->querystring['between'])>0)?('('.implode(" OR ",$this->querystring['between']).')'):(''));
            $str .= ((count($this->querystring['having'])>0)?('HAVING '):(''));
            $str .= ((is_array($this->querystring['having']) && count($this->querystring['having'])>0)?(implode(" ",$this->querystring['having'])):(''));

        }
        else if($type=='COUNT')
        {

            $str  = 'SELECT ';
            $str .= 'COUNT(*) ';
            $str .= ' FROM ';
            $str .= (($this->querystring['alias']!='')?(' '.$this->querystring['table'].' AS '.$this->querystring['alias'].' '):(' '.$this->querystring['table'].' '));
            $str .= ((is_array($this->querystring['join']) && count($this->querystring['join'])>0)?(implode(" ",$this->querystring['join'])):(''));
            $str .= ((is_array($this->querystring['inner_join']) && count($this->querystring['inner_join'])>0)?(implode(" ",$this->querystring['inner_join'])):(''));
            $str .= ((is_array($this->querystring['left_join']) && count($this->querystring['left_join'])>0)?(implode(" ",$this->querystring['left_join'])):(''));
            $str .= ((is_array($this->querystring['right_join']) && count($this->querystring['right_join'])>0)?(implode(" ",$this->querystring['right_join'])):(''));
            $str .= ((is_array($this->querystring['full_join']) && count($this->querystring['full_join'])>0)?(implode(" ",$this->querystring['full_join'])):(''));
            $str .= (((count($this->querystring['where'])>0) || (count($this->querystring['like'])>0) || (count($this->querystring['in'])>0)  || (count($this->querystring['between'])>0))?(' WHERE '):(''));
            $str .= ((is_array($this->querystring['where']) && count($this->querystring['where'])>0)?('('.implode(" ",$this->querystring['where']).')'):(''));
            $str .= ((is_array($this->querystring['like']) && count($this->querystring['like'])>0)?('('.implode(" ",$this->querystring['like']).')'):(''));
            $str .= ((is_array($this->querystring['in']) && count($this->querystring['in'])>0)?('('.implode(" OR ",$this->querystring['in']).')'):(''));
            $str .= ((is_array($this->querystring['between']) && count($this->querystring['between'])>0)?('('.implode(" OR ",$this->querystring['between']).')'):(''));
            $str .= ((count($this->querystring['having'])>0)?('HAVING '):(''));
            $str .= ((is_array($this->querystring['having']) && count($this->querystring['having'])>0)?(implode(" ",$this->querystring['having'])):(''));
            $str .= ((isset($this->querystring['groupby']) && $this->querystring['groupby']!='')?(' '.$this->querystring['groupby'].' '):(''));
            $str .= ((isset($this->querystring['orderby']) && $this->querystring['orderby']!='')?(' '.$this->querystring['orderby'].' '):(''));

        }

        $this->last_query_string = $str; // SET LAST QUERY STRİNG

        return $str;
    }


}