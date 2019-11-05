	// Calculator 
	var width = document.querySelector('input[name=width]');
	var length = document.querySelector('input[name=length]');
	var totalArea = document.querySelector('.totalArea');
	var widthMax = document.querySelector('#calcWidth');
	var lengthMax = document.querySelector('#calcLength');
	var costMultiplier = document.querySelector('#calcCostMultiplier');
	var finalCost = document.querySelector('.finalCost');
	var finalCalculatedCost = document.querySelector('#finalCalcCost');

	var calc = function () {
		width.addEventListener('keyup', function(){

			var roundedInt = Number.parseFloat((this.value * length.value)/1000).toFixed(4);

			if (Number(width.value) > Number(widthMax.value)) {

				width.value = widthMax.value;

			} else if (Number(width.value) < 0){

				width.value = 0;
			}

			if (roundedInt < 0) {
				roundedInt = 0;
			}

			var areaMtrSqrWidth = (roundedInt / 1000);

			totalArea.textContent = Number.parseFloat(areaMtrSqrWidth).toFixed(4);

			areaMtrSqrWidth *= costMultiplier.value;
			areaMtrSqrWidth = Number.parseFloat(areaMtrSqrWidth).toFixed(2);
			finalCost.textContent = areaMtrSqrWidth;
			finalCalculatedCost.value = (finalCost.textContent/1.2);

		});

		length.addEventListener('keyup', function(){
			
			var roundedInt = Number.parseFloat((this.value * width.value)/1000).toFixed(4);
			
			if(Number(length.value) > Number(lengthMax.value)) {
			
				length.value = lengthMax.value;
			
			}else if (Number(length.value) < 0){
			
				length.value = 0;
			}
			
			if (roundedInt < 0) {
				roundedInt = 0;
			}

			var areaMtrSqrWidth = (roundedInt / 1000);

			totalArea.textContent = Number.parseFloat(areaMtrSqrWidth).toFixed(4);
			
			areaMtrSqrWidth *= costMultiplier.value;
			areaMtrSqrWidth = Number.parseFloat(areaMtrSqrWidth).toFixed(2);
			finalCost.textContent =  areaMtrSqrWidth;
			
			finalCalculatedCost.value = (finalCost.textContent/1.2);
			
		});

		console.log(finalCalculatedCost.value);
	}

	calc();