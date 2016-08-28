<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class HomeController extends Controller
{


    public function action(Request $request){
        if(substr($request->input('text'), 0, strlen('#register')) === "#register")
        {

            return $this->register($request);
        }

        $path = $this->generateImagePath("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR");

        return response()->json([
            'image' => $path,
        ]);
    }

    public function register($request){
        $text = $request->input('text');
        $key= explode(" ", $text)[1];
        $user=User::where('key', $key)->first();

        if($user)
        {
            if($request->has('facebook'))
                $user->facebook_key= $request->input('facebook');

            if($request->has('slack'))
                $user->slack_key= $request->input('slack');

            if($request->has('whatsapp'))
                $user->whatsapp_key= $request->input('whatsapp');

            $user->save();

            return response()->json([
                'text' => 'User added to' . $key
            ]);
        }
        else{

            $user = new User;
            $user->key = $key;

            if($request->has('facebook'))
                $user->facebook_key=$request->input('facebook');

            if($request->has('slack'))
                $user->slack_key=$request->input('slack');

            if($request->has('whatsapp'))
                $user->whatsapp_key=$request->input('whatsapp');

            $user->save();

            return response()->json([
                'text' => 'User registered correctly, use this key to register on other platforms: ' . $key,
            ]);
        }
    }

    public function generateImagePath($fenstr)
    {
        $name= base_convert(mt_rand (1, 1125899906842623), 10, 32) . ".html";
        $path= public_path() . '//'. "images/" . $name;
        $myfile = fopen($path, "w") or die("Unable to open file!");
        fwrite($myfile, $this->generateGrid($fenstr));
        fclose($myfile);

        return url("/images" . "/" . $name );
    }

    public function generateGrid($fenstr){
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
