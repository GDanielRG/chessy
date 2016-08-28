<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Team;
use App\Game;
use App\LobbyUser;
use App\TeamUser;
use App\Movement;
use Ryanhs\Chess\Chess;


class HomeController extends Controller
{

    public function createImage(Request $request)
    {
        $name = $request->input('name');
        $image = $request->input('base64');
        // file_put_contents("../../../images" . "/" . $name , base64_decode($image));

        // $name= base_convert(mt_rand (1, 1125899906842623), 10, 32) . ".html";
        $path= public_path() . '//'. "images/" . $name;
        $myfile = fopen($path, "w") or die("Unable to open file!");
        fwrite($myfile, base64_decode($image));
        fclose($myfile);
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

        if(substr($request->input('text'), 0, strlen('#move')) === "#move")
        {
            return $this->move($request);
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


        $chess = new Chess();
        $fen= $chess->fen();

        $game = Game::create(["fen"=>$fen,"creator"=>$user->id, "team_id1" => $team1->id, "team_id2" => $team2->id, "turn" => $team2->id, "key"=> base_convert(mt_rand (1, 1125899906842623), 10, 32)]);
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

        $response = Curl::to('peaceful-badlands-59453.herokuapp.com/sendMesagges')
        ->withData( array(  'text' =>  $user->key .' has joined your game loby.',
                            'facebookIds' => $facebookIds,
                            'slackIds' => $slackIds ) )
        ->asJson( true )
        ->post();


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

        if($key == "black")
            $teamUsers=teamUser::where("team_id", $black->id)->get();
        if($key == "white")
            $teamUsers=teamUser::where("team_id", $white->id)->get();


        $users=User::whereIn("id", $teamUsers->pluck('id'))->get();
        $facebookIds=[];
        $slackIds=[];
        foreach ($users as $user) {
            if($user->facebook_key)
                $facebookIds[]=$user->facebook_key;
            if($user->slack_key)
                $slackIds[]=$user->slack_key;
        }

        //envia a todos los companeros del equipo KEY que tal persona se unio al equipo
        $response = Curl::to('peaceful-badlands-59453.herokuapp.com/sendMesagges')
            ->withData( array(  'text' =>  $user->key .' has joined ' . $key . '\'s team.',
                                'facebookIds' => $facebookIds,
                                'slackIds' => $slackIds ) )
            ->asJson( true )
            ->post();


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

    public function move(Request $request){
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

        $teamToPlay= Team::find($game->turn);
        if($this->playerColor($game, $user) != $teamToPlay->color)
        return response()->json([
            'text' => 'Cannot move',
        ]);

        $text = $request->input('text');
        $key= explode(" ", $text)[1];

        $chess = new Chess;
        $chess->load($game->fen);
        if(!in_array($key, $chess->moves()))
        return response()->json([
            'text' => 'Invalid move',
        ]);

        $movement=Movement::create(["move"=>$key, "game_id" => $game->id, "user_id" => $user->id, "team_id"=>$teamToPlay->id]);
        $votes=Movement::where("game_id", $game->id)->where("team_id", $teamToPlay->id)->get();
        if($votes->count()>(TeamUser::where("team_id", $teamToPlay->id)->get()->count() / 2) ||($votes->count()==1 && TeamUser::where("team_id", $teamToPlay->id)->get()->count()==1))

        {
            $movesVotes=[];
            foreach ($votes as $vote) {
                if(!array_key_exists($vote->move, $movesVotes)){
                    $movesVotes[ $vote->move ] = 1;
                }
                else{
                    $movesVotes[$vote->move] = $movesVotes[$vote->move] + 1;
                }
            }
            $higher=0;
            $higherMove="";
            foreach ($movesVotes as $key => $value) {
                if($value > $higher)
                {
                    $higherMove = $key;
                }
            }
            $chess->move($higherMove);
            $game->fen=$chess->fen();
            $game->turn=$this->switchTurn($game);
            $game->save();
            foreach ($votes as $vote) {
                $vote->delete();
            }
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

            $path = $this->generateImagePath($game->fen);

            if(Team::find($game->turn)->color == 'b')
                $color = 'black\'s';
            if(Team::find($game->turn)->color == 'w')
                $color = 'white\'s';

            return response()->json([
                'text' => 'It\'s ' . $color . ' turn.',
                'facebookIds' => $facebookIds,
                'slackIds' => $slackIds,
                'image' => $path,
            ]);
        }
        else{

            if ( $teamToPlay->color == "b") {
                $black=Team::where('id', $game->team_id1)->first();
                $teamUsers=teamUser::where("team_id", $black->id)->get();
            }
            else {
                $white=Team::where('id', $game->team_id2)->first();
                $teamUsers=teamUser::where("team_id", $white->id)->get();
            }

            $users=User::whereIn("id", $teamUsers->pluck('id'))->get();
            $facebookIds=[];
            $slackIds=[];
            foreach ($users as $user) {
                if($user->facebook_key)
                    $facebookIds[]=$user->facebook_key;
                if($user->slack_key)
                    $slackIds[]=$user->slack_key;
            }

            // Dile a tu equipo el movimiento que hiciste
            $response = Curl::to('peaceful-badlands-59453.herokuapp.com/sendMesagges')
            ->withData( array(  'text' =>  $user->key .' has voted ' . $key ,
                                'facebookIds' => $facebookIds,
                                'slackIds' => $slackIds ) )
            ->asJson( true )
            ->post();



            return response()->json([
                'text' => 'Vote added.',
            ]);
        }


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
        td.num {text-align: right;
        font-size: 15px;
        }
        td.letter{text-align: center;
        font-size: 15px;
        vertical-align: top;
        }
        td.b{
        background-color:#987654;
        }
        td.w{
        background-color:#CBA987;
        }</style>

        ";

        $fentable = explode("/", $fenstr);
        $squarecont = 0;
        $htmlcontent .= '<table style="width:100%; height:100%;">';
        for ($i = 0; $i < 8; $i++) {
            $htmlcontent .= '<tr><td class="num">' . (8 - $i) . "</td>";
        	$rest = 8;
        	for ($j = 0; $j < $rest; $j++) {
        		if(is_numeric($fentable[$i][$j])){
        			$rest -= ($fentable[$i][$j]-1);
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
        $htmlcontent .= "<tr><td></td>";
for ($i = 0; $i < 8; $i++) {
	$htmlcontent .= '<td class="letter">' . $this->getLetter($i) . "</td>";
}
        $htmlcontent .= "</tr></table>";

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

    function getLetter($n){
	switch($n){
		case 0: return "a";
		case 1: return "b";
		case 2: return "c";
		case 3: return "d";
		case 4: return "e";
		case 5: return "f";
		case 6: return "g";
		case 7: return "h";
	}
}



    public function playerColor($game, $user)
    {
        $black=Team::where('id', $game->team_id1)->first();
        $white=Team::where('id', $game->team_id2)->first();
        $blackTeamUser= TeamUser::where("user_id", $user->id)->where("team_id", $black->id)->first();
        $whiteTeamUser= TeamUser::where("user_id", $user->id)->where("team_id", $white->id)->first();

        if($blackTeamUser)
        return 'b';
        return 'w';
    }

    public function switchTurn($game)
    {
        $black=Team::where('id', $game->team_id1)->first();
        $white=Team::where('id', $game->team_id2)->first();

        if($game->turn == $black->id)
        return $white->id;
        return $black->id;
    }

}
