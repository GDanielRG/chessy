<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        \Log::info($request);
        $name= base_convert(mt_rand (1, 1125899906842623), 10, 32) . ".html";
        $path= public_path() . '\\' . "images". '\\'. $name;
        $myfile = fopen($path, "w") or die("Unable to open file!");
        fwrite($myfile, $this->generateGrid());
        fclose($myfile);

        chmod($path, 0777);


        return response()->json([
            'path' => url("/images/" . $name ),
        ]);

    }

    public function generateGrid(){
        $fenstr = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR";
        $fentable = explode("/", $fenstr);
        $htmlcontent =  "<style>
        td {text-align: center;
        height: 50px;
        width: 50px;
        font-size: 40px;
        }
        td.b{
        background-color:#987654;
        }
        td.w{
        background-color:#CBA987;
        }</style>";

        $fentable = explode("/", $fenstr);
        $squarecont = 0;
        $htmlcontent .= '<table>';
        for ($i = 0; $i < 8; $i++) {
        	$htmlcontent .= "<tr>";
        	$rest = 8;
        	for ($j = 0; $j < $rest; $j++) {
        		if(is_numeric($fentable[$i][$j])){
        			$rest -= $fentable[$i][$j];
        			for($k = 0; $k < $fentable[$i][$j]; $k++){
        				if($i%2==0){
        					if(($j+$k)%2==0){
        						$c = "w";
        					}else{
        						$c = "b";
        					}
        				}else{
        					if(($j+$k)%2==0){
        						$c = "b";
        					}else{
        						$c = "w";
        					}
        				}
        				$htmlcontent .= '<td class="'. $c . '"></td>';
        			}
        		} else{
        			if($i%2==0){
        				if($j%2==0){
        					$c = "w";
        				}else{
        					$c = "b";
        				}
        			}else{
        				if($j%2==0){
        					$c = "b";
        				}else{
        					$c = "w";
        				}
        			}
        			$htmlcontent .= '<td class="'. $c . '">' . $this->pieceSwitch($fentable[$i][$j]) . "</td>";
        		}
        	}
        	$htmlcontent .= "</tr>";
        }
        $htmlcontent .= "</table>";

        return $htmlcontent;
    }

    function pieceSwitch($piece){
    	switch($piece){
    		case 'K':
    			return "<span style=\"color:white;\">&#9818;</span>";
    			break;
    		case 'Q':
    			return "<span style=\"color:white;\">&#9819;</span>";
    			break;
    		case 'R':
    			return "<span style=\"color:white;\">&#9820;</span>";
    			break;
    		case 'B':
    			return "<span style=\"color:white;\">&#9821;</span>";
    			break;
    		case 'N':
    			return "<span style=\"color:white;\">&#9822;</span>";
    			break;
    		case 'P':
    			return "<span style=\"color:white;\">&#9823;</span>";
    			break;
    		case 'k':
    			return "&#9818;";
    			break;
    		case 'q':
    			return "&#9819;";
    			break;
    		case 'r':
    			return "&#9820;";
    			break;
    		case 'b':
    			return "&#9821;";
    			break;
    		case 'n':
    			return "&#9822;";
    			break;
    		case 'p':
    			return "&#9823;";
    			break;
    	}
    }

}
