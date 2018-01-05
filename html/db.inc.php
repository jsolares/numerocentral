<?php
    /**
     * Para poder utilizar esta clase, es necesario tener instalado
     * el modulo DB de PEAR para PHP4 (http://pear.php.net)
     */
    include_once "DB.php";

    class DB_Sql {
        var $DBMS     = "";
        var $Host     = "";
        var $Database = "";
        var $User     = "";
        var $Password = "";
        var $dsn      = "";

        var $Link_ID  = 0;
        var $Query_ID = 0;
        var $Record   = array();
        var $Row      = 0;

        var $fetchmode;

        var $Errno    = 0;
        var $Error    = "";

        var $Auto_Free = 0;

        function DB_Sql( $DBMS, $Host, $Database, $User, $Password, $FetchMode = DB_FETCHMODE_ASSOC )
        {
            $this->DBMS      = $DBMS;
            $this->Host      = $Host;
            $this->Database  = $Database;
            $this->User      = $User;
            $this->Password  = $Password;
            $this->fetchmode = $FetchMode;
        }

        function copy()
        {
            return new DB_Sql(
                $this->DBMS,
                $this->Host,
                $this->Database,
                $this->User,
                $this->Password,
                $this->fetchmode
            );
        }

        function connect()
        {
            if ( !$this->Link_ID ) {
                $this->Link_ID = DB::connect( $this->getDSN(), true );
                if ( DB::isError( $this->Link_ID ) ) {
                    $this->halt( "DB::isError == true, DB::connect failed" );
                } 
            }
        }

        function getDSN()
        {
            $dbms = $this->DBMS;
            $user = $this->User;
            $pass = $this->Password;
            $host = $this->Host;
            $db   = $this->Database;

            return "$dbms://$user:$pass@$host/$db";
        }

        function query( $query_string )
        {
            if ( $query_string == "" )
                return 0;

            $this->connect();

            $this->Query_ID = $this->Link_ID->query( $query_string );
            $this->Row = 0;

            if ( DB::isError( $this->Query_ID ) ) {
                $this->Error = $this->Query_ID->getMessage();
                $this->halt( "QUERY=" . $query_string );
            }

            return $this->Query_ID;
        }

        function next_record()
        {
            $this->Record = $this->Query_ID->fetchRow( $this->fetchmode, $this->Row++ );

            if ( !$this->Record ) {
                if ( $this->Auto_Free ) {
                    $this->Query_ID->free();
                    $this->Query_ID = 0;
                }
            }
            return $this->Record;
        }

        function tableInfo()
        {
            return $this->Query_ID->tableInfo();
        }

        function seek( $pos )
        {
            $this->Row = $pos;
        }

        function next_id( $seq )
        {
            if ( $seq == "" )
                return 0;

            $this->connect();

            return $this->Link_ID->nextId( $seq );
        }

        function affected_rows()
        {
            return $this->Link_ID->affectedRows();
        }

        function num_rows()
        {
            return $this->Query_ID->numRows();
        }

        function num_fields()
        {
            return $this->Query_ID->numCols();
        }

        function nf()
        {
            return $this->num_rows();
        }

        function np()
        {
            return $this->num_rows();
        }

        function f( $field_name )
        {
            return $this->Record[strtolower( $field_name )];
        }

        function p( $field_name )
        {
            return $this->f( $field_name );
        }

        function halt( $msg )
        {
            printf( "</td></tr></table><b>Database error:</b> %s<br>\n", $msg );
            printf( "<b>%s</b> <br>\n", $this->Error );
            die( "Session halted." );
        }

    }

?>
