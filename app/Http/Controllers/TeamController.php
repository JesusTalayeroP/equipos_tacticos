<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Soldier;
use App\Models\Mission;
use App\Models\SoldierMission;

class TeamController extends Controller
{
	 /** POST 
	 * Crear equipos con /teams/create
	 * 
	 * Se introduce como parámetro (petición) el nombre del equipo.
	 * 
	 * @return response OK si se ha añadido el equipo   
	 */
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
			// Crear el equipo
			$team = new Team();

			// Rellenar los campos del equipo nuevo
			$team->name = $data->name;

			try{
				$team->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}			
		} else $response = "Datos incorrectos";
		// Enviar la respuesta
		return response($response);
	}

	 /** POST
	 * Actualizar los equipos con teams/update/{id}
	 *
	 * Se reciben los parametros que se quieren actualizar del equipo. En este caso
	 * solo se puede actualizar el nombre del equipo.
	 * 
	 * @return response OK si se ha actualizado el equipo
	 * @param $id id del equipo que hay que actualizar
	 */
	public function updateTeam(Request $request, $id){

		$response = "";

		//Buscar el equipo por su id
		$team = Team::find($id);
		// Si encuentra el equipo
		if($team){

			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el equipo
			if($data){
			
				// Actualizar el nombre del equipo
				$team->name = (isset($data->name) ? $data->name : $team->name);

				try{
					// Guardar el equipo actualizado en la base de datos
					$team->save();
					$response = "OK";
				}catch(\Exception $e){
					$response = $e->getMessage();
				}
			}
		}else{
			$response = "No team";
		}
		// Enviar respuesta
		return response($response);
	}

	 /** POST
	 * Borrar los equipos con teams/delete/{id}
	 *
	 * Se recibe la id del equipo que se quiere eliminar y lo elimina de 
	 * la base de datos.
	 * 
	 * @return response OK si se ha borrado el equipo
	 * @param $id id del equipo que hay que eliminar
	 */
	public function deleteTeam(Request $request, $id){

		$response = "";
		
		//Buscar el equipo por su id
		$team = Team::find($id);
		// Si existe el equipo
		if($team){

			try{
				// Eliminar el equipo
				$team->delete();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}						
		}else{
			$response = "No team";
		}
		// Enviar respuesta
		return response($response);
	}

	 /** POST
	 * Añadir un lider al equipo con teams/add/leader
	 *
	 * Se recibe en la petición el id del soldado que será lider y el id del
	 * equipo al que va a liderar. {"soldier":"$idsoldado", "team":"idteam"}
	 * El soldado se añade automáticamente al equipo.
	 *
	 * @return response OK si se ha añadido el lider al equipo
	 */
	public function addLeader(Request $request){

		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Buscar el equipo por su id
		$team = Team::find($data->team);
		// Buscar el soldado que lidera el equipo por su id
		$soldier = Soldier::find($data->soldier);

		//Si hay un json válido, añadir el soldado como lider del equipo
		if($data && $team && $soldier){

			// Guardar el lider del equipo
			$team->leader_id = (isset($data->soldier) ? $data->soldier : $team->soldier);
			// Añadir al soldado al equipo
			$soldier->team_id = (isset($data->team) ? $data->team : $soldier->team);
			
			try{
				// Guardar el equipo
				$team->save();
				// Guardar el soldado
				$soldier->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}
		} else {
			$response = "Los datos introducidos son incorrectos";
		}
		// Enviar respuesta
		return response($response);
	}

	 /** POST
	 * Añadir un soldado al equipo con teams/add/soldier
	 *
	 * Se recibe en la petición el id del soldado que va a añadir y el id del
	 * equipo al que pertenece. {"soldier":"$idsoldado", "team":"idteam"}
	 * El soldado se añade al equipo.
	 *
	 * @return response OK si se ha añadido el soldado al equipo correctamente
	 */
	public function addSoldier(Request $request){

		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);
		// Buscar al soldado que va a añadir al equipo
		$soldier = Soldier::find($data->soldier);

		//Si hay un json válido, y existe el equipo, añadir el soldado al equipo
		if($data && Team::find($data->team) && $soldier){
			
			// Añadir el soldado al equipo
			$soldier->team_id = $data->team;

			try{
				// Guardar el soldado
				$soldier->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}
		}else {
			$response = "Datos incorrectos";
		}
		// Enviar respuesta
		return response($response);
	}

	 /** POST
	 * Asignar una misión al equipo con teams/add/mission. Actualiza el estado de la misión y guarda los soldados
	 *
	 * Se recibe en la petición el id del equipo al que se le va a asignar 
	 * la misión y y el id de la misión a la que se va a asignar. 
	 * {"mission":"$idmision", "team":"idteam"} // la misión se añade al equipo.
	 * Actualiza el estado de la misión a "In_progress"
	 * Guarda los soldados pertenecientes al equipo que realiza la misión para 
	 * poder tener el historial guardado.
	 * 
	 * @return response OK si se ha asignado la misión al equipo correctamente
	 */
	public function addMission(Request $request) {
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Buscar el equipo por su id
		$team = Team::find($data->team);
		// Buscar la misión por su id
		$mission = Mission::find($data->mission);

		//Si hay un json válido, validar los datos y añadir la misión
		if($data && $team && $mission && !$team->mission_id){

			// Asignar la misión al equipo		
			$team->mission_id = (isset($data->mission) ? $data->mission : $team->mission);
			// Actualizar el estado de la misión
			$mission->state = "in_progress";
			
			// Buscar a los soldados que pertenecen al equipo llamando a getSoldiers()
			$soldiers = TeamController::getSoldiers($data->team);

			// Añadir los soldados a la tabla intermedia SoldierMission
			for ($i=0; $i < count($soldiers); $i++) { 
			 	$soldierMission = new SoldierMission();
				$soldierMission->soldier_id = $soldiers[$i]['id'];
				$soldierMission->mission_id = $data->mission;
				// Guardar el historial de los soldados
				$soldierMission->save();
			 } 

			try{
				// Asigna misión al equipo
				$team->save();
				// Actualiza el estado de la misión
				$mission->save();
				$response = "OK";
			}catch(\Exception $e){
				$response = $e->getMessage();
			}
		} else {
			$response = "Los datos introducidos son incorrectos";
		}
		// Enviar respuesta
		return response($response);
	}

	 /** GET
	 * Recibir todos los soldados que pertenecen a un equipo
	 * 
	 * @return lista de soldados que pertenecen al equipo
	 * @param $team_id id del equipo al que pertenecen los soldados
	 */
	public function getSoldiers ($team_id) {
		// Busca la lista de todos los soldados
		$soldiers = Soldier::all();		

		// Recorre la lista de soldados
		for ($i=0; $i < count($soldiers); $i++) { 
			$soldier = $soldiers[$i];
			// Si el soldado pertenece al equipo
			if($soldier->team_id == $team_id) {
				// Guarda el id del soldado
				$response [] = [
					'id' => $soldier->id
				];
			}
		}			
		// Envia el id de los soldados que pertenecen al equipo
		return $response;
	}

}
