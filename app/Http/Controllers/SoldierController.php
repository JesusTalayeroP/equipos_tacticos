<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Soldier;

use App\Models\SoldierMission;

use App\Models\Mission;


class SoldierController extends Controller
{
    public function createSoldier(Request $request)
	{

		//Para crear el primer Json de referencia
    	/*$json = [
    		"name" => 'Joselito',
    		'surname' => 'el loquito',
    		'birthdate' => '1992-08-03',
    		'incorporation_date' => '2014-05-05',
    		'badge_number' => '12345',
    		'rank' => 'captain',
    		'state' => 'active'
    	];

    	echo json_encode($json);
		*/
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Si hay un json válido, crear el soldado
		if($data){
			$soldier = new Soldier();

			//Validar los datos antes de guardar el soldado

			$soldier->name = $data->name;
			$soldier->surname = $data->surname;
			$soldier->birthdate = $data->birthdate;
			$soldier->incorporation_date = $data->incorporation_date;
			$soldier->badge_number = $data->badge_number;
			$soldier->rank = $data->rank;
			$soldier->state = $data->state;

			try{
				$soldier->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}			
		} else $response = "Datos incorrectos";
		
		return response($response);
	}


	public function updateSoldier(Request $request, $id){

		$response = "";

		//Buscar el soldado por su id

		$soldier = Soldier::find($id);

		if($soldier){

			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el soldado
			if($data){
			
				//TODO: Validar los datos antes de guardar el soldado
				$soldier->name = (isset($data->name) ? $data->name : $soldier->name);
				$soldier->surname = (isset($data->surname) ? $data->surname : $soldier->surname);
				$soldier->birthdate = (isset($data->birthdate) ? $data->birthdate : $soldier->birthdate);
				$soldier->incorporation_date = (isset($data->incorporation_date) ? $data->incorporation_date : $soldier->incorporation_date);
				$soldier->badge_number = (isset($data->badge_number) ? $data->badge_number : $soldier->badge_number);
				$soldier->rank = (isset($data->rank) ? $data->rank : $soldier->rank);

				try{
					$soldier->save();
					$response = "OK";
				}catch(\Exception $e){
					$response = $e->getMessage();
				}
			}

		}else{
			$response = "No soldier";
		}
	
		return response($response);
	}


	public function stateSoldier(Request $request, $id){

		$response = "";

		//Buscar el soldado por su id

		$soldier = Soldier::find($id);

		if($soldier){

			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el soldado
			if($data){
			
				//TODO: Validar los datos antes de guardar el soldado
				$soldier->state = (isset($data->state) ? $data->state : $soldier->state);

				try{
					$soldier->save();
					$response = "OK";
				}catch(\Exception $e){
					$response = $e->getMessage();
				}
			}

		}else{
			$response = "No soldier";
		}
	
		return response($response);
	}


	public function viewAll(){

		$response = "";
		$soldiers = Soldier::all();
		$response = [];

		if($soldiers){

			foreach ($soldiers as $soldier) {

				if ($soldier->team_id) {
					$response [] = [
					"name" => $soldier->name,
					"surname" => $soldier->surname,
					"rank" => $soldier->rank,
					"badge_number" => $soldier->badge_number,
					"team_id" => $soldier->team->id,
					"team_name" => $soldier->team->name
					];
				} else {
					$response [] = [
					"name" => $soldier->name,
					"surname" => $soldier->surname,
					"rank" => $soldier->rank,
					"badge_number" => $soldier->badge_number,
					"team_id" => "no team id",
					"team_name" => "no team"
					];
				}			
			}		

		}else{
			$response = "Soldado no encontrado";
		}

		return response()->json($response);
	}


	public function detailsSoldier($id){
		$response = "";
		$soldier = Soldier::find($id);

		if($soldier->team && $soldier->team->leader_id) {
		$leader = Soldier::find($soldier->team->leader_id);
		}

		$response = [
			"name" => $soldier->name,
			"surname" => $soldier->surname,
			"birthdate" => $soldier->birthdate,
			"incorporation_date" => $soldier->incorporation_date,
			"badge_number" => $soldier->badge_number,
			"rank" => $soldier->rank,
			"state" => $soldier->state,
			"team_id" => "no team id",
			"team_name" => "no team name",
			"leader_id" => "no leader id",
			"leader_rank" => "no captain rank",
			"leader_surname" => "no captain surname"
		];
		
		if ($soldier->team_id){
			$response ['team_id'] = $soldier->team->id;
			$response ['team_name'] = $soldier->team->name;
		} 
		if ($soldier->team && $soldier->team->leader_id){		
			$response ['leader_id'] = $leader->id;
			$response ['leader_rank'] = $leader->rank;
			$response ['leader_surname'] = $leader->surname;	
		}

		return response()->json($response);
	}


	public function missionHistory($id){
		$response = "";
		$soldiers = SoldierMission::all();
		$response = [];

		foreach ($soldiers as $soldier) {
			if($soldier->soldier_id == $id) {
				$mission = Mission::find($soldier->mission_id);
				$response [] = [
					'mission_id' => $mission->id,
					'mission_date' => $mission->register_date,
					'mission_state' => $mission->state	
				];
			} 
		}

		if($response == "") {
			$response = "información no disponible";
		}

		return response()->json($response);
	}
}
