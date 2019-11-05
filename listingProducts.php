<?

/// code used in a wider MVC controller to display products using that order by name and price, as well as filters such as colour and size. 

use core\data\SQL;
// $orderBy = array('price' => 'ASC');

$order_column = 'price';
$order_direction = 'asc';
	if(isset($request['orderBy'])) {
		if($request['orderBy'] === 'nameasc') {
			$order_column = 'name';
			$order_direction = 'asc';
		} elseif ($request['orderBy'] === 'namedesc'){
			$order_column = 'name';
			$order_direction = 'desc';
		} elseif ($request['orderBy'] === 'price'){
			$order_column = 'price';
			$order_direction = 'asc';
		}
		// elseif ($request['orderBy'] === 'size'){
		// 	$order_column = 'size';
		// 	$order_direction = 'asc';
		// }
	}

$productCollectionWhere = array();
$productAttributes = array();



$uri = '';

if(!empty($slug)){
	$uri .= $slug;

	if(!empty($subCat)){

		$uri .= '-'.$subCat;

		if(!empty($subCatTwo)){

			$uri .= '-'.$subCatTwo;
		}
	}
}


if($request->method() === "POST") {

	$_SESSION['product_filters'][$uri] = $request->toArray();	
	$app->redirect($app->requestUri());
}

$userValues = array();

if(isset($_SESSION['product_filters'][$uri]) && !empty($_SESSION['product_filters'][$uri])) {

	$userValues = $_SESSION['product_filters'][$uri];
	$category_ids = array();

	foreach ($userValues as $category_slug => $attribute_value_array) {
		foreach ($attribute_value_array as $key => $value_id) {
			$category_ids[] = $value_id;
		}
	}
}

