<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Team;

use App\Models\Soldier;

use App\Models\Mission;

use App\Models\SoldierMission;

class TeamController extends Controller
{
    public function createTeam(Request $request)
	{
/*
		//Para crear el primer Json de referencia
    	$json = [
    		"name" => 'Alpha',
    	];

    	echo json_encode($json);
*/		
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Si hay un json válido, crear el equipo
		if($data){
			$team = new Team();

			//Validar los datos antes de guardar el equipo

			$team->name = $data->name;

			try{
				$team->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}			
		} else $response = "Datos incorrectos";
		
		return response($response);
	}


	public function updateTeam(Request $request, $id){

		$response = "";

		//Buscar el equipo por su id

		$team = Team::find($id);

		if($team){

			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el equipo
			if($data){
			
				//TODO: Validar los datos antes de guardar el equipo
				$team->name = (isset($data->name) ? $data->name : $team->name);

				try{
					$team->save();
					$response = "OK";
				}catch(\Exception $e){
					$response = $e->getMessage();
				}
			}

		}else{
			$response = "No team";
		}
	
		return response($response);
	}


	public function deleteTeam(Request $request, $id){

		$response = "";
		
		//Buscar el equipo por su id

		$team = Team::find($id);

		if($team){

			try{
				$team->delete();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}
						
		}else{
			$response = "No team";
		}

		return response($response);
	}


	public function addLeader(Request $request){

		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Buscar el equipo por su id
		$team = Team::find($data->team);

		$soldier = Soldier::find($data->soldier);

		//Si hay un json válido, crear el equipo
		if($data && $team && $soldier){

			//TODO: Validar los datos antes de guardar el equipo
			$team->leader_id = (isset($data->soldier) ? $data->soldier : $team->soldier);
			$soldier->team_id = (isset($data->team) ? $data->team : $soldier->team);
			
			try{
				$team->save();
				$soldier->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}

		} else {
			$response = "Los datos introducidos son incorrectos";
		}
		return response($response);
	}


	public function addSoldier(Request $request){

		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		$soldier = Soldier::find($data->soldier);

		//Si hay un json válido, añadir el soldado al equipo
		if($data && Team::find($data->team) && $soldier){
			

			//TODO: Validar los datos antes de añadir soldado
			$soldier->team_id = $data->team;

			try{
				$soldier->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}
		}else {
			$response = "Datos incorrectos";
		}
		return response($response);
	}


	public function addMission(Request $request) {
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Buscar el equipo por su id
		$team = Team::find($data->team);
		
		$mission = Mission::find($data->mission);

		//Si hay un json válido, añadir la misión
		if($data && $team && $mission && !$team->mission_id){

			//TODO: Validar los datos antes de guardar el equipo			
			$team->mission_id = (isset($data->mission) ? $data->mission : $team->mission);
			$mission->state = "in_progress";
			
			// Llamar a los soldados que están en el equipo
			$soldiers = TeamController::getSoldiers($data->team);

			// Añadir los soldados a la tabla intermedia SoldierMission
			for ($i=0; $i < count($soldiers); $i++) { 
			 	$soldierMission = new SoldierMission();
				$soldierMission->soldier_id = $soldiers[$i]['id'];
				$soldierMission->mission_id = $data->mission;

				$soldierMission->save();
			 } 

			try{
				$team->save();
				$mission->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}

		} else {
			$response = "Los datos introducidos son incorrectos";
		}
		return response($response);
	}


	public function getSoldiers ($team_id) {
		$soldiers = Soldier::all();		

		for ($i=0; $i < count($soldiers); $i++) { 
			$soldier = $soldiers[$i];
			
			if($soldier->team_id == $team_id) {

				$response [] = [
					'id' => $soldier->id
				];
			}
		}			
		return $response;
	}

}
