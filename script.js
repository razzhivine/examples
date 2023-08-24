document.addEventListener("DOMContentLoaded", function(event) { 

	function getCookie(name) {
		let matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		));
		return matches ? decodeURIComponent(matches[1]) : undefined;
	}

	function setCookie(name, value, options = {}) {

		options = {
			path: '/',
			...options
		};
	  
		if (options.expires instanceof Date) {
		  	options.expires = options.expires.toUTCString();
		}
	  
		let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
	  
		for (let optionKey in options) {
			updatedCookie += "; " + optionKey;
			let optionValue = options[optionKey];
			if (optionValue !== true) {
				updatedCookie += "=" + optionValue;
			}
		}
		document.cookie = updatedCookie;
	}

	function cookieControl(cookie, element){
		let arCookie = getCookie(cookie);
		let res = true;
		if(!arCookie){
			setCookie(cookie, Array(element.dataset.id));
			counter.forEach(element => {
				element.innerText = '1';
			});
		}else{
			arCookie = arCookie.split(',');
			if(arCookie.includes(element.dataset.id)){
				arCookie.splice(arCookie.indexOf(element.dataset.id), 1);
				res = false;
			}else{
				arCookie.push(element.dataset.id)	
			}
			setCookie(cookie, arCookie);
			counter.forEach(element => {
				element.innerText = arCookie.length;
			});
		}
		return res;
	}

	function add2Cart(id, q){
		const request = new XMLHttpRequest();
		const url = "/_local/ajax/addToCart.php";
		const params = "PRODUCT_ID=" + id + "&QUANTITY= " + q;
		request.open("POST", url, true);
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.addEventListener("readystatechange", () => {
			if(request.readyState === 4 && request.status === 200) {       
				const field = document.querySelector('.cart-popup');
				const loadElement = '<div class="cart-popup__close"></div><div class="popup-loader"><img src="/bitrix/templates/sortmet/images/dest/preloader.svg"></div>';
				field.insertAdjacentHTML('beforeend', loadElement);
				BX.ajax({
				  url: '/bitrix/templates/sortmet/ajax/popup-ajax-cart.php',
				  method: 'POST',
				  dataType: 'html',
				  data: {},
				  start: true,
				  onsuccess: function(data){
					field.insertAdjacentHTML('beforeend', data);
					let overlay = document.getElementById('overlay');
					overlay.classList.add('overlay_z-index');
					fadeIn(overlay)
					fadeIn(document.querySelector('.cart-popup'), false, 1)
					document.querySelector('.popup-loader').remove();
					let closeBtn = document.querySelectorAll('.cart-popup__close');
					closeBtn.forEach(element => {
						element.addEventListener('click', ()=>{
							let overlay = document.querySelector('.overlay');
							overlay.classList.remove('overlay_z-index');
							fadeOut(overlay);
							fadeOut(element.parentNode);
							element.parentNode.innerText = '';
						})
					});
		
					let buyOneClick = document.querySelectorAll('.buy-one-click');
					buyOneClick.forEach(element => {
						element.addEventListener('click', ()=>{
							let cartPopup = document.querySelector('.cart-popup');
							fadeOut(cartPopup);
							cartPopup.innerText = '';
							const field = document.querySelector('.buy-one-click-popup');
							const loadElement = '<div class="buy-one-click-popup__close"></div><div class="popup-loader"><img src="/bitrix/templates/sortmet/images/dest/preloader.svg"></div>';
							field.insertAdjacentHTML('beforeend', loadElement);
							BX.ajax({
								url: '/bitrix/templates/sortmet/ajax/buy-one-click.php',
								method: 'GET',
								dataType: 'html',
								data: {},
								start: true,
								onsuccess: function(data){
									field.insertAdjacentHTML('beforeend', data);
									fadeIn(document.querySelector('.buy-one-click-popup'), false, 1)
									document.querySelector('.popup-loader').remove();
									let closeBtn = document.querySelectorAll('.buy-one-click-popup__close');
									closeBtn.forEach(element => {
										element.addEventListener('click', ()=>{
											let overlay = document.querySelector('.overlay');
											overlay.classList.remove('overlay_z-index');
											fadeOut(overlay);
											fadeOut(element.parentNode);
											element.parentNode.innerText = '';
										})
									});
								},
							});
						})
					});
		
				  },
				  onfailure: function(){  //действия в случаи ошибки
					if (document.querySelector('.popup-loader')) {
					  document.querySelector('.popup-loader').remove();
					}
					field.insertAdjacentHTML('beforeend', '<div class="popup__wrap popup__error"><div class="popup__title">Возникла ошибка!</div><div class="popup__description">Попробуйте перезагрузить страницу и попробовать снова.</div><button class="btn btn-big popup__btn" onClick="window.location.reload();">Перезагрузить</button></div>');
				  }
				});
			}
		});
		request.send(params);
	}

	function fadeOut(el) {
		el.style.opacity = 0.5;
		(function fade() {
			  if ((el.style.opacity -= .05) < 0) {
				el.style.display = "none";
			  } else {
				requestAnimationFrame(fade);
			  }
			}
		)();
	  };
	
	  function fadeIn(el, display, opacity = 0.5) {
		el.style.opacity = 0;
		el.style.display = display || "block";
		(function fade() {
			  var val = parseFloat(el.style.opacity);
			  if (!((val += .05) > opacity)) {
				el.style.opacity = val;
				requestAnimationFrame(fade);
			  }
			}
		)();
	  };

	// Добавление товара в избранное
	let favoriteElementControllers = document.querySelectorAll('.favorite-element-controller');
	
	favoriteElementControllers.forEach(element => {
		element.addEventListener('click', ()=>{
			let favoriteAlert = element.querySelector('.actions__item-alert')
			element.classList.toggle('-active');
			counter = document.querySelectorAll('.favorite-counter')

			if(cookieControl('BITRIX_SM_FAVORITE', element, counter)){
				favoriteAlert.classList.add('-active');
				setTimeout(() => {
					favoriteAlert.classList.remove('-active');
				}, 5000);
			}
			
		});
	});

	// Добавление товара в сравнение
	let compareElementControllers = document.querySelectorAll('.compare-element-controller');
	
	compareElementControllers.forEach(element => {
		element.addEventListener('click', ()=>{
			let compareAlert = element.querySelector('.actions__item-alert')
			element.classList.toggle('-active');
			counter = document.querySelectorAll('.compare-counter')

			if(cookieControl('BITRIX_SM_COMPARE', element, counter)){
				compareAlert.classList.add('-active');
				setTimeout(() => {
					compareAlert.classList.remove('-active');
				}, 5000);
			}

		});
	});
	  
	// Изменение количества товара 
	let quantityController = document.querySelectorAll('.quantity-controller');
	quantityController.forEach(element => {
		element.addEventListener('click', ()=>{
			let quantity = document.querySelector('.quantity__value');
			let quantitySpan = document.querySelector('.quantity__cur-value');
			quantityValue = quantity.dataset.range;
			ratio = document.querySelector('.unit__item_active')
			ratio = ratio.dataset.ratio

			if(element.dataset.type == 'plus'){
				val = Number(quantitySpan.value)*1000 + Number(ratio)*1000
				val = val / 1000;
				quantitySpan.value = val;
			}else{
				val = Number(quantitySpan.value)*1000 - Number(ratio)*1000
				val = val / 1000;
				quantitySpan.value = val;
			}
			

			if (Number(quantitySpan.value) <=0 ){
				quantitySpan.value = 1;
			}
		})
	});

	// добавление товара в корзину
	let cartElementController = document.querySelectorAll('.cart-element-controller');
	cartElementController.forEach(element => {
		element.addEventListener('click', ()=>{

			let q = Number(document.querySelector('.quantity__cur-value').value);
			add2Cart(element.dataset.id, q);
		});
	});
		
	// переключение торговых предложений
	if (document.querySelectorAll('.product-controller') !== null) {
		let productController = document.querySelectorAll('.product-controller');
		productController.forEach(element => {
			element.addEventListener('click', ()=>{
				let id = element.dataset.id;
				let measure = element.dataset.measure;
				let measureTitle = element.dataset.measureTitle;
				let curPrice = element.dataset.curPrice;
				let oldPrice = element.dataset.oldPrice;
				let discount = element.dataset.discount;

				if (document.querySelector(".cart-element-controller") !== null) {
					let cartBtn = document.querySelector('.cart-element-controller');
					cartBtn.dataset.id = id;
				}
				if (document.querySelector(".quantity__value") !== null) {
					let measureBlock = document.querySelector('.quantity__value');
					measureBlock.dataset.value = measure;
				}
				if (document.querySelector(".quantity__cur-value") !== null) {
					let measureBlockValue = document.querySelector('.quantity__cur-value');
					measureBlockValue.value = measure;
				}
				if (document.querySelector(".cur-measure") !== null) {
					let measureTitleBlock = document.querySelector('.cur-measure');
					measureTitleBlock.innerText = measureTitle;
				}
				if (document.querySelector(".cur-price") !== null) {
					let curPriceBlock = document.querySelector('.cur-price');
					curPriceBlock.innerText = curPrice;
				}
				if (document.querySelector(".old-price") !== null) {
					let oldPriceBlock = document.querySelector('.old-price');
					oldPriceBlock.innerText = oldPrice;
				}
				if (document.querySelector(".discount") !== null) {
					let discountBlock = document.querySelector('.discount');
					discountBlock.innerText = discount;
				}

				productController.forEach(p => {p.classList.remove('unit__item_active')});
				element.classList.add('unit__item_active')
			})
		});
	}

	// получение новых отзывов
	let getReviewsBtn = document.getElementById('get-reviews');
	getReviewsBtn.addEventListener('click', ()=>{
		let id = getReviewsBtn.dataset.id;
		let list = document.querySelector('.reviews-second__list');
		let items = document.querySelectorAll('.reviews-second__item');
		let itemsIds = [];
		items.forEach(item => {
			itemsIds.push(item.dataset.id);
		});

		const request = new XMLHttpRequest();
		const url = "/bitrix/templates/sortmet/ajax/get_product_reviews.php";
		const params = "ID=" + id + '&IDS=' + itemsIds;
		request.open("POST", url, true);
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.addEventListener("readystatechange", () => {
			if(request.readyState === 4 && request.status === 200) {     
				if(request.response.length >= 10){
					list.insertAdjacentHTML('beforeend', request.response);
				}else{
					getReviewsBtn.innerText = 'Больше нет отзывов';
					getReviewsBtn.classList.add('-empty');
				}
			}
		});
		request.send(params);
	})

});
