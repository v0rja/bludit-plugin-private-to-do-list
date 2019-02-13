<?php

class PrivateToDo extends Plugin
{

    public function init()
    {
        $this->dbFields = array(
            'enable' => true,
			'static-page' => '',
			'data' => ''
        );
    }

    public function form()
    {
        global $L;

        $html  = '<div>';
        $html .= '<label>' . $L->get('enable-private-to-do') . '</label>';
        $html .= '<select name="enable">';
        $html .= '<option value="true" ' . ($this->getValue('enable') === true ? 'selected' : '') . '>Enabled</option>';
        $html .= '<option value="false" ' . ($this->getValue('enable') === false ? 'selected' : '') . '>Disabled</option>';
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div>';
		$html .= '<label>' . $L->get('uri-page') . '</label>';
		$html .= '<input name="static-page" id="jsstatic-page" type="text" value="' . $this->getValue('static-page') . '">';
        $html .= '</div>';

        return $html;
    }
	
	private function contains($text,$string){
		str_replace($text,'',$string,$find);
		if($find) return true; else return false;
	}

    public function pageEnd()
    {
        if ($this->getValue('enable')) {
			global $url;
		
			$obj_array = (Array) $url;         //cast the object as an Array
			$text = null;
			
            /**
             * 302 Redirect to admin if not logged in.
             */
            $login = new Login();
            if ($login->isLogged() && $this->contains($this->getValue('static-page'),$obj_array["\0*\0" . 'uri'])) {
				
				$valueDatabase = $this->getValue('static-page');
			
				$data = json_decode(Sanitize::htmlDecode($this->getValue($valueDatabase)),true);
				
				if(isset($_POST['task'])){
					foreach($data as $k=>$val){
						$insert[$k]['task'] = $val['task'];
						$insert[$k]['estado'] = $val['estado'];		
					}
					
					$insert[time()]['task'] = $_POST['task'];
					$insert[time()]['estado'] = 0;
					
					$this->db[$valueDatabase] = Sanitize::html(json_encode($insert));
					$this->save();
				}
				
				if(isset($_POST['udp'])){
					foreach($data as $k=>$val){
						$insert[$k]['task'] = $val['task'];
						$insert[$k]['estado'] = $val['estado'];
						if($_POST['id'] == $k && isset($_POST['done'])) $insert[$k]['estado'] = time();
						if($_POST['id'] == $k && isset($_POST['undone'])) $insert[$k]['estado'] = 0;
					}
										
					$this->db[$valueDatabase] = Sanitize::html(json_encode($insert));
					$this->save();
				}
				
				
				if(isset($_POST['showAll'])){
					$text .= '<form method="post" class="text-right"><input type="hidden" name="showall" value="0"><input type="submit" class="btn btn-default" name="showAllcancel" value="Cancel"></form>';
				} else {
					$text .= '<form method="post" class="text-right"><input type="hidden" name="showall" value="0"><input type="submit" class="btn btn-default" name="showAll" value="Show Completed"></form>';
				}
				
				

				// datos actualizados
				$data = json_decode(Sanitize::htmlDecode($this->getValue($valueDatabase)),true);
				
				$text .= '<table class="table table-hover" style="border-radius:10px !important">';
				$text .= '<tr><th width="20%">Date</th><th width="50%">Task</th><th width="10%">Action</th></tr>';
				foreach($data as $date=>$val){
					if($val['estado'] == 0){
						if(!isset($_POST['showAll'])) $text .= '<form method="post"><tr><td>'.date('Y-m-d H:i:s',$date).'</td><td>'.$val['task'].'</td><td class="text-right"><input type="hidden" name="udp"><input type="hidden" name="id" value="'.$date.'"><input type="submit" name="done" class="btn btn-primary btn-xs" value="Done" style="padding:5px;font-size:12px"></td></tr></form>';				
					} else {
						if(isset($_POST['showAll'])) $text .= '<form method="post"><tr><td>'.date('Y-m-d H:i:s',$date).'</td><td>'.$val['task'].'</td><td class="text-right"><input type="hidden" name="udp"><input type="hidden" name="id" value="'.$date.'"><input type="submit" name="undone" class="btn btn-danger btn-xs" value="Cancel" style="padding:5px;font-size:12px"></td></tr></form>';				
					}
				}
				$text .= '</table>';
				
				$text .= '<form method="post"><input type="text" class="form-control" name="task" placeholder="Introduce a new task to do"></form>';
				echo $text;
            }
        }
    }
}