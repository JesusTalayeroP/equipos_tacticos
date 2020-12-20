<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Mission;

use App\Models\Team;

use App\Models\Soldier;

use App\Models\SoldierMission;


class MissionController extends Controller
{

    public function createMission(Request $request)
	{
		/*
		//Para crear el primer Json de referencia
    	$json = [
    		"description" => 'Matar a Hitler en Nueva Zelanda',
    		'priority' => 'urgent',
    		'register_date' => '2020-12-18',
    		'state' => '',
    	];
		
    	echo json_encode($json);
		*/
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Si hay un json válido, crear la misión
		if($data){
			$mission = new Mission();

			//Validar los datos antes de guardar la misión

			$mission->description = $data->description;
			$mission->priority = $data->priority;
			$mission->register_date = $data->register_date;
			
			if(isset($data->state)) {
				$mission->state = $data->state;
			}
			
			try{
				$mission->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}			
		} else $response = "Datos incorrectos";
		
		return response($response);
	}


	public function updateMission(Request $request, $id){

		$response = "";

		//Buscar la misión por su id

		$mission = Mission::find($id);

		if($mission){

			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar la misión
			if($data){
			
				//TODO: Validar los datos antes de guardar la misión
				$mission->description = (isset($data->description) ? $data->description : $mission->description);
				$mission->priority = (isset($data->priority) ? $data->priority : $mission->priority);
				$mission->state = (isset($data->state) ? $data->state : $mission->state);

				try{
					$mission->save();
					$response = "OK";
				}catch(\Exception $e){
					$response = $e->getMessage();
				}
			}

		}else{
			$response = "No mission";
		}
	
		return response($response);
	}


	public function viewAll(){

		$response = "";
		// 1 puta hora para conseguir que se ordene pero conseguido ^.^
		$mission = Mission::orderBy('priority', 'DESC')->get();
		$response = [];

		if($mission){

			foreach ($mission as $mission) {				
				$response [] = [
				"id" => $mission->id,	
				"register_date" => $mission->register_date,
				"priority" => $mission->priority,
				"state" => $mission->state,
				];
			}

		}else{
			$response = "Misión no encontrada";
		}

		return response()->json($response);
	}


	public function detailsMission($id) {
		$response = "";

		$mission = Mission::find($id);

		if($mission) {
			$response = [
				"description" => $mission->description,
				"priority" => $mission->priority,		
				"register_date" => $mission->register_date,	
				"state" => $mission->state,		
			];

			$team = $mission->team;
			
			if($team) {
				$response["team_id"] = $team->id;
				$response["team_name"] = $team->name;

				if($mission->team->leader_id) {					
					$leader = Soldier::find($mission->team->leader_id);
					$response["leader_id"] = $leader->id;
					$response["leader_badge"] = $leader->badge_number;
					$response["leader_rank"] = $leader->rank;
					$response["leader_surname"] = $leader->surname;
				}
			}
			
			$soldiers = SoldierMission::all();
			foreach ($soldiers as $soldier) {
				if($soldier->mission_id == $id) {
					$soldier = Soldier::find($soldier->soldier_id);
					$response [] = [
						'soldier_id' => $soldier->id,
						'soldier_badge_number' => $soldier->badge_number,
						'soldier_rank' => $soldier->rank,
						'soldier_surname' => $soldier->surname
					];
				}
			}

		} else {
			$response = "Misión no encontrada";
		}

		return response()->json($response);

	}
}
