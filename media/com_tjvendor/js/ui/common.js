import { Common } from "../services/common";

/**
 * Common UI file
 */
export class CommonUI{
	/**
	 * Init commonUI constructor
	 *
	 */
	constructor() {
	}

	generateStates(country, isAdmin, state, city)
	{
		var countryId = jQuery("#" + country).val();

		if (countryId == 0) {
			jQuery("#jform_region").val('');
			jQuery("#jform_city").val('');

			var regionField = document.getElementById('jform_region');
			jQuery(regionField).trigger("liszt:updated");
			jQuery(regionField).trigger("chosen:updated");

			var cityField = document.getElementById('jform_city');
			jQuery(cityField).trigger("liszt:updated");
			jQuery(cityField).trigger("chosen:updated");

			return;
		}

		new Common(countryId).getRegions((err, resp) => {
			this.generateOptions(err, resp, state);
		});

		new Common(countryId).getCities((err, resp) => {
			this.generateCityOptions(err, resp, city);
		});
	}

	/**
	 * Generate region dropdown
	 *
	 * @param  err  object  Error if any
	 * @param  res  object  Regions list
	 *
	 * @return  void
	 */
	generateOptions(err, resp, selectedRegion)
	{
		try
		{
			let regionField = document.getElementById('jform_region');
			jQuery(regionField).empty();

			if (!err && resp){
				let regions = JSON.parse(resp);

				if(regions.success === true){
					regions.data.forEach(region => {
						let option = document.createElement("option");
						option.value = region.id;
						option.text = region.region;
						regionField.add(option);
					});

					// Set selected region
					if(selectedRegion) {
						regionField.value = selectedRegion;
					}

					jQuery(regionField).trigger("liszt:updated");
					jQuery(regionField).trigger("chosen:updated");
				}else{
					Joomla.renderMessages({
						'error': [Joomla.Text._('COM_TJVENDOR_VENDOR_FORM_AJAX_FAIL_ERROR_MESSAGE')]
					});
					jQuery("html, body").animate({
						scrollTop: 0
					}, "slow");
				}
			}
		}
		catch(err){
			Joomla.renderMessages({
				'error': [Joomla.Text._('COM_TJVENDOR_VENDOR_FORM_AJAX_FAIL_ERROR_MESSAGE')]
			});
			jQuery("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
	}

	/**
	 * Generate region dropdown
	 *
	 * @param  err  object  Error if any
	 * @param  res  object  Regions list
	 *
	 * @return  void
	 */
	generateCityOptions(err, resp, selectedCity){
		try{
			let cityField = document.getElementById('jform_city');
			jQuery(cityField).empty();

			if (!err && resp){
				let cities = JSON.parse(resp);

				if(cities.success === true){
					cities.data.forEach(city => {
						let option = document.createElement("option");
						option.value = city.id;
						option.text = city.city;
						cityField.add(option);
					});

					// Set selected region
					if(selectedCity) {
						cityField.value = selectedCity;
					}

					jQuery(cityField).trigger("liszt:updated");
					jQuery(cityField).trigger("chosen:updated");
				}else{
					Joomla.renderMessages({
						'error': [Joomla.Text._('COM_TJVENDOR_VENDOR_FORM_AJAX_FAIL_ERROR_MESSAGE')]
					});
					jQuery("html, body").animate({
						scrollTop: 0
					}, "slow");
				}
			}
		}
		catch(err){
			Joomla.renderMessages({
				'error': [Joomla.Text._('COM_TJVENDOR_VENDOR_FORM_AJAX_FAIL_ERROR_MESSAGE')]
			});
			jQuery("html, body").animate({
				scrollTop: 0
			}, "slow");
		}
	}

	showOtherCity(cityId, cityValue = ''){
		var city = jQuery('#' + cityId).val();

		if (cityValue){
			city = cityValue;
		}

		if (city == 'other'){
			jQuery('#jform_other_city').removeClass('hide');
			jQuery('#jform_other_city-lbl').removeClass('hide');
			jQuery('#jform_other_city-lbl').addClass('hasPopover');
			jQuery('#jform_other_city').addClass('show');
			jQuery('#jform_other_city-lbl').addClass('show');
		}else{
			jQuery("#jform_option_city").val('');
			jQuery('#jform_other_city').removeClass('show');
			jQuery('#jform_other_city-lbl').removeClass('show');
			jQuery('#jform_other_city').addClass('hide');
			jQuery('#jform_other_city-lbl').addClass('hide');
			jQuery('#jform_other_city-lbl').removeClass('hasPopover');
		}
	}
}
