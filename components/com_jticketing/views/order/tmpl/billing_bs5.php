<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
/** @var $this JticketingViewOrder */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('jquery.token');
HTMLHelper::_('script', 'libraries/techjoomla/assets/js/tjvalidator.js');

$config              = JT::config();
$regexForAttendeeMob = $config->get('regexforAttendeeMob', '/^(\+\d{1,3}[- ]?)?\d{10}$/');
$document   = Factory::getDocument();
$document->addScriptDeclaration('var regexForAttendeeMob=new RegExp(' . $regexForAttendeeMob . ');');

?>

<!-- Start OF billing_info_tab-->
<div class="af-mt-10 tjBs5" id="jtwrap">
	<div class="row">
	<div class="col-sm-8 xs-p-0">
		<?php
		// Event Detail on every page
		echo $this->loadTemplate('event_info_' . JTICKETING_LOAD_BOOTSTRAP_VERSION);
		?>
		<div class="panel af-bg-white af-br-5 border-gray af-d-block d-sm-none">
			<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
		</div>
		<div id="billing-info" class="jticketing-checkout-steps">
			<form name="billing_info_form" action="" id="billing_info_form" class="form-validate af-mt-20">
				<div class="panel with-nav-tabs panel-default af-br-5">
					<div class="checkout-tab af-pb-0 af-br-t5">
						<ul class="af-mb-0 nav nav-tabs">
							<li class="nav-item">
								<a class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1default">
									<?php
									if(empty($this->userid))
									{
										echo Text::_('COM_JTICKETING_BILLING_GUEST_CHECKOUT');
									}
									else
									{
									?>
										<strong>
										<?php echo Text::_('COM_JTICKETING_BILLING_INFO');?>
										</strong>
									<?php
									}
									?>
								</a>
							</li>
							<?php if(empty($this->userid)):?>
								<li class="nav-item">
									<a class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2default">
								<?php echo Text::_('COM_JTICKETING_BILLING_CHECKOUT_MEMBER_LOGIN');?>
									</a>
								</li>
							<?php
							endif;
							?>
						</ul>
					</div>
					<div class="card">
					<div class="card-body">
						<div class="tab-content af-border-0 af-p-0">
							<div class="tab-pane fade show active" id="tab1default" role="tabpanel">
								<div id="<?php if (empty($this->userid)) echo 'billing_info_data';?>">
									<div class="af-p-10">
										<?php if (empty($this->userid)):
											if ($this->jtParams->get('allow_buy_guestreg'))
											{?>
												<div>
													<label for="register">
														<?php echo Text::_('COM_JTICKETING_USER_DONT_HAVE_AN_ACCOUNT');?>
													<b><?php echo Text::_('COM_JTICKETING_CHECKOUT_REGISTER');?></b>
														<input type="checkbox" name="account_jt" value="register" id="register" class="af-ml-5"/>
													</label>
												</div>
												<?php
											} ?>
												<h3 class=""><strong><?php echo Text::_('COM_JTICKETING_BILLING_INFO');?></strong></h3>
											<?php
										endif;
										?>
										<div>
											<div class="jticketing-checkout-content checkout-first-step-billing-info" id="billing-info-tab">
												<div class="row">
													<br>
													<div id="jt_billmail_msg_div">
														<span class="help-inline" id="billmail_msg"></span>
													</div>

													<label for="req" class="col-xs-12 af-font-600">
														<?php echo Text::_('COM_JTICKETING_BILLIN_REQ'); ?>
													</label>
													<br>
													<div class="col-xs-12 col-sm-6 af-mb-10">
														<label for="fname" class=" control-label"><?php
															echo Text::_('COM_JTICKETING_BILLIN_FNAM') . Text::_('COM_JTICKETING_STAR'); ?>
														</label>
															<input id="fname" class="input-medium bill form-control inputbox required validate-name" type="text" value="<?php
															$firstName = (isset($this->userbill->firstname)) ? $this->escape($this->userbill->firstname) : '';
															echo $this->escape($firstName);
															?>" maxlength="250" size="32" name="bill[fname]" title="<?php
															echo Text::_('COM_JTICKETING_BILLIN_FNAM_DESC');
															?>">
													</div> 
													<div class="col-xs-12 col-sm-6 af-mb-10">
														<label for="lname" class=" control-label"><?php
															echo Text::_('COM_JTICKETING_BILLIN_LNAM') . Text::_('COM_JTICKETING_STAR');
															?></label>
															<input id="lname" class="input-medium bill form-control inputbox required validate-name" type="text"
															value="<?php $lastName = (isset($this->userbill->lastname)) ? $this->escape($this->userbill->lastname) : ''; echo $this->escape($lastName); ?>" maxlength="250" size="32" name="bill[lname]" title="<?php echo Text::_('COM_JTICKETING_BILLIN_LNAM_DESC');?>">
													</div>
												</div>
												<div class="row">
													<div class="col-xs-12 col-sm-6 af-mb-10">
														<label for="email1" class="control-label"><?php
															echo Text::_('COM_JTICKETING_BILLIN_EMAIL') . Text::_('COM_JTICKETING_STAR'); ?>
														</label>

														<input id="email1" type="text"
															class="input-medium bill form-control inputbox required validate-email"
															data-user-id="<?php echo $this->userid;?>" type="text"
															value="<?php $billEmail = (isset($this->userbill->user_email)) ? $this->escape($this->userbill->user_email) : $this->escape(Factory::getUser()->email);
															echo "" . $this->escape($billEmail);
															?>"
															maxlength="250" size="32" name="bill[email1]"
															title="<?php echo Text::_('COM_JTICKETING_BILLIN_EMAIL_DESC');?>">
													</div>
													<div class="col-xs-12 col-sm-6 af-mb-10">
														<label for="country_mobile_code"  class="control-label"><?php
															echo Text::_('COM_JTICKETING_FORM_LBL_VENUE_COUNTRY');?>
														</label>
														<label for="phone"  class="  control-label"><?php
															echo Text::_('COM_JTICKETING_BILLIN_PHON') . Text::_('COM_JTICKETING_STAR');?>
														</label>
														<div class="row">
															<div class="country_mobile_code col-4">
																<select name="bill[country_mobile_code]"  id="country_mobile_code"  class="form-select af-mb-10 required" data-chosen="com_jticketing">
																	<option data-countryCode="GB" value="44" >UK (+44)</option>
																	<option data-countryCode="US" value="1">USA (+1)</option>
																	<option data-countryCode="DZ" value="213">Algeria (+213)</option>
																	<option data-countryCode="AD" value="376">Andorra (+376)</option>
																	<option data-countryCode="AO" value="244">Angola (+244)</option>
																	<option data-countryCode="AI" value="1264">Anguilla (+1264)</option>
																	<option data-countryCode="AG" value="1268">Antigua &amp; Barbuda (+1268)</option>
																	<option data-countryCode="AR" value="54">Argentina (+54)</option>
																	<option data-countryCode="AM" value="374">Armenia (+374)</option>
																	<option data-countryCode="AW" value="297">Aruba (+297)</option>
																	<option data-countryCode="AU" value="61">Australia (+61)</option>
																	<option data-countryCode="AT" value="43">Austria (+43)</option>
																	<option data-countryCode="AZ" value="994">Azerbaijan (+994)</option>
																	<option data-countryCode="BS" value="1242">Bahamas (+1242)</option>
																	<option data-countryCode="BH" value="973">Bahrain (+973)</option>
																	<option data-countryCode="BD" value="880">Bangladesh (+880)</option>
																	<option data-countryCode="BB" value="1246">Barbados (+1246)</option>
																	<option data-countryCode="BY" value="375">Belarus (+375)</option>
																	<option data-countryCode="BE" value="32">Belgium (+32)</option>
																	<option data-countryCode="BZ" value="501">Belize (+501)</option>
																	<option data-countryCode="BJ" value="229">Benin (+229)</option>
																	<option data-countryCode="BM" value="1441">Bermuda (+1441)</option>
																	<option data-countryCode="BT" value="975">Bhutan (+975)</option>
																	<option data-countryCode="BO" value="591">Bolivia (+591)</option>
																	<option data-countryCode="BA" value="387">Bosnia Herzegovina (+387)</option>
																	<option data-countryCode="BW" value="267">Botswana (+267)</option>
																	<option data-countryCode="BR" value="55">Brazil (+55)</option>
																	<option data-countryCode="BN" value="673">Brunei (+673)</option>
																	<option data-countryCode="BG" value="359">Bulgaria (+359)</option>
																	<option data-countryCode="BF" value="226">Burkina Faso (+226)</option>
																	<option data-countryCode="BI" value="257">Burundi (+257)</option>
																	<option data-countryCode="KH" value="855">Cambodia (+855)</option>
																	<option data-countryCode="CM" value="237">Cameroon (+237)</option>
																	<option data-countryCode="CA" value="1">Canada (+1)</option>
																	<option data-countryCode="CV" value="238">Cape Verde Islands (+238)</option>
																	<option data-countryCode="KY" value="1345">Cayman Islands (+1345)</option>
																	<option data-countryCode="CF" value="236">Central African Republic (+236)</option>
																	<option data-countryCode="CL" value="56">Chile (+56)</option>
																	<option data-countryCode="CN" value="86">China (+86)</option>
																	<option data-countryCode="CO" value="57">Colombia (+57)</option>
																	<option data-countryCode="KM" value="269">Comoros (+269)</option>
																	<option data-countryCode="CG" value="242">Congo (+242)</option>
																	<option data-countryCode="CK" value="682">Cook Islands (+682)</option>
																	<option data-countryCode="CR" value="506">Costa Rica (+506)</option>
																	<option data-countryCode="HR" value="385">Croatia (+385)</option>
																	<option data-countryCode="CU" value="53">Cuba (+53)</option>
																	<option data-countryCode="CY" value="90392">Cyprus North (+90392)</option>
																	<option data-countryCode="CY" value="357">Cyprus South (+357)</option>
																	<option data-countryCode="CZ" value="42">Czech Republic (+42)</option>
																	<option data-countryCode="DK" value="45">Denmark (+45)</option>
																	<option data-countryCode="DJ" value="253">Djibouti (+253)</option>
																	<option data-countryCode="DM" value="1809">Dominica (+1809)</option>
																	<option data-countryCode="DO" value="1809">Dominican Republic (+1809)</option>
																	<option data-countryCode="EC" value="593">Ecuador (+593)</option>
																	<option data-countryCode="EG" value="20">Egypt (+20)</option>
																	<option data-countryCode="SV" value="503">El Salvador (+503)</option>
																	<option data-countryCode="GQ" value="240">Equatorial Guinea (+240)</option>
																	<option data-countryCode="ER" value="291">Eritrea (+291)</option>
																	<option data-countryCode="EE" value="372">Estonia (+372)</option>
																	<option data-countryCode="ET" value="251">Ethiopia (+251)</option>
																	<option data-countryCode="FK" value="500">Falkland Islands (+500)</option>
																	<option data-countryCode="FO" value="298">Faroe Islands (+298)</option>
																	<option data-countryCode="FJ" value="679">Fiji (+679)</option>
																	<option data-countryCode="FI" value="358">Finland (+358)</option>
																	<option data-countryCode="FR" value="33">France (+33)</option>
																	<option data-countryCode="GF" value="594">French Guiana (+594)</option>
																	<option data-countryCode="PF" value="689">French Polynesia (+689)</option>
																	<option data-countryCode="GA" value="241">Gabon (+241)</option>
																	<option data-countryCode="GM" value="220">Gambia (+220)</option>
																	<option data-countryCode="GE" value="7880">Georgia (+7880)</option>
																	<option data-countryCode="DE" value="49">Germany (+49)</option>
																	<option data-countryCode="GH" value="233">Ghana (+233)</option>
																	<option data-countryCode="GI" value="350">Gibraltar (+350)</option>
																	<option data-countryCode="GR" value="30">Greece (+30)</option>
																	<option data-countryCode="GL" value="299">Greenland (+299)</option>
																	<option data-countryCode="GD" value="1473">Grenada (+1473)</option>
																	<option data-countryCode="GP" value="590">Guadeloupe (+590)</option>
																	<option data-countryCode="GU" value="671">Guam (+671)</option>
																	<option data-countryCode="GT" value="502">Guatemala (+502)</option>
																	<option data-countryCode="GN" value="224">Guinea (+224)</option>
																	<option data-countryCode="GW" value="245">Guinea - Bissau (+245)</option>
																	<option data-countryCode="GY" value="592">Guyana (+592)</option>
																	<option data-countryCode="HT" value="509">Haiti (+509)</option>
																	<option data-countryCode="HN" value="504">Honduras (+504)</option>
																	<option data-countryCode="HK" value="852">Hong Kong (+852)</option>
																	<option data-countryCode="HU" value="36">Hungary (+36)</option>
																	<option data-countryCode="IS" value="354">Iceland (+354)</option>
																	<option data-countryCode="IN" value="91">India (+91)</option>
																	<option data-countryCode="ID" value="62">Indonesia (+62)</option>
																	<option data-countryCode="IR" value="98">Iran (+98)</option>
																	<option data-countryCode="IQ" value="964">Iraq (+964)</option>
																	<option data-countryCode="IE" value="353">Ireland (+353)</option>
																	<option data-countryCode="IL" value="972">Israel (+972)</option>
																	<option data-countryCode="IT" value="39">Italy (+39)</option>
																	<option data-countryCode="JM" value="1876">Jamaica (+1876)</option>
																	<option data-countryCode="JP" value="81">Japan (+81)</option>
																	<option data-countryCode="JO" value="962">Jordan (+962)</option>
																	<option data-countryCode="KZ" value="7">Kazakhstan (+7)</option>
																	<option data-countryCode="KE" value="254">Kenya (+254)</option>
																	<option data-countryCode="KI" value="686">Kiribati (+686)</option>
																	<option data-countryCode="KP" value="850">Korea North (+850)</option>
																	<option data-countryCode="KR" value="82">Korea South (+82)</option>
																	<option data-countryCode="KW" value="965">Kuwait (+965)</option>
																	<option data-countryCode="KG" value="996">Kyrgyzstan (+996)</option>
																	<option data-countryCode="LA" value="856">Laos (+856)</option>
																	<option data-countryCode="LV" value="371">Latvia (+371)</option>
																	<option data-countryCode="LB" value="961">Lebanon (+961)</option>
																	<option data-countryCode="LS" value="266">Lesotho (+266)</option>
																	<option data-countryCode="LR" value="231">Liberia (+231)</option>
																	<option data-countryCode="LY" value="218">Libya (+218)</option>
																	<option data-countryCode="LI" value="417">Liechtenstein (+417)</option>
																	<option data-countryCode="LT" value="370">Lithuania (+370)</option>
																	<option data-countryCode="LU" value="352">Luxembourg (+352)</option>
																	<option data-countryCode="MO" value="853">Macao (+853)</option>
																	<option data-countryCode="MK" value="389">Macedonia (+389)</option>
																	<option data-countryCode="MG" value="261">Madagascar (+261)</option>
																	<option data-countryCode="MW" value="265">Malawi (+265)</option>
																	<option data-countryCode="MY" value="60">Malaysia (+60)</option>
																	<option data-countryCode="MV" value="960">Maldives (+960)</option>
																	<option data-countryCode="ML" value="223">Mali (+223)</option>
																	<option data-countryCode="MT" value="356">Malta (+356)</option>
																	<option data-countryCode="MH" value="692">Marshall Islands (+692)</option>
																	<option data-countryCode="MQ" value="596">Martinique (+596)</option>
																	<option data-countryCode="MR" value="222">Mauritania (+222)</option>
																	<option data-countryCode="YT" value="269">Mayotte (+269)</option>
																	<option data-countryCode="MX" value="52">Mexico (+52)</option>
																	<option data-countryCode="FM" value="691">Micronesia (+691)</option>
																	<option data-countryCode="MD" value="373">Moldova (+373)</option>
																	<option data-countryCode="MC" value="377">Monaco (+377)</option>
																	<option data-countryCode="MN" value="976">Mongolia (+976)</option>
																	<option data-countryCode="MS" value="1664">Montserrat (+1664)</option>
																	<option data-countryCode="MA" value="212">Morocco (+212)</option>
																	<option data-countryCode="MZ" value="258">Mozambique (+258)</option>
																	<option data-countryCode="MN" value="95">Myanmar (+95)</option>
																	<option data-countryCode="NA" value="264">Namibia (+264)</option>
																	<option data-countryCode="NR" value="674">Nauru (+674)</option>
																	<option data-countryCode="NP" value="977">Nepal (+977)</option>
																	<option data-countryCode="NL" value="31">Netherlands (+31)</option>
																	<option data-countryCode="NC" value="687">New Caledonia (+687)</option>
																	<option data-countryCode="NZ" value="64">New Zealand (+64)</option>
																	<option data-countryCode="NI" value="505">Nicaragua (+505)</option>
																	<option data-countryCode="NE" value="227">Niger (+227)</option>
																	<option data-countryCode="NG" value="234">Nigeria (+234)</option>
																	<option data-countryCode="NU" value="683">Niue (+683)</option>
																	<option data-countryCode="NF" value="672">Norfolk Islands (+672)</option>
																	<option data-countryCode="NP" value="670">Northern Marianas (+670)</option>
																	<option data-countryCode="NO" value="47">Norway (+47)</option>
																	<option data-countryCode="OM" value="968">Oman (+968)</option>
																	<option data-countryCode="PW" value="680">Palau (+680)</option>
																	<option data-countryCode="PA" value="507">Panama (+507)</option>
																	<option data-countryCode="PG" value="675">Papua New Guinea (+675)</option>
																	<option data-countryCode="PY" value="595">Paraguay (+595)</option>
																	<option data-countryCode="PE" value="51">Peru (+51)</option>
																	<option data-countryCode="PH" value="63">Philippines (+63)</option>
																	<option data-countryCode="PL" value="48">Poland (+48)</option>
																	<option data-countryCode="PT" value="351">Portugal (+351)</option>
																	<option data-countryCode="PR" value="1787">Puerto Rico (+1787)</option>
																	<option data-countryCode="QA" value="974">Qatar (+974)</option>
																	<option data-countryCode="RE" value="262">Reunion (+262)</option>
																	<option data-countryCode="RO" value="40">Romania (+40)</option>
																	<option data-countryCode="RU" value="7">Russia (+7)</option>
																	<option data-countryCode="RW" value="250">Rwanda (+250)</option>
																	<option data-countryCode="SM" value="378">San Marino (+378)</option>
																	<option data-countryCode="ST" value="239">Sao Tome &amp; Principe (+239)</option>
																	<option data-countryCode="SA" value="966">Saudi Arabia (+966)</option>
																	<option data-countryCode="SN" value="221">Senegal (+221)</option>
																	<option data-countryCode="CS" value="381">Serbia (+381)</option>
																	<option data-countryCode="SC" value="248">Seychelles (+248)</option>
																	<option data-countryCode="SL" value="232">Sierra Leone (+232)</option>
																	<option data-countryCode="SG" value="65">Singapore (+65)</option>
																	<option data-countryCode="SK" value="421">Slovak Republic (+421)</option>
																	<option data-countryCode="SI" value="386">Slovenia (+386)</option>
																	<option data-countryCode="SB" value="677">Solomon Islands (+677)</option>
																	<option data-countryCode="SO" value="252">Somalia (+252)</option>
																	<option data-countryCode="ZA" value="27">South Africa (+27)</option>
																	<option data-countryCode="ES" value="34">Spain (+34)</option>
																	<option data-countryCode="LK" value="94">Sri Lanka (+94)</option>
																	<option data-countryCode="SH" value="290">St. Helena (+290)</option>
																	<option data-countryCode="KN" value="1869">St. Kitts (+1869)</option>
																	<option data-countryCode="SC" value="1758">St. Lucia (+1758)</option>
																	<option data-countryCode="SD" value="249">Sudan (+249)</option>
																	<option data-countryCode="SR" value="597">Suriname (+597)</option>
																	<option data-countryCode="SZ" value="268">Swaziland (+268)</option>
																	<option data-countryCode="SE" value="46">Sweden (+46)</option>
																	<option data-countryCode="CH" value="41">Switzerland (+41)</option>
																	<option data-countryCode="SI" value="963">Syria (+963)</option>
																	<option data-countryCode="TW" value="886">Taiwan (+886)</option>
																	<option data-countryCode="TJ" value="7">Tajikstan (+7)</option>
																	<option data-countryCode="TH" value="66">Thailand (+66)</option>
																	<option data-countryCode="TG" value="228">Togo (+228)</option>
																	<option data-countryCode="TO" value="676">Tonga (+676)</option>
																	<option data-countryCode="TT" value="1868">Trinidad &amp; Tobago (+1868)</option>
																	<option data-countryCode="TN" value="216">Tunisia (+216)</option>
																	<option data-countryCode="TR" value="90">Turkey (+90)</option>
																	<option data-countryCode="TM" value="7">Turkmenistan (+7)</option>
																	<option data-countryCode="TM" value="993">Turkmenistan (+993)</option>
																	<option data-countryCode="TC" value="1649">Turks &amp; Caicos Islands (+1649)</option>
																	<option data-countryCode="TV" value="688">Tuvalu (+688)</option>
																	<option data-countryCode="UG" value="256">Uganda (+256)</option>
																	<option data-countryCode="UA" value="380">Ukraine (+380)</option>
																	<option data-countryCode="AE" value="971">United Arab Emirates (+971)</option>
																	<option data-countryCode="UY" value="598">Uruguay (+598)</option>
																	<option data-countryCode="UZ" value="7">Uzbekistan (+7)</option>
																	<option data-countryCode="VU" value="678">Vanuatu (+678)</option>
																	<option data-countryCode="VA" value="379">Vatican City (+379)</option>
																	<option data-countryCode="VE" value="58">Venezuela (+58)</option>
																	<option data-countryCode="VN" value="84">Vietnam (+84)</option>
																	<option data-countryCode="VG" value="84">Virgin Islands - British (+1284)</option>
																	<option data-countryCode="VI" value="84">Virgin Islands - US (+1340)</option>
																	<option data-countryCode="WF" value="681">Wallis &amp; Futuna (+681)</option>
																	<option data-countryCode="YE" value="969">Yemen (North)(+969)</option>
																	<option data-countryCode="YE" value="967">Yemen (South)(+967)</option>
																	<option data-countryCode="ZM" value="260">Zambia (+260)</option>
																	<option data-countryCode="ZW" value="263">Zimbabwe (+263)</option>
																</select>
															</div>
															<div class="col-8">
																<input type="hidden" class="regexForAttendeeMob" value="<?php echo $regexForAttendeeMob; ?>">
																<input id="phone" class="input-small bill form-control inputbox required validate-integer" type="text"
																onkeyup="jtSite.order.checkForAlpha(this,43);" maxlength="50"
																value="<?php echo "" . (isset($this->userbill->phone)) ? $this->escape($this->userbill->phone) : '';    ?>"
																size="32" name="bill[phone]"
																title="<?php echo Text::_('COM_JTICKETING_BILLIN_PHON_DESC');?>">
															</div>
														</div>
													</div>
												</div>

													<!-- Register as a business starts -->
													<?php
													if ($this->jtParams->get('enable_buy_as_business') == "1")
													{
														$status1=$status2='';

														if (isset($this->userbill->registration_type))
														{
															if ($this->userbill->registration_type)
																$status1=' btn-success active ';
															else
																$status2=' btn-success active ';
														}
														else
														{
															$status2=' btn-success active ';
														}
													?>
														<div class="col-xs-12 col-sm-12 af-mb-10">
															<label  class="control-label"><?php
																	echo Text::_('COM_JTICKETING_REGISTRATION_TYPE');
																	?>
															</label>
															<div class="btn-group radio af-pl-10">
															<input type="radio" value="1" class="hide">
															<label for="registration_type" id="registration_type1" onclick="jtSite.order.getRegistrationType('1')" class="btn <?php echo $status1; ?>">
																<?php echo  Text::_("JYES"); ?>
															</label>
															<input type="radio" value="0" class="hide">
															<label for="registration_type" id="registration_type0" onclick="jtSite.order.getRegistrationType('0')" class=" btn <?php echo $status2; ?>">
																<?php echo  Text::_("JNO"); ?>
															</label>
															</div>
															<input type="hidden" name="registration_type" value="<?php echo !empty($this->userbill->registration_type) ? $this->userbill->registration_type : ''; ?>">
														</div>

														<div class="col-xs-12 col-sm-6 af-mb-10 business_detail">
																<label for="business_name"  class="control-label"><?php
																	echo Text::_('COM_JTICKETING_BUSINESS_NAME');
																	?></label>
															<div>
																 <input id="business_name" class="input-small bill form-control inputbox validate-blankspace" type="text" value="<?php
																			$business_name = (isset($this->userbill->business_name)) ? $this->escape($this->userbill->business_name) : '';
																			$business_name = $this->escape($business_name);
																			echo "" . $business_name;
																		?>" size="32" name="bill[business_name]" title="<?php
																	echo Text::_('COM_JTICKETING_BUSINESS_NAME_DESC');
																		?>">
															</div>
														</div>

														<div class="col-xs-12 col-sm-6 af-mb-10 business_detail">
																<label for="vat_num"  class="control-label"><?php
																	echo Text::_('COM_JTICKETING_BILLIN_VAT_NUM');
																	?></label>
																<div class="">
																 <input id="vat_num" class="input-small bill form-control inputbox validate-blankspace validate-integer" type="text" value="<?php
																			$vat_number = (isset($this->userbill->vat_number)) ? $this->escape($this->userbill->vat_number) : '';
																			$vat_number = $this->escape($vat_number);
																			echo "" . $vat_number;
																		?>" size="32" name="bill[vat_num]" title="<?php
																	echo Text::_('COM_JTICKETING_BILLIN_VAT_NUM_DESC');
																		?>">
															</div>
														</div>
														<?php
													}
													?>
													<!-- Register as a business ends -->

													<?php
													if (isset($this->address_config) == 0)
													{
														$address = (isset($this->userbill->address)) ? $this->escape($this->userbill->address) : '';
														$address = $this->escape($address); ?>
														<div class="col-xs-12 col-sm-12 af-mb-10">
															<div class="">
																<label for="addr"  class="control-label"><?php
																	echo Text::_('COM_JTICKETING_BILLIN_ADDR') . Text::_('COM_JTICKETING_STAR');?>
																</label>
																<div class="">
																<textarea id="addr" class="required" name="bill[addr]" maxlength="250" rows="3" title="<?php echo Text::_('COM_JTICKETING_BILLIN_ADDR_DESC');?>"><?php echo $address;?></textarea>
																<p class="help-block"><?php
																 echo Text::_('COM_JTICKETING_BILLIN_ADDR_HELP');?> </p>
																</div>
															</div>
														</div>
														<?php
													} ?>
													<div class="row">
													<?php
													if (isset($this->country_config) == 0)
													{
														?>
														<div class="col-xs-12 col-sm-6 af-mb-10">
																<label for="country"  class="control-label"><?php
																	echo Text::_('COM_JTICKETING_BILLIN_COUNTRY') . Text::_('COM_JTICKETING_STAR');
																	?></label>
																	<?php
																		$country = $this->country;
																		$default = isset($this->userbill->country_code) ? $this->userbill->country_code : $this->defaultCountry;
																		$options = array();
																		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_BILLIN_SELECT_COUNTRY'));

																		foreach ($country as $value)
																		{
																			$options[] = HTMLHelper::_('select.option', $value['id'], $value['country']);
																		}

																		$tprice = 1;
																		echo $this->dropdown = HTMLHelper::_('select.genericlist', $options, 'bill[country]',
																					'class="chzn-done form-select jt_select bill"  required="required" aria-invalid="false"
																					onchange=\'jtSite.order.jticketingGenerateState("country","",' . $tprice . ', "")\' ',
																					'value', 'text', $default, 'country');
																	?>
																</div>
														<?php
													}

													if (isset($this->country_config) == 0 && isset($this->state_config) == 0)
													{
														?>
														<div class="col-xs-12 col-sm-6 af-mb-10">
																<label for="state" class="control-label"><?php
																	echo Text::_('COM_JTICKETING_BILLIN_STATE'); ?>
																</label>
																<div class="custom-select-height">
																	<?php
																	$options = array();
																	$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JTICKETING_BILLIN_SELECT_STATE'));
																	echo $this->dropdown = HTMLHelper::_('select.genericlist', $options,
																	'bill[state]',
																	'class="chzn-done form-select jt_select bill" aria-invalid="false" size="1"',
																	'value', 'text', '', 'state');
																	?>
																</div>
														</div>
														<?php
													} ?>
														<?php
														if (isset($this->city_config) == 0)
														{
															?>
															<div class="col-xs-12 col-sm-6 af-mb-10">
																	<label for="city" class="control-label"><?php
																		echo Text::_('COM_JTICKETING_BILLIN_CITY') . Text::_('COM_JTICKETING_STAR'); ?>
																	</label>
																		<input id="city" class="input-medium bill form-control inputbox required validate-name" type="text" onkeyup="jtSite.order.validateSpecialChar(this);" value="<?php
																			$city = (isset($this->userbill->city)) ? $this->escape($this->userbill->city) : '';
																			$city = $this->escape($city);
																			echo "" . $city;
																		?>" maxlength="250" size="32" name="bill[city]" title="<?php
																		echo Text::_('COM_JTICKETING_BILLIN_CITY_DESC');
																		?>">
															</div>
															<?php
														}
														if (isset($this->zip_config) == 0)
														{
														?>
														<div class="col-xs-12 col-sm-6 af-mb-10">
																<label for="zip"  class=" control-label"><?php
																	echo Text::_('COM_JTICKETING_BILLIN_ZIP') . Text::_('COM_JTICKETING_STAR'); ?>
																</label>
																	<input id="zip"  class="input-small bill form-control inputbox required " type="text" onkeyup="jtSite.order.validateSpecialChar(this);" value="<?php
																	$zipcode = (isset($this->userbill->zipcode)) ? $this->escape($this->userbill->zipcode) : '';
																	echo  "" . $this->escape($zipcode);
																	?>" onblur="" maxlength="20" size="32" name="bill[zip]" title="<?php
																	echo Text::_('COM_JTICKETING_BILLIN_ZIP_DESC');
																	?>">
														</div>
													<?php
														}
														?>
													<?php
													if (isset($this->customer_note_config) == 0)
													{
													?>
														<div class="col-xs-12 col-sm-6 af-mb-10">
																<label for="" class="control-label"><?php
																	echo Text::_('COM_JTICKETING_USER_COMMENT'); ?>
																</label>
																<textarea rows="1" class="form-control" maxlength="135" size="28" name="jt_comment"><?php echo (isset($this->userbill->comment)) ? $this->escape($this->userbill->comment) : '';?></textarea>
														</div>
													<?php
													} 
													?>
												</div>
												</div>
												</div>
											<!-- END OF row-->
											<?php 
											if (!empty($this->concent))
											{ ?>
												<div class="form-group">
													<div class="checkbox">
														<?php
															$link = Route::_(Uri::root() . "index.php?option=com_content&view=article&id=" . $this->orderArticle . "&tmpl=component");
															?>
														<label for="accept_privacy_term" class="d-flex>
															<input data-consent="<?php echo $this->concent;?>" class="jticketing_checkbox_style required" type="checkbox" name="accept_privacy_term" id="accept_privacy_term" size="30" />
															<?php
																$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
																$modalConfig['url'] = $link;
																$modalConfig['title'] = Text::_('TERMS_CONDITION');
																echo HTMLHelper::_('bootstrap.renderModal', 'accept_tnc', $modalConfig);
															?>
															<a data-bs-target="#accept_tnc" data-bs-toggle="modal" class="af-relative af-d-inline"> <?php echo Text::_('TERMS_CONDITION');?></a>
															<span class="star">&nbsp;*</span>
														</label>
													</div>
												</div>
										<?php } ?>
										<div class="clearfix"></div>
											<div class="text-center af-mt-20 generic-btn">
												<button id="billingCheckout" data-order-id="<?php echo $this->order->id;?>" class="billingCheckout btn btn-primary w-100" type="button">
													<?php echo Text::_('COM_JTICKETING_BILLING_SAVE_DETAILS'); ?>
												</button>
											</div>
										</div>
									</div>
								</div>
							<div class="tab-pane fade" id="tab2default" role="tabpanel">
							 <!--Start User Details Tab-->
							<?php
								if (!$this->userid)
								{
								?>
									<div class="user-info">
										<h3>
											<strong><?php echo Text::_('COM_JTICKETING_USER_INFO');?></strong>
										</h3>
										<div id="user-info" class="jticketing-checkout-steps">
											<div class="jticketing-checkout-content checkout-first-step-user-info" id="user-info-tab">
												<div id="login" class="col-sm-8">
													<p><?php echo Text::_('COM_JTICKETING_CHECKOUT_RETURNING_CUSTOMER_WELCOME');?></p>
													<div class="af-mb-10">
														<div>
															<label><?php echo Text::_('COM_JTICKETING_CHECKOUT_USERNAME'); ?></label>
														</div>
														<div>
															<input type="text" class="form-control" name="email" id="loginEmail" value="" />
														</div>
													</div>
													<div>
														<div>
															<label><?php echo Text::_('COM_JTICKETING_CHECKOUT_PASSWORD'); ?></label>
														</div>
														<div>
															<input type="password" class="form-control" id="loginPassword" name="password" value="" />
														</div>
													</div>
													<div class="text-end af-mb-20">
														<a href="<?php
														echo Route::_('index.php?option=com_users&view=reset', false);
														?>" target="_blank">
														<?php
														echo Text::_('COM_JTICKETING_FORGOT_YOUR_PASSWORD');
														?>
														</a>
													</div>
													<button type="button" id="button-login" class="orderLogin btn btn-primary af-mb-20 text-center" data-order-id="<?php echo $this->order->id; ?>">
													<?php echo Text::_('COM_JTICKETING_CHECKOUT_LOGIN'); ?>
													</button>
												</div>
												<div class="col-xs-12 float-start"> </div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								<!--End User Details Tab-->
							</div>
						</div>
					</div>
					</div>
				</div>
				</form>
			<!-- END OF Form-->
			</div>
		</div>
	<div class="col-sm-4 af-mb-20 d-none d-sm-block">
		<div class="af-bg-white af-br-5 border-gray">
			<?php echo $this->loadTemplate('cart_' . JTICKETING_LOAD_BOOTSTRAP_VERSION); ?>
		</div>
	</div>
	</div>
</div>
<!-- END OF billing_info_tab-->
<?php
$this->userbill = (isset($this->userbill) && !empty($this->userbill)) ? $this->userbill : new stdclass;
$this->userbill->state_code = (isset($this->userbill->state_code)) ? $this->userbill->state_code : "";
$this->userbill->registration_type = (isset($this->userbill->registration_type)) ? $this->userbill->registration_type : "";
$script = '
		jQuery(document).ready(function() {
			var DBuserbill="' . $this->userbill->state_code . '";
			jtSite.order.jticketingGenerateState("country",DBuserbill,"' . Text::_("ADS_BILLIN_SELECT_STATE") . '");
			var logged_in_userid="' . $this->userid . '";
			jQuery("#country_mobile_code").val("' . $this->defaultCountryMobileCode . '");
			jQuery(document).ready(function (){';

			if ($this->userbill->registration_type == "1")
			{
				$script .= '
				jQuery(".business_detail").show();
				jQuery("#business_name").addClass("required");';
			}
			else
			{
				$script .= '
				jQuery(".business_detail").hide();
				jQuery("#business_name").removeClass("required");';
			}

			$script .= '});';
		$script .= '});';

Factory::getDocument()->addScriptDeclaration($script);
