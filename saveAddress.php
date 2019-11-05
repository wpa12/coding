<? 

use \website\core\SQL; 

// Get order
try {
	$order = $checkout->order();
} catch(\Exception $e) {
	$messages->error('No active order.');
	$app->redirect('/basket');
}

// Load order lines to calculate totals
$orders->lines()->on($order);

if ($request->method() == 'POST') {

	$formData = $request->toArray();

	// var_dump($formData);
	// die();

	if(
		isset($formData['address_line_1'])
		&& isset($formData['address_line_2'])
		&& isset($formData['address_line_city'])
		&& isset($formData['address_line_postcode'])
		&& !empty($formData['address_line_1'])
		&& !empty($formData['address_line_2'])
		&& !empty($formData['address_line_city'])
		&& !empty($formData['address_line_postcode'])
		&& !empty($formData['userEmail'])
	) {

		$postcode = strtoupper($formData['address_line_postcode']);

		// if(preg_match('/^[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][ABD-HJLNP-UW-Z]{2}$/', $postcode)) {
			$message = 'correct postcode format'; 

			if(isset($formData['deliveryAddressSame']) && $formData['deliveryAddressSame'] == 1){

				$sameLine1 = $formData['shipping_line_1'] = $formData['address_line_1'];
				$sameLine2 = $formData['shipping_line_2'] = $formData['address_line_2'];
				$sameLineCity = $formData['shipping_line_city'] = $formData['address_line_city'];
				$sameLinePostcode = $formData['shipping_line_postcode'] = strtoupper($formData['address_line_postcode']);

				// Create billing address
				$billing_address_values = [
					'address_line_1' => $formData['address_line_1'],
					'address_line_2' => $formData['address_line_2'],
					'town' => $formData['address_line_city'],
					'postcode' => strtoupper($formData['address_line_postcode']),
				];
				$billing_address = $addresses->post(null, null, $billing_address_values);

				// Because billing and shipping address are the same,
				// We an just copy the values across
				$shipping_address_values = $billing_address_values;
				$shipping_address = $addresses->post(null, null, $shipping_address_values);

				// We update the address ids in the order
				// With the addresses just created
				$order_values = [
					'shipping_address_id' => $shipping_address->id,
					'billing_address_id' => $billing_address->id,
					'email' => $formData['userEmail'],
					'name' => $formData['userName'],
					'order_status_id' => 4,
				];
				$orders->post(null, $order, $order_values);

				// redirect to checkout payment
				return $app->redirect('/checkout/payment');
			}

			// Create billing address
			$billing_address_values = [
				'address_line_1' => $formData['address_line_1'],
				'address_line_2' => $formData['address_line_2'],
				'town' => $formData['address_line_city'],
				'postcode' => strtoupper($formData['address_line_postcode']),
			];
			$billing_address = $addresses->post(null, null, $billing_address_values);


			// Create shipping address
			$shipping_address_values = [
				'address_line_1' => $formData['shipping_line_1'],
				'address_line_2' => $formData['shipping_line_2'],
				'town' => $formData['shipping_line_city'],
				'postcode' => strtoupper($formData['shipping_line_postcode']),
			];
			$shipping_address = $addresses->post(null, null, $shipping_address_values);

			// We update the address ids in the order
			// With the addresses just created
			$order_values = [
				'shipping_address_id' => $shipping_address->id,
				'billing_address_id' => $billing_address->id,
				'email' => $formData['userEmail'],
				'name' => $formData['userName'],
				'order_status_id' => 4,
			];
			$orders->post(null, $order, $order_values);

			// redirect to checkout payment
			return $app->redirect('/checkout/payment');
		// }

		$message = '<h2 class="alert alert-danger text-center"> There was an error with the address data submitted.</h2>';

		return $template('/checkout', array(
			'message' => $message,
		));
	}

	$message = '<h2 class="alert alert-danger text-center"> There was an error with the address data submitted!</h2>'; 

	return $template('/checkout', array(
		'message' => $message,
	));
}
