<?
/**
 * REMOVE ITEM
 */

$response = array(
	'success' => false, 
	'messages' => array()
);

$message = array();

if($request->method() == 'POST') {

   $formData = $applicationControl->jsonValues();

    if (!empty($formData['order_line_id'])){

        $currentOrderLine = $orders->lines()->find()->where(array(
            'id' => $formData['order_line_id'],

        ))->first();

            $removedLine = $orders->lines()->find()->where(array(
            'id' => $formData['order_line_id'],
            ))->first()->delete();

        if (!empty($removedLine)){
            echo json_encode(['success' => true]);
            exit;
        }
        else{
            echo json_encode(['success' => false]);
            exit;
        }
    }
    else{
        echo json_encode(['success' => false]);
        exit;
    }
}
else{
    echo json_encode(['success' => false]);
    exit;
}
