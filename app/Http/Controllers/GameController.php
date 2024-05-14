<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
     /**
     * .
     */

    private function generateNumber() {
        $str = '0123456789';
        $number = '';
        $arr = array_fill(0, 10, 0);
        for ($i = 1; $i <= 4; $i++) {
            $rand = rand(0, strlen($str)-1);
            $number .= $str[$rand];
            $arr[$str[$rand]] = $i;
            $str = str_replace($str[$rand], '', $str);
        }
        session(['number' => $number, 'arr' => $arr, 'attempts' => 0]);
    }
    /**
     * @OA\Get(
     *     path="/api/index",
     *     summary="All Games Details",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Not found"),
     *     
     * )
     */
    public function index()
    {
        $game = Game::all();
        if (!$game) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['game' => $game,'message' => 'Success'], 200);
    }

    /**
* @OA\Post(
     *     path="/api/store",
     *     summary="Register a new Game",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="User's name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="age",
     *         in="query",
     *         description="User's age",
     *         @OA\Schema(type="integer")
     *     ), 
     *     @OA\Response(response="200", description="Game registered successfully"),
     *     
     * )
     */
    public function store(Request $request)
    {
        $game = new Game();
        $game->name = $request->input('name');
        $game->age = $request->input('age');
        session(['name' => $game->name]);
        $this->generateNumber();
        $game->random_number = session('number');

        $date=  Carbon::now();
        $now = ($date->format("H:i:s"));

        $timeout = Carbon::parse($now)->addMinutes(10);
        $endTime = ($timeout->format("H:i:s"));

        $game->time_to_open = $now;
        session(['startTime' => $now]);

        $game->time_to_close = $endTime;

        session(['endTime' => $endTime]);

        $game->attempts = 0;

        $game->eval = '0';

        session(['attempts' => $game->attempts]);

        $game->is_won = false;

        session(['is_won' => $game->is_won]);

        session(['lastCombination' => '0000']);

        session(['eval' => '0']);

        //$request->session->put('random_number', $game->random_number);
        //$request->session()->save();
        
       

        $game->save();
        return response()->json([ 'message' => 'Game Created' ], 200);
    }

   

    /**
     * @OA\Get(
     *     path="/api/proposeCombination",
     *    
     *     summary="Evaluate the Propose Combination",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="400", description="General Request Error"),
     *     
     * )
     */

     public function proposeCombination($guess) {
        //$this->generateNumber();
        $number = session('number');
        //$number = '2658';
        $arr = session('arr');
        $lastCombination = session('lastCombination');
        $date=  Carbon::now();
        $now = ($date->format("H:i:s"));
        $nowparse = Carbon::parse($now);
        $timeout = Carbon::parse($now)->addMinutes(10);
        $endTime = ($timeout->format("H:i:s"));
        if (!$number) {
            return response()->json([ 'message' => 'Number Not Found' ], 404);
        }
        if (!is_numeric($guess) || strlen($guess) !== 4) {
            return response()->json([ 'message' => 'Incorrect number Format' ], 400);
        }
        $numAttemps = session()->increment('attempts');
        if ($number == $guess) {
            
            return response()->json(['is_won' => true, 'attempts' => session('attempts'), 'endTime' => $timeout], 200);
        }
        if ($lastCombination == $guess) {
            return response()->json([ 'message' => 'Combination Used Before' ], 400);
        }

        if (session('endTime') == $timeout) {
             return response()->json([ 'message' => 'Game Over, Timeout','target' => $number ], 400);
        }

        $timeLeft = $nowparse->diffInMinutes($timeout);

        $seconds = $timeLeft*60;

        $eval = $seconds / 2;
        $evalFinal = $eval + session()->increment('attempts');

        

        $bulls = 0;
        $cows = 0;
        $guessArr = array_fill(0, 10, 0);
        for ($i = 0; $i < 4; $i++) {
            $guessArr[$guess[$i]]++;
            if ($guessArr[$guess[$i]] > 1) {
                return  response()->json([ 'message' => 'Duplicated numbers in the combination' ], 400);
            }
            if ($arr[$guess[$i]] > 0) {
                if ($arr[$guess[$i]] == $i+1) {
                    $bulls++;
                }
                else {
                    $cows++;
                }
            }
        }
        $arrTries[$numAttemps] = $guess;
        session(['arrTries' => $arrTries]);
        return response()->json(['bulls' => $bulls, 'cows' => $cows,'attempts' => $numAttemps,'lastCombination' => $guess,'timeLeft' => $timeLeft,'evalFinal' => $evalFinal], 200);
    }

    /**
     * .
     */

    public function evaluateAnswers($num)
    {
        $arr = session('arrTries');
        $answer = $arr[$num];
        if ($answer == null) {
            return response()->json([ 'message' => 'Not found' ], 404);
        }

        return response()->json([ 'answer' => $answer ], 200);

    }

    /**
     * @OA\Get(
     *     path="/api/show",
     *     summary="Get the specified Game Details",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Game not found"),
     *     
     * )
     */
    public function show($id)
    {
        $game = Game::find($id);
        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }
        return response()->json(['game' => $game,'message' => 'Success'], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/update",
     *     summary="Update the specified resource in storage",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Game not found"),
     *     
     * )
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $game = Game::find($id);


        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }


        $game->update($validatedData);


        return response()->json(['game' => $game,'message' => 'Success'], 200);
    }

     /**
     * @OA\Delete(
     *     path="/api/destroy",
     *     summary="Remove the specified resource from storage",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Not found"),
     *     
     * )
     */

    public function destroy($id)
    {
        $game = Game::find($id);


        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }


        $game->delete();


        return response()->json(['message' => 'Game deleted'], 200);
        
    }
}
