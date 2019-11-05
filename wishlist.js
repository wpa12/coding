var wishlist = document.querySelector('#addToWishlist');
var productEntity = document.querySelector('#productInternal');
var productNumber = productEntity.getAttribute('product-data');

wishlist.addEventListener('click', function(e) {
	var xhr = new XMLHttpRequest();

	xhr.open('POST', '/wishlist' , true);
	xhr.addEventListener('load', function(e){
		if(this.status == 200){
			console.log(this.responseText);
			wishlist.title.text = "Delete from your dashboard wishlist to re-enable."
			location.reload();
		}
	});
	xhr.send(JSON.stringify({number:productNumber, u : <?= $user->id; ?>,}));
});