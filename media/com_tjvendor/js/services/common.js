import { Base  } from "./base";

/**
 * Common service to get regions,city by country
 */
export class Common{
	/**
	 * Country Id
	 */
	countryId;

	/**
	 * Init Common constructor
	 *
	 * @param int  Country id
	 *
	 */
	constructor(countryId){
		this.countryId = countryId;
	}

	/**
	 * Get Country id
	 *
	 * @return  int  Country id
	 */
	getCountry(){
		return this.countryId;
	}

	/**
	 * Get Regions by country
	 *
	 * @param  cb  Callback function
	 *
	 * @return  void
	 */
	getRegions (cb){
		new Base(Joomla.getOptions('system.paths').base + '/index.php?option=com_tjvendors&task=vendor.getRegion&format=json&country=' + this.getCountry()).get(cb);
	}

	/**
	 * Get Cities by country
	 *
	 * @param  cb  Callback function
	 *
	 * @return  void
	 */
	getCities (cb){
		new Base(Joomla.getOptions('system.paths').base + '/index.php?option=com_tjvendors&task=vendor.getCity&format=json&country=' + this.getCountry()).get(cb);
	}
}
