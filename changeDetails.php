<? 
// namespace website\data
// error_reporting(-1); 

use website\data\AddressType;

$response = array(
	'success' => false,
	'message' => '',
);

if($request->method() == 'POST') {
// die('after');
	$errors = array();

	if(isset($request['first_name']) && !empty($request['first_name'])) {
		
		$users->post(null, $user, array(
			'first_name' => $request['first_name']
		));
	}

	if(isset($request['last_name']) && !empty($request['last_name'])){
		
		$users->post(null, $user, array(
			'last_name' => $request['last_name']
		));
	}

	if(isset($request['email']) && !empty($request['email'])){

		$users->post(null, $user, array(
			'email' => $request['email']
		));
	}


// ADDRESS DATA //

$address = $addresses->find();//->where('user_id', $user->id)->first();

$formData = $request->toArray();


	$post_code_regex = '/([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})/';



	if(!empty($formData['addr_postcode'])) {


	// preg_match($post_code_regex, $formData['addr_postcode'] , $output_array);


	if(preg_match($post_code_regex, $formData['addr_postcode'])){
		// var_dump($formData); 


		if($formData['addType'] == 4){
			$users->post(null, $user, array('billing_address_id' => $formData['addType']));
			// var_dump($address);
			// die();
			$addresses->post(null, null, array(
				'address_type_id' => $formData['addType'],
				'user_id' => $user->id,
				'first_name' => ucfirst($user->first_name),
				'last_name' => ucfirst($user->last_name),
				'address_line_1' => ucfirst($formData['addr_line_1']),
				'address_line_2' => ucfirst($formData['addr_line_2']),
				'town' => ucfirst($formData['addr_city_name']),
				'postcode' => strtoupper($formData['addr_postcode']),
			));
		} elseif ($formData['addType'] == 6) {
				$users->post(null, $user, array('shipping_address_id' => $formData['addType']));
				$addresses->post(null, null, array(
				'address_type_id' => $formData['addType'],
				'user_id' => $user->id,
				'first_name' => ucfirst($user->first_name),
				'last_name' => ucfirst($user->last_name),
				'address_line_1' => ucfirst($formData['addr_line_1']),
				'address_line_2' => ucfirst($formData['addr_line_2']),
				'town' => ucfirst($formData['addr_city_name']),
				'postcode' => strtoupper($formData['addr_postcode']),
			));
		}
	} else {
		die('error');
	}
  
	}




	if($formData['addType'] > 0) {

		// $errors = array();


		$addressType = filter_var($request['addType'] ,FILTER_SANITIZE_NUMBER_INT);

		if(!empty($addressType)){
			$addressTypeCollection = AddressType::find();
			foreach ($addressTypeCollection as $key => $entity){
				if($addressType == $key){
					$addresses->post(null, $address, array(
						'address_type_id' => $key,
					));

					break; 
				}
			}
		}


	}
	// echo '<pre>';
	// var_dump ($_POST);

	header('Location: /dashboard');

}	

