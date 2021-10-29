/* Product Variations */
var baseProduct = {};

function updateSelectedVariation(variation, id){
	if(typeof(baseProduct.price) == 'undefined'){
		if($('.add_to_cart_button').css('display') == "none"){
			var cartVisible = false;
		}
		else {
			var cartVisible = true;
		}
		baseProduct = {
			price:       $('.variation_product_price').html(),
			sku:         $('.variation_product_sku').html(),
			weight:      $('.variation_product_weight').html(),
			thumb:       $('.product-image img').attr('src'),
			thumbLink:   $('.product-image a').attr('href'),
			cartButton:  cartVisible
		};
	}

	// Show the defaults again
	if(typeof(variation) == 'undefined'){
		$('.variation_product_price').html(baseProduct.price);
		$('.variation_product_sku').html(baseProduct.sku);
		$('.variation_product_weight').html(baseProduct.weight);
		$('.cart_variation_id').val('');
		if(baseProduct.sku == ''){
			$('.product_sku').hide();
		}
		$('.InventoryLevel').hide();
	} else { // Othersie, showing a specific variation
		$('.variation_product_price').html(variation.price);
		if(variation.sku != ''){
			$('.variation_product_sku').html(variation.sku);
			$('.product_sku').show();
		} else {
			$('.variation_product_sku').html(baseProduct.sku);
			if(baseProduct.sku){
				$('.product_sku').show();
			} else {
				$('.product_sku').hide();
			}
		}
		$('.variation_product_weight').html(variation.weight);
		if(variation.image != ''){
			$('.product-image a').prop('href', variation.image);
			$('.product-image img').prop('src', variation.image);
            $('.selected_image').val(variation.image);
		}
		$('.cart_variation_id').val(id);
	}
}

$(document).ready(function(){
	$('form#product-form').submit(function(){
		var variationVal = $('.cart_variation_id').val();
		if(variationVal == '' && variationRequired == 1){
			alert({
	            title: 'ERROR',
	            text: 'You must choose from the available options before proceed.'
	        });
            return false;
		} else {
			$('#product-form').submit();
		}
	});
	// Select boxes are used if there is more than one variation type
	if($('.product_option_list select').length > 0){
		$('.product_option_list select').each(function(index){
			$(this).change(function(){
				if($(this).val()){
					var next = $('.product_option_list select').get(index+1);
					if(next){
						$('.product_option_list select').get(index+1).resetNext();
						$('.product_option_list select').get(index+1).fill();
						$('.product_option_list select').get(index+1).disabled = false;
					}
				} else {
					this.resetNext();
				}
				// Do we have a full match?
				ourCombination = this.getFullCombination();
				for(x in VariationList){
					variation = VariationList[x];
					if(variation.combination == ourCombination){
						updateSelectedVariation(variation, x);
						return;
					}
				}
				// No match or incomplete selection
				updateSelectedVariation();
			});
			this.getFullCombination = function(){
				var selected = new Array();
				$('.product_option_list select').each(function(){
					selected[selected.length] = $(this).val();
				});
				return selected.join(',');
			}
			this.getCombination = function(){
				var selected = new Array();
				var thisSelect = this;
				$('.product_option_list select').each(function(){
					if(thisSelect == this){
						return false;
					}
					selected[selected.length] = $(this).val();
				});
				// Add the current item
				selected[selected.length] = $(this).val();
				return selected.join(',');
			}
			this.resetNext = function(){
				$(this).nextAll().each(function(){
					this.selectedIndex = 0;
					this.disabled = true;
				});
			};
			this.fill = function(){
				// Remove everything but the first option
				$(this).find('option:gt(0)').remove();

				var show = true;
				var previousSelection;

				// Get the values of the previous selects
				var previous = $('.product_option_list select')[index-1];
				if(previous){
					previousSelection = previous.getCombination();
				}
				for(var i = 1; i < this.variationOptions.length; i++){
					for(x in VariationList){
						variation = VariationList[x];
						if(previousSelection){
							var show = false;
							if((variation.combination + ',').indexOf(previousSelection + ',' + this.variationOptions[i].value + ',') == 0){
								var show = true;
								break;
							}
							else {}
						}
					}
					if(show){
						this.options[this.options.length] = new Option(this.variationOptions[i].text, this.variationOptions[i].value);
					}
				}
			};
			// Steal the options and store them away for later
			variationOptions = new Array();
			$(this).find('option').each(function(){
				if(typeof(this.text) == undefined){
					this.text = this.innerHTML;
				}
				variationOptions[variationOptions.length] = {value: this.value, text: this.text };
			});
			this.variationOptions = variationOptions;
			this.selectedIndex = 0;
			if(index == 0){
				this.fill();
			} else {
				this.disabled = true;
			}
		});
	} else {	// Otherwise, radio buttons which are very easy to deal with
		$('.product_option_list input[type=radio]').click(function(){
			for(x in VariationList){
				variation = VariationList[x];
				if(variation.combination == $(this).val()){
					updateSelectedVariation(variation, x);
					return;
				}
			}
			// No match or incomplete selection
			updateSelectedVariation();
		});
		$('.product_option_list input[type=radio]:checked').trigger('click');
	}
});