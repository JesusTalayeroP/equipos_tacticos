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
		
		$exit = false;

		$teams = Team::all();
		for ($i=0; $i < count($teams); $i++) { 
			$thisTeam = $teams[$i];
			if($thisTeam->mission_id == $data->mission) {
				$exit = true;
				$response = "Esa misión ya está asignada a un equipo";
			}
		}
		//Si hay un json válido, validar los datos y añadir la misión
		if($data && $team && $mission && !$team->mission_id && !$exit){

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
		$response = "";
		$response = [];

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

	/** GET
	 * Muestra el jefe de un equipo así como todos los soldados pertenecientes
	 *
	 * Busca el equipo por $id introducido y devuelve los datos del jefe del equipo
	 * así como los datos de todos los soldados que pertecen al equipo actualmente
	 *
	 * @return Jefe y soldados que pertenecen al equipo
	 */
	public function teamMembers ($id) {
		$response = "";
		// Buscar el equipo por id
		$team = Team::find($id);
		
		$response = [];
		// Si el equipo encontrado tiene lider
		if($team->leader_id) {
			// Buscar el lider del equipo
			$leader = Soldier::find($team->leader_id);
			// Guarda sus datos
			$response [] = [
			"leader_name" => $leader->name,
			"leader_surname" => $leader->surname,
			"leader_birthdate" => $leader->birthdate,
			"leader_incorporation_date" => $leader->incorporation_date,
			"leader_badge_number" => $leader->badge_number,
			"leader_rank" => $leader->rank,
			"leader_state" => $leader->state
			];
		}
		// Busca todos los soldados
		$soldiers = Soldier::all();
		// Recorre los soldados
		foreach ($soldiers as $soldier) {
			// Si el soldado pertenece al equipo
			if($soldier->team_id == $id) {
				// Guarda sus datos
				$response [] = [
					"soldier_name" => $soldier->name,
					"soldier_surname" => $soldier->surname,
					"soldier_birthdate" => $soldier->birthdate,
					"soldier_incorporation_date" => $soldier->incorporation_date,
					"soldier_badge_number" => $soldier->badge_number,
					"soldier_rank" => $soldier->rank,
					"soldier_state" => $soldier->state
				];
			}
		}
		// Si el equipo no existe
		if(!$team) {
			$response = "Equipo no encontrado";
		} else if (!$team->leader_id) {
			$response = "El equipo no tiene lider";
		}
		// Devuelve los detalles de los soldados
		return response()->json($response);
	}

	/** POST
	 * Elimina a un soldado del equipo
	 *
	 * Se recibe en la petición el id del equipo al que se le va eliminar 
	 * el soldado, y el id del soldado que se va a eliminar.
	 * {"soldier":"$idsoldado", "team":"idteam"} // 
	 * El soldado se elimina del equipo
	 *
	 * @return response OK si se ha eliminado el soldado del equipo correctamente
	 */
	public function sackSoldier (Request $request) {
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);
		// Buscar al soldado que se va a eliminar del equipo
		$soldier = Soldier::find($data->soldier);

		//Si hay un json válido, y el soldado pertene al equipo del que se le va a eliminar
		if($data && Team::find($data->team) && $soldier->team_id == $data->team){

			// Eliminar el soldado del equipo
			$soldier->team_id = null;

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
	 * Cambia el lider de un equipo
	 *
	 * Se recibe en la petición el id del equipo al que se le va a cambiar 
	 * el lider, y el id del antiguo lider como soldier y el id del nuevo lider
	 * como leader.
	 * {"soldier":"$idsoldado", "leader":"$idlider", "team":"idteam"} // 
	 * El lider antiguo se queda sin equipo y el nuevo lider se añade al equipo
	 *
	 * @return response OK si se ha eliminado al antiguo lider del equipo, y se ha añadido el nuevo lider
	 */
	public function changeLeader (Request $request) {
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		// Buscar al antiguo lider que se va a eliminar del equipo
		$soldier = Soldier::find($data->soldier);

		// Buscar al soldado que se va a ser el nuevo lider del equipo
		$leader = Soldier::find($data->leader);

		//Buscar al equipo al que se le va a cambiar el lider
		$team = Team::find($data->team);

		//Si hay un json válido, y los datos son correctos
		if($data && $team->leader_id && $soldier->id == $team->leader_id && $leader){

			// Eliminar el antiguo lider del equipo
			$soldier->team_id = null;
			// Guardar el lider del equipo
			$team->leader_id = (isset($data->leader) ? $data->leader : $team->leader);
			// Añadir al soldado al equipo
			$leader->team_id = (isset($data->team) ? $data->team : $leader->team);

			try{
				// Actualizar el antiguo lider
				$soldier->save();
				// Guardar el equipo con nuevo lider
				$team->save();
				// Añadir nuevo lider al equipo
				$leader->save();
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

}
