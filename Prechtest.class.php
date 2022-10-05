<?php

namespace FreePBX\modules;

/*
 * Class stub for BMO Module class
 * In _Construct you may remove the database line if you don't use it
 * In getActionbar change extdisplay to align with whatever variable you use to decide if the page is in edit mode.
 *
 */

class Prechtest implements \BMO {

    public function __construct($freepbx = null) {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
        $this->AstMan = $freepbx->astman;

        // Variables to try. If the key is blank/unset, use the value instead.
        $vars = array(
            "CDRDBHOST" => "AMPDBHOST",
            "CDRDBPORT" => "AMPDBPORT",
            "CDRDBUSER" => "AMPDBUSER",
            "CDRDBPASS" => "AMPDBPASS",
            "CDRDBTYPE" => "AMPDBTYPE",
            // This is removed if unset
            "CDRDBSOCK" => "AMPDBSOCK",
            // Note - no default, we check later.
            "CDRDBNAME" => "CDRDBNAME",
            "CDRDBTABLENAME" => "CDRDBTABLENAME",
            "CDRUSEGMT" => "CDRUSEGMT",
        );

        $cdr = array();
        foreach ($vars as $conf => $default) {
            $tmp = \FreePBX::Config()->get($conf);
            // Is our config blank for this setting?
            if (!$tmp) {
                // How about the default?
                $defvalue = \FreePBX::Config()->get($default);
                if ($defvalue) {
                    $cdr[$conf] = $defalue;
                } else {
                    // Well that's blank. Is it part of FreePBX::$conf? (That's the parsed output of /etc/freepbx.conf)
                    if (empty(\FreePBX::$conf[$default])) {
                        // No. Set it to blank.
                        $cdr[$conf] = "";
                    } else {
                        $cdr[$conf] = \FreePBX::$conf[$default];
                    }
                }
            } else {
                // We have a setting
                $cdr[$conf] = $tmp;
            }
        }

        // If CDRDBNAME is blank, set it to asteriskcdrdb
        if (!$cdr['CDRDBNAME']) {
            $dsnarray = array("dbname" => "asteriskcdrdb");
        } else {
            $dsnarray = array("dbname" => $cdr['CDRDBNAME']);
        }

        // If we don't have a type (bogus install, possibly?), assume mysql
        if (!$cdr['CDRDBTYPE']) {
            $engine = "mysql";
        } else {
            // The db 'type' name can be wrong. Remap it to the correct one if it is
            if ($cdr['CDRDBTYPE'] == "postgres") {
                $engine = "pgsql";
            } else {
                $engine = $cdr['CDRDBTYPE'];
            }
        }

        // If we have a socket, we don't want host and port.
        if ($cdr['CDRDBSOCK']) {
            $dsnarray['unix_socket'] = $cdr['CDRDBSOCK'];
        } else {
            $dsnarray['host'] = $cdr['CDRDBHOST'];
            // Do we have a port?
            if ($cdr['CDRDBPORT']) {
                $dsnarray['port'] = $cdr['CDRDBPORT'];
            }
        }

        // If there's no cdrdbtablename, set it to cdr
        if (!$cdr['CDRDBTABLENAME']) {
            $this->db_table = "cdr";
        } else {
            $this->db_table = $cdr['CDRDBTABLENAME'];
        }

        // If this is sqlite, ignore everything we've just done.
        if (strpos($engine, "sqlite") === 0) {
            // This is our raw parsed variables from /etc/freepbx.conf
            $ampconf = \FreePBX::$amp_conf;
            if (isset($amp_conf['cdrdatasource'])) {
                $dsn = "$engine:" . $amp_conf['cdrdatasource'];
            } elseif (!empty($amp_conf['datasource'])) {
                $dsn = "$engine:" . $amp_conf['datasource'];
            } else {
                throw new \Exception("Datasource set to sqlite, but no cdrdatasource or datasource provided");
            }
            $user = "";
            $pass = "";
        } else {
            // Not SQLite.
            $user = $cdr["CDRDBUSER"];
            $pass = $cdr["CDRDBPASS"];

            // Note - http_build_query() is a simple shortcut to change a key=>value array
            // to a string.
            $dsn = "$engine:" . http_build_query($dsnarray, '', ';');
        }
        // Now try to get a DB handle using our DSN
        try {
            $this->cdrdb = new \Database($dsn, $user, $pass);
        } catch (\Exception $e) {
            die("Unable to connect to CDR Database using dsn '$dsn' with user '$user' and password '$pass' - " . $e->getMessage());
        }
    }

    //Install method. use this or install.php using both may cause weird behavior
    public function install() {

    }

    //Uninstall method. use this or install.php using both may cause weird behavior
    public function uninstall() {

    }

    //Not yet implemented
    public function backup() {

    }

    //not yet implimented
    public function restore($backup) {

    }

    //process form
    public function doConfigPageInit($page) {

    }

    //This shows the submit buttons
    public function getActionBar($request) {
        $buttons = array();
        switch ($_GET['display']) {
            case 'prechtest':
                $buttons = array(
                    'delete' => array(
                        'name' => 'delete',
                        'id' => 'delete',
                        'value' => _('Delete')
                    ),
                        //					'reset' => array(
                        //						'name' => 'reset',
                        //						'id' => 'reset',
                        //						'value' => _('Reset')
                        //					),
                        //					'submit' => array(
                        //						'name' => 'submit',
                        //						'id' => 'submit',
                        //						'value' => _('Submit')
                        //					)
                );
                if (empty($_GET['extdisplay'])) {
                    unset($buttons['delete']);
                }
                break;
        }
        return $buttons;
    }

