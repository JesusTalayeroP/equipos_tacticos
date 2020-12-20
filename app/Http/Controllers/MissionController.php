<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mission;
use App\Models\Team;
use App\Models\Soldier;
use App\Models\SoldierMission;

class MissionController extends Controller
{
	 /** POST
	 * Crear misiones con missions/create
	 *
	 * Se introduce como parámetro (petición) la descripción, prioridad y fecha de registro
	 * de la misión, también puede introducirse el estado, pero no es necesario ya
	 * que por defecto será pendiente.
	 *
	 * @return response OK si se ha añadido la misión 
	 */
    public function createMission(Request $request)
	{
		/*//Para crear el primer Json de referencia
    	$json = [
    		"description" => 'Matar a Hitler en Nueva Zelanda',
    		'priority' => 'urgent',
    		'register_date' => '2020-12-18',
    		'state' => '',
    	];
		
    	echo json_encode($json); */
		
		$response = "";
		//Leer el contenido de la petición
		$data = $request->getContent();

		//Decodificar el json
		$data = json_decode($data);

		//Si hay un json válido, crear la misión
		if($data){
			$mission = new Mission();
			// Rellenar los campos
			$mission->description = $data->description;
			$mission->priority = $data->priority;
			$mission->register_date = $data->register_date;
			
			// Comprobar que el estado ha sido introducido
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
		// Enviar la respuesta
		return response($response);
	}

	 /** POST
	 * Actualizar misiones con missions/update/{id}
	 *
	 * Se reciben los parametros que se quieren actualizar de la misión. Se pueden
	 * actualizar la descripción, prioridad y estado. La fecha de registro no es editable.
	 *
	 * @return response OK si se ha actualizado la misión 
	 * @param $id id de la misión que hay que actualizar
	 */
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
				// Actualizar los campos con la petición recibida
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
		// Enviar respuesta
		return response($response);
	}


	 /** GET
	 * Recibir las misiones ordenadas por prioridad con missions/viewAll
	 * 
	 * Devuelve todas las misiones almacenadas en la base de datos, ordenadas 
	 * por prioridad, de más importante a menos (critic, urgent, normal). 
	 * Muestra el id, fecha de registro, prioridad y estado de la misión.
	 *
	 * @return todas las misiones ordenadas por prioridad 
	 */
	public function viewAll(){

		$response = "";
		// 1 hora para conseguir que se ordene pero conseguido ^.^
		// Guardar todas las misiones ordenadas por prioridad
		$mission = Mission::orderBy('priority', 'DESC')->get();
		$response = [];

		//Si se han recibido misiones
		if($mission){
			// Recorrer todas las misiones recibidas
			foreach ($mission as $mission) {	
				// Guardar los datos de las misiones que queremos mostrar al usuario			
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
		// Enviar las misiones ordenadas por prioridad
		return response()->json($response);
	}


	/** GET
	 * Recibir los detalles de la misión pedida con /missions/details/{id}
	 * 
	 * Devuelve todos los detalles de la misión requerida. Devuelve la descripción,
	 * prioridad, fecha de registro y estado de la misión; id y nombre del equipo que 
	 * la realiza (en caso de tenerlo); id, placa, rango y apellido del lider del
	 * equipo (en caso de tenerlo), y todos los soldados que participan en la misión
	 *
	 * @return todos los detalles de la misión 
	 * @param $id el id de la misión  
	 */
	public function detailsMission($id) {
		$response = "";
		// Buscar la misión por id
		$mission = Mission::find($id);
		// Si la encuentra
		if($mission) {
			// Guarda los detalles de la misión
			$response = [
				"description" => $mission->description,
				"priority" => $mission->priority,		
				"register_date" => $mission->register_date,	
				"state" => $mission->state,		
			];
			// Busca si algún equipo está realizando la misión
			$team = $mission->team;
			// Si lo encuentra
			if($team) {
				// Guarda los datos del equipo
				$response["team_id"] = $team->id;
				$response["team_name"] = $team->name;
				// Si tiene un lider
				if($mission->team->leader_id) {	
					// Busca el soldado que es lider del equipo				
					$leader = Soldier::find($mission->team->leader_id);
					// Guarda sus datos
					$response["leader_id"] = $leader->id;
					$response["leader_badge"] = $leader->badge_number;
					$response["leader_rank"] = $leader->rank;
					$response["leader_surname"] = $leader->surname;
				}
			}
			// Busca todos los soldados que han realizado alguna misión
			$soldiers = SoldierMission::all();
			// Recorre la lista de soldados
			foreach ($soldiers as $soldier) {
				// Si el soldado ha realizado la misión que estamos guardando
				if($soldier->mission_id == $id) {
					// Busca el soldado
					$soldier = Soldier::find($soldier->soldier_id);
					// Guarda sus datos
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
		// Devuelve todos los detalles de la misión
		return response()->json($response);
	}
}
