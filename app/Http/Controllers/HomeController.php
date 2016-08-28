<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Team;
use App\Game;
use App\LobbyUser;
use App\TeamUser;

class HomeController extends Controller
{

    public function createImage(Request $request)
    {
        $name = $request->input('name');
        $image = $request->input('base64');
        file_put_contents("../../../images" . "/" . $name , base64_decode($image));
        return response()->json([
            'created' => true,
        ]);

    }
    public function action(Request $request){


        if(substr($request->input('text'), 0, strlen('#register')) === "#register")
        {
            return $this->register($request);
        }

        if(substr($request->input('text'), 0, strlen('#create')) === "#create")
        {
            return $this->quickplay($request);
        }

        if(substr($request->input('text'), 0, strlen('#join')) === "#join")
        {
            return $this->join($request);
        }

        if(substr($request->input('text'), 0, strlen('#side')) === "#side")
        {
            return $this->side($request);
        }

        if(substr($request->input('text'), 0, strlen('#start')) === "#start")
        {
            return $this->start($request);
        }

        $path = $this->generateImagePath("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR");

        return response()->json([
            'image' => $path,
        ]);
    }

    public function quickplay(Request $request){
        $user=$this->getUser($request);

        if(!$user)
            return response()->json([
                'text' => 'User needs to register again',
            ]);

        $team1 = Team::create(["key"=> base_convert(mt_rand (1, 1125899906842623), 10, 32), "color" => 'b']);
        $team2 = Team::create(["key"=> base_convert(mt_rand (1, 1125899906842623), 10, 32), "color" => 'w']);
        $game = Game::create(["creator"=>$user->id, "team_id1" => $team1->id, "team_id2" => $team2->id, "key"=> base_convert(mt_rand (1, 1125899906842623), 10, 32)]);
        $user->active_game = $game->id;

        $lobbyUser = LobbyUser::create(["user_id"=> $user->id, "game_id" => $game->id]);
        return response()->json([
            'text' => 'Game created. You are now on the lobby of the game ' . $game->key . " Your friends can join this game with #join {key}, and you can start picking a side with #side white or #side black",
        ]);

    }

    public function join(Request $request){
        $user=$this->getUser($request);
        if(!$user)
            return response()->json([
                'text' => 'User needs to register again',
            ]);
        $text = $request->input('text');
        $key= explode(" ", $text)[1];
        $game=Game::where('key', $key)->first();

        if(!$game)
            return response()->json([
                'text' => 'Game does not exists',
            ]);

        $lobbyUser = LobbyUser::firstOrCreate(["user_id"=> $user->id, "game_id" => $game->id]);
        $user->active_game = $game->id;
        $user->save();

        return response()->json([
            'text' => 'You are now on the lobby of the game ' . $game->key . " Your friends can join this game with #join {key}, and you can start picking a side with #side white or #side black",
        ]);

    }

    public function side(Request $request){
        $user=$this->getUser($request);
        if(!$user)
            return response()->json([
                'text' => 'User needs to register again',
            ]);
        $text = $request->input('text');
        $key= explode(" ", $text)[1];
        $game=Game::where('id', $user->active_game)->first();

        if(!$game)
            return response()->json([
                'text' => 'Game does not exists',
            ]);

        $black=Team::where('id' , $game->team_id1)->first();
        $white=Team::where('id' , $game->team_id2)->first();

        if($key != "black" && $key != "white")
        return response()->json([
            'text' => 'Bad side',
        ]);
        if($key == "black")
            $teamUser= TeamUser::firstOrCreate(["team_id"=>$black->id,
            "user_id" => $user->id]);
        if($key == "white")
            $teamUser= TeamUser::firstOrCreate(["team_id"=>$white->id,
            "user_id" => $user->id]);

        return response()->json([
            'text' => 'You are now on the  ' . $key . " team.",
        ]);

    }

    public function start(Request $request){
        $user=$this->getUser($request);
        if(!$user)
            return response()->json([
                'text' => 'User needs to register again',
            ]);
        $game=Game::where('id', $user->active_game)->first();

        if(!$game)
            return response()->json([
                'text' => 'Game does not exists',
            ]);

        if($game->started || $game->ended || $game->creator != $user->id)
        return response()->json([
            'text' => 'Game cannot start',
        ]);

        $game->started=true;
        $game->save();

        $black=Team::where('id', $game->team_id1)->first();
        $white=Team::where('id', $game->team_id2)->first();

        $teamUsers=teamUser::where("team_id", $black->id)->orWhere("team_id", $white->id)->get();
        $users=User::whereIn("id", $teamUsers->pluck('id'))->get();
        $facebookIds=[];
        $slackIds=[];
        foreach ($users as $user) {
            if($user->facebook_key)
                $facebookIds[]=$user->facebook_key;
            if($user->slack_key)
                $slackIds[]=$user->slack_key;
        }

        return response()->json([
            'text' => 'Game ' . $game->key . ' has started. White moves.',
            'facebookIds' => $facebookIds,
            'slackIds' => $slackIds,

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

    public function getUser($request)
    {
        if($request->has('facebook'))
            $user =  User::where('facebook_key', $request->input('facebook'))->first();

        if($request->has('slack'))
            $user =  User::where('slack_key', $request->input('slack'))->first();

        if($request->has('whatsapp'))
            $user =  User::where('whatsapp_key', $request->input('whatsapp'))->first();

        return $user;

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
