<?
 
// Ajax for adding items to basket


$response = array(
	'success' => false,
	'messages' => array()
);
$message = array();

if ($request->method() == 'POST'){
	$post_values = $applicationControl->jsonValues();

	if (isset($post_values['product_id']) && !empty($post_values['product_id']) && is_numeric($post_values['product_id']) ) {

		$productEntity = $products->find()->where('id', $post_values['product_id'])->request('variants', true)->first();

		if ($productEntity->isVariant() === true) {

			$productParentEntity = $products->find()->where(['id' => $productEntity->parent_id])->request('variants', true)->first();
		}
		else{
			$productParentEntity = $productEntity;
		}


		if (!empty($productParentEntity)) {
			if(isset($post_values['prodWidth']) && isset($post_values['prodLength'])) {
				$width = $post_values['prodWidth'];
				$length = $post_values['prodLength'];

				$meterSq = number_format((($width/1000) * ($length/1000)), 4);

				$price = $meterSq * $productEntity->calc_cost_multiplier;

				$calculated_cost = number_format($price, 2);
				
			} else {

				$width = null;
				$length = null;
				$calculated_cost = null;
			}
			$added_item = $basket->add($productEntity, $post_values['quantity'], null, $width, $length, $calculated_cost);


			if (!empty($added_item)){

				$message[] = 'Item added to basket';
				$response['success'] = true;
			}
			else{
				$message[] = 'Unable to add item to basket';
			}
		}
		else{
			$message[] = 'Parent product not found or not on sale';
		}
	}
	else{
		$message[] = 'Invalid form submission';
	}
}
else{
	$message[] = 'Invalid request';
}

$response['messages'] = $message;

return json_encode($response);
exit;