if(isset($slug) && !empty($slug)){

	// Check parent exists first
	$parentCategoryEntity = $categories
		->find()
		->where(array('slug' => $slug))
		->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content', 'image', 'banner_image'))
		->limit(1)
		->first();

	if (isset($subCat) && !empty($subCat)) {

		$entity = $categories->find()->where('slug', $subCat)->limit(1)->first();

		// Check parent exists first
		$childCategoryEntity = $categories
			->find()
			->where(array(
				'slug' => $subCat,
				'parent' => $parentCategoryEntity->id
			))
			->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content', 'image', 'banner_image'))
			->limit(1)
			->first();

		if (isset($subCatTwo) && !empty($subCatTwo)) {

			$entity = $categories->find()->where('slug', $subCatTwo)->limit(1)->first();

			// Check parent exists first
			$childCategoryTwoEntity = $categories
				->find()
				->where(array(
					'slug' => $subCatTwo,
					'parent' => $childCategoryEntity->id
				))
				->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content' , 'image', 'banner_image'))
				->limit(1)
				->first();

				// Return the products for this subcat
				$productsToFetch = array();

				$pivotProducts = $childCategoryTwoEntity->join('products');

				foreach ($pivotProducts as $key => $pivotEntity) {

					$productsToFetch[] = $pivotEntity->source;
				}
					
				$allProductsToFetch = $productsToFetch;

				// Mutate the id array 
				if (isset($category_ids) && !empty($category_ids)) {

					$modelCollection = \website\data\ProductAttribute::find(
						array(
							SQL::condition(array(
								'target' => $category_ids,
								'source' => $productsToFetch,
							), 'AND')
						)
					);

					$prodAttibCollection = new \core\data\Collection($modelCollection);

					$productsToFetchFilters = array();

					$prodAttibCollection->filter(function($model) use (&$productsToFetchFilters) {


						if (in_array($model->source, $productsToFetchFilters) === false) {
							$productsToFetchFilters[] = $model->source;
						}

					});

					$productsToFetch = $productsToFetchFilters;
				}

				$productCollectionWhere['id'] = $productsToFetch;

				if(count($productsToFetch) == 0){
					$productCollection = new \core\data\Collection;
				}else {

					$productCollection = $products
						->find()
						// ->where($productCollectionWhere)->orderBy($orderBy, $range);
						->where($productCollectionWhere)
						->orderBy($order_column , $order_direction);
				}

				$filterArrayAssigned = array();

				if( count($allProductsToFetch) == 0) {
					$productFilterPivotCollection = new \core\data\Collection;
				} else {
					
					$productFilterPivotCollection = $product_attributes->find()->where(['source' => $allProductsToFetch]);
				}

				foreach ($productFilterPivotCollection as $key => $pivotEntity) {

					$variantAttribValueEntity = $pivotEntity->join('target');
					$variantAttribNameEntity = $variantAttribValueEntity->join('variation_attribute_name_id');

					if (!isset($filterArrayAssigned[$variantAttribNameEntity->id])) {

						$filterArrayAssigned[$variantAttribNameEntity->id] = array(
							'category_name' => $variantAttribNameEntity->name,
							'category_slug' => $variantAttribNameEntity->slug,
							'category_values' => array(
								"{$variantAttribValueEntity->id}" => array(
									'name' => $variantAttribValueEntity->name,
									'slug' => $variantAttribValueEntity->slug,
									'id' => $variantAttribValueEntity->id,
								)
							)
						);
					}
					else{
						$filterArrayAssigned[$variantAttribNameEntity->id]['category_values'] 
							= array_merge(
								$filterArrayAssigned[$variantAttribNameEntity->id]['category_values'], 
								array(
									"{$variantAttribValueEntity->id}" => array(
										'name' => $variantAttribValueEntity->name,
										'slug' => $variantAttribValueEntity->slug,
										'id' => $variantAttribValueEntity->id,
									)
								)
							);
					}
				}

				// unset($_SESSION['product_filters']);

				return $template('cats', array(
					'userValues' => $userValues,
					'productFilters' => $filterArrayAssigned,
					'productCollectionWhere' => $productCollectionWhere,
					'entity' => $childCategoryTwoEntity,
					'listings' => $productCollection->toArray(),
					'viewType' => 1,
					'sidebar' => $childCategoryTwoCollection,
					'parentCategoryEntity' => $parentCategoryEntity,
					'baseLinkURI' => '/products/',
					'baseSidebarURI' => '/categories/' . $parentCategoryEntity->slug . '/',
				));
		}

		if (!empty($parentCategoryEntity)) {

			if (!empty($childCategoryEntity)) {

				$childCategoryCollection = $categories
					->find()
					->where(array(
						'parent' => $entity->id
					))
					->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content',  'image', 'banner_image'));

				$childCategoryCollection->toArray();

				// check for child cat
				$checkChildCategoryCollection = $categories
					->find()
					->where(array(
						'parent' => $entity->id
					))
					->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content', 'image', 'banner_image'));

				if ($checkChildCategoryCollection->count() > 0 ){

					// Return the sub category
					$nextTierSubCategories = $checkChildCategoryCollection->toArray();

					return $template('cats', array(
							'entity' => $childCategoryEntity,
							'sidebar' => (!empty($childCategoryCollection)) ? $childCategoryCollection : null,
							'listings' => (!empty($nextTierSubCategories)) ? $nextTierSubCategories : null,
							'viewType' => 2,
							'parentCategoryEntity' => $parentCategoryEntity,
							'baseLinkURI' => '/categories/' . $parentCategoryEntity->slug . '/' . $childCategoryEntity->slug . '/',
							'baseSidebarURI' => '/categories/' . $parentCategoryEntity->slug . '/' . $childCategoryEntity->slug . '/',
					));
				}
				else{
					// Return the products for this subcat
					$productsToFetch = array();

					$pivotProducts = $childCategoryEntity->join('products');

					foreach ($pivotProducts as $key => $pivotEntity) {

						$productsToFetch[] = $pivotEntity->source;
					}
					
					$allProductsToFetch = $productsToFetch;

					// Mutate the id array 
					if (isset($category_ids) && !empty($category_ids)) {

						$modelCollection = \website\data\ProductAttribute::find(
							array(
								SQL::condition(array(
									'target' => $category_ids,
									'source' => $productsToFetch,
								), 'AND')
							)
						);

						$prodAttibCollection = new \core\data\Collection($modelCollection);

						$productsToFetchFilters = array();

						$prodAttibCollection->filter(function($model) use (&$productsToFetchFilters) {

							if (in_array($model->source, $productsToFetchFilters) === false) {
								$productsToFetchFilters[] = $model->source;
							}

						});

						$productsToFetch = $productsToFetchFilters;
					}

					$productCollectionWhere['id'] = $productsToFetch;

					if(count($productsToFetch) == 0){
						$productCollection = new \core\data\Collection;
					}else {

						$productCollection 
						= $products
							->find()
							->where($productCollectionWhere)
							->orderBy($order_column , $order_direction);
					}

					$filterArrayAssigned = array();

					if( count($allProductsToFetch) == 0) {
						$productFilterPivotCollection = new \core\data\Collection;
					} else {
						
						$productFilterPivotCollection = $product_attributes->find()->where(['source' => $allProductsToFetch]);
					}

					foreach ($productFilterPivotCollection as $key => $pivotEntity) {

						$variantAttribValueEntity = $pivotEntity->join('target');
						$variantAttribNameEntity = $variantAttribValueEntity->join('variation_attribute_name_id');

						if (!isset($filterArrayAssigned[$variantAttribNameEntity->id])) {

							$filterArrayAssigned[$variantAttribNameEntity->id] = array(
								'category_name' => $variantAttribNameEntity->name,
								'category_slug' => $variantAttribNameEntity->slug,
								'category_values' => array(
									"{$variantAttribValueEntity->id}" => array(
										'name' => $variantAttribValueEntity->name,
										'slug' => $variantAttribValueEntity->slug,
										'id' => $variantAttribValueEntity->id,
									)
								)
							);
						}
						else{
							$filterArrayAssigned[$variantAttribNameEntity->id]['category_values'] 
								= array_merge(
									$filterArrayAssigned[$variantAttribNameEntity->id]['category_values'], 
									array(
										"{$variantAttribValueEntity->id}" => array(
											'name' => $variantAttribValueEntity->name,
											'slug' => $variantAttribValueEntity->slug,
											'id' => $variantAttribValueEntity->id,
										)
									)
								);
						}
					}
				
					// unset($_SESSION['product_filters']);

					return $template('cats', array(
						'userValues' => $userValues,
						'productFilters' => $filterArrayAssigned,
						'entity' => $childCategoryEntity,
						'listings' => $productCollection->toArray(),
						'viewType' => 1,
						'sidebar' => $childCategoryCollection,
						'parentCategoryEntity' => $parentCategoryEntity,
						'baseLinkURI' => '/products/',
						'baseSidebarURI' => '/categories/' . $parentCategoryEntity->slug . '/',
					));
				}
			}
			else{
				echo $app->dispatch('404', array('slug' => $subCat));
			}
		}
		else{
			echo $app->dispatch('404', array('slug' => $subCat));
		}
	}
	else{
		// Return parent category
		$parentCategoryEntity = $categories
			->find()
			->where(array(
				'slug' => $slug,
				'parent' => 0
			))
			->select(array('id', 'slug', 'name', 'strapline', 'content', 'image', 'banner_image'))
			->limit(1)
			->first();

		if (!empty($parentCategoryEntity)) {

			// Gather all sub categories
			$childCategories = $categories
				->find()
				->where(array(
					'parent' => $parentCategoryEntity->id
				))
				->select(array('id', 'parent', 'slug', 'name', 'strapline', 'content', 'image', 'banner_image'));

			$childCategories = $childCategories->toArray();

			// Gather all categories
			$parentCategoryCollection = $categories
				->find()
				->where(array('parent' => 0));

			$parentCategoryCollection->toArray();

			return $template('cats', array(
					'entity' => $parentCategoryEntity,
					'sidebar' => (!empty($childCategories)) ? $childCategories : null,
					'listings' => (!empty($childCategories)) ? $childCategories : null,
					'viewType' => 0,
					'baseLinkURI' => '/categories/' . $parentCategoryEntity->slug . '/',
					'baseSidebarURI' => '/categories/' . $parentCategoryEntity->slug . '/',
			));
		}
		else{
			echo $app->dispatch('404', array('slug' => $slug));
		}
	}
} 
else {
	$categoryCollection = $categories->find()->select(array('id', 'slug', 'name', 'parent', 'strapline', 'content', 'image', 'banner_image'));

	return $template('cats-top', array(
		'entity' => $categoryCollection,
	));
} ?>