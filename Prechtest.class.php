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
	}
	//Install method. use this or install.php using both may cause weird behavior
	public function install() {}
	//Uninstall method. use this or install.php using both may cause weird behavior
	public function uninstall() {}
	//Not yet implemented
	public function backup() {}
	//not yet implimented
	public function restore($backup) {}
	//process form
	public function doConfigPageInit($page) {}
	//This shows the submit buttons
	public function getActionBar($request) {
		$buttons = array();
		switch($_GET['display']) {
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
	public function showPage(){
                include("classes/AstMan.php");
		// $vars = array('helloworld' => _("Hello World"));
		$vars = array("prech_var" => "siema");
                
                //print_r(json_encode($calls));
                
                return load_view(__DIR__.'/views/main.php',$vars);
	}
        
        public function cli_runcommand($txtCommand) {
		if ($this->AstMan) {
			$response = $this->AstMan->send_request('Command',array('Command'=>"$txtCommand"));
			if(!empty($response['data'])) {
				$response = explode("\n",$response['data']);
				unset($response[0]); //remove the Priviledge Command line
				$response = implode("\n",$response);
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
			default:
				return false;
			break;
		}
	}
	public function ajaxHandler(){
		switch ($_REQUEST['command']) {
			case 'getJSON':
                            $out = $this->cli_runcommand("core show channels verbose");
                            while(strpos($out, "  ") != false) {
                                $out = str_replace("  ", " ", $out);
                            }
                            $out = str_replace("\t", " ", $out);
                            $out = str_replace("\n", "<br />", $out);
                            $arr = explode("<br />", $out);
                            $calls = array();
                            $retHtml = "";
                            //print_r($arr);
                            foreach($arr as $row) {
                                $rowdb = explode(" ", $row);
                                if (
                                        count($rowdb) > 6 && 
                                        $row != $arr[0] && 
                                        count($arr) > 4 &&
                                        strpos($row, "Channel") === false &&
                                        strpos($row, "active channels") === false &&
                                        strpos($row, "active calls") === false &&
                                        strpos($row, "calls processed") === false
                                    ) {
                                    
                                    $channel = $rowdb[0];
                                    $extdb = explode("@", $channel);
                                    if (count($extdb) > 1) $ext = explode("/", $extdb[0])[1];
                                    if (!isset($ext) || $ext != $rowdb[2]) {
                                        $rowobj = new \stdClass();
                                        $rowobj->caller_ext = $ext;
                                        $rowobj->duration = isset($rowdb[9]) ? $row[9] : null;
                                        $target = isset($rowdb[1]) ? $rowdb[1] : null;
                                        $rowobj->target_number = isset($rowdb[2]) ? $rowdb[2] : null;
                                        $rowobj->status = isset($rowdb[4]) ? $rowdb[4] : null;
                                        $rowobj->application = isset($rowdb[5]) ? $rowdb[5] : null;
                //                        print_r($rowobj);
                                        if ($rowobj->caller_ext != null && $rowobj->caller_ext != $rowobj->target_number) {
                                            array_push($calls, $rowobj);
                                            $retHtml .= "<tr><td>$rowobj->caller_ext</td><td>$rowobj->target_number</td><td>$rowobj->duration</td><td>$rowobj->status</td><td>$rowobj->application</td></tr>";
                                        }
                                    }
                                }
                            }
                            $returnobj = new \stdClass();
                            $returnobj->status = true;
                            $returnobj->message = $retHtml;
                            return $returnobj;
			break;

			default:
				return false;
			break;
		}
	}
	public function getRightNav($request) {
		$html = '';
		return $html;
	}

}