    public function showPage() {
        // $vars = array('helloworld' => _("Hello World"));

        $callLogHtml = "";
        $q = "SELECT * FROM cdr ORDER BY calldate DESC LIMIT 100";
        $r = $this->cdrdb->prepare($q);
        $r->execute(array("query" => "%" . $q . "%"));
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $callLogHtml .= "<tr>
                            <td>" . $row["calldate"] . "</td>
                            <td>" . $row["src"] . "</td>
                            <td>" . $row["dst"] . "</td>
                            <td>" . gmdate("H:i:s", $row["duration"]) . "</td>
                            <td>" . $row["cnum"] . "</td>
                            <td>" . $row["lastapp"] . "</td>
                            </tr>";
        }
        $vars = array("prech_var" => $callLogHtml);

        return load_view(__DIR__ . '/views/main.php', $vars);
    }

    public function cli_runcommand($txtCommand) {
        if ($this->AstMan) {
            $response = $this->AstMan->send_request('Command', array('Command' => "$txtCommand"));
            if (!empty($response['data'])) {
                $response = explode("\n", $response['data']);
                unset($response[0]); //remove the Priviledge Command line
                $response = implode("\n", $response);
                $html_out = htmlspecialchars($response);
                return $html_out;
            } else {
                return _("No Output Returned");
            }
        }
    }

    public function ajaxRequest($req, &$setting) {
        switch ($req) {
            case 'getJSON':
                return true;
                break;
            case 'hangup':
                return true;
            case 'call':
                return true;
            default:
                return false;
                break;
        }
    }

    public function ajaxHandler() {
        switch ($_REQUEST['command']) {
            case 'getJSON':
                $out = $this->cli_runcommand("core show channels concise");
                while (strpos($out, "  ") != false) {
                    $out = str_replace("  ", " ", $out);
                }
                $out = str_replace("\t", " ", $out);
                $out = str_replace("\n", "<br />", $out);
                $arr = explode("<br />", $out);
                $calls = array();
                if (count($arr) > 0) {
                    $returnobj = new \stdClass();
                    $returnobj->status = true;
                    $returnobj->message = "";
                    $returnarr = array();
                    $callarr = array();
                    foreach ($arr as $row) {
                        $rowdb = explode("!", $row);
                        //print_r($rowdb);
                        $rowobj = new \stdClass();
                        if (count($rowdb) > 10) {
                            $channelstr = $rowdb[0];
                            $rowobj->caller_ext = $rowdb[0];
                            $rowobj->call_id = $rowdb[12];
                            $rowobj->target_number = $rowdb[2];
                            if ($rowobj->target_number == 1)
                                $rowobj->target_number = "None";
                            $rowobj->duration = gmdate("H:i:s", $rowdb[11]);
                            $rowobj->application = $rowdb[5];
                            $rowobj->status = $rowdb[4];
                            $rowobj->CID = $rowdb[7];
                            $rowobj->target_ext = is_int($rowdb[2]) ? $rowdb[2] : null;
                            array_push($returnarr, $rowobj);
                            //print_r($returnarr);
                        }
                    }
                    foreach ($returnarr as $returnrow) {
                        $call = $this->GetCallFromArray($callarr, $returnrow->caller_ext, $returnrow->call_id);
                        if ($call != null && !is_int($call->target_ext)) {
                            $returnrow->target_ext = $call->CID;
                            array_push($callarr, $returnrow);
                        } else {
                            array_push($callarr, $returnrow);
                        }
                    }
                    foreach ($callarr as $rowobj) {
                        $returnobj->message .= "<tr class='text-center'>
                            <td>$rowobj->CID</td>
                            <td>$rowobj->target_number</td>
                            <td>$rowobj->duration</td>
                            <td>$rowobj->status</td>
                            <td>$rowobj->application</td>
                            <td><a class='btn btn-danger' href='#' onclick='hangupCall(\"$channelstr\");'>HangUp</a></td>
                            </tr>";
                    }


                    return $returnobj;
                } else {
                    $returnobj = new \stdClass();
                    $returnobj->status = true;
                    $returnobj->message = "";
                    return $returnobj;
                }
                break;
            case 'hangup':
                $callId = $_GET["call"];
                $out = $this->cli_runcommand("channel request hangup $callId");
                return $out;
                break;
            case 'call':
                $src = $_GET["src"];
                $dsc = $_GET["dsc"];
                $cmd = "channel originate local/$src@from-internal extension $dsc@from-internal";
                //print_r($cmd);
                $out = $this->cli_runcommand($cmd);
                return $out;
                break;
            default:
                return false;
                break;
        }
    }

    public function GetCallFromArray($arr, $caller, $callid) {
        foreach ($arr as $r) {
            if ($r->call_id === $callid && $caller != $r->caller_ext) {
                return $r;
            }
        }
        return null;
    }

    public function getRightNav($request) {
        $html = '';
        return $html;
    }

}
