var tjanalytics = {
	ga: {
		addProduct: function(data) {
			try {
				var dimension = '';
				/** global: productTypeDimensionId */
				if (typeof productTypeDimensionId !== "undefined") {
					dimension = 'dimension' + productTypeDimensionId;
				}

				if (data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						var product = data[i];
						var obj = {};
						obj['id'] = product.id;
						obj['name'] = product.title;
						obj['category'] = product.category;
						obj['brand'] = product.brand;
						obj['variant'] = product.subscription;
						obj['price'] = product.price;
						obj['quantity'] = product.quantity;

						if (typeof dimension !== "undefined") {
							obj[dimension] = product.productTypeDimensionValue;
						}

						ga('ec:addProduct', obj);
					}
				}
				else {
					throw 'Prodcut Id is empty';
				}
			}
			catch (err) {
				console.log(err);
			}
		},
		setAction: function(actionData) {
			try {
				if (actionData.step_number) {
					ga('ec:setAction', 'checkout', {
						'step': actionData.step_number,
						'option': actionData.option
					});
					ga('send', 'pageview');
				}
				else {
					throw 'Step is empty';
				}
			}
			catch (err) {
				console.log(err);
			}
		},

		setTransaction: function(data) {
			try {
				if (data.order_id) {
					ga('ec:setAction', 'purchase', {
						'id': data.order_id,
						'affiliation': '',
						'revenue': data.revenue,
						'tax': data.tax,
						'shipping': data.shipping,
						'coupon': data.coupon_code
					});
					ga('send', 'pageview'); /*Send transaction data with initial pageview.";*/
				}
				else {
					throw 'Order Id is empty';
				}
			}
			catch (err) {
				console.log(err);
			}
		}
	}
}
