<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soldier;
use App\Models\SoldierMission;
use App\Models\Mission;

class SoldierController extends Controller
{
	 /** POST
	 * Crear soldados con soldiers/create
	 *
	 * Se introduce como parámetro (petición) el nombre, apellido, fecha de nacimiento,
	 * fecha de incorporación, número de placa, que tiene que ser único, rango, y estado.
	 * 
	 * @return response OK si se ha añadido el soldado 
	 */
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

			// Rellenar los campos del nuevo soldado
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
		// Enviar la respuesta
		return response($response);
	}

	 /** POST
	 * Actualizar soldados con soldiers/update/{id}
	 *
	 * Se reciben los parametros que se quieren actualizar del soldado. Se pueden
	 * actualizar el nombre, apellido, fecha de nacimiento, fecha de incorporación
	 * número de placa y rango. 
	 *
	 * @return response OK si se ha actualizado el soldado
	 * @param $id id del soldado que hay que actualizar
	 */
	public function updateSoldier(Request $request, $id){

		$response = "";

		//Buscar el soldado por su id
		$soldier = Soldier::find($id);
		// Si encuentra el soldado
		if($soldier){
			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el soldado
			if($data){
			
				// Actualizar los campos con la petición recibida
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
		// Enviar respuesta
		return response($response);
	}

	/** POST
	 * Actualizar el estado de los soldados con soldiers/state/{id}
	 *
	 * Recibe el nuevo estado del soldado que hay que actualizar en la peticion. Puede
	 * ser activo, retirado o baja.
	 *
	 * @return response OK si se ha actualizado el estado del soldado
	 * @param $id id del soldado que hay que actualizar
	 */
	public function stateSoldier(Request $request, $id){

		$response = "";

		//Buscar el soldado por su id
		$soldier = Soldier::find($id);
		// Si encuentra el soldado
		if($soldier){
			//Leer el contenido de la petición
			$data = $request->getContent();

			//Decodificar el json
			$data = json_decode($data);

			//Si hay un json válido, buscar el soldado
			if($data){
			
				// Actualizar el estado del soldado
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
		// Enviar respuesta 
		return response($response);
	}

	 /** GET
	 * Recibir todos los soldados con soldiers/viewAll
	 * 
	 * Devuelve todos los soldados almacenados en la base de datos. 
	 * Muestra el nombre, apellido, rango, numero de placa, id del equipo 
	 * al que pertenece y nombre del mismo.
	 *
	 * @return todos los soldados
	 */
	public function viewAll(){

		$response = "";
		// Guardar todos los soldados
		$soldiers = Soldier::all();
		$response = [];

		// Si hay soldados
		if($soldiers){
			// Recorrer todos los soldados recibidos
			foreach ($soldiers as $soldier) {
				// Si pertenece a un equipo
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
					// Si no pertenece a un equipo
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
		// Enviar los soldados 
		return response()->json($response);
	}

	/** GET
	 * Recibir los detalles del soldado solicitado con /soldiers/details/{id}
	 * 
	 * Devuelve todos los detalles del soldado. Devuelve el nombre, apellido,
	 * fecha de nacimiento, fecha de incorporación, numero de placa, rango, estado,
	 * id y nombre del equipo al que pertenece(en caso de pertenecer a un equipo), 
	 * y datos del lider del equipo en caso de tenerlo (id, rango y apellido del lider).
	 *
	 * @return todos los detalles del soldado 
	 * @param $id el id del soldado  
	 */
	public function detailsSoldier($id){
		$response = "";
		// Buscar el soldado por id
		$soldier = Soldier::find($id);
		// Si el soldado encontrado pertenece a un equipo y tiene lider
		if($soldier->team && $soldier->team->leader_id) {
			// Buscar el lider del equipo
			$leader = Soldier::find($soldier->team->leader_id);
		}
		// Guardar los datos "por defecto" del soldado 
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
		// En caso de que tenga equipo, guardar sus datos
		if ($soldier->team_id){
			$response ['team_id'] = $soldier->team->id;
			$response ['team_name'] = $soldier->team->name;
		} 
		// En caso de que el equipo tenga lider, guardar sus datos
		if ($soldier->team && $soldier->team->leader_id){		
			$response ['leader_id'] = $leader->id;
			$response ['leader_rank'] = $leader->rank;
			$response ['leader_surname'] = $leader->surname;	
		}
		// Devuelve los detalles del soldado
		return response()->json($response);
	}

	 /** GET
	 * Recibir los detalles de las misiones en las que ha participado un soldado con /soldiers/history/{id}
	 * 
	 * Devuelve todos los detalles de las misiones en las que ha participado un soldado
	 * Devuelve el código, fecha de registro y estado de todas las misiones en las que haya 
	 * participado el soldado en cuestión. 
	 * 
	 * @return todos los detalles de las misiones en las que ha participado el soldado 
	 * @param $id el id del soldado 
	 * 
	 */
	public function missionHistory($id){
		$response = "";
		// Buscar todos los soldados en tabla intermedia SoldierMission
		$soldiers = SoldierMission::all();
		$response = [];
		// Recorrer cada soldado almacenado en la tabla
		foreach ($soldiers as $soldier) {
			// Si el id del soldado introducido coincide con alguno de la lista
			if($soldier->soldier_id == $id) {
				// Buscar la mission en la que ha participado el soldado
				$mission = Mission::find($soldier->mission_id);
				// Guardar los datos de la misión
				$response [] = [
					'mission_id' => $mission->id,
					'mission_date' => $mission->register_date,
					'mission_state' => $mission->state	
				];
			} 
		}
		// Si no encuentra ningún soldado que haya participado en misiones
		if($response == "") {
			$response = "información no disponible";
		}
		// Devuelve los detalles de la/s misión/es en la/s que haya participado el soldado
		return response()->json($response);
	}
}
