<?

if($request->method() === 'POST'){
	
	$formData = $applicationControl->jsonValues();

	if (isset($formData['qty']) && $formData > 0 && is_numeric($formData['qty']) && isset($formData['order_line_id']) && !empty($formData['order_line_id'])){

		$currentOrderLine = $orders->module('lines')->find()->where('id' , $formData['order_line_id'])->first();

		if($formData['qty'] !== $currentOrderLine->quantity){

			$product = $products->find($currentOrderLine->target_id)->request('varients', true)->first();
			$basket->save($currentOrderLine, $product, $formData['qty'], NULL);
			echo json_encode(['success' => true]);
			exit;
			
		}
	}
}
